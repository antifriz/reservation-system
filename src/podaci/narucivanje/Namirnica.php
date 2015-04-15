<?php

/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 03.01.15.
 * Time: 16:47
 */
class Namirnica
{
    /**
     * @var string
     */
    private $naziv;
    /**
     * @var int
     */
    private $idUgostitelj;
    /**
     * @var int
     */
    private $kolicina;
    /**
     * @var int
     */
    private $id;

    /**
     * @var
     */
    private $data;

    /**
     * @param $data
     *
     */
    private function __construct($data)
    {
        $this->data = $data;
        $this->id = $this->getFromData('id');
        if (array_key_exists('id_namirnica', $data))
            $this->id = $this->getFromData('id_namirnica');
        $this->naziv = $this->getFromData('naziv');
        $this->idUgostitelj = $this->getFromData('id_ugostitelj');
        $this->kolicina = $this->getFromData('kolicina');
    }

    /**
     * @param $id_ili_data
     *
     * @return \Namirnica
     */
    public static function dohvati($id_ili_data)
    {
        if(is_array($id_ili_data))
            return new self($id_ili_data);
        $id_ili_data = DBfacade::receiveAll("CALL namirnica__ucitaj(:id)", ['id' => $id_ili_data])[0];
        return new self($id_ili_data);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIdUgostitelj()
    {
        return $this->idUgostitelj;
    }

    /**
     * @return int
     */
    public function getKolicina()
    {
        return $this->kolicina;
    }

    /**
     * @return string
     */
    public function getNaziv()
    {
        return $this->naziv;
    }

    /**
     * @param $key
     *
     * @return null|string
     */
    private function getFromData($key)
    {
        $data = $this->data;
        return array_key_exists($key, $data) ? trim($data[$key]) : null;
    }

    /**
     * @param int $kolicina
     */
    public function setKolicina($kolicina)
    {
        $this->kolicina = $kolicina;
    }


}