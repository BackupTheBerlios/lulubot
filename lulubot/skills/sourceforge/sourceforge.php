<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/sourceforge/sourceforge.php,v 1.1 2004/07/18 10:46:18 wolff_borg Exp $

  Copyright (c) 2004 Stephan Borg
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

require_once('lib/pear/HTTP/Request.php');

if (!defined('SOURCEFORGE_NAME')) define('SOURCEFORGE_NAME', 'SourceForge.net');
if (!defined('SOURCEFORGE_PROJECT')) define('SOURCEFORGE_PROJECT', 'tikipro');    // don't forget ending slash
if (!defined('SOURCEFORGE_PROJECT_URL'))  define('SOURCEFORGE_PROJECT_URL',  'http://sourceforge.net/projects/');
if (!defined('SOURCEFORGE_TRACKER_URL'))  define('SOURCEFORGE_TRACKER_URL',  'http://sourceforge.net/support/tracker.php?aid=');

class sourceforge extends skill {

	var $hrefOpts = '&set=custom&_assigned_to=0&_category=100&_group=100&order=artifact_id&sort=DESC&_status=';

	var $statusOpt = array('any'=>100, 'open'=>1, 'closed'=>2, 'deleted'=>3, 'pending'=>4);

	var $bugLink = '/"([^"]+)">Bugs/';

	function sourceforge() { 
		$this->name = 'sourceforge';
		$this->description = "Accesses Sourceforge.net for various things.";
		$this->actions[] = array(
			'function' => 'sf_do',
			'trigger'  => '^,sf ',
			'name'     => 'SourceForge.net',
			'help'     => 'Type ,sf <command> <something> to execute a request. ,sf help to have a list.'
		);
	}

// **************************************************************************

	function getTrackerURL($project, $regex, $status) {
		$req = new HTTP_Request(SOURCEFORGE_PROJECT_URL.$project);
		if (PEAR::isError($oError=$req->sendRequest())) {
			return "An error occurred retrieving URL";
		}
		$data = $req->getResponseBody();
		$matches = array();
		preg_match($regex, $data, $matches);
		$url = (function_exists('html_entity_decode')) ? html_entity_decode($matches[1]) : str_replace(array("&gt;", "&lt;", "&quot;", "&amp;"), array(">", "<", "\"", "&"), $matches[1]);
		return "http://sourceforge.net".$url.$this->hrefOpts.$this->statusOpt[$status];
	}

	function getTrackerList($url) {
		$req = new HTTP_Request($url);
		if (PEAR::isError($oError=$req->sendRequest())) {
			return "An error occurred retrieving URL";
		}
		$data = $req->getResponseBody();
		$head = '#%s: %s';
		
	}

	function formatResp($text, $num = '') {
//		if ($num) {
			
	}

	function sf_bugs($args) {
		$project = SOURCEFORGE_PROJECT;
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,sf bugs [any,open,closed,deleted,pending] [<project>]] Returns a list of the most recent bugs filed against <project>. <project> is not needed if there is a default project set. Search defaults to open bugs.";
		} else {
			$status = 'open';
		}
		if (isset($args[1])) {
			$project = $args[1];
		}
		return $this->getTrackerURL($project, $this->bugLink, $status);
	}

	function sf_who($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,sf who] Returns who is connected on ".SOURCEFORGE_NAME." right now.";
		} else {
			$users = $this->userlib->get_online_users();
			foreach ($users as $u) {
				$all[] = $u['user'];
			}
			$count = count($users);
			if ($count == 0) {
				return "There is nobody known right now on ".SOURCEFORGE_NAME;
			} elseif ($count == 1) {
				return "There is someone right now on ".SOURCEFORGE_NAME." (".$all[0].")";			 
			} else {
				return "There is ".count($users)." known users right now on ".SOURCEFORGE_NAME." (".implode(', ',$all).")";			 
			}
		}
	}
	
	function sf_stats($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,sf stats] Returns statistics of usage of ".SOURCEFORGE_NAME;
		} else {
			$i = $this->statslib->site_stats();
			return "Since ".date("Y-m-d",$i["started"])." (".$i["days"]." days) we got ".$i["pageviews"]." pages viewed on ".SOURCEFORGE_NAME." (".round($i["ppd"])." per day).";
		}
	}

	function sf_dir($args) {
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,sf dir] Returns the last added directory site";
		} else {
			if (!isset($args[0]) or $args[0] < 0 or $args[0] > 20) { $args[0] = 0; }
			$dir = $this->gTikiSystem->dir_list_all_valid_sites2($args[0], 1, 'created_desc', '');
			return "Directory ( ".$args[0]." )( ".$dir['data'][0]['name']." )( ".$dir['data'][0]['url']." )";
		}
	}

	function sf_find($args) {
		return "This is a test!";
		if (isset($args[0]) and $args[0] == 'help') {
			return "[,sf find] Finds a wiki page including string";
		} else {
			$page = array();
			if (is_int($args[0]) and $args[0] < 20) {
				$arg = array_shift($args);
			} else {
				$arg = 0;
			}
			$page = $this->gTikiSystem->list_pages($arg, 1, 'lastModif_desc', implode(' ',$args));
			if ($page['cant'] > 0) {
				return "Match ".$args[0]." in (".SOURCEFORGE_URL."wiki/index.php?page=".$page['data'][0]['pageName'].")(#".$arg.")";
			} else {
				return "Sorry, no page name matches ".implode(' ',$args);
			}
		}
	}
	
	function sf_help($args) {
		if (isset($args[0])) {
			$help = "sf_".$args[0];
		}
		if (isset($help) and method_exists($this,$help)) {
			return $this->$help('help',1);
		} else {
			return "[,sf help] bugs rfes sf totalbugs totalrfes tracker";
		}
	}


// **************************************************************************

	function sf_do(&$irc,&$data) {
		$method = "sf_".$data->messageex[1];
		$args = array_slice($data->messageex,2);
		if (!method_exists($this,$method)) {
			$back = $this->sf_help($args);
		} else {
			$back = $this->$method($args);
		}
		$this->talk(&$irc,&$data,$back);
		$this->log(&$irc,&$data,$back);
	}


}

?>
