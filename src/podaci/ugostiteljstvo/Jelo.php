<?php

/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.12.14.
 * Time: 22:10
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT."/sastavnice/DBfacade.php";
require_once DOCUMENT_ROOT."/podaci/ugostiteljstvo/PonudaJela.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";

/**
 * Class Jelo
 */
class Jelo
{

    /**
     * @var null|string
     */
    private $id;
    /**
     * @var null|string
     */
    private $naziv;
    /**
     * @var null|string
     */
    private $cijena;
    /**
     * @var
     */
    private $kolicina = 0;
    /**
     * @var null|string
     */
    private $vrsta;
    /**
     * @var
     */
    private $data;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->id = $this->getFromData('id_jelo');
        $this->naziv = $this->getFromData('ime_jela');
        $this->vrsta = $this->getFromData('id_vrsta_jela');
        $this->cijena = $this->getFromData('cijena_jela');
    }

    /**
     * @param $key
     * @return null|string
     */
    private function getFromData($key)
    {
        $data = $this->data;
        return array_key_exists($key, $data) ? trim($data[$key]) : null;
    }

    /**
     * @return mixed
     */
    public function getCijena()
    {
        return $this->cijena;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getKolicina()
    {
        return $this->kolicina;
    }

    /**
     * @param mixed $kolicina
     */
    public function setKolicina($kolicina)
    {
        $this->kolicina = $kolicina;
    }

    /**
     * @return mixed
     */
    public function getNaziv()
    {
        return $this->naziv;
    }

    /**
     * @return mixed
     */
    public function getVrsta()
    {
        return $this->vrsta;
    }

    /**
     * @param $ponude
     */
    public function osvjeziPonudeJela($ponude)
    {
        DBfacade::send("DELETE FROM ponuda WHERE ponuda.id_jelo =:id",['id'=>$this->getId()]);
        foreach($ponude as $ponuda)
            (new PonudaJela($ponuda,null))->dodajJelo($this);
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


}