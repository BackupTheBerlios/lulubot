<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/thesaurus/thesaurus.php,v 1.1 2004/07/05 18:48:43 mose Exp $

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


class thesaurus extends skill {

	function thesaurus() { 
		$this->name = 'thesaurus';
		$this->description = "Finds words definitions and synonyms from http://thesaurus.reference.com";
		$this->actions[] = array(
			'function' => 'thes',
			'trigger'  => '^,w ',
			'name'     => 'Thesaurus',
			'help'     => 'Type ,w <anything> to see the result for <anything> in thesaurus.'
		);
	}

// **************************************************************************

	function thes(&$irc,&$data) {
		$param = implode(" ", array_slice($data->messageex,1));
		$url = "http://thesaurus.reference.com/search?q=".urlencode($param);
		$this->thes_search(&$irc,&$data,$url,$param);
	}

	function thes_search(&$irc,&$data,$url,$param) {
		$buffer = "";
		if ($fp = fsockopen ("thesaurus.reference.com", 80, $errno, $errstr, 30)) {
			fputs ($fp, "GET $url HTTP/1.0\r\nHost: perdu.com\r\n\r\n");
			while (!feof($fp)) $buffer .= fgetss ($fp, 1024);
			fclose ($fp);
			$this->log(&$irc,&$data,"thes for ".$param);
		} else {
			$this->log(&$irc,&$data,"thes for ".$param." and socket failed : $errstr ($errno)");
		}
		
		$start = strpos ($buffer, 'Entry:') + 7;
		echo substr($buffer,0,255);
		$buffer = substr($buffer, $start);
		echo substr($buffer,0,255);
		$end = strpos ($buffer, 'Source:');
		$buffer = substr($buffer, 0, $end);
		echo substr($buffer,0,255);
		//$buffer = str_replace('</b>', '', $buffer);
		//$buffer = str_replace('<b>', '', $buffer);
		$buffer = str_replace("\n", ' ', $buffer);
		if ($buffer) {
			$this->talk(&$irc,&$data,$buffer);
		} else {
			$this->talk(&$irc,&$data,'Search string not found');
		}
	}

}

?>
