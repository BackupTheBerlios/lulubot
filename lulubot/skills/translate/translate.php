<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/skills/translate/translate.php,v 1.1 2004/07/05 18:48:43 mose Exp $

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


class translate extends skill {

	var $lang = array();

	function translate() { 
		$this->name = 'translate';
		$this->description = "Translates strings using babelfish.";
		$this->actions[] = array(
			'function' => 'trans',
			'trigger'  => '^,tr ',
			'name'     => 'Translate',
			'help'     => 'Type ,tr <lang_lang> <anything> to translate <anything> from lang to lang.'
		);
		$this->langs = array(
			'en_zh',
			'en_fr',
			'en_de',
			'en_it',
			'en_ja',
			'en_ko',
			'en_pt',
			'en_es',
			'zh_en',
			'fr_en',
			'fr_de',
			'de_en',
			'de_fr',
			'it_en',
			'ja_en',
			'ko_en',
			'pt_en',
			'ru_en',
			'es_en'
		);
	}

// **************************************************************************

	function trans(&$irc,&$data) {
		array_shift($data->messageex);
		$lang = array_shift($data->messageex);
		$param = implode(" ", $data->messageex);
		
		if(!function_exists('curl_init')) {
			$this->talk(&$irc,&$data,'TRANSLATE: Not fully enabled. See log for details.');
			$this->log(&$irc,&$data,"TRANSLATE: Requires curl support to work.");
			return;
		}
		
		if (!in_array($lang,$this->langs)) {
			$this->talk(&$irc,&$data,'TRANSLATE: language not available.');
			return;
		}
		if($param and $lang) {
			$buffer = '';
			$str = 'urltext='.urlencode($param).'&lp='.$lang.'&submit=Translate';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"http://babelfish.altavista.com/babelfish/tr");
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
			$start = strpos ($buffer, 'padding:10px')+14;
			$buffer = substr($buffer, $start);
			$end = strpos ($buffer, '</div>');
			$buffer = trim(substr($buffer, 0, $end));
			if (strstr($buffer,"The translation server is currently unavailable")) {
				$this->talk(&$irc,&$data,'Sorry, BabelFish is down.');
				$this->log(&$irc,&$data,"TRANSLATE: BabelFish unavalaible");
			} elseif(!empty($buffer)) {
				$this->log(&$irc,&$data,"TRANSLATE: Translating '$param' with '$lang'.");
				$this->talk(&$irc,&$data,'('.$lang.') '.$param." = ".$buffer);
			} else {
				$this->talk(&$irc,&$data,'There was an error translating your text.');
			}
		} else {
			$this->talk(&$irc,&$data,'TRANSLATE HELP: Usage: ,tr <language code> text.');
		}
	}

}

?>
