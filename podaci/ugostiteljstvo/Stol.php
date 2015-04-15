<?php

/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.12.14.
 * Time: 22:10
 */
class Stol
{

    /**
     * @var null|string
     */
    private $id;
    /**
     * @var null|string
     */
    private $redniBroj;

    /**
     * @return null|string
     */
    public function getRedniBroj()
    {
        return $this->redniBroj;
    }

    /**
     * @var null|string
     */
    private $kapacitet;
    /**
     * @var
     */
    private $zauzet;
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
        $this->id = $this->getFromData('id_stol');
        $this->redniBroj = $this->getFromData('rbr_stol');
        $this->kapacitet = $this->getFromData('kapacitet');
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
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getKapacitet()
    {
        return $this->kapacitet;
    }

    /**
     * @return mixed
     */
    public function getZauzet()
    {
        return $this->zauzet;
    }

    /**
     * @param mixed $zauzet
     */
    public function setZauzet($zauzet)
    {
        $this->zauzet = $zauzet;
    }


}