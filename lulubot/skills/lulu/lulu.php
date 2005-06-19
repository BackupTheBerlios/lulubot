<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/lulu/lulu.php,v 1.2 2005/06/19 09:02:23 wolff_borg Exp $

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


class lulu extends skill {

	var $lang = array();

	function lulu() { 
		$this->name = 'lulu';
		$this->description = "Search the Lulu knowledgebase";
		$this->actions[] = array(
			'function' => 'lulu_kdb',
			'trigger'  => '^,l ',
			'name'     => 'Lulu',
			'help'     => 'Type ,l <keywords> to search the lulu knowledgebase.'
		);
	}

// **************************************************************************

	function lulu_kdb(&$irc,&$data) {
		array_shift($data->messageex);
		$param = implode(" ", $data->messageex);
		if(!function_exists('curl_init')) {
			$this->talk($irc,$data,'Not fully enabled. See log for details.');
			$this->log($irc,$data,"Requires curl support to work.");
			return;
		}
		$buffer = '';
		$str = 'keys='.urlencode($param);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://www.lulu.com/help/search");
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
		ob_start();
		curl_exec ($ch);
		$buffer = ob_get_contents();
		ob_end_clean();
		curl_close ($ch);
		$start = strpos ($buffer, 'www.lulu.com/help/node/view/')+28;
		$buffer = substr($buffer, $start);
		$end = strpos ($buffer, '"');
		$node = trim(substr($buffer, 0, $end));
		$buffer = substr($buffer,$end + 2);
		$end = strpos ($buffer, '<');
		$title = substr($buffer,0,$end);
		echo "$node : $title\n";
		if(!empty($buffer)) {
			$this->log($irc,$data,"$param found '$title' (http://lulu.com/help/node/view/$node)");
			$this->talk($irc,$data,"$param found '$title' (http://lulu.com/help/node/view/$node)");
		} else {
			$this->talk($irc,$data,'Sorry no result.');
		}
	}

}

?>
