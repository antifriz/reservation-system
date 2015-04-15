<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledPretrage.php";

Session::start();

(new PogledPretrage())->generirajOkvir();






