<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 31.12.14.
 * Time: 00:03
 */

/**
 *
 */
/**
 *
 */
    defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/DBfacade.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Jelo.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";


/**
 * Class Jelovnik
 */
class PonudaJela
{

    /**
     * @var
     */
    private $id;
    /**
     * @var
     */
    private $naziv;
    /**
     * @var \Jelo[]
     */
    private $jela;

    /**
     * @param $id_ponude_jela
     * @param $naziv_ponude_jela
     */
    public function __construct($id_ponude_jela,$naziv_ponude_jela)
    {
        $this->id = $id_ponude_jela;
        $this->naziv = $naziv_ponude_jela;
    }

    /**
     * @param $id_ponude_jela
     * @return PonudaJela
     */
    public static function dohvati($id_ponude_jela)
    {
        $ponudaJela = new self($id_ponude_jela,null);

        $ponudaJela->ucitajJela();
        $ponudaJela->ucitajNaziv();

        return $ponudaJela;
    }

    /**
     *
     */
    private function ucitajJela()
    {
        $querry = "CALL ponuda_jela__ucitaj_jela(:id_ponude)";
        $params = [
            "id_ponude" => $this->getId()
        ];
        $jelaRaw = DBfacade::receiveAll($querry, $params);

        $jela = [];
        foreach ($jelaRaw as $jeloRaw)
            $jela[] = new Jelo($jeloRaw);

        $this->jela = $jela;
    }


    /**
     * @param Jelo $jelo
     * @return bool
     */
    public function isJeloUPonudi($jelo)
    {
        foreach ($this->jela as $jeloTmp)
            if ($jeloTmp->getId() == $jelo->getId())
                return true;
        return false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     */
    private function ucitajNaziv()
    {
        $querry = "CALL ponuda_jela__ucitaj_naziv(:id_ponude)";
        $params = [
            "id_ponude" => $this->getId()
        ];
        $all = DBfacade::receiveAll($querry, $params);

        $this->naziv = trim($all[0]['naziv_ponuda']);
    }

    /**
     * @return mixed
     */
    public function getNaziv()
    {
        return $this->naziv;
    }

    /**
     * @return \Jelo[]
     */
    public function getJela()
    {
        return $this->jela;
    }


    /**
     * @param Jelo $jelo
     */
    public function dodajJelo($jelo)
    {
        $querry = "CALL ponuda_jela__dodaj_jelo(:id_ponuda,:id_jelo)";
        $params = [
            'id_ponuda' => $this->getId(),
            'id_jelo' => $jelo->getId()
        ];
        try {
            DBfacade::send($querry, $params);
            ZapisAdminDnevnika::zapisi("Jelo " . $jelo->getNaziv() . " uspiješno dodano u ponudu jela " . $this->getNaziv(). ".");
        }
        catch(PDOException $e){
            ZapisAdminDnevnika::zapisi("Jelo " . $jelo->getNaziv() . " nije dodano u ponudu jela " . $this->getNaziv() . ".");

        }
    }

    /**
     * @param Jelo $jelo
     */
    public function ukloniJelo($jelo)
    {
        $querry = "CALL ponuda_jela__ukloni_jelo(:id_ponuda,:id_jelo)";
        $params = [
            'id_ponuda' => $this->getId(),
            'id_jelo' => $jelo->getId()
        ];
       try {
            DBfacade::send($querry, $params);
            ZapisAdminDnevnika::zapisi("Jelo " . $jelo->getNaziv() . " uspiješno uklonjeno iz ponude jela " . $this->getNaziv());
        }
        catch(PDOException $e){
           ZapisAdminDnevnika::zapisi("Jelo " . $jelo->getNaziv() . " nije uklonjeno iz ponude jela " . $this->getNaziv());

        }
    }
} 