<?php


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledStanjeZaliha.php";

$trenutniKorisnik = Session::dohvatiTrenutnogKorisnika();
$msg = null;

if(!Session::jeUgostitelj() and ! Session::jeDobavljac())
    Session::preusmjeriNaPocetnuStranicu();

if(!Session::jeUgostitelj())
{
    $id = filter_input(INPUT_GET,'noviDobavljac');
    if($id)
    {
        try
        {
            $trenutniKorisnik->postaviDobavljaca($id);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }
    }
}

(new PogledStanjeZaliha($trenutniKorisnik,$msg))->generirajOkvir();