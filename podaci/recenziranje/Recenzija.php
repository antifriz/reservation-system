<?php

/**
 * Created by PhpStorm.
 * User: Lovro
 * Date: 15.12.2014.
 * Time: 19:24
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";


class Recenzija
{

    /**
     *
     */
    private function __construct()
    {
    }

    /**
     * @param $dolazni_id
     *
     * @return mixed
     * @throws Exception
     */
    public static function dohvati($dolazni_id)
    {
        try
        {
            $row = DBfacade::receiveAll( "SELECT * FROM recenzija WHERE id_recenzija=:id ", [ //todo: poziv procedure
                    ':id' => $dolazni_id
                ] )[0];
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjeli dohvat recenzije");
        }
        return $row;
    }

    /**
     * @throws Exception
     */
    public static function objavi($idUgostitelj,$idKorisnik,$ocjena,$tekst)
    {
        try
        {
            DBfacade::send( "Call recenzija__objavi(:ugostitelj,:korisnik,:ocjena,:tekst) ", [
                    ':ugostitelj'      => $idUgostitelj,
                    ':korisnik'        => $idKorisnik,
                    ':ocjena'          => $ocjena,
                    ':tekst'           => $tekst
                ]);
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjela objava recenzije");
        }
    }


    /**
     * @param $idRecenzije
     *
     * @throws Exception
     */
    public static function ukloni($idRecenzije)
    {
        try
        {
        DBfacade::send(" DELETE FROM recenzija WHERE  id_recenzija=:id_rec ",[ //todo: poziv procedure
            ':id_rec' => $idRecenzije
        ]);
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo uklanjanje recenzije");
        }
    }
}