<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 12/16/14
 * Time: 6:18 PM
 */


/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
//require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";

/**
 * Class Inspekcija
 */
class Inspekcija extends Korisnik
{


    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param $id
     *
     * @return Dobavljac
     */
    public static function dohvati($id)
    {
        $korisnik = new self($id);

        $korisnik->ucitaj();

        return $korisnik;
    }


    /**
     * @return array
     * @throws Exception
     */
    public function dohvatiRecenzije()
    {
        try
        {
            return DBfacade::receiveAll(
                "CALL inspekcija__dohvati_recenzije(:id)",
                [
                    'id' => $this->getId()
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo učitavanje recenzija");
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function dohvatiNeodobreneUgostitelje() //todo: call
    {
        try
        {
            return DBfacade::receiveAll(
                "SELECT *
FROM ugostitelj
WHERE id_ugostitelj NOT IN (SELECT id_ugostitelj
                            FROM recenzija
                            WHERE id_autor IN (SELECT id
                                               FROM korisnik
                                               WHERE id_vrsta = 4) AND ocjena > 1) and prihvacen_admin;",
                []
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dohvaćanje neodobrenih ugostitelja.");
        }
    }



    /**
     * @throws Exception
     */
    public function dohvatiOcjene()
    {
        try
        {
            return DBfacade::receiveAll(
                "CALL inspekcija__dohvati_ocjene(:id)",
                [
                    'id' => $this->getId()
                ]
            )[0];
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dohvaćanje ocjena");
        }
    }

}