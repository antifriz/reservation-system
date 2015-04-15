<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', ".");


require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledRegistriranje.php";


/**
 * ulogiran nema tu šta raditi, preusmjeri ga na njemu početnu stranicu
 */
Session::akoJeUlogiranPreusmjeri();


