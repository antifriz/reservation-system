<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.12.14.
 * Time: 20:15
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT."/sastavnice/Session.php";
require_once DOCUMENT_ROOT."/pogledi/PogledRezerviranja.php";


/**
 * ako nije gost ili konobar nema tu šta raditi
 */
if(! Session::jeKonobar())
    Session::preusmjeriNaPocetnuStranicu();


$trenutniKorisnik = Session::dohvatiTrenutnogKorisnika();
/**
 * generiraj pogled
 */
(new PogledRezerviranja($trenutniKorisnik))->generirajOkvir();