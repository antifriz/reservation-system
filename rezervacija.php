<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.12.14.
 * Time: 20:15
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/podaci/rezerviranje/Rezervacija.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledRezervacije.php";

$trenutniKorisnik = Session::dohvatiTrenutnogKorisnika();


if (Session::jeGost())
{
    /**
     * obradi $_GET
     */
    $idKreacije = filter_input(INPUT_GET, 'kreiraj');
    if ($idKreacije)
    {
        /**
         * AKO POSTOJI parametar kreiraj
         *
         * prima get parametar kreiraj => idUgostitelj
         *
         * pomoću svog identifikatora i identifikatora ugostitelja stvara novu rezervaciju
         */
        try
        {
            $rezervacija = Rezervacija::kreiraj($trenutniKorisnik->getId(), $idKreacije);
        }
        finally
        {
            /**
             * ako nije uspio stvoriti rezervaciju preusmjeri na početnu stranicu
             *
             * dva moguća razloga:
             * - neispravan unos parametra kreiraj
             * - baza ne sluša
             */
            if (!$rezervacija)
                Session::preusmjeriNaPocetnuStranicu();
        }
    }
    else
    {
        $idRezervacije = filter_input(INPUT_GET, 'id');
        if ($idRezervacije)
        {
            /**
             * AKO POSTOJI parametar id
             *
             * prima get parametar id => idRezervacije
             */
            try
            {
                $rezervacija = Rezervacija::dohvati($idRezervacije);
                if ($rezervacija->getIdGost() != $trenutniKorisnik->getId())
                {
                    unset($rezervacija);
                    throw new Exception("Gost nema pristup toj rezervaciji");
                }
            }
            finally
            {
                /**
                 * ako nije uspio stvoriti rezervaciju preusmjeri na početnu stranicu
                 *
                 * mogući razlozi:
                 * - neispravan unos id rezervacije
                 * - baza ne sluša (ne postoji takva rezervacija)
                 * - gost nema pristup toj rezervaciji (nije njegova)
                 */
                if (!$rezervacija)
                    Session::preusmjeriNaPocetnuStranicu();
            }
        }
        else
        {
            /**
             * trećeg nema => neispravno
             */
            Session::preusmjeriNaPocetnuStranicu();
        }
    }
}
elseif (Session::jeKonobar())
{
    /**
     * dohvati ID ugostitelja
     */
    try
    {
        $idUgostitelj = Konobar::dohvati($trenutniKorisnik->getId())->getIdUgostitelj();
    }
    finally
    {
        /**
         * ako nije uspio preusmjeri
         */
        if (!$idUgostitelj)
            Session::preusmjeriNaPocetnuStranicu();
    }
    /**
     * obradi $_GET
     */
    $idKreacije = filter_input(INPUT_GET, 'kreiraj');

    if ($idKreacije)
    {
        /**
         * AKO POSTOJI parametar kreiraj
         *
         * prima get parametar kreiraj => idGost
         *
         * pomoću svog identifikatora i identifikatora gosta stvara novu rezervaciju
         */
        try
        {
            $rezervacija = Rezervacija::kreiraj(null, $idKreacije);
        }
        catch(Exception $e)
        {
            $e->getMessage();
            return;
        }
        finally
        {
            /**
             * ako nije uspio stvoriti rezervaciju preusmjeri na početnu stranicu
             *
             * mogući razlozi:
             * - neispravan unos parametra kreiraj
             * - baza ne sluša
             */
            if (!$rezervacija)
                Session::preusmjeriNaPocetnuStranicu();
        }
    }
    else
    {
        $idRezervacije = filter_input(INPUT_GET, 'id');
        if ($idRezervacije)
        {
            /**
             * AKO POSTOJI parametar id
             *
             * prima get parametar id => idRezervacije
             */
            try
            {
                $rezervacija = Rezervacija::dohvati($idRezervacije);
                if ($rezervacija->getIdUgostitelj() != $idUgostitelj)
                {
                    unset($rezervacija);
                    throw new Exception("Konobar nema pristup toj rezervaciji");
                }
            }
            finally
            {
                /**
                 * ako nije uspio stvoriti rezervaciju preusmjeri na početnu stranicu
                 *
                 * mogući razlozi:
                 * - neispravan unos id rezervacije
                 * - baza ne sluša (ne postoji takva rezervacija)
                 * - konobar nema pristup toj rezervaciji (nije od njegovog objekta)
                 */
                if (!$rezervacija)
                    Session::preusmjeriNaPocetnuStranicu();
            }
        }
        else
        {
            /**
             * trećeg nema => neispravno
             */
            Session::preusmjeriNaPocetnuStranicu();
        }
    }
}
else
{
    /**
     * ako nije gost ili konobar tu nema što raditi
     */
    Session::preusmjeriNaPocetnuStranicu();
}

/** @var Rezervacija $rezervacija */
(new PogledRezervacije($rezervacija))->generirajOkvir();
