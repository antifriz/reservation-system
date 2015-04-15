<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 12/15/14
 * Time: 6:29 PM
 */


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/DBfacade.php";
require_once DOCUMENT_ROOT . "/sastavnice/Session.php";


require_once DOCUMENT_ROOT."/podaci/korisnici/Gost.php";
require_once DOCUMENT_ROOT."/podaci/korisnici/Ugostitelj.php";
require_once DOCUMENT_ROOT."/podaci/korisnici/Inspekcija.php";
require_once DOCUMENT_ROOT."/podaci/korisnici/Konobar.php";
require_once DOCUMENT_ROOT."/podaci/korisnici/Dobavljac.php";
require_once DOCUMENT_ROOT."/podaci/korisnici/Administrator.php";

require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";


/**
 * Class Korisnik
 */
abstract class Korisnik
{
    const GOST = 0;
    const UGOSTITELJ = 1;
    const KONOBAR = 2;
    const ADMIN = 3;
    const INSPEKCIJA = 4;
    const DOBAVLJAC = 5;

    /**
     * @var string[]
     */
    private static $redirectMap = [
        self::GOST => "/index.php",
        self::UGOSTITELJ => "/profil.php",
        self::KONOBAR => "/profil.php",
        self::INSPEKCIJA => "/profil.php",
        self::DOBAVLJAC => "/dobavljanje.php",
        self::ADMIN => "/profil.php"
    ];


    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $nadimak;

    /**
     * @var
     */
    protected $idVrsta;

    /*
    public static function dohvatiVrstuKorisnika($korisnik)
    {
        return strtolower(get_class($korisnik));
    }*/

    /**
     * @param $nadimak
     * @param $zaporka
     *
     * @return Dobavljac|Gost|Konobar|Ugostitelj
     * @throws ErrorException
     */
    public static function dohvatiZaPrijavu($nadimak, $zaporka)
    {
        $rows = DBfacade::receiveRowsByColumnId('korisnik', 'korisnicko_ime', $nadimak);

        $row = $rows->fetch();

        if (!$row) {
            //ZapisAdminDnevnika::zapisi("Prijava s nadimkom nije uspjela (Korisničko ime ne postoji)");
            throw new ErrorException("Korisničko ime ne postoji");
        }

        if ($zaporka !== trim($row['lozinka'])) {
           // ZapisAdminDnevnika::zapisi("Prijava s nadimkom ". $nadimak . " nije uspjela (Neispravna šifra)");
            throw new ErrorException("Neispravna šifra");
        }

        $id = $row['id'];

        $vrsta = $row['id_vrsta'];

        switch ($vrsta) {
            case self::GOST:
                return Gost::dohvati($id);
            case self::UGOSTITELJ:
                return Ugostitelj::dohvati($id);
            case self::INSPEKCIJA:
                return Inspekcija::dohvati($id);
            case self::KONOBAR:
                return Konobar::dohvati($id);
            case self::DOBAVLJAC:
                return Dobavljac::dohvati($id);
            case self::ADMIN:
                return Administrator::dohvati($id);
            default:
                throw new ErrorException("Nepoznata vrsta korisnika");
        }
    }

    /**
     * @param          $nadimak
     * @param          $zaporka
     * @param          $id_vrsta
     *
     * @return int
     * @throws ErrorException
     *
     */
    public static function registriraj($nadimak, $zaporka, $id_vrsta)
    {
        if(strlen($zaporka)<5) throw new ErrorException("Zaporka mora imati barem 5 znakova!");

        $row = DBfacade::receiveAll("CALL korisnik__registriraj(:korisnicko_ime,:lozinka,:id_vrsta);", [
            ':korisnicko_ime' => $nadimak,
            ':lozinka' => $zaporka,
            ':id_vrsta' => $id_vrsta

        ])[0];

        $status = $row['status'];
        if ($status !== 'OK')
            throw new ErrorException($status);

        return $row['id'];
    }

    /**
     * @return string
     */
    public function getNadimak()
    {
        return $this->nadimak;
    }

    public function preusmjeriNaPocetnuStranicu()
    {
        Session::preusmjeriNaStranicu(self::$redirectMap[$this->getIdVrsta()]);
    }

    /**
     * @return mixed
     */
    public function getIdVrsta()
    {
        return $this->idVrsta;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    protected function ucitaj()
    {
        $row = DBfacade::receiveAll("call korisnik__ucitaj(:id)", ['id' => $this->getId()])[0];
        $this->nadimak = trim($row['korisnicko_ime']);
        $this->idVrsta = trim($row['id_vrsta']);
    }


} 