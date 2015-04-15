<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 12/16/14
 * Time: 5:01 PM
 */


/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";

/**
 * Class Gost
 */
class Gost extends Korisnik
{

    /**
     * @var
     */
    private $imePrezime;
    /**
     * @var
     */
    private $email;

    /**
     * @return mixed
     */
    public function getImePrezime()
    {
        return $this->imePrezime;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getTelefon()
    {
        return $this->telefon;
    }

    /**
     * @return mixed
     */
    public function getBrojkartice()
    {
        return $this->brojkartice;
    }

    /**
     * @var
     */
    private $telefon;
    /**
     * @var
     */
    private $brojkartice;

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
     * @return Gost
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
    protected function ucitaj()
    {
        parent::ucitaj();

        $querry = "CALL gost__ucitaj(:id)";
        $params = [
            'id' => $this->getId()
        ];
        $res = DBfacade::receiveAll($querry, $params)[0];

        $this->imePrezime = trim($res['ime_prezime']);
        $this->brojkartice = trim($res['br_kartice']);
        $this->email = trim($res['email']);
        $this->telefon = trim($res['br_telefona']);
    }

    /**
     * @param $nadimak
     * @param $zaporka
     *
     * @param $email
     * @param $ime
     *
     * @return int
     * @throws Exception
     */
    public static function registriraj($nadimak, $zaporka, $email, $ime)
    {
        $id = null;
        try
        {
            $id = parent::registriraj($nadimak, $zaporka, self::GOST);

            DBfacade::send(
                "CALL gost__registriraj(:id,:ime,:email)",
                [
                    'id'       => $id
                    ,
                    ':email'   => $email
                    ,
                    ':ime'     => $ime
                ]
            );

        }
        catch (Exception $e)
        {
            if ($id)
            {
                DBfacade::send(
                    "DELETE FROM korisnik WHERE id = :id;DELETE FROM gost WHERE id_gost = :id;",
                    ['id' => $id]
                );
                pretty($e);
                throw new Exception("Neispravan unos podataka");
            }
            throw $e;
        }

        return $id;
    }


    /**
     * @param $data
     *
     * @throws Exception
     */
    public function osvjezi($data) //todo: call
    {
        try
        {
            $query = "
              UPDATE gost
              SET
              gost.email = :email,
              gost.ime_prezime = :ime,
              gost.br_kartice = :kartica,
              gost.br_telefona = :telefon
              WHERE
              gost.id_gost = :id;
        ";
            $params = [
                ':email'         => $data['email'],
                ':ime'           => $data['ime'],
                ':kartica'       => $data['kartica'],
                ':telefon'       => $data['telefon']
                //, ':id_dobavljac' => $data['id_dobavljac']
                //, ':prihvacen_admin' => $data['prihvacen_admin']  nedam! :D
                //, ':prihvacen_inspekcija' => $data['prihvacen_inspekcija']  nedam! :D
                ,
                ':id' => $this->getId()
            ];

            DBfacade::send($query, $params);
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjela promjena podataka");
        }
    }


    /**
     * @return array
     * @throws Exception
     */
    public function dohvatiRezervacije()
    {
        try
        {
            return DBfacade::receiveAll(
                "CALL gost__dohvati_rezervacije(:id)",
                [
                    'id' => $this->getId()
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dohvaćanje rezervacija");
        }
    }


    /**
     *
     */
    public function dohvatiRecenzije()
    {
        try
        {
            $rows = DBfacade::receiveAll(
                "CALL gost__dohvati_recenzije(:id)",
                [
                    'id' => $this->getId()
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo učitavanje recenzija");
        }
        return $rows;
    }

    /**
     * @throws Exception
     */
    public function dohvatiOcjene()
    {
        try
        {
            return DBfacade::receiveAll(
                "CALL gost__dohvati_ocjene(:id)",
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