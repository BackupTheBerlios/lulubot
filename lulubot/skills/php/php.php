<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/php/php.php,v 1.2 2005/06/19 09:01:34 wolff_borg Exp $

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


class php extends skill {

	function php() { 
		$this->name = 'php';
		$this->description = "PhpFun gives syntax of php functions.";
		$this->actions[] = array(
			'function' => 'php_fun',
			'trigger'  => '^,php ',
			'name'     => 'PHP Functiones',
			'help'     => 'Type ,php <function_name> to see syntax for function <function_name>..'
		);
	}

// **************************************************************************


	function php_fun(&$irc,&$data) {
		$request = $data->messageex[1];
		$fp = fopen("skills/php/php.def", 'r');
		while (!feof($fp)) {
			$buf = fgets($fp, 10000);
			if (substr($buf,0,strpos($buf,'(')) == $request) {
				ereg("^(.+)\((.+)\) (.+)$", $buf, $regs);
				$this->talk($irc,$data,$regs[1]." (".$regs[2].") - ".trim($regs[3])." - http://php.net/".$regs[1]);
				return;
			}
		}
		$this->talk($irc,$data,'No PHP function named '.$request);
	}

}

?>
