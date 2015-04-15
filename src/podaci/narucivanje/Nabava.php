<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 03.01.15.
 * Time: 16:47
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/DBfacade.php";

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Konobar.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Dobavljac.php";

require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Jelovnik.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/RasporedStolova.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";


/**
 * Class Nabava
 *
 * u bazi podataka nabava.status = 0|1|2|3
 *  0 => kreirano
 *  1 => prihvaceno
 *  2 => odbijeno
 *  3 => dostavljeno
 *
 */
class Nabava
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var Namirnica[]
     */
    private $naruceno;

    /**
     * @var int
     */
    private $vremenskaOznaka;
    /**
     * @var int
     */
    private $idUgostitelj;
    /**
     * @var int
     */
    private $idDobavljac;

    /**
     * @var int
     */
    private $status;

    /**
     * @param int $id
     */
    private function __construct($id)
    {
        $this->id = $id;
    }


    /**
     * @param $idUgostitelj
     * @param $implodedNamirnice
     *
     * @throws Exception
     */
    public static function kreiraj($idUgostitelj, $implodedNamirnice) //todo: uredi zapis todo: backdorable
    {
        try
        {

            $id = DBfacade::receiveAll(
                "CALL nabava__kreiraj(:id_ugostitelj)",
                [
                    'id_ugostitelj' => $idUgostitelj
                ]
            )[0]['id'];

            $nabava = new self($id);


            $namirniceVrijednostDict = explode('|', $implodedNamirnice);


            if ($id)
                ZapisAdminDnevnika::zapisi(
                    "Kreirana nova nabava (id nabave: " . $nabava->id . ") od strane ugostitelja. "
                );
            foreach ($namirniceVrijednostDict as $namirnicaKolicina)
            {
                $namirnicaKolicina = explode(':', $namirnicaKolicina);
                $nabava->dodaj($namirnicaKolicina[0], $namirnicaKolicina[1]);
            }

        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjela kreacija nabave");
        }
    }

    /**
     *
     * @param $namirnicaId
     * @param $namirnicaKolicina
     *
     */
    private function dodaj($namirnicaId, $namirnicaKolicina)
    {
        try
        {
            DBfacade::send(
                "CALL nabava__dodaj_namirnica(:id_nabava,:id_namirnica,:kolicina)",
                [
                    'id_nabava'    => $this->getId(),
                    'id_namirnica' => $namirnicaId,
                    'kolicina'     => $namirnicaKolicina
                ]
            );
            ZapisAdminDnevnika::zapisi(
                "Namirnica (id: " . $namirnicaId . ", količina: " . $namirnicaKolicina . ") dodana u nabavu (id nabave: " . $this->getId(
                ) . ")."
            );
        }
        catch (PDOException $e)
        {

            ZapisAdminDnevnika::zapisi(
                "Namirnica (id: " . $namirnicaId . ", količina: " . $namirnicaKolicina . ") nije dodana u nabavu (id nabave: " . $this->getId(
                ) . ")."
            );

        }


    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $idUgostitelj
     *
     * @return Nabava[]
     */
    public static function dohvatiSveZaUgostitelja($idUgostitelj)
    {
        $rows = DBfacade::receiveAll("CALL nabava__dohvati_sve_za_ugostitelja(:id)", ['id' => $idUgostitelj]);

        $nabave = [];
        foreach ($rows as $row)
            $nabave [] = self::dohvati($row['id_nabava']);
        return $nabave;
    }

    /**
     * @param int $id_nabava
     *
     * @return Nabava
     */
    public static function dohvati($id_nabava)
    {
        $nabava = new self($id_nabava);

        $nabava->ucitajOsnovno();
        $nabava->ucitajNaruceno();

        return $nabava;
    }

    /**
     *
     */
    private function ucitajOsnovno()
    {
        $row = DBfacade::receiveAll("SELECT * FROM nabava WHERE id_nabava = :id", ['id' => $this->getId()])[0];

        $this->vremenskaOznaka = $row['vremenska_oznaka'];
        $this->idUgostitelj = $row['id_ugostitelj'];
        $this->idDobavljac = $row['id_dobavljac'];
        $this->status = $row['status'];
    }

    /**
     *
     */
    private function ucitajNaruceno()
    {
        $querry = "CALL nabava__ucitaj_namirnice(:id)";
        $params = [
            "id" => $this->getId()
        ];
        $namirniceRaw = DBfacade::receiveAll($querry, $params);

        $namirnice = [];
        foreach ($namirniceRaw as $namirnicaRaw)
        {
            $namirnice[] = Namirnica::dohvati($namirnicaRaw);
        }
        $this->naruceno = $namirnice;
    }

    /**
     * @param int $idDobavljac
     *
     * @return Nabava[]
     */
    public static function dohvatiSveZaDobavljaca($idDobavljac)
    {
        $rows = DBfacade::receiveAll("CALL nabava__dohvati_sve_za_dobavljaca(:id)", ['id' => $idDobavljac]);

        $nabave = [];
        foreach ($rows as $row)
            $nabave [] = self::dohvati($row['id_nabava']);
        return $nabave;
    }

    /**
     * @return Namirnica[]
     */
    public function getNaruceno()
    {
        return $this->naruceno;
    }


    /**
     * @return int
     */
    public function getVremenskaOznaka()
    {
        return $this->vremenskaOznaka;
    }

    /**
     * @return int
     */
    public function getIdUgostitelj()
    {
        return $this->idUgostitelj;
    }

    const STATUS_NARUCENO = 0;
    const STATUS_ISPORUCENO = 1;
    const STATUS_ODBIJENO = 2;
    const STATUS_PREUZETO = 3;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusOpis()
    {
        switch ($this->getStatus())
        {
            case 0:
                return "Naručeno";
            case 1:
                return "Isporučeno";
            case 2:
                return "Odbijeno";
            case 3:
                return "Preuzeto";
            default:
                return "Odbijeno";
        }
    }

} 