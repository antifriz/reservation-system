<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 12/22/14
 * Time: 5:02 PM
 */


/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/DBfacade.php";

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";

defined('DNEVNIK_PUTANJA') or define('DNEVNIK_PUTANJA', DOCUMENT_ROOT . "/podaci/administratorstvo/dnevnik.txt");

/**
 * Class Administrator
 */
class Administrator extends Korisnik
{


    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param $id
     *
     * @return Dobavljac
     */
    public static function dohvati($id)
    {
        $korisnik = new self($id);

        $korisnik->ucitaj();

        return $korisnik;
    }


    /**
     *
     */
    public function prikaziAdminDnevnik()
    {

        $file = file_get_contents(DNEVNIK_PUTANJA);
        $convert = explode("\n", $file);
        for ($i = 0; $i < count($convert); $i++)
        {
            echo $convert[$i];
            echo "<br>";
        }
        echo "<br>";

        /* $myFile = "dne.txt";
         $fh = fopen($myFile, 'r');
         if(!$fh) echo "greskaaaaaa";
         $theData = fgets($fh);
         fclose($fh);
         echo $theData;*/

        /* $handle = fopen("dne.txt", "r");
         if ($handle) {
             while (($line = fgets($handle)) !== false) {
                 echo $line;
             }
         } else {
             echo "error";
         }
         fclose($handle);*/

        //echo "admindnevki";

    }


    /**
     * @return array
     * @throws Exception
     */
    public function dohvatiNeodobreneUgostitelje() //todo: call //todo:ipak sam đubre pa dohvaćam sve
    {
        try
        {
            return DBfacade::receiveAll(
                "SELECT *
                 FROM ugostitelj JOIN korisnik ON korisnik.id = ugostitelj.id_ugostitelj
                 ORDER BY korisnik.vrijeme_dodavanja DESC",
                []
            );
        }
        catch (Exception $e)
        {
            throw new Exception("Neuspjelo dohvaćanje neodobrenih ugostitelja.");
        }
    }

    /**
     * @param int $idUgostitelj
     *
     * @throws Exception
     */
    public function prihvatiUgostitelja($idUgostitelj)
    {
        (new Ugostitelj($idUgostitelj))->odobriOdAdmina();
    }

    /**
     * @param int $idUgostitelj
     *
     * @throws Exception
     */
    public function izbrisiUgostitelja($idUgostitelj)
    {
        (new Ugostitelj($idUgostitelj))->izbrisi();
    }


    /**
     * @param $xml
     */
    public function dodajUBazu($xml)
    {
    }
} 