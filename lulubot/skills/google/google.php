<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/google/google.php,v 1.1 2004/07/05 18:48:42 mose Exp $

  Copyright (c) 2004 mose & Lulu Enterprises, Inc.
  http://forge.tikipro.org/projects/lulubot/

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


class google extends skill {

	function google() { 
		$this->name = 'google';
		$this->description = "Google is the wellknown search engine at http://google.com";
		$this->actions[] = array(
			'function' => 'google_en',
			'trigger'  => '^,g ',
			'name'     => 'Google',
			'help'     => 'Type ,g <anything> to see the result for <anything> in google.'
		);
	}

// **************************************************************************

	function google_en(&$irc,&$data) {
		$param = implode(" ", array_slice($data->messageex,1));
		$url = "http://www.google.com/search?hl=en&lr=lang_en&q=".urlencode($param);
		$this->google_search(&$irc,&$data,$url,$param);
	}

	function google_search(&$irc,&$data,$url,$param) {
		$buffer = "";
		if ($fp = fsockopen ("www.google.com", 80, $errno, $errstr, 30)) {
			fputs ($fp, "GET $url HTTP/1.0\r\nHost: perdu.com\r\n\r\n");
			while (!feof($fp)) $buffer .= fgets ($fp, 1024);
			fclose ($fp);
			$this->log(&$irc,&$data,"googles for ".$param);
		} else {
			$this->log(&$irc,&$data,"googles for ".$param." and socket failed : $errstr ($errno)");
		}
		$start = strpos ($buffer, 'class=g')+16;
		$buffer = substr($buffer, $start);
		$end = strpos ($buffer, '</a>');
		$buffer = substr($buffer, 0, $end);
		$buffer = str_replace('</b>', '', $buffer);
		$buffer = str_replace('<b>', '', $buffer);
		$buffer = str_replace('&#39;', "'", $buffer);
		$results = explode('>',$buffer);
		if ($results[1] != '<head') {
			$this->talk(&$irc,&$data,$results[1].' ('.$results[0].')');
		} else {
			$this->talk(&$irc,&$data,'Search string not found');
		}
	}

}

?>
