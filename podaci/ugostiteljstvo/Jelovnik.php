<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.12.14.
 * Time: 22:10
 */


/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/DBfacade.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Jelo.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/PonudaJela.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";


/**
 * Class Jelovnik
 */
class Jelovnik
{

    /**
     * @var
     */
    private $idUgostitelj;
    /**
     * @var Jelo[]
     */
    private $jela;
    /**
     * @var PonudaJela[]
     */
    private $ponudeJela;
    /**
     * @var string[]
     */
    private $vrsteJela;

    /**
     * @return \string[]
     */
    public function getVrsteJela()
    {
        return $this->vrsteJela;
    }

    /**
     * @param $id_ugostitelj
     */
    public function __construct($id_ugostitelj)
    {
        $this->idUgostitelj = $id_ugostitelj;
    }

    /**
     * @param $id_ugostitelj
     *
     * @return Jelovnik
     */
    public static function dohvati($id_ugostitelj)
    {
        $jelovnik = new self($id_ugostitelj);

        $jelovnik->ucitajJela();
        $jelovnik->ucitajPonudeJela();
        $jelovnik->ucitajVrsteJela();

        return $jelovnik;
    }

    /**
     *
     */
    private function ucitajJela()
    {
        $querry = "CALL jelovnik__ucitaj_jela(:id)";
        $params = [
            "id" => $this->getIdUgostitelj()
        ];
        $jelaRaw = DBfacade::receiveAll($querry, $params);

        $jela = [];
        foreach ($jelaRaw as $jeloRaw)
        {
            $jela[] = new Jelo($jeloRaw);
        }
        $this->jela = $jela;
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
    private function ucitajVrsteJela()
    {
        $vrsteRaw = DBfacade::receiveAll("CALL jelovnik__ucitaj_vrste_jela()", []);
        $vrste = [];
        foreach ($vrsteRaw as $vrstaRaw)
            $vrste[$vrstaRaw['id_vrsta_jela']] = $vrstaRaw['naziv_vrsta_jela'];

        $this->vrsteJela = $vrste;
    }

    /**
     *
     */
    private function ucitajPonudeJela()
    {
        $querry = "CALL jelovnik__ucitaj_ponude_jela(:id)";
        $params = [
            'id' => $this->getIdUgostitelj()
        ];
        $ponudeRaw = DBfacade::receiveAll($querry, $params);

        $ponude = [];
        foreach ($ponudeRaw as $ponudaRaw)
            $ponude [] = PonudaJela::dohvati($ponudaRaw['id_ponuda']);

        $this->ponudeJela = $ponude;
    }

    /**
     * @return \Jelo[]
     */
    public function getJela()
    {
        return $this->jela;
    }


    /**
     * @return array
     */
    public function getJelaPoVrstama()
    {
        $jela = [];

        $vrste = $this->vrsteJela;
        foreach ($vrste as $id => $vrsta)
        {
            $jela[$vrsta . "|" . $id] = [];
        }
        $jelaRaw = $this->getJela();
        foreach ($jelaRaw as $jeloRaw)
        {
            $id = $jeloRaw->getVrsta();
            $vrsta = $vrste[$id] . "|" . $id;
            $jela[$vrsta][] = $jeloRaw;
        }
        return $jela;
    }

    /**
     * @return \PonudaJela[]
     */
    public function getPonudeJela()
    {
        return $this->ponudeJela;
    }

    /**
     * @param Jelo|PonudaJela $jelo_ponuda
     *
     * @throws UnexpectedValueException
     */
    public function dodaj($jelo_ponuda)
    {
        if ($jelo_ponuda instanceof Jelo)
            return $this->dodajJelo($jelo_ponuda);
        elseif ($jelo_ponuda instanceof PonudaJela)
            return $this->dodajPonudaJela($jelo_ponuda);
        else
            throw new UnexpectedValueException();
    }

    /**
     * @param Jelo $jelo
     */
    private function dodajJelo($jelo)
    {
        $querry = "CALL jelovnik__dodaj_jelo(:ugostitelj,:naziv,:vrsta,:cijena)";
        $params = [
            'ugostitelj' => $this->getIdUgostitelj(),
            'naziv'      => $jelo->getNaziv(),
            'vrsta'      => $jelo->getVrsta(),
            'cijena'     => $jelo->getCijena()
        ];
        $rows = DBfacade::receiveAll($querry, $params);
        return $rows[0]['id'];
    }

    /**
     * @param PonudaJela $jelo_ponuda
     */
    private function dodajPonudaJela($jelo_ponuda)
    {
        $querry = "CALL jelovnik__dodaj_ponuda_jela(:ugostitelj,:naziv_ponuda)";
        $params = [
            'ugostitelj'   => $this->getIdUgostitelj(),
            'naziv_ponuda' => $jelo_ponuda->getNaziv()
        ];
        $rows = DBfacade::receiveAll($querry, $params);
        return $rows[0]['id'];
    }

    /**
     * @param Jelo|PonudaJela $jelo_ponuda
     *
     * @throws UnexpectedValueException
     */
    public function uredi($jelo_ponuda)
    {
        if ($jelo_ponuda instanceof Jelo)
            $this->urediJelo($jelo_ponuda);
        elseif ($jelo_ponuda instanceof PonudaJela)
            $this->urediPonudaJela($jelo_ponuda);
        else
            throw new UnexpectedValueException();
    }

    /**
     * @param Jelo $jelo
     */
    private function urediJelo($jelo)
    {
        $querry = "CALL jelovnik__uredi_jelo(:id,:naziv,:cijena)";
        $params = [
            'id'     => $jelo->getId(),
            'naziv'  => $jelo->getNaziv(),
            'cijena' => $jelo->getCijena()
        ];
        DBfacade::send($querry, $params);
    }

    /**
     * @param PonudaJela $jelo_ponuda
     */
    private function urediPonudaJela($jelo_ponuda)
    {
        $querry = "CALL jelovnik__uredi_ponuda_jela(:id_ponuda,:naziv_ponuda)";
        $params = [
            'id_ponuda'    => $jelo_ponuda->getId(),
            'naziv_ponuda' => $jelo_ponuda->getNaziv()
        ];
        DBfacade::send($querry, $params);
    }

    /**
     * @param Jelo|PonudaJela $jelo_ponuda
     *
     * @throws UnexpectedValueException
     */
    public function ukloni($jelo_ponuda)
    {
        if ($jelo_ponuda instanceof Jelo)
            $this->ukloniJelo($jelo_ponuda);
        elseif ($jelo_ponuda instanceof PonudaJela)
            $this->ukloniPonudaJela($jelo_ponuda);
        else
            throw new UnexpectedValueException();
    }

    /**
     * @param Jelo $jelo
     */
    private function ukloniJelo($jelo)
    {
        $querry = "CALL jelovnik__ukloni_jelo(:id)";
        $params = [
            "id" => $jelo->getId()
        ];
        DBfacade::send($querry, $params);
    }

    /**
     * @param PonudaJela $jelo_ponuda
     */
    private function ukloniPonudaJela($jelo_ponuda)
    {
        $querry = "CALL jelovnik__ukloni_ponuda_jela(:id_ponuda)";
        $params = [
            'id_ponuda' => $jelo_ponuda->getId()
        ];
        DBfacade::send($querry, $params);
    }

    /**
     * @return array
     */
    public function getJelaPoPonudama()
    {
        $ponudeRaw = $this->getPonudeJela();

        $ponude = [];
        foreach ($ponudeRaw as $ponudaRaw)
        {
            $ids = [];

            $jela = $ponudaRaw->getJela();
            foreach ($jela as $jelo)
                $ids [] = $jelo->getId();

            $ponude[$ponudaRaw->getId()] = $ids;
        }
        return $ponude;
    }

} 