<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/tikiwiki/tikiwiki.php,v 1.1 2004/07/05 18:48:32 mose Exp $

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

if (!defined('TIKIWIKI_NAME')) define('TIKIWIKI_NAME', 'TikiWiki');
if (!defined('TIKIWIKI_PATH')) define('TIKIWIKI_PATH', '/var/www/tikiwiki/');    // don't forget ending slash
if (!defined('TIKIWIKI_URL'))  define('TIKIWIKI_URL',  'http://localhost/tikiwiki/');  // don't forget ending slash


class tikiwiki extends skill {

	var $tikilib;
	var $statslib;

	function tikiwiki() { 
		$this->name = 'tikiwiki';
		$this->description = "Tikiwiki skills provide the bot the ability to communicate with local tikiwiki instance.";
		$this->actions[] = array(
			'function' => 'tiki_do',
			'trigger'  => '^,tw',
			'name'     => 'Tikiwiki',
			'help'     => 'Type ,t <command> <something> to execute a request. ,t help to have a list.'
		);
		chdir(TIKIWIKI_PATH);
		include_once("lib/init/initlib.php");
		include_once("tiki-setup_base.php");
		include_once("lib/stats/statslib.php");
		$this->tikilib =& $tikilib;
		$this->statslib =& $statslib;
		$this->userlib =& $userlib;
	}

// **************************************************************************


	function tiki_rpage($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,tw rpage] Returns a random wiki page url.";
		} else {
			global $tikilib;
			list($page) = $this->tikilib->get_random_pages("1");
			return "Want a page ? Try that one : ".TIKIWIKI_URL."$page !";
		}
	}

	function tiki_who($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,tw who] Returns who is connected on ".TIKIWIKI_NAME." right now.";
		} else {
			$users = $this->tikilib->get_online_users();
			foreach ($users as $u) {
				$all[] = $u['user'];
			}
			$count = count($users);
			if ($count == 0) {
				return "There is nobody known right now on ".TIKIWIKI_NAME;
			} elseif ($count == 1) {
				return "There is someone right now on ".TIKIWIKI_NAME." (".$all[0].")";			 
			} else {
				return "There is ".count($users)." known users right now on ".TIKIWIKI_NAME." (".implode(', ',$all).")";			 
			}
		}
	}
	
	function tiki_stats($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,tw stats] Returns statistics of usage of ".TIKIWIKI_NAME;
		} else {
			$i = $this->statslib->site_stats();
			return "Since ".date("Y-m-d",$i["started"])." (".$i["days"]." days) we got ".$i["pageviews"]." pages viewed on tw.o (".round($i["ppd"])." per day).";
		}
	}

	function tiki_dir($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,tw dir] Returns the last added directory site";
		} else {
			if (!isset($args[0]) or $args[0] < 0 or $args[0] > 20) { $args[0] = 0; }
			$dir = $this->tikilib->dir_list_all_valid_sites2($args[0], 1, 'created_desc', '');
			return "Directory ( ".$args[0]." )( ".$dir['data'][0]['name']." )( ".$dir['data'][0]['url']." )";
		}
	}

	function tiki_find($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,tw find] Finds a wiki page including string";
		} else {
			$page = array();
			if (is_int($args[0]) and $args[0] < 20) {
				$arg = array_shift($args);
			} else {
				$arg = 0;
			}
			$page = $this->tikilib->list_pages($arg, 1, 'lastModif_desc', implode(' ',$args));
			if ($page['cant'] > 0) {
				return "Match ".$args[0]." in (".TIKIWIKI_URL.$page['data'][0]['pageName'].")(#".$arg.")";
			} else {
				return "Sorry, no page name matches ".implode(' ',$args);
			}
		}
	}
	
	function tiki_help($args) {
		if (isset($args[0])) {
			$help = "tiki_".$args[0];
		}
		if (method_exists($this,$help)) {
			return $this->$help('help',1);
		} else {
			return "[,tw help] who stats rpage dir find";
		}
	}


// **************************************************************************

	function tiki_do(&$irc,&$data) {
		$method = "tiki_".$data->messageex[1];
		$args = array_slice($data->messageex,2);
		if (!method_exists($this,$method)) {
			$back = $this->tiki_help($args);
		} else {
			$back = $this->$method($args);
		}
		$this->talk(&$irc,&$data,$back);
		$this->log(&$irc,&$data,$back);
	}


}

?>
