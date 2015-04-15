<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 02.01.15.
 * Time: 15:14
 */


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Jelovnik.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/RasporedStolova.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Stol.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Jelo.php";
require_once DOCUMENT_ROOT . "/podaci/rezerviranje/Rezervacija.php";
require_once DOCUMENT_ROOT . "/podaci/recenziranje/Recenzija.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $msg = null;
    $data = $_POST;

    $trenutniKorisnik = Session::dohvatiTrenutnogKorisnika();


    switch ($data['akcija'])
    {
        case 'dodajStol':
            try
            {
                (new RasporedStolova($data['ugostitelj']))->dodaj(new Stol($data));
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'urediStol':
            try
            {
                (new RasporedStolova($data['ugostitelj']))->uredi(new Stol($data));
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'izbrisiStol':
            try
            {
                (new RasporedStolova($data['ugostitelj']))->ukloni(new Stol($data));
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'dodajJelo':
            try
            {
                $jelovnik = (new Jelovnik($data['ugostitelj']));
                $jelo = new Jelo($data);

                $id = $jelovnik->dodaj($jelo);

                $jelo->setId($id);

                $jelo->osvjeziPonudeJela($data['ponude']);

                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'urediJelo':
            try
            {
                $jelovnik = (new Jelovnik($data['ugostitelj']));
                $jelo = new Jelo($data);

                $jelovnik->uredi($jelo);
                $jelo->osvjeziPonudeJela($data['ponude']);
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'izbrisiJelo':
            try
            {
                (new Jelovnik($data['ugostitelj']))->ukloni(new Jelo($data));
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'dodajPonudaJela':
            try
            {
                (new Jelovnik($data['ugostitelj']))->dodaj(new PonudaJela(null, $data['naziv_ponuda']));
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'urediPonudaJela':
            try
            {
                $jelovnik = (new Jelovnik($data['ugostitelj']));
                $ponuda = new PonudaJela($data['id_ponuda'], $data['naziv_ponuda']);

                $jelovnik->uredi($ponuda);
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'izbrisiPonudaJela':
            try
            {
                (new Jelovnik($data['ugostitelj']))->ukloni(new PonudaJela($data['id_ponuda'], null));
                return;
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'dohvatiDostupnostStolovaUTerminu':
            try
            {
                $stolovi = Rezervacija::dohvatiDostupnostStolovaUTerminu(
                    $data['vrijemePocetak'],
                    $data['vrijemeKraj'],
                    $data['brojGostiju'],
                    $data['idUgostitelj'],
                    $data['rezervacijaId']
                );
                header('Content-Type: application/json');
                die(json_encode($stolovi));
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'rezerviraj':
            try
            {

                $rezervacija = new Rezervacija($data['rezervacijaId']);
                $rezervacija->rezerviraj(
                    $data['vrijemePocetak'],
                    $data['vrijemeKraj'],
                    $data['brojGostiju'],
                    $data['izabraniStolovi'],
                    $data['jela']
                );
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'izbrisiRezervaciju':
            try
            {

                $rezervacija = new Rezervacija($data['rezervacijaId']);
                $rezervacija->obrisi(
                    $trenutniKorisnik->getId()
                );
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'kreirajRecenziju':
            try
            {
                Recenzija::objavi(
                    $data['ugostitelj'],
                    $trenutniKorisnik ? $trenutniKorisnik->getId() : null,
                    $data['ocjena'],
                    $data['tekst']
                );
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'pretragaRestorana':
            try
            {
                header('Content-Type: application/json');
                die(json_encode(Ugostitelj::dohvatiUgostiteljeUzUvjete()));
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'dodajKonobar':
            try
            {
                $trenutniKorisnik->dodajKonobara($data['korisnickoIme'], $data['lozinka']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'izbrisiKonobar':
            try
            {
                $trenutniKorisnik->ukloniKonobara($data['id']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'opskrbiNabavom':
            try
            {
                $trenutniKorisnik->opskrbiNabavom($data['id']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'posaljiNabavu':
            try
            {
                $trenutniKorisnik->posaljiNabavu($data['id']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'odbijNabavu':
            try
            {
                $trenutniKorisnik->odbijNabavu($data['id']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'izbrisiNamirnicu':
            try
            {
                (new StanjeZaliha($trenutniKorisnik->getId()))->ukloni($data['id']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'urediNamirnicu':
            try
            {
                (new StanjeZaliha($trenutniKorisnik->getId()))->uredi($data['id'], $data['vrijednost']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'dodajNamirnicu':
            try
            {
                (new StanjeZaliha($trenutniKorisnik->getId()))->dodaj($data['naziv'], $data['vrijednost']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'naruciNamirnice':
            try
            {

                Nabava::kreiraj($trenutniKorisnik->getId(),$data['namirnice']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'izbrisiUgostitelja':
            try
            {
                $trenutniKorisnik->izbrisiUgostitelja($data['id']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        case 'prihvatiUgostitelja':
            try
            {
                $trenutniKorisnik->prihvatiUgostitelja($data['id']);
                die('OK');
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
                break;
            }
        default:
            $msg = "Neispravan pristup";
            break;
    }
    echo $msg;
}
