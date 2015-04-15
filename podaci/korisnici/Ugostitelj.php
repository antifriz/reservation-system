<?php
/**
 * PHP Version 5
 *
 * Created by PhpStorm.
 * User: david
 * Date: 12/16/14
 * Time: 6:55 PM
 */


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/DBfacade.php";

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Konobar.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Dobavljac.php";

require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Jelovnik.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/RasporedStolova.php";

require_once DOCUMENT_ROOT . "/podaci/narucivanje/Nabava.php";
require_once DOCUMENT_ROOT . "/podaci/narucivanje/StanjeZaliha.php";

require_once DOCUMENT_ROOT . "/podaci/rezerviranje/Rezervacija.php";

require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";

/**
 * Class Ugostitelj
 */
class Ugostitelj extends Korisnik
{

    /**
     * @var
     */
    private $imeRestoran;
    /**
     * @var
     */
    private $adresa;

    /**
     * @var
     */
    private $opis;
    /**
     * @var
     */
    private $email;
    /**
     * @var
     */
    private $urlSlikeLokala;
    /**
     * @var
     */
    private $urlSlikeStolova;
    /**
     * @var Dobavljac
     */
    private $dobavljac;
    /**
     * @var
     */
    private $jePrihvacenAdmin;
    /**
     * @var
     */
    private $jePrihvacenInspekcija;
    /**
     * @var string
     */
    private $vrsteRestorana;

    /**
     * @var string[][]
     */
    private $radnoVrijeme;

    /**
     * @var bool
     */
    private $jeSveUneseno;


    /**
     * @param $data
     *
     * @throws Exception
     */
    public function osvjezi($data)
    {
        try
        {
            DBfacade::send(
                "CALL ugostitelj__osvjezi(:id,:imeRestoran,:adresa,:opis,:vrste,:email,:urlLokacije,:urlRasporeda,:roR,:rdR,:ros,:rds,:ron,:rdn)",
                [
                    'id'           => $this->getId(),
                    'imeRestoran'  => $data['imeRestoran'],
                    'adresa'       => $data['adresa'],
                    'email'        => $data['email'],
                    'opis'         => $data['opis'],
                    'vrste'        => $data['vrste'],
                    'urlLokacije'  => $data['lokacijaSlika'],
                    'urlRasporeda' => $data['rasporedSlika'],
                    'roR'          => $data['roR'],
                    'rdR'          => $data['rdR'],
                    'ros'          => $data['ros'],
                    'rds'          => $data['rds'],
                    'ron'          => $data['ron'],
                    'rdn'          => $data['rdn']
                ]
            );
            ZapisAdminDnevnika::zapisi("Ugostitelj promijenio svoje podatke.");
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjela promjena podataka");
        }
    }


    /**
     * @return string
     */
    public function getRadiOdRadniDan()
    {
        return $this->radnoVrijeme[0]['radi_od'];
    }

    /**
     * @return string
     */
    public function getRadiDoRadniDan()
    {
        return $this->radnoVrijeme[0]['radi_do'];
    }

    /**
     * @return string
     */
    public function getRadiOdSubota()
    {
        return $this->radnoVrijeme[5]['radi_od'];
    }

    /**
     * @return string
     */
    public function getRadiDoSubota()
    {
        return $this->radnoVrijeme[5]['radi_do'];
    }

    /**
     * @return string
     */
    public function getRadiOdNedjelja()
    {
        return $this->radnoVrijeme[6]['radi_od'];
    }

    /**
     * @return string
     */
    public function getRadiDoNedjelja()
    {
        return $this->radnoVrijeme[6]['radi_do'];
    }


    /**
     * @param $id
     *
     * @throws BadMethodCallException
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param $id
     *
     * @return Ugostitelj
     * @throws BadMethodCallException
     */
    public static function dohvati($id)
    {
        $korisnik = new self($id);

        $korisnik->ucitaj();

        return $korisnik;
    }

    /**
     * @throws BadMethodCallException
     */
    protected function ucitaj()
    {
        parent::ucitaj();

        $querry = "CALL ugostitelj__ucitaj(:id)";
        $params = [
            'id' => $this->getId()
        ];
        try
        {
            $res = DBfacade::receiveAll($querry, $params)[0];
        }
        finally
        {
            if (!$res)
                throw new BadMethodCallException("Ne postoji ugostitelj s tim identifikatorom");
        }

        $this->imeRestoran = trim($res['ime_restoran']);
        $this->adresa = trim($res['adresa']);
        $this->email = trim($res['email']);
        $this->opis = trim($res['opis']);
        $this->vrsteRestorana = trim($res['vrste_restoran']);
        $this->urlSlikeLokala = trim($res['url_slike_lokala']);
        $this->urlSlikeStolova = trim($res['url_slike_stolova']);
        $this->dobavljac = new Dobavljac($res['id_dobavljac']);
        $this->jePrihvacenAdmin = $res['prihvacen_admin'] == '1';
        $this->jePrihvacenInspekcija = $res['prihvacen_inspekcija'];

        $this->jeSveUneseno = !(empty($this->imeRestoran) or empty($this->adresa) or empty($this->email) or empty($this->urlSlikeStolova) or empty($this->urlSlikeLokala) or empty($this->opis));


        $this->ucitajRadnoVrijeme();
    }


    /**
     * @param $nadimak
     * @param $zaporka
     *
     * @param $email
     * @param $ime_restorana
     * @param $adresa
     *
     * @return int|null
     * @throws Exception
     */
    public static function registriraj($nadimak, $zaporka, $email, $ime_restorana, $adresa)
    {
        $id = null;
        try
        {
            $id = parent::registriraj($nadimak, $zaporka, self::UGOSTITELJ);

            DBfacade::send(
                "CALL ugostitelj__registriraj(:id,:ime_restoran,:email,:adresa)",
                [
                    'id'            => $id,
                    ':adresa'       => $adresa,
                    ':email'        => $email,
                    ':ime_restoran' => $ime_restorana
                ]
            );
        }
        catch (Exception $e)
        {
            if ($id)
            {
                DBfacade::send(
                    "DELETE FROM korisnik WHERE id = :id;DELETE FROM ugostitelj WHERE id_ugostitelj = :id;",
                    ['id' => $id]
                );
                throw $e;// new Exception("Neispravan unos podataka"); //todo: što s tim?
            }
            ZapisAdminDnevnika::zapisi("Ugostitelj nije uspiješno registriran. Neispravan unos podataka.");
            throw $e;
        }

        if ($id)
            ZapisAdminDnevnika::zapisi("Ugostitelj " . $ime_restorana . " (id: " . $id . ") registriran.");

        return $id;
    }


    /**
     * mapa se koristi u dohvatiUgostiteljeUzUvjete
     *
     * @see dohvatiUgostiteljeUzUvjete
     * @var array
     */
    private static $filterMap = [
        'ocjena_inspekcija_manja_od' => "ugostitelj_za_pretragu.ocjena_inspekcija<='%PARAM%'+0.05",
        'ocjena_inspekcija_veca_od'  => "ugostitelj_za_pretragu.ocjena_inspekcija>='%PARAM%'-0.05",
        'ocjena_korisnik_manja_od'   => "ugostitelj_za_pretragu.ocjena_korisnik<='%PARAM%'+0.05",
        'ocjena_korisnik_veca_od'    => "ugostitelj_za_pretragu.ocjena_korisnik>='%PARAM%'-0.05",
        'vrsta_restorana'            => "ugostitelj_za_pretragu.dio_vrste_restorana LIKE concat('%',lower(ukloni_palatale(replace('%PARAM%',' ',''))),'%')",
        'dio_naziva_restorana'       => "ugostitelj_za_pretragu.dio_naziva_restorana LIKE concat('%',lower(ukloni_palatale(replace('%PARAM%',' ',''))),'%')"
    ];

    /**
     *
     * KORISTI $_POST VARIJABLU // sramotno ccc
     *
     *
     * uzeti ce u obzir kljuceve:
     *
     *  sortiranje: ocjenaA|ocjenaD|abecednoA|abecednoD|nasumicno
     *
     *  ocjena_inspekcija_veca_od: int
     *  ocjena_inspekcija_manja_od: int
     *  ocjena_korisnik_veca_od: int
     *  ocjena_korisnik_manja_od: int
     *  vrsta_restorana: string
     *  dio_naziva_restorana: string
     *
     * sortiranja: ocjena|abecedno|nasumicno
     *
     * filteri:  ocjena_veca_od|ocjena_manja_od|vrsta_restorana|dio_naziva_restorana todo:
     * slobodno_datum|slobodno_vrijeme ? nedamise to radit :D
     *
     * @return array ne vraća Ugostitelja!!! vraća retke iz tablice (brže)
     */
    public static function dohvatiUgostiteljeUzUvjete()
    {
        /**
         * kreiraj WHERE dio upita
         */
        $filtri = [];
        $mapa = self::$filterMap;
        foreach ($mapa as $key => $val)
        {

            $param = filter_input(INPUT_POST, $key);
            if (!$param)
                continue;
            $filtri[] = str_replace("%PARAM%", $param, $val);
        }

        $filtri = ($filtri) ? "WHERE " . join(' and ', $filtri) : "";


        /**
         * kreiraj ORDER BY dio upita
         */
        $sortiranje = filter_input(INPUT_POST, 'sortiranje');
        switch ($sortiranje)
        {
            case "ocjenaKorisnikA":
                $sortiranje = "ORDER BY ocjena_korisnik asc";
                break;
            case "ocjenaKorisnikD":
                $sortiranje = "ORDER BY ocjena_korisnik is null,ocjena_korisnik desc";
                break;
            case "ocjenaInspekcijaA":
                $sortiranje = "ORDER BY ocjena_inspekcija asc";
                break;
            case "ocjenaInspekcijaD":
                $sortiranje = "ORDER BY ocjena_inspekcija is null,ocjena_inspekcija desc";
                break;
            case "abecednoA":
                $sortiranje = "ORDER BY ime_restoran asc";
                break;
            case "abecednoD":
                $sortiranje = "ORDER BY ime_restoran desc";
                break;
            case "nasumicno":
                $sortiranje = "ORDER BY rand() asc";
                break;
            default:
                $sortiranje = "";
                break;
        }

        /**
         * pošalji upit u bazu
         */
        $querry = "
            SELECT ugostitelj.*, round(ugostitelj_za_pretragu.ocjena_korisnik,1) as ocjena_korisnik, round(ugostitelj_za_pretragu.ocjena_inspekcija,1) as ocjena_inspekcija
            FROM ugostitelj join ugostitelj_za_pretragu on ugostitelj.id_ugostitelj = ugostitelj_za_pretragu.id_ugostitelj
             {$filtri} {$sortiranje}
        ";
        /**
         * ako se desi neki exception vrati da nema rezultata
         */
        try
        {
            return DBfacade::receiveAll($querry, []);
        }
        catch (Exception $e)
        {
            return [];
        }
    }

    /**
     * @return Jelovnik
     */
    public function dohvatiJelovnik()
    {
        return Jelovnik::dohvati($this->getId());
    }


    /**
     * @return mixed
     */
    public function getAdresa()
    {
        return $this->adresa;
    }

    /**
     * @return Dobavljac
     */
    public function getDobavljac()
    {
        return Dobavljac::dohvati($this->dobavljac->getId());
    }

    /**
     * @return mixed
     */
    public function getImeRestoran()
    {
        return $this->imeRestoran;
    }

    /**
     * @return mixed
     */
    public function getJePrihvacenAdmin()
    {
        return $this->jePrihvacenAdmin;
    }

    /**
     * @return mixed
     */
    public function getJePrihvacenInspekcija()
    {
        return $this->jePrihvacenInspekcija;
    }

    /**
     * @return mixed
     */
    public function getUrlSlikeLokala()
    {
        return $this->urlSlikeLokala;
    }

    /**
     * @return mixed
     */
    public function getUrlSlikeStolova()
    {
        return $this->urlSlikeStolova;
    }

    /**
     * @return string
     */
    public function getVrsteRestorana()
    {
        return $this->vrsteRestorana;
    }


    /**
     * @return RasporedStolova
     */
    public function dohvatiRasporedStolova()
    {
        return RasporedStolova::dohvati($this->getId());
    }

    /**
     *
     */
    public function odobriOdAdmina() //todo: ne koristi se call
    {
        try
        {
            DBfacade::send(
                "UPDATE ugostitelj SET ugostitelj.prihvacen_admin = TRUE WHERE ugostitelj.id_ugostitelj=:id",
                ['id' => $this->getId()]
            );

            ZapisAdminDnevnika::zapisi(
                "Ugostitelj " . $this->getImeRestoran() . " (id: " . $this->getId() . ") odobren od admina."
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo odobravanje ugostitelja");
        }
    }


    /**
     * @param $nadimak
     * @param $zaporka
     *
     * @return int
     * @throws Exception
     */
    public function dodajKonobara($nadimak, $zaporka)
    {
        return Konobar::registriraj($nadimak, $zaporka, $this->getId());
    }

    /**
     * @param int $id_konobar
     */
    public function ukloniKonobara($id_konobar)
    {
        (new Konobar($id_konobar))->ukloni($this->id);
    }


    /**
     * @return array
     */
    public function dohvatiKonobare()  //todo:call
    {
        $query = "
            SELECT id, korisnicko_ime FROM konobar LEFT JOIN korisnik ON konobar.id_konobar=korisnik.id
            WHERE id_ugostitelj=:id_ug
        ";

        return DBfacade::receiveAll($query, ['id_ug' => $this->getId()]);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function dohvatiRezervacije()
    {
        try
        {
            return DBfacade::receiveAll(
                "CALL ugostitelj__dohvati_rezervacije(:id)",
                [
                    'id' => $this->getId()
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dohvaćanje rezervacija");
        }
    }

    private function ucitajRadnoVrijeme()
    {
        try
        {

            $radnoVrijemeRaw = DBfacade::receiveAll(
                "CALL ugostitelj__ucitaj_radno_vrijeme(:id)",
                ["id" => $this->getId()]
            );

            $radnoVrijeme = [];
            foreach ($radnoVrijemeRaw as $r)
            {
                $v = $r['id_dan'];
                $radnoVrijeme[$v] = $r;
            }
            $this->radnoVrijeme = $radnoVrijeme;
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo učitavanje radnog vremena");
        }
    }


    /**
     * @param $id_nabava
     *
     * @throws Exception
     */
    public function opskrbiNabavom($id_nabava)
    {
        try
        {
            $query = "CALL ugostitelj__opskrbi_nabavom(:id_nabava, :id_ugostitelj)";

            DBfacade::send($query, ['id_nabava' => $id_nabava, 'id_ugostitelj' => $this->getId()]);
        }
        catch (Exception $e)
        {
            pretty($e);
            throw new Exception("Neuspjelo opskrbljivanje nabavom");
        }
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function dohvatiRecenzije()
    {
        try
        {
            return DBfacade::receiveAll(
                "CALL ugostitelj__dohvati_recenzije(:id)",
                [
                    'id' => $this->getId()
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo učitavanje recenzija");
        }
    }

    /**
     * @return mixed
     */
    public function getOpis()
    {
        return $this->opis;
    }

    /**
     * @return bool
     */
    public function jeSveUneseno()
    {
        return $this->jeSveUneseno;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function dohvatiOcjena()
    {
        try
        {
            return DBfacade::receiveAll(
                "CALL ugostitelj__dohvati_ocjena(:id)",
                [
                    'id' => $this->getId()
                ]
            )[0];
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dohvaćanje ocjene");
        }
    }

    /**
     * @return StanjeZaliha
     */
    public function dohvatiStanjeZaliha()
    {
        return StanjeZaliha::dohvati($this->getId());
    }

    /**
     * @return Nabava[]
     */
    public function dohvatiNabave()
    {
        return Nabava::dohvatiSveZaUgostitelja($this->getId());
    }

    public function izbrisi()//todo: ne koristi se call
    {
        try
        {
            DBfacade::send(
                "delete from ugostitelj WHERE ugostitelj.id_ugostitelj=:id",
                ['id' => $this->getId()]
            );

            ZapisAdminDnevnika::zapisi(
                "Ugostitelj " . $this->getImeRestoran() . " (id: " . $this->getId() . ") izbrisan."
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo brisanje ugostitelja");
        }
    }

    /**
     * @param $id_dobavljac
     *
     * @throws Exception
     */
    public function postaviDobavljaca($id_dobavljac)
    {
        try
        {
            DBfacade::send(
                "update ugostitelj(id_dobavljac) set (:id_dobavljac) where id_ugostitelj = :id" ,
                ['id' => $this->getId(),
                'id_dobavljac' => $id_dobavljac]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo postavljanje dobavljača");
        }
    }

}