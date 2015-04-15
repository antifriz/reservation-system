<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 12/22/14
 * Time: 5:03 PM
 */


/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";

/**
 * Class Dobavljac
 */
class Dobavljac extends Korisnik
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
     *
     */
    /* public function dohvatiNabave(){      //ovo imamo u nabavama

     }*/


    /**
     * @param $id_nabava
     *
     * @throws Exception
     */
    public function posaljiNabavu($id_nabava)
    {  //todo: call
        $query = "
            UPDATE nabava
            SET status=1
            WHERE id_nabava=:id
        ";
        try
        {
            DBfacade::send($query, [':id' => $id_nabava]);
            ZapisAdminDnevnika::zapisi("Dobavljač prihvatio nabavu (id nabave: " . $id_nabava . ").");

        }
        catch (PDOException $e)
        {
            ZapisAdminDnevnika::zapisi("Prihvaćanje nabave (id nabave: " . $id_nabava . ") nije uspijelo.");
            throw new Exception("Neuspjelo prihvaćanje nabave");
        }

    }


    /**
     * @param $id_nabava
     *
     * @throws Exception
     */
    public function odbijNabavu($id_nabava)
    { //todo:call
        $query = "
            UPDATE nabava SET status =2 WHERE id_nabava = :id;
        ";

        try
        {
            DBfacade::send($query, [':id' => $id_nabava]);
            ZapisAdminDnevnika::zapisi("Dobavljač odbio nabavu (id nabave: " . $id_nabava . ").");
        }
        catch (PDOException $e)
        {
            ZapisAdminDnevnika::zapisi("Odbijanje nabave (id nabave: " . $id_nabava . ") nije uspijelo.");
            throw new Exception("Neuspjelo odbijanje nabave");
        }

    }

    /**
     * @return Nabava[]
     */
    public function dohvatiNabave()
    {
        return Nabava::dohvatiSveZaDobavljaca($this->getId());
    }

    /**
     * @return array
     */
    public static function dohvatiDobavljace()
    {
        return DBfacade::receiveAll("Select * from korisnik where id_vrsta=5;",[]);
    }


}