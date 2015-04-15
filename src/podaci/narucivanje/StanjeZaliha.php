<?php

/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 03.01.15.
 * Time: 16:47
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";

class StanjeZaliha
{
    /**
     * @return int
     */
    public function getIdUgostitelj()
    {
        return $this->idUgostitelj;
    }

    /**
     * @var int
     */
    private $idUgostitelj;

    /**
     * @var Namirnica[]
     */
    private $namirnice;

    /**
     * @return Namirnica[]
     */
    public function getNamirnice()
    {
        return $this->namirnice;
    }

    /**
     * @param int $idUgostitelj
     */
    public function __construct($idUgostitelj)
    {
        $this->idUgostitelj = $idUgostitelj;
    }

    /**
     * @param int $idUgostitelj
     *
     * @return \StanjeZaliha
     */
    public static function dohvati($idUgostitelj)
    {
        $stanje = new self($idUgostitelj);

        $stanje->ucitajNamirnice();

        return $stanje;
    }

    private function ucitajNamirnice()
    {
        $rows = DBfacade::receiveAll("CALL stanje_zaliha__ucitaj_namirnice(:id)", ['id' => $this->getIdUgostitelj()]);

        $namirnice = [];
        foreach ($rows as $row)
            $namirnice [] = Namirnica::dohvati($row);
        $this->namirnice = $namirnice;
    }


    /**
     * @param $naziv
     * @param $vrijednost
     *
     * @throws Exception
     */
    public function dodaj($naziv, $vrijednost) //todo: backdoorable
    {
        try
        {

            DBfacade::send(
                "CALL stanje_zaliha__dodaj_namirnica(:id_ugostitelj,:naziv,:kolicina)",
                [
                    'id_ugostitelj' => $this->getIdUgostitelj(),
                    'naziv'         => $naziv,
                    'kolicina'      => $vrijednost
                ]
            );
            ZapisAdminDnevnika::zapisi("Dodavanje namirnice " . $vrijednost . " uspjelo.");
        }
        catch (PDOException $e)
        {
            ZapisAdminDnevnika::zapisi("Dodavanje namirnice " . $vrijednost . " nije uspjelo.");
            throw new Exception("Neuspjelo dodavanje namirnice");
        }
    }

    /**
     * @param $id_namirnica
     * @param $vrijednost
     *
     * @throws Exception
     */
    public function uredi($id_namirnica, $vrijednost) //todo: backdoorable
    {
        try
        {
            DBfacade::send(
                "CALL stanje_zaliha__uredi_namirnica(:id_namirnica,:vrijednost)",
                [
                    'id_namirnica' => $id_namirnica,
                    'vrijednost'   => $vrijednost
                ]
            );

            ZapisAdminDnevnika::zapisi(
                "Promjena namirnice s identifikatorom " . $id_namirnica . " na vrijednost " . $vrijednost . " uspijela."
            );

        }
        catch (PDOException $e)
        {
            ZapisAdminDnevnika::zapisi(
                "Promjena namirnice s identifikatorom " . $id_namirnica . " na vrijednost " . $vrijednost . " nije uspijela."
            );
            throw new Exception("Neuspjelo uređivanje namirnice");
        }
    }


    /**
     * @param $id_namirnica
     *
     * @throws Exception
     */
    public function ukloni($id_namirnica)
    {
        try
        {
            DBfacade::send(
                "CALL stanje_zaliha__ukloni_namirnica(:id_namirnica)",
                [
                    'id_namirnica' => $id_namirnica
                ]
            );

            ZapisAdminDnevnika::zapisi("Uklanjanje namirnice s identifikatorom " . $id_namirnica . " uspijelo.");
        }
        catch (PDOException $e)
        {
            ZapisAdminDnevnika::zapisi("Uklanjanje namirnice s identifikatorom " . $id_namirnica . " nije uspijelo.");
            throw new Exception("Neuspjelo uklanjanje namirnice");
        }
    }
}