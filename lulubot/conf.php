<?php
/*$Header: /home/xubuntu/berlios_backup/github/tmp-cvs/lulubot/Repository/lulubot/conf.php,v 1.2 2005/06/19 09:18:15 wolff_borg Exp $

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


#  $conf['server'] = 'irc.freenode.net';

#  $conf['port']  = 6667;

#  $conf['nick'] = 'lulubot';

#  $conf['join'][] = '#lulubot';

#  $conf['join'][] = '#lulutest';

// Skills config
$conf['skills'][] = 'nickometer';

$conf['skills'][] = 'faq';

$conf['skills'][] = 'fortune';

$conf['skills'][] = 'google';

$conf['skills'][] = 'php';

$conf['skills'][] = 'translate';

$conf['skills'][] = 'thesaurus';

$conf['skills'][] = 'lulu';

#  $conf['skills'][] = 'tikiwiki';
#  define('TIKIWIKI_NAME', 'tw.o');
#  define('TIKIWIKI_PATH', '/var/www/tikiwiki/');    // don't forget ending slash
#  define('TIKIWIKI_URL',  'http://localhost/tikiwiki/');  // don't forget ending slash

#  $conf['skills'][] = 'tikipro';
#  define('TIKIPRO_NAME', 'tp.o');
#  define('TIKIPRO_PATH', '/var/www/tikipro/');    // don't forget ending slash
#  define('TIKIPRO_URL',  'http://localhost/tikipro/');  // don't forget ending slash

global $_SERVER;
// use this setting to set multi-domain tiki sites
$_SERVER["HTTP_HOST"] = "lulubot";
$_SERVER["SERVER_NAME"] = "lulubot";
$_SERVER["HTTP_USER_AGENT"] = "lulubot";
$_SERVER["REMOTE_ADDR"] = "";

?>
