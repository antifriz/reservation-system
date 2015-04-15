<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.12.14.
 * Time: 22:10
 */


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/DBfacade.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Stol.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";


/**
 * Class RasporedStolova
 */
class RasporedStolova
{
    /**
     * @var int
     */
    private $idUgostitelj;
    /**
     * @var Stol[]
     */
    private $stolovi;

    /**
     * @param $id_ugostitelj int
     */
    public function __construct($id_ugostitelj)
    {
        $this->idUgostitelj = $id_ugostitelj;
    }

    /**
     * @param $id_ugostitelj
     *
     * @return RasporedStolova
     */
    public static function dohvati($id_ugostitelj)
    {
        $raspored = new self($id_ugostitelj);

        $raspored->ucitajStolove();

        return $raspored;
    }

    /**
     *
     */
    private function ucitajStolove()
    {
        $querry = "CALL raspored_stolova__ucitaj_stolove(:id)";
        $params = [
            "id" => $this->getIdUgostitelj()
        ];
        $stoloviRaw = DBfacade::receiveAll($querry, $params);

        $stolovi = [];
        foreach ($stoloviRaw as $stolRaw)
        {
            $stolovi[] = new Stol($stolRaw);
        }
        $this->stolovi = $stolovi;
    }

    /**
     * @return int
     */
    public function getIdUgostitelj()
    {
        return $this->idUgostitelj;
    }


    /**
     * @return \Stol[]
     */
    public function getStolovi()
    {
        return $this->stolovi;
    }


    /**
     * @param Stol $stol
     *
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public function dodaj($stol)
    {
        if ($stol instanceof Stol)
            $this->dodajStol($stol);
        else
            throw new UnexpectedValueException();
    }

    /**
     * @param Stol $stol
     *
     * @throws Exception
     */
    private function dodajStol($stol)
    {
        $querry = "CALL raspored_stolova__dodaj_stol(:ugostitelj,:rbr,:kapacitet)";
        $params = [
            'ugostitelj' => $this->getIdUgostitelj(),
            'rbr'        => $stol->getRedniBroj(),
            'kapacitet'  => $stol->getKapacitet()
        ];

        try
        {
            DBfacade::send($querry, $params);
            ZapisAdminDnevnika::zapisi("Stol ". $stol->getRedniBroj() ." uspiješno dodan.");
        }
        catch(PDOException $e)
        {
            ZapisAdminDnevnika::zapisi("Stol nije uspiješno dodan. Unos takvog stola već postoji.");
            throw new Exception("Provjerite postoji li već unos takvog stola.");
        }
    }


    /**
     * @param Stol $stol
     *
     * @throws UnexpectedValueException
     */
    public function uredi($stol)
    {
        if ($stol instanceof Stol)
            $this->urediStol($stol);
        else
            throw new UnexpectedValueException();
    }

    /**
     * @param Stol $stol
     */
    private function urediStol($stol)
    {
        $querry = "CALL raspored_stolova__uredi_stol(:id,:rbr,:kapacitet)";
        $params = [
            'id'        => $stol->getId(),
            'rbr'       => $stol->getRedniBroj(),
            'kapacitet' => $stol->getKapacitet()
        ];
        try {
            DBfacade::send($querry, $params);
            ZapisAdminDnevnika::zapisi("Stol ". $stol->getRedniBroj() ." uspiješno uređen");
        }
        catch(PDOException $e){

        }

    }


    /**
     * @param Stol $stol
     *
     * @throws UnexpectedValueException
     */
    public function ukloni($stol)
    {
        if ($stol instanceof Stol)
            $this->ukloniStol($stol);
        else
            throw new UnexpectedValueException();
    }

    /**
     * @param Stol $stol
     */
    private function ukloniStol($stol)
    {
        $querry = "CALL raspored_stolova__ukloni_stol(:id)";
        $params = [
            "id" => $stol->getId()
        ];
        try {
            DBfacade::send($querry, $params);
            ZapisAdminDnevnika::zapisi("Stol " .$stol->getRedniBroj(). " uspiješno uklonjen.");
        }
        catch(PDOException $e){
            ZapisAdminDnevnika::zapisi("Stol " .$stol->getRedniBroj(). " nije uspiješno uklonjen.");

        }
    }
} 