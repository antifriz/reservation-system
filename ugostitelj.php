<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 5.1.2015.
 * Time: 12:00
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";

require_once DOCUMENT_ROOT . "/podaci/korisnici/Ugostitelj.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledUgostitelj.php";

$idUgostitelj = filter_input(INPUT_GET, 'id');
if (!$idUgostitelj and Session::jeUgostitelj())
    $idUgostitelj = Session::dohvatiTrenutnogKorisnika()->getId();

try
{
    $ugostitelj = Ugostitelj::dohvati($idUgostitelj);
}
finally
{
    if (!$ugostitelj)
        Session::preusmjeriNaPocetnuStranicu();
}

(new PogledUgostitelj($ugostitelj))->generirajOkvir();





