<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 1/2/15
 * Time: 2:14 PM
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);


require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Ugostitelj.php";
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


    /**
     * ucitaj preostale POST podatke
     */
    $ime = filter_input(INPUT_POST, 'ime_restoran');
    $adresa = filter_input(INPUT_POST, 'adresa');
    $email = filter_input(INPUT_POST, 'email');


    /**
     * probaj registirati ugostitelja
     * - ako baci iznimku uhvati ju i ispiši njenu poruku u $err
     * - inače prijavi
     */
    try {
        if(empty($nadimak))
            throw new Exception("Nadimak nije upisan");
        if(empty($zaporka))
            throw new Exception("Šifra nije upisana");
        if(empty($ime))
            throw new Exception("Ime restorana nije upisano");
        if(empty($adresa))
            throw new Exception("Adresa restorana nije upisana");
        if(empty($email))
            throw new Exception("Email nije upisan");

        $id = Ugostitelj::registriraj($nadimak, $zaporka, $email ,$ime, $adresa);
        Session::prijavi($nadimak,$zaporka);
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

/**
 * generiraj pogled
 */
(new PogledRegistriranje($err,false))->generirajOkvir();

