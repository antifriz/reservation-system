<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 12/16/14
 * Time: 6:27 PM
 */


/**
 *
 */
/**
 *
 */
    defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";



/**
 * Class Konobar
 *
 */
class Konobar extends Korisnik
{

    /**
     * @var int
     */
    private $idUgostitelj;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     *
     * @return Konobar
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

        $querry = "CALL konobar__ucitaj(:id)";
        $params = [
            'id' => $this->getId()
        ];
        $res = DBfacade::receiveAll($querry, $params)[0];

        $this->idUgostitelj = $res['id_ugostitelj'];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $nadimak
     * @param $zaporka
     * @param $idUgostitelj
     *
     * @return int
     * @throws Exception
     */
    public static function registriraj($nadimak, $zaporka, $idUgostitelj)
    {
            $id = null;
            try {
                $id = parent::registriraj($nadimak, $zaporka, self::KONOBAR);

                DBfacade::send("CALL konobar__registriraj(:id,:id_ugostitelj)", [
                    'id' => $id,
                    'id_ugostitelj'=> $idUgostitelj
                ]);
            }
            catch (Exception $e)
            {
                if($id) {
                    DBfacade::send("DELETE FROM korisnik WHERE id = :id;Delete from konobar where id_konobar = :id;", ['id' => $id]);
                    throw new Exception("Neispravan unos podataka");
                }
                throw $e;
            }

            return $id;
    }

    /**
     * @return int
     */
    public function getIdUgostitelj()
    {
        return $this->idUgostitelj;
    }


    /**
     *
     */
    public function ukloni($id_ugostitelj)
    {
        $querry = "CALL konobar__ukloni(:id,:id_ugostitelj)";
        $params = [
            'id' => $this->getId(),
            'id_ugostitelj' => $id_ugostitelj
        ];
        DBfacade::send($querry, $params);
    }


}