<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/faq/faq.php,v 1.2 2005/06/19 09:05:41 wolff_borg Exp $

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


class faq extends skill {

	var $score;
	
	function faq() { 
		$this->name = 'faq';
		$this->description = "FAQ is a simple thesaurus of keywords associated with definitions.";
		$this->actions[] = array(
			'function' => 'read_faq',
			'trigger'  => '^,f ',
			'name'     => 'FAQ',
			'help'     => 'Type ,f <keyword> to get the answer to that <keyword>.'
		);
		$this->actions[] = array(
			'function' => 'add_faq',
			'trigger'  => '^,f\+ ',
			'name'     => 'Add FAQ Entry',
			'help'     => 'Type ,f+ <keyword> <definition> to add an faq entry.'
		);
		$this->actions[] = array(
			'function' => 'drop_faq',
			'trigger'  => '^,f- ',
			'name'     => 'Drop FAQ Entry',
			'help'     => 'Type ,f- <keyword> to remove a faq entry.'
		);
	}

// **************************************************************************

	function read_faq(&$irc,&$data) {
		array_shift($data->messageex);
		$item = array_shift($data->messageex);
		$this->log($irc,$data,"asks for ".$item);
		$found = false;
		$faq = 'skills/faq/faq.'.$data->channel.'.txt';
		if ($data->channel && file_exists($faq)) {
			$fp = fopen($faq, 'r');
			if ($fp) {
				while (!feof($fp)) {
					$output = explode(":-:",fgets($fp,10000));
					if ($output[0] == $item) {
						$this->talk($irc,$data,$item.' = '.$output[1]);
						$this->log($irc,$data,"gets ".$item." = ".$output[1]);
						$found = true;
						break;
					}
				}
			}
			fclose($fp);
		}
		$faq = 'skills/faq/faq.txt';
		if (!$found && file_exists($faq)) {
			$fp = fopen($faq, 'r');
			if ($fp) {
				while (!feof($fp)) {
					$output = explode(":-:",fgets($fp,10000));
					if ($output[0] == $item) {
						$this->talk($irc,$data,$item.' = '.$output[1]);
						$this->log($irc,$data,"gets ".$item." = ".$output[1]);
						$found = true;
						break;
					}
				}
			}
			fclose($fp);
		}
		if (!$found) {
			$this->talk($irc,$data,'I dont know what that is.');
			$this->log($irc,$data,'not found '.$item.'.');
		}
	}

	function add_faq(&$irc,&$data) {
		if ($data->channel) {
			$faq = 'skills/faq/faq.'.$data->channel.'.txt';
		} else {
			$faq = 'skills/faq/faq.txt';
		}
		array_shift($data->messageex);
		$item = array_shift($data->messageex);
		$def  = implode(" ", $data->messageex);
		$fp = fopen($faq, 'a');
		fputs($fp,$item.":-:".$def."\n");
		fclose($fp);
		$this->talk($irc,$data,'okay I stored '.$item.'.');
		$this->log($irc,$data,'added in '.$faq.' : '.$item.' = '.$def);
	}


	function drop_faq(&$irc,&$data) {
		array_shift($data->messageex);
		$item = array_shift($data->messageex);
		if ($data->channel) {
			if (is_file("skills/faq/faq.".$data->channel.".txt")) {
				$file = file("skills/faq/faq.".$data->channel.".txt");
				$fp = fopen("skills/faq/faq.".$data->channel.".txt", 'w+');
				foreach($file as $line) {
					echo $line;
					if (substr($line,0,strlen($item)) != $item and trim($line)) fputs($fp, $line);
				}
				fclose($fp);
			}
		}
		$file = file("skills/faq/faq.txt");
		$fp = fopen("skills/faq/faq.txt", 'w+');
		foreach($file as $line) {
			if (substr($line,0,strlen($item)) != $item and trim($line)) fputs($fp, $line);
		}
		fclose($fp);
		$this->talk($irc,$data,'okay I removed '.$item.'.');
		$this->log($irc,$data,'removed '.$item.'.');
	}

}

?>
