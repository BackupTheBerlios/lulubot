<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/lulubotlib.php,v 1.2 2004/07/10 00:57:02 wolff_borg Exp $

  Copyright (c) 2004 mose & Lulu Enterprises, Inc.
  http://lulubot.berlios.de/

  This software is free software; you can redistribute it and/or
  modify it under the terms of the GNU Lesser General Public
  License as published by the Free Software Foundation; either
  version 2.1 of the License, or (at your option) any later version.

  This software is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public
  License along with this software; if not, write to 
  the Free Software Foundation, Inc., 
  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

define('LULUBOT_VERSION', '$Version$');

// **** change this ! ****

define('LULUBOT_CONF',         'conf.php' );
define('LULUBOT_LOGDIR',       'logs/' );
define('LULUBOT_MODULEDIR',    'skills/' );
define('LULUBOT_SQL_PARAMS',   'db.php' );
define('LULUBOT_DEBUG',         false );
define('LULUBOT_LOG',           true );

// **** end of changethis ****
error_reporting (E_ALL);

include('SmartIRC.php');

class lulubot {

	var $skills = array();
	var $conf = array();
	var $help = array();
	var $logpointer = array();
	var $chanusers = array();

	function lulubot(&$irc) {
		if (!is_dir(LULUBOT_LOGDIR)) {
			die('Bad log dir '.LULUBOT_LOGDIR);
		}
		if (!is_file(LULUBOT_CONF)) die('Bad conf file '.LULUBOT_CONF);
		$this->conf['server']   = 'irc.freenode.net';
		$this->conf['port']     = 6667;
		$this->conf['nick']     = 'lulubot'.substr(time(),-4,4);
		$this->conf['realname'] = 'LuluBot '.LULUBOT_VERSION.' from SmartIrc '.SMARTIRC_VERSION;
		$this->conf['username'] = 'LuluBot';
		$this->conf['usermode'] = '0';
		$this->conf['join'][]   = '#lulubot';
		$this->conf['skills']   = array();
		$this->run(&$irc);
	}
	
	function run(&$irc) {
		// default settings
		require_once(LULUBOT_CONF);
		foreach ($conf as $k=>$v) {
			$this->conf["$k"] = $v;
		}
		
		// smartirc setup
		$irc->setDebug(SMARTIRC_DEBUG_NOTICE);
		$irc->setLogfile(LULUBOT_LOGDIR.'debug.log');
		$irc->setLogdestination(SMARTIRC_FILE);
		$irc->setUseSockets(true);
		$irc->setChannelSyncing(true);
		$irc->setAutoRetry(true);
		$irc->setAutoReconnect(true);
		$irc->setReceiveTimeout(6000);
		$irc->setTransmitTimeout(6000);
		$irc->setCtcpVersion('LuluBot version '.LULUBOT_VERSION);
		$irc->setSendDelay(500);
		
		// go go go
		foreach ($this->conf['skills'] as $m) {
			if (is_file(LULUBOT_MODULEDIR.$m.'/'.$m.'.php')) {
				include_once(LULUBOT_MODULEDIR.$m.'/'.$m.'.php');
				$irc->log(SMARTIRC_DEBUG_MODULES,"DEBUG_MODULES: Acquiring *$m*");
				$this->skills[$m] = &new $m;
				$this->skills[$m]->learn(&$irc);
			} else {
				$irc->log(SMARTIRC_DEBUG_MODULES,"DEBUG_MODULES: module *$m* not found");
			}
		}
		$irc->connect($this->conf['server'],$this->conf['port']);
		$irc->login($this->conf['nick'], $this->conf['realname'], $this->conf['usermode'], $this->conf['username']);
		$irc->join($this->conf['join']);
		if (LULUBOT_LOG) {
			$this->startlog(&$irc);
		}
		$irc->listen();
		$irc->disconnect();
		if (LULUBOT_LOG) {
			$this->stoplog();
		}
	}

// *************************************************************
	
	function startlog(&$irc) {
		$irc->registerActionhandler(
			SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_NOTICE|SMARTIRC_TYPE_JOIN|
			SMARTIRC_TYPE_ACTION|SMARTIRC_TYPE_TOPICCHANGE|SMARTIRC_TYPE_NICKCHANGE|
			SMARTIRC_TYPE_QUIT|SMARTIRC_TYPE_PART,'.*',&$this,'log2file');
		foreach ($this->conf['join'] as $where) {
			$this->logpointer["$where"] = fopen(LULUBOT_LOGDIR.$where.'.log','a');
			$this->chanusers["$where"] = array();
		}
	}

	function stoplog() {
		foreach ($this->conf['join'] as $where) {
			fclose($this->logpointer["$where"]);
		}
	}

	function log2file(&$irc,&$data) {
		$now = date("[d-m-Y/H:i]");
		$reload_users = false;
		switch ($data->type) {
			case SMARTIRC_TYPE_CHANNEL:
				$line = '<'.$data->nick.'> '.$data->message;
				break;
			case SMARTIRC_TYPE_ACTION:
				$line = '* '.$data->nick.substr($data->message,7);
				break;
			case SMARTIRC_TYPE_NICKCHANGE:
				$line = '*** '.$data->nick.' is now known as '.$data->message;
				break;
			case SMARTIRC_TYPE_TOPICCHANGE:
				$line = '*** '.$data->nick.' changed topic to '.$data->message;
				break;
			case SMARTIRC_TYPE_JOIN:
				$line = '*** Joined '.$data->nick.' ('.$data->ident.'@'.$data->host.')';
				break;
			case SMARTIRC_TYPE_PART:
				$line = '*** Parted '.$data->nick.' ('.$data->ident.'@'.$data->host.')';
				break;
			case SMARTIRC_TYPE_QUIT:
				$line = '*** '.$data->nick.' signoff: '.$data->message;
				break;
			default:
				$line = $data->rawmessage;
		}
		if ($data->channel and isset($this->logpointer[$data->channel])) {
			fwrite($this->logpointer[$data->channel],"$now $line\n");
			$this->chanusers[$data->channel] = array_keys($irc->channel[$data->channel]->users);
		} else {
			$done = false;
			foreach ($this->conf['join'] as $where) {
				if (count($this->chanusers[$where]) < 2) {
					$this->chanusers[$where] = array_keys($irc->channel[$where]->users);
				}
				if (in_array($data->nick,$this->chanusers["$where"])) {
					fwrite($this->logpointer[$where],"$now $line\n");
					$done = true;
				}
				$this->chanusers[$where] = array_keys($irc->channel[$where]->users);
			}
			if (!$done) {
				echo "$now $line\n";
			}
		}
	}
	
}

class skill {
	var $name;
	var $description;
	var $actions = array();
	var $times = array();

	function skill() { }

	function learn(&$irc) {
		foreach ($this->actions as $i=>$a) {
			$this->actions[$i]['id'] = $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY,$a['trigger'],&$this,$a['function']);
		}
		$irc->log(SMARTIRC_DEBUG_MODULES, 'DEBUG_MODULES: *** init '.$this->name);
  } 
  
	function forget(&$irc) {
		foreach ($this->actions as $x) { $irc->unregisterActionid($x['id']); }
		foreach ($this->times as $x)   { $irc->unregisterTimeid($x['id']); } 
		$irc->log(SMARTIRC_DEBUG_MODULES, 'DEBUG_MODULES: *** close '.$this->name);
  } 

	function talk(&$irc,&$data,$message) {
		if ($data->channel) {
			$target = $data->channel;
			$where = SMARTIRC_TYPE_CHANNEL;
			$prefix = $data->nick.': ';
		} else {
			$target = $data->nick;
			$where = SMARTIRC_TYPE_QUERY;
			$prefix = '';
		}
		$irc->message($where,$target,$prefix.$message);
	}

	function log(&$irc,&$data,$message) {
		if ($data->channel) {
			$where = 'on '.$data->channel;
		} else {
			$where = 'in private';
		}
		$irc->log(SMARTIRC_DEBUG_MODULES,'DEBUG_MODULES: '.$this->name.' ('.$data->nick.' '.$where.') '.$message);
	}

	function unhtmlentities($string) {
		$trans_tbl = array_flip(get_html_translation_table (HTML_ENTITIES));
		return strtr($string, $trans_tbl);
	}

}
?>
