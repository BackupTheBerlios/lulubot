<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/fortune/fortune.php,v 1.1 2004/07/05 18:48:31 mose Exp $

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


class fortune extends skill {

	function fortune() { 
		$this->name = 'fortune';
		$this->description = "Fortune cookie is the traditional wisdom random dispatcher.";
		$this->actions[] = array(
			'function' => 'tell_fortune',
			'trigger'  => '^,o$',
			'name'     => 'Fortune',
			'help'     => 'Type ,o to hear a random witty.'
		);
	}

// **************************************************************************

	function tell_fortune(&$irc,&$data) {
		$fortune = str_replace("\n"," ",`/usr/games/fortune`);
		$fortune = preg_replace("/\s+/"," ",$fortune);
		$this->talk(&$irc,&$data,$fortune);
		$this->log(&$irc,&$data,"asks for a fortune.");
	}

}

?>
