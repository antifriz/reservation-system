<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 20.11.14.
 * Time: 20:15
 */

/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);


require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";

/**
 * @param $var
 */
function pretty($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Class Session
 */
class Session
{

    /**
     * @var bool ako je već sjednica pokrenuta nemoj ju opet pokretati
     *           (neće se desiti ništa bitnog ako se pokrene ali nema potrebe)
     */
    private static $started_flag = false;

    /**
     * @param $nadimak
     * @param $lozinka
     *
     * @return string
     */
    public static function prijavi($nadimak, $lozinka, $zapocni_korisnika = true)
    {
        try
        {
            $korisnik = Korisnik::dohvatiZaPrijavu($nadimak, $lozinka);
        }
        catch (Exception $e)
        {
            ZapisAdminDnevnika::zapisi(
                "Prijava s nadimkom " . $nadimak . " nije uspjela (" . $e->getMessage() . ")."
            );
            return $e->getMessage();
        }
        self::zapocniKorisnika($korisnik, $zapocni_korisnika);


        return null;
    }

    /**
     * @param Korisnik $korisnik
     *
     * @internal param $row
     */
    private static function zapocniKorisnika($korisnik, $zapocni_korisnika)
    {
        self::start();

        self::pohraniTrenutnogKorisnika($korisnik);

        ZapisAdminDnevnika::zapisi(
            "Prijava korisnika " . $korisnik->getNadimak() . " (id: " . $korisnik->getId() . ") uspjela."
        );
        if ($zapocni_korisnika)
            self::preusmjeriNaPocetnuStranicu();
    }

    /**
     *
     */
    public static function start()
    {
        if (self::$started_flag)
            return;

        self::postaviKonstante();

        // za errore:
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        ini_set('display_errors', 'on');
        //


        header('Content-Type: text/html; charset=utf-8');
        date_default_timezone_set('Europe/Zagreb');
        session_start();

        self::$started_flag = true;
    }

    /**
     *
     */
    private static function postaviKonstante()
    {
        defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

        defined('POGLED_NARUCI') or define('POGLED_NARUCI', 1);
        defined('POGLED_PRIKAZI') or define('POGLED_PRIKAZI', 2);
        defined('POGLED_UREDI') or define('POGLED_UREDI', 3);

        defined('AKCIJA_UREDI_STOL') or define('AKCIJA_UREDI_STOL', 1);
        defined('AKCIJA_IZBRISI_STOL') or define('AKCIJA_IZBRISI_STOL', 2);


    }

    /**
     * @param $korisnik
     */
    private static function pohraniTrenutnogKorisnika($korisnik)
    {
        $_SESSION['user'] = $korisnik;
    }

    /**
     *
     */
    public static function preusmjeriNaPocetnuStranicu()
    {
        if (self::jePrijavljen())
            self::dohvatiTrenutnogKorisnika()->preusmjeriNaPocetnuStranicu();
        else
            self::preusmjeriNaKorijenskuStranicu();
    }

    /**
     * @return bool
     */
    public static function jePrijavljen()
    {
        self::start();
        return $_SESSION and array_key_exists('user', $_SESSION) and isset($_SESSION['user']);
    }

    /**
     * @return Ugostitelj|Gost|Inspekcija|Administrator|Dobavljac|Konobar|null
     */
    public static function dohvatiTrenutnogKorisnika()
    {
        self::start();
        return array_key_exists('user', $_SESSION) ? $_SESSION['user'] : null;
    }

    /**
     * @param Ugostitelj|Gost|Inspekcija|Administrator|Dobavljac|Konobar|null
     *
     * ne koristi nikad pa ni tad osim za osvjezavanje zapisa
     *
     * @throws BadMethodCallException
     */
    public static function postaviTrenutnogKorisnika($korisnik)
    {
        self::start();
        if (self::dohvatiTrenutnogKorisnika()->getId() != $korisnik->getId())
            throw new BadMethodCallException("Nedozvoljena radnja");
        $_SESSION['user'] = $korisnik;
        return $korisnik;
    }

    /**
     *
     */
    public static function preusmjeriNaKorijenskuStranicu()
    {
        self::start();
        self::preusmjeriNaStranicu('/');
    }

    /**
     * @param $location
     */
    public static function preusmjeriNaStranicu($location)
    {
        header('Location: ' . $location);
        die('Redirecting ... ' . $location);
    }

    /**
     *
     */
    public static function akoJeUlogiranPreusmjeri()
    {
        self::start();
        if (self::jePrijavljen())
            self::preusmjeriNaPocetnuStranicu();
    }

    /**
     *
     */
    public static function akoNijeUlogiranPreusmjeri()
    {
        self::start();
        if (!self::jePrijavljen())
            self::preusmjeriNaPocetnuStranicu();
    }

    /**
     *
     */
    public static function preusmjeriNaStranicuZaPrijavu()
    {
        self::preusmjeriNaStranicu('/prijava.php');
    }

    /**
     * @return bool
     */
    public static function jeUgostitelj()
    {
        return self::jePrijavljen() and self::dohvatiTrenutnogKorisnika()->getIdVrsta() == Korisnik::UGOSTITELJ;
    }

    /**
     * @return bool
     */
    public static function jeGost()
    {
        return self::jePrijavljen() and self::dohvatiTrenutnogKorisnika()->getIdVrsta() == Korisnik::GOST;
    }

    /**
     * @return bool
     */
    public static function jeKonobar()
    {
        return self::jePrijavljen() and self::dohvatiTrenutnogKorisnika()->getIdVrsta() == Korisnik::KONOBAR;
    }

    /**
     * @return bool
     */
    public static function jeDobavljac()
    {
        return self::jePrijavljen() and self::dohvatiTrenutnogKorisnika()->getIdVrsta() == Korisnik::DOBAVLJAC;
    }

    /**
     * @return bool
     */
    public static function jeAdmin()
    {
        return self::jePrijavljen() and self::dohvatiTrenutnogKorisnika()->getIdVrsta() == Korisnik::ADMIN;
    }

    /**
     * @return bool
     */
    public static function jeInspekcija()
    {
        return self::jePrijavljen() and self::dohvatiTrenutnogKorisnika()->getIdVrsta() == Korisnik::INSPEKCIJA;
    }


    /**
     *
     */
    public static function odjavi()
    {
        $korisnik = self::dohvatiTrenutnogKorisnika();
        self::start();
        $_SESSION = [];
        session_destroy();

        if ($korisnik)
            ZapisAdminDnevnika::zapisi(
                "Korisnik " . $korisnik->getNadimak() . " (id: " . $korisnik->getId() . ") uspiješno odjavljen."
            );
        self::preusmjeriNaPocetnuStranicu();
    }


}