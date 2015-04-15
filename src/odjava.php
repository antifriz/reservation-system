<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";


/**
 * kratko i jasno, odjavi ako ima čega i preusmjeri na početnu stranicu
 */
Session::odjavi();


