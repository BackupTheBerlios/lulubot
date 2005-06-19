<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/tikipro/tikipro.php,v 1.3 2005/06/19 09:08:22 wolff_borg Exp $

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

if (!defined('TIKIPRO_NAME')) define('TIKIPRO_NAME', 'Tikipro');
if (!defined('TIKIPRO_PATH')) define('TIKIPRO_PATH', '/var/www/tp/');    // don't forget ending slash
if (!defined('TIKIPRO_URL'))  define('TIKIPRO_URL',  'http://localhost/tp/');  // don't forget ending slash


class tikipro extends skill {

	var $gTikiSystem;
	var $statslib;

	function tikipro() { 
		$this->name = 'tikipro';
		$this->description = "Tikipro skills provide the bot the ability to communicate with local tikipro instance.";
		$this->actions[] = array(
			'function' => 'tiki_do',
			'trigger'  => '^,tp ',
			'name'     => 'Tikipro',
			'help'     => 'Type ,tp <command> <something> to execute a request. ,t help to have a list.'
		);
		$oldpath = getcwd();
		chdir(TIKIPRO_PATH);
		include_once("tiki_setup_inc.php");
		include_once( STATS_PKG_PATH."stats_lib.php" );
		include_once( USERS_PKG_PATH."TikiUser.php" );
		include_once( WIKI_PKG_PATH."TikiPage.php" );
		include_once( WIKI_PKG_PATH."wiki_lib.php" );
		chdir($oldpath);
		$this->gTikiSystem =& $gTikiSystem;
		$this->gTikiUser =& $gTikiUser;
		$this->statslib =& $statslib;
		$this->wikilib =& $wikilib;
	}

// **************************************************************************


	function tiki_rpage($args) {
		if (isset($args[0]) && ($args[0] == 'help' || $args[0] == '?')) {
			return "[,tp rpage] Returns a random wiki page url.";
		} else {
			$page = array_pop($this->wikilib->get_random_pages("1"));
			$page = htmlspecialchars($page);
			/* Change the whitespace into _*/
			$page = preg_replace("/ /", "+", $page);
			return "Want a page ? Try that one : ".TIKIPRO_URL.WIKI_PKG_URL."index.php?page=$page !";
		}
	}

	function tiki_who($args) {
		if (isset($args[0]) && ($args[0] == 'help' || $args[0] == '?')) {
			return "[,tp who] Returns who is connected on ".TIKIPRO_NAME." right now.";
		} else {
			global $gTikiUser;
			$users = $gTikiUser->get_online_users();
			foreach ($users as $u) {
				$all[] = $u['user'];
			}
			$count = count($users);
			if ($count == 0) {
				return "There is nobody known right now on ".TIKIPRO_NAME;
			} elseif ($count == 1) {
				return "There is someone right now on ".TIKIPRO_NAME." (".$all[0].")";			 
			} else {
				return "There is ".count($users)." known users right now on ".TIKIPRO_NAME." (".implode(', ',$all).")";			 
			}
		}
	}
	
	function tiki_stats($args) {
		if ((isset($args[0]) && ($args[0] == 'help' || $args[0] == '?'))) {
			return "[,tp stats] Returns statistics of usage of ".TIKIPRO_NAME;
		} else {
			$i = $this->statslib->site_stats();
			return "Since ".date("Y-m-d",$i["started"])." (".$i["days"]." days) we got ".$i["pageviews"]." pages viewed on ".TIKIPRO_NAME." (".round($i["ppd"])." per day).";
		}
	}

	function tiki_dir($args) {
		if (isset($args[0]) && ($args[0] == 'help' || $args[0] == '?')) {
			return "[,tp dir] Returns the last added directory site";
		} else {
			if (!isset($args[0]) or $args[0] < 0 or $args[0] > 20) { $args[0] = 0; }
			$dir = $this->gTikiSystem->dir_list_all_valid_sites2($args[0], 1, 'created_desc', '');
			return "Directory ( ".$args[0]." )( ".$dir['data'][0]['name']." )( ".$dir['data'][0]['url']." )";
		}
	}

	function tiki_find($args) {
		if (!isset($args[0]) || (isset($args[0]) && ($args[0] == 'help' || $args[0] == '?'))) {
			return "[,tp find] Finds a wiki page including string";
		} else {
			$page = array();
			if (is_int($args[0]) and $args[0] < 20) {
				$arg = array_shift($args);
			} else {
				$arg = 0;
			}
			$content = new TikiPage();
			$page = $content->getList($arg, 1, 'last_modified_desc', implode(' ',$args));

			if ($page['cant'] > 0) {
				return "Match ".$args[0]." in (".TIKIPRO_URL.$page['data'][0]['display_link'].")(#".$arg.")";
			} else {
				return "Sorry, no page name matches ".implode(' ',$args);
			}
		}
	}
	
	function tiki_help($args) {
		if (isset($args[0]) && ($args[0] == 'help' || $args[0] == '?')) {
			$help = "tiki_".$args[0];
		}
		if (isset($help) && method_exists($this,$help)) {
			return $this->$help('help',1);
		} else {
			return "[,tp help] who stats rpage dir find";
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
		$this->talk($irc,$data,$back);
		$this->log($irc,$data,$back);
	}


}

?>
