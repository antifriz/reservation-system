<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT."/pogledi/PogledPrijava.php";

$err = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nadimak = filter_input(INPUT_POST,'username');
    $zaporka = filter_input(INPUT_POST,'password');

    $err = Session::prijavi($nadimak,$zaporka);
}

Session::start();

$pogled= new PogledPrijava($err);
$pogled->generirajOkvir();

