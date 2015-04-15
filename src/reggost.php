<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 1/2/15
 * Time: 2:14 PM
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);


require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Gost.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledRegistriranje.php";

/**
 * ulogiran nema tu šta raditi, preusmjeri ga na njemu početnu stranicu
 */
Session::akoJeUlogiranPreusmjeri();

/**
 * postavi err na null ==> nema grešaka
 */
$err = null;


/**
 * ako je POST zahtjev obradi ga
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    /**
     * ucitaj POST podatke
     */
    $nadimak = filter_input(INPUT_POST, 'username');
    $zaporka = filter_input(INPUT_POST, 'password');


    //TODO: ispitaj ispravnost svakog elementa (što ako)

    /**
     * ucitaj preostale POST podatke
     */
    $ime = filter_input(INPUT_POST, 'ime');
    $email = filter_input(INPUT_POST, 'email');


    /**
     * probaj registirati gosta
     * - ako baci iznimku uhvati ju i ispiši njenu poruku u $err
     * - inače prijavi
     */
    try {
        $id = Gost::registriraj($nadimak, $zaporka, $email, $ime);
        Session::prijavi($nadimak,$zaporka,false);
        Session::preusmjeriNaStranicu("/profil.php");
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

/**
 * generiraj pogled
 */
(new PogledRegistriranje($err,true))->generirajOkvir();
