<?php
/**
 * Created by PhpStorm.
 * User: Lovro
 * Date: 16.12.2014.
 * Time: 17:55
 */

/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once DOCUMENT_ROOT . "/podaci/korisnici/Gost.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Ugostitelj.php";
require_once DOCUMENT_ROOT . "/podaci/administratorstvo/ZapisAdminDnevnika.php";


/**
 * Class Rezervacija
 */
class Rezervacija
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $idGost;
    /**
     * @var int
     */
    private $idUgostitelj;
    /**
     * @var
     */
    private $brojOsoba;
    /**
     * @var int
     */
    private $vrijemeNastanka;

    /**
     * @var int
     */
    private $vrijemePocetak;

    /**
     * @var int
     */
    private $vrijemeKraj;


    /**
     * @var bool
     */
    private $novokreirana = false;

    /**
     * @param $vrijemePocetak
     * @param $vrijemeKraj
     * @param $idUgostitelj
     *
     * @return array
     */
    public static function dohvatiDostupnostStolovaUTerminu($vrijemePocetak, $vrijemeKraj,$brojGostiju, $idUgostitelj,$bezRezervacije = null)
    {
        $res = DBfacade::receiveAll(
            "CALL rezervacija__dohvati_dostupnost_stolova_u_terminu(:start,:end,:gosti,:id,:rezervacija)",
            [
                'start' => $vrijemePocetak / 1000,
                'end'   => $vrijemeKraj / 1000,
                'gosti' => $brojGostiju,
                'id'    => $idUgostitelj,
                'rezervacija' => $bezRezervacije
            ]
        );

        return $res;
    }

    /**
     * @param $date string
     *
     * @return int
     */
    public static function mysqlVrijeme2epoch($date)
    {
        return strtotime($date) * 1000;
    }

    /**
     * @return boolean
     */
    public function jeNovokreirana()
    {
        return $this->novokreirana;
    }

    /**
     * @param $id int
     *
     */
    function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param $idGost
     * @param $idUgostitelj
     *
     * @return null|Rezervacija
     * @throws BadMethodCallException
     * @throws Exception
     */
    public static function kreiraj($idGost, $idUgostitelj)
    {
        if (!(ctype_digit($idGost) or $idGost == null) || !ctype_digit($idUgostitelj))
            throw new BadMethodCallException("Identifikatori gosta i ugostitelja trebaju biti cijeli brojevi");

        $id = self::dohvatiNoviId($idGost, $idUgostitelj);

        $rezervacija = self::dohvati($id);

        $rezervacija->novokreirana = true;

        return $rezervacija;
    }

    /**
     * @param $idGost
     * @param $idUgostitelj
     *
     * @return int|null
     * @throws Exception
     */
    private static function dohvatiNoviId($idGost, $idUgostitelj)
    {
        try
        {
            $params = [
                "gost"       => $idGost,
                "ugostitelj" => $idUgostitelj
            ];
            $rows = DBfacade::receiveAll("CALL rezervacija__kreiraj(:gost,:ugostitelj)", $params);
            return $rows[0]['id'];
        }
        catch (Exception $e)
        {
            throw new Exception("Unos nove rezervacije nije uspio");
        }
    }

    /**
     * @param $id
     *
     * @return Rezervacija
     * @throws BadMethodCallException
     */
    public static function dohvati($id)
    {
        $rezervacija = new self($id);

        $rezervacija->ucitaj();

        return $rezervacija;
    }

    /**
     * @throws BadMethodCallException
     */
    private function ucitaj()
    {
        $querry = "CALL rezervacija__ucitaj(:id)";
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
                throw new BadMethodCallException("Ne postoji rezervacija s tim identifikatorom");
        }

        $this->idGost = $res['id_gost'];
        $this->idUgostitelj = $res['id_ugostitelj'];
        $this->brojOsoba = $res['broj_osoba'];
        $this->vrijemeNastanka = self::mysqlVrijeme2epoch($res['vremenska_oznaka']);

        $this->vrijemePocetak = self::mysqlVrijeme2epoch($res['vrijeme_pocetak']);
        $this->vrijemeKraj = self::mysqlVrijeme2epoch($res['vrijeme_kraj']);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * zasada i new Gost bi bio dovoljan
     *
     * @return Gost
     */
    public function getGost()
    {
        return Gost::dohvati($this->getIdGost());
    }

    /**
     * @return int
     */
    public function getIdGost()
    {
        return $this->idGost;
    }

    /**
     *
     * ne može new Ugostitelj
     *
     * @see PogledJelovnik
     *
     * @return Ugostitelj
     */
    public function getUgostitelj()
    {
        return Ugostitelj::dohvati($this->getIdUgostitelj());
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
    public function getBrojOsoba()
    {
        return $this->brojOsoba;
    }

    /**
     * @return int
     */
    public function getVrijemeNastanka()
    {
        return $this->vrijemeNastanka;
    }

    /**
     * @return string
     */
    public function dohvatiJela()
    {
        return json_encode(DBfacade::receiveAll("call rezervacija__dohvati_jela(:id)",[
            'id'=>$this->getId()
        ]));
    }

    /**
     * @return string
     */
    public function dohvatiStolove()
    {
        return json_encode(DBfacade::receiveAll("call rezervacija__dohvati_stolove(:id)",[
            'id'=>$this->getId()
        ]));
    }

    /**
     * @param $vrijemePocetak
     * @param $vrijemeKraj
     * @param $izabraniStoloviSpojeni
     * @param $brojGostiju
     *
     * @throws Exception
     */
    public function rezerviraj($vrijemePocetak, $vrijemeKraj, $brojGostiju, $izabraniStoloviSpojeni, $izabranaJelaSpojena)
    {
        try
        {
            $this->osvjeziOsnovno($vrijemePocetak, $vrijemeKraj, $brojGostiju);

            $identifikatoriIzabranihStolova = ($izabraniStoloviSpojeni)?explode('|', $izabraniStoloviSpojeni):[];
            $this->osvjeziStolove($identifikatoriIzabranihStolova,$vrijemePocetak,$vrijemeKraj);

            $izabranaJelaKolicina = ($izabranaJelaSpojena)?explode('|', $izabranaJelaSpojena):[];


            $this->osvjeziJela($izabranaJelaKolicina);

        }
        catch (Exception $e)
        {
            $this->otkazi();
            throw $e;//new Exception("Neuspjela rezervacija");
        }
    }

    /**
     * @param $vrijemePocetak
     * @param $vrijemeKraj
     * @param $brojGostiju
     *
     * @throws Exception
     */
    public function osvjeziOsnovno($vrijemePocetak, $vrijemeKraj, $brojGostiju)
    {
        try
        {
            DBfacade::send(
                "call rezervacija__osvjezi_osnovno(:id,:vrijemePocetak,:vrijemeKraj,:brojOsoba);",
                [
                    'id'             => $this->getId(),
                    'vrijemePocetak' => $vrijemePocetak/1000,
                    'vrijemeKraj'    => $vrijemeKraj/1000,
                    'brojOsoba'    => $brojGostiju
                ]
            );
        }
        catch(Exception $e)
        {
            throw new Exception("Neuspjelo osvjezavanje rezervacije");
        }
    }

    /**
     *
     */
    public function otkazi()
    {

    }

    /**
     * @return int
     */
    public function getVrijemeKraj()
    {
        return $this->vrijemeKraj;
    }

    /**
     * @return int
     */
    public function getVrijemePocetak()
    {
        return $this->vrijemePocetak;
    }

    /**
     * @param $identifikatoriIzabranihStolova
     *
     * @throws Exception
     */
    public function osvjeziStolove($identifikatoriIzabranihStolova,$vrijemePocetak,$vrijemeKraj)
    {
        try
        {
            $this->ukloniSveStolove();
            foreach ($identifikatoriIzabranihStolova as $identifikator)
            {
                $this->dodajStol($identifikator, $vrijemePocetak, $vrijemeKraj);
            }
        }
        catch (Exception $e)
        {
            throw  new Exception("Neuspjelo dodavanje stolova");
        }
    }

    /**
     * @param $jelaWrapped
     *
     * @throws Exception
     *
     */
    public function osvjeziJela($jelaWrapped)
    {
        try
        {
            $this->ukloniSvaJela();
            foreach ($jelaWrapped as $jeloWrapped)
            {
                $jelo = explode(':',$jeloWrapped);
                $this->dodajJelo($jelo[0],$jelo[1]);
            }
        }
        catch (Exception $e)
        {
            throw  new Exception("Neuspjelo dodavanje jela");
        }
    }


    /**
     * @param $idStol
     * @param $vrijemePocetak
     * @param $vrijemeKraj
     *
     * @throws Exception
     */
    private function dodajStol($idStol,$vrijemePocetak,$vrijemeKraj)
    {
        try
        {
            DBfacade::send(
                "CALL rezervacija__dodaj_stol(:id_rezervacija,:id_stol,:vrijeme_pocetak,:vrijeme_kraj)",
                [
                    'id_rezervacija' => $this->getId(),
                    'id_stol'        => $idStol,
                    'vrijeme_pocetak'=>$vrijemePocetak,
                    'vrijeme_kraj'=>$vrijemeKraj
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dodavanje stola " . $idStol);
        }
    }

    /**
     * @throws Exception
     */
    private function ukloniSveStolove()
    {
        try
        {

            DBfacade::send(
                "CALL rezervacija__ukloni_stolove(:id)",
                [
                    'id' => $this->getId()
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo uklanjanje svih stolova");
        }
    }

    /**
     * @throws Exception
     */
    private function ukloniSvaJela()
    {
        try
        {

            DBfacade::send(
                "CALL rezervacija__ukloni_jela(:id)",
                [
                    'id' => $this->getId()
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo uklanjanje svih jela");
        }
    }


    /**
     * @param $idJelo
     * @param $kolicina
     *
     * @throws Exception
     *
     */
    private function dodajJelo($idJelo,$kolicina)
    {
        try
        {
            DBfacade::send(
                "CALL rezervacija__dodaj_jelo(:id_rezervacija,:id_jelo,:kolicina)",
                [
                    'id_rezervacija' => $this->getId(),
                    'id_jelo'        => $idJelo,
                    'kolicina'=>$kolicina
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dodavanje jela " . $idJelo);
        }
    }

    /**
     * @param $idGost
     *
     * @throws Exception
     */
    public function obrisi($idGost)
    {
        try
        {
            DBfacade::send(
                "CALL rezervacija__obrisi(:id_rezervacija,:id_gost)",
                [
                    'id_rezervacija' => $this->getId(),
                    'id_gost'        => $idGost
                ]
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo brisanje rezervacije " . $this->getId());
        }
    }


}