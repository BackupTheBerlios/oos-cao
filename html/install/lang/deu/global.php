<?php
/* ----------------------------------------------------------------------
   $Id: global.php,v 1.1 2005/01/10 10:41:16 r23 Exp $
   ----------------------------------------------------------------------
   Based on:
   
   File: global.php,v 1.17.2.1 2002/04/03 21:03:19 jgm 
   ----------------------------------------------------------------------
   POST-NUKE Content Management System
   Copyright (C) 2001 by the Post-Nuke Development Team.
   http://www.postnuke.com/
   ----------------------------------------------------------------------
   Original Author of file: Gregor J. Rothfuss
   Purpose of file: Installer language defines.
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

if (strstr($_ENV["OS"],"Win")) {
  @setlocale(LC_TIME, 'ge'); 
} else {
  @setlocale(LC_TIME, 'de_DE'); 
}

define('DATE_FORMAT_LONG', '%A, %d. %B %Y'); // this is used for strftime()
define('DATE_TIME_FORMAT', DATE_FORMAT_LONG . ' %H:%M:%S');

define('HTML_PARAMS','dir="LTR" lang="de"');
define('CHARSET', 'iso-8859-1');
define('INSTALLATION', 'OOS [OSIS Online Shop] Installation');

define('BTN_CONTINUE', 'Weiter');
define('BTN_NEXT' ,'Weiter');
define('BTN_RECHECK', 'wiederholen');
define('BTN_SUBMIT','best&auml;tigen');
define('BTN_CHANGE_INFO', 'Info &auml;ndern');
define('BTN_LOGIN_SUBMIT','Admin installieren');
define('BTN_SET_LOGIN', 'Weiter');
define('BTN_FINISH', 'Beenden');

define('GREAT', 'Willkommen bei OOS [OSIS Online Shop]!');
define('GREAT_1', 'Der OOS [OSIS Online Shop] ist eine umfassende Internet-Shopping-L&ouml;sung. Diese besticht durch ein besonders hohes Ma&szlig; an Anpassungsf&auml;higkeit, Schnelligkeit  und hohe Performance. Die OSIS Online-Shop Standard Software ist mit allen Grundfunktionen  f&uuml;r Online- Verkauf, Bestellung, Bezahlung, Statistik und Administration  ausgestattet. Die Wartung der Produktdatenbank kann jederzeit online vorgenommen  werden. So ist gew&auml;hrleistet, dass den Kunden stets das aktuellste Online-Angebot  pr&auml;sentiert wird.');
define('SELECT_LANGUAGE_1', 'Auswahl Ihrer Sprache.');
define('SELECT_LANGUAGE_2', 'Sprachen: ');

define('DEFAULT_1', 'GNU/GPL License:');
define('DEFAULT_2', 'OOS [OSIS Online Shop] ist freie Software.');
define('DEFAULT_3', 'Ich akzeptiere die GPL License');


define('PHP_CHECK_1', 'PHP Diagnose');
define('PHP_CHECK_2', 'Hier pr&uuml;fen wir die Konfigurationseinstellungen Ihrer PHP Installation. <a href=\'phpinfo.php\' target=\'_blank\'>PHP Info</a>');
define('PHP_CHECK_3', 'Ihre PHP Version ist ');
define('PHP_CHECK_4', 'Bitte installieren Sie eine aktuelle PHP Version - <a href=\'http://www.php.net\' target=\'_blank\'>http://www.php.net</a>');
define('PHP_CHECK_OK', 'Es sind uns keine Probleme mit Ihrer PHP Version in Verbindung mit OOS [OSIS Online Shop] bekannt.');
define('PHP_CHECK_6', 'magic_quotes_gpc is Off.');
define('PHP_CHECK_7', 'Tragen Sie in Ihre .htaccess Datei folgende Zeile ein:<br />php_flag magic_quotes_gpc On');
define('PHP_CHECK_8', 'magic_quotes_gpc is ON.');
define('PHP_CHECK_9', 'magic_quotes_runtime is On.');
define('PHP_CHECK_10', 'Tragen Sie in Ihre .htaccess Datei folgende Zeile ein:<br />php_flag magic_quotes_runtime Off');
define('PHP_CHECK_11', 'magic_quotes_runtime is Off.');
define('PHP_CHECK_12', 'keine Grafik-Funktionen'); 
define('PHP_CHECK_13', 'F&uuml;r die Grafik-Funktionen ben&ouml;tigen Sie die GD-Bibliothek gd-lib (empfohlen version 2.0 oder h&ouml;her) <br />verf&uuml;gbar unter - <a href=\'http://www.boutell.com/gd/\' target=\'_blank\'>http://www.boutell.com/gd/</a>');
define('PHP_CHECK_14', 'keine truecolor Grafik-Funktionen'); 
define('PHP_CHECK_15', 'F&uuml;r die Grafik-Funktionen im OOS [OSIS Online Shop] empfehlen wir Ihnen die <br />GD-Bibliothek gd-lib Version 2.0 oder h&ouml;her - <a href=\'http://www.boutell.com/gd/\' target=\'_blank\'>http://www.boutell.com/gd/</a>');
define('PHP_CHECK_16', 'PHP_SELF');
define('PHP_CHECK_17', 'Der Dateiname des gerade ausgef&uuml;hrten Skripts, relativ zum Wurzel-Verzeichnis des Dokuments ist nicht verf&uuml;gbar.');


define('MADE', ' erstellt.');
define('MAKE_DB_1', 'Datenbank konnte nicht erstellt werden');
define('MAKE_DB_2', 'wurde angelegt.');
define('MAKE_DB_3', 'Keine Datenbank erstellt.');
define('MODIFY_FILE_1', 'Error: unable to open for read:');

define('YES', 'aktiviert');
define('NO', 'deaktiviert');

define('NOTMADE', ' nicht erstellt');
define('NOTUPDATED', '<img src="images/no.gif" alt="FEHLER" border="0" align="absmiddle">  FEHLER ');
define('UPDATED', 'aktualisiert');
define('NOW_104', 'Ihre OOS [OSIS Online Shop] Datenbank wurde erfolgreich aktualisiert!');

define('CONTINUE_1', 'Shop Administrator');
define('CONTINUE_2', 'Legen Sie nun den Administrator-Account f&uuml;r OOS [OSIS Online Shop] fest. Sie k&ouml;nnen sp&auml;ter mit der Email - Adresse und dem Passwort Ihren OOS [OSIS Online Shop] konfigurieren.');
define('CONTINUE_3', 'Bitte kontrollieren Sie Ihre Angaben. Eine &Auml;nderung ist sp&auml;ter nicht mehr m&ouml;glich!');

define('ADMIN_GENDER', 'Admin Anrede');
define('MALE', 'Herr');
define('FEMALE', 'Frau');

define('ADMIN_FIRSTNAME', 'Admin Vorname');
define('ADMIN_NAME', 'Admin Name');
define('ADMIN_EMAIL','Admin E-Mail');
define('ADMIN_PHONE', 'Admin Telefon');
define('ADMIN_FAX', 'Admin Fax');
define('ADMIN_PASS','Admin Passwort');
define('ADMIN_REPEATPASS','Passwort best&auml;tigen');
define('PASSWORD_HIDDEN', '--VERSTECKT--');
define('OWP_URL', 'Virtual Path (URL)');
define('ROOT_DIR', 'Webserver Root Directory');
define('ADMIN_INSTALL', 'Sind die Angaben korrekt, klicken Sie bitte auf <code>Admin installieren</code>');
define('PASSWORD_ERROR', 'Das \'Passwort\' und die \'Best&auml;tigung\' m&uuml;ssen &uuml;bereinstimmen!');
define('ADMIN_ERROR', 'Fehler:');
define('ADMIN_PASSWORD_ERROR', 'Bitte geben Sie ein \'Passwort\' ein!');
define('ADMIN_EMAIL_ERROR', 'Bitte geben Sie Ihre \'E-Mail Adresse\' ein!');

define('INPUT_DATA', 'Daten f&uuml;r OOS [OSIS Online Shop] ');

define('FINISH_1', 'Danksagung');
define('FINISH_2', 'Bei dieser Gelegenheit m&ouml;chten wir allen danken, die zur Entwicklung von OOS [OSIS Online Shop] beigetragen haben. Unser spezieller Dank geb&uuml;hrt den Entwicklern  von PHP. ');
define('FINISH_3', 'Sie haben OOS [OSIS Online Shop] erfolgreich installiert. Bitte l&ouml;schen Sie nun das Installations Verzeichnis');
define('FINISH_4', 'OOS [OSIS Online Shop] Admin');


define('FOOTER', 'Diese WebSite wurde mit <a target="_blank" href="http://www.oos-shop.de/">OOS [OSIS Online Shop]</a> erstellt. <br /><a target="_blank" href="http://www.oos-shop.de/">OOS [OSIS Online Shop]</a> ist als freie Software unter der <a target="_blank" href="http://www.gnu.org/">GNU/GPL Lizenz</a> erhältlich.');

define('LINK_BACK', 'Zurück');
define('LINK_TOP', 'Nach Oben');

?>