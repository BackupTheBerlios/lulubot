<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/nickometer/nickometer.php,v 1.1 2004/07/05 18:48:31 mose Exp $

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


class nickometer extends skill {

	var $score;
	
	function nickometer() { 
		$this->name = 'nickometer';
		$this->description = "Nickometer is an historical tool that give out the lame percent for a given nick.";
		$this->actions[] = array(
			'function' => 'nicko',
			'trigger'  => '^,n ',
			'name'     => 'Nickometer',
			'help'     => 'Type ,n <nickname> to evaluate lame factor for <nickname>.'
		);
	}

// **************************************************************************

	function nicko(&$irc,&$data) {
		$args = array_slice($data->messageex,1);
		$nick = $args[0];
		if (strlen($nick) > 0) {
			$this->score = $shifts = 0;
			$special_cost = array('69'		=> 500,
					'dea?th'		=> 500,
					'dark'		=> 400,
					'n[i1]ght'		=> 300,
					'n[i1]te'		=> 500,
					'fuck'		=> 500,
					'sh[i1]t'		=> 500,
					'coo[l1]'		=> 500,
					'kew[l1]'		=> 500,
					'lame'		=> 500,
					'dood'		=> 500,
					'dude'		=> 500,
					'[l1](oo?|u)[sz]er'	=> 500,
					'[l1]eet'		=> 500,
					'e[l1]ite'		=> 500,
					'[l1]ord'		=> 500,
					'pron'		=> 1000,
					'warez'		=> 1000,
					'xx'		=> 100,
					'\[rkx]0'		=> 1000,
					'\0[rkx]'		=> 1000
					);
			foreach ($special_cost as $special => $cost) {
				$special_pattern = $special;
				if (ereg($special, $nick)) $this->punish($cost, "special");
			}
			$clean = eregi_replace("[^A-Z0-9]", "", $nick);
			$this->punish(pow(10, (strlen($nick) - strlen($clean)))-1, "non-alha ($clean vs $nick)");
			$k3wlt0k_weights = array(5, 5, 2, 5, 2, 3, 1, 2, 2, 2);
			for ($digit = 0; $digit < 10; $digit++) {
				$this->punish($k3wlt0k_weights[$digit] * substr_count($nick, $digit), "leet digits");
			}
			$this->punish($this->slow_pow(9, similar_text($nick, strtoupper($nick)))-1, "lowercase");
			if (ereg("^.*[XZ]$", $nick)) $this->punish(50, "lame endings");
			if (eregi("[0-9][a-z]", $nick, $regs)) {
				$shifts = @sizeof($regs) - 1;
				unset($regs);
			}
			if (eregi("[a-z][0-9]", $nick, $regs)) {
				$shifts = @sizeof($regs) - 1;
				unset($regs);
			}
			$this->punish($this->slow_pow(9, $shifts) - 1, "shifts");
			if (ereg("[A-Z]", $nick, $regs)) {
				$caps = @sizeof($regs) - 1;
				unset($regs);
				$this->punish($this->slow_pow(7, $caps), "upper case");
			}
			$percentage = 100 * (1 + tanh(($this->score-400)/400)) * (1 - 1/(1+$this->score/5)) / 2;
			$digits = 2 * (2 - floor(log(100 - $percentage) / log(10)));
			$out = "'$nick' is ".sprintf ("%.".$digits."f", $percentage)."% lame";
		} else {
			$out = "'".$data['nick']."' is 100% lame";
		}
		$this->talk(&$irc,&$data,$out);
		$this->log(&$irc,&$data,$out);
	}

// **************************************************************************

	function slow_pow ($x, $y) {
		return pow($x, $this->slow_exponent($y));
	}
	
	function slow_exponent ($x) {
		return 1.3 * $x * (1 - atan($x/6) *2/pi());
	}
	
	function punish ($x, $y) {
		$this->score += $x;
	}

}

?>
