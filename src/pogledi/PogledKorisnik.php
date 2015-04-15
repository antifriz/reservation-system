<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledRasporedStolova.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledJelovnik.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledKritika.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledRezerviranja.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Gost.php";


/**
 * Class PogledKorisnik
 */
class PogledKorisnik extends Pogled
{
    /**
     * @param Administrator|Gost|Inspekcija|Ugostitelj|Konobar $korisnik
     *
     */
    private $korisnik;

    /**
     * @var int
     */
    private $idVrsta;

    /**
     * @var PogledRasporedStolova
     */
    private $pogledRasporedStolova;
    /**
     * @var PogledJelovnik
     */
    private $pogledJelovnik;


    /**
     * @var PogledRezerviranja
     */
    private $pogledRezerviranja;
    /**
     * @var
     */
    private $err;
    /**
     * @var PogledKritika
     */
    private $pogledKritika;

    /**
     * @param Administrator|Gost|Inspekcija|Ugostitelj|Konobar $korisnik
     *
     */
    function __construct($korisnik, $err = null)
    {
        $this->korisnik = $korisnik;
        $this->idVrsta = $korisnik->getIdVrsta();
        $this->err = $err;
    }

    /**
     * generiraj pogled s obzirom na vrstu korisnika
     *
     * @throws Exception zasad nek se ne hvata (vjerojatno i nece jer je malo nepotrebna)
     */
    public function generiraj()
    {
        /**
         * generiraj određeni pogled
         *
         * @throws Exception
         */
        switch ($this->idVrsta)
        {
            case Korisnik::GOST:
                $this->korisnik = Session::postaviTrenutnogKorisnika(Gost::dohvati($this->korisnik->getId()));
                $this->pogledRezerviranja = new PogledRezerviranja($this->korisnik);
                $this->pogledKritika = new PogledKritika($this->korisnik);
                $this->genGostinjski();
                break;
            case Korisnik::UGOSTITELJ:
                $this->korisnik = Session::postaviTrenutnogKorisnika(Ugostitelj::dohvati($this->korisnik->getId()));
                $this->pogledJelovnik = new PogledJelovnik($this->korisnik, null);
                $this->pogledRasporedStolova = new PogledRasporedStolova($this->korisnik);

                $this->genUgostiteljski();
                break;
            case Korisnik::ADMIN:
                $this->genAdministratorski();
                break;
            case Korisnik::INSPEKCIJA:
                $this->pogledKritika = new PogledKritika($this->korisnik);
                $this->genInspekcijski();
                break;
            case Korisnik::KONOBAR:
                $this->pogledRezerviranja = new PogledRezerviranja($this->korisnik);
                $this->genKonobarski();
                break;
            case Korisnik::DOBAVLJAC:
                /**
                 * njemu ne treba profil
                 */
                $this->korisnik->preusmjeriNaPocetnuStranicu();
                break;
            default:
                throw new Exception("Nepoznata vrsta korisnika");
                break;
        }
    }


    private function genUgostiteljski()
    {
        ?>
        <div class="ugostitelj-profil container">
            <div class="page-header text-center">
                <h1>
                    <a href="ugostitelj.php?id=<?= $this->korisnik->getId() ?>">
                        <?= $this->korisnik->getImeRestoran(); ?>
                    </a>
                    <small>
                        <?= $this->korisnik->getAdresa() ?>
                    </small>
                </h1>
            </div>
            <?php if ($this->err): ?>
                <div class="alert alert-danger">
                    <strong>Greška! </strong><?= $this->err ?>
                </div>
            <?php endif;?>
            <?php if (!$this->korisnik->jeSveUneseno()): ?>
                <div class="alert alert-warning">
                    <strong>Pozor! </strong>Molimo unesite sve podatke kako bi Vas administrator i inspekcija odobrili
                </div>
            <?php endif;?>
            <div class="row" style="padding-bottom: 20px;border-bottom: 1px solid #e0e0e0">
                <div class="ugostitelj-uredi-podatke col-xs-7">

                    <img class="one_full img-thumbnail " src="<?= $this->korisnik->getUrlSlikeLokala() ?>"
                         alt="Slika lokala" style="margin: 20px auto">

                    <div class="col-xs-6" style="margin-bottom: 20px">
                        <?php
                        $this->generirajGumb(
                            'btn-primary one_full',
                            'glyphicon-file',
                            'PREGLEDAJ PONUDU',
                            null,
                            "/ugostitelj.php"
                        ); ?>
                    </div>
                    <div class="col-xs-6">
                        <?php
                        $this->generirajGumb(
                            'btn-primary one_full',
                            'glyphicon glyphicon-usd',
                            'UPRAVLJAJ POSLOVANJEM',
                            null,
                            "/dobavljanje.php"
                        ); ?>
                    </div>
                    <form class="form" action="" method="post" enctype="multipart/form-data">
                        <div class="col-xs-12">

                            <div class="form-group ">
                                <label for="input-ime-restoran">Ime restorana</label>
                                <input type="text" id="input-ime-restoran" class="form-control" name="imeRestoran"
                                       value="<?= $this->korisnik->getImeRestoran() ?>"
                                       placeholder="Unesite ime restorana" required/>
                            </div>
                        </div>
                        <div class="col-xs-12">

                            <div class="form-group ">
                                <label for="input-adresa">Adresa restorana</label>
                                <input type="text" id="input-adresa" class="form-control" name="adresa"
                                       value="<?= $this->korisnik->getAdresa() ?>"
                                       placeholder="Unesite adresu restorana" required/>
                            </div>
                        </div>
                        <div class="col-xs-12">

                            <div class="form-group ">
                                <label for="input-opis">Opis restorana</label>
                            <textarea id="input-opis" class="form-control" name="opis" rows="3"
                                      placeholder="Unesite kratki opis restorana" required><?= $this->korisnik->getOpis(
                                ) ?></textarea>
                            </div>
                        </div>
                        <div class="col-xs-12">

                            <div class="form-group ">
                                <label for="input-vrste">Vrste restorana</label>
                            <textarea id="input-vrste" class="form-control" name="vrste" rows="2"
                                      placeholder="Unesite vrstu restorana ili više njih odvojene zarezom"
                                      required><?= $this->korisnik->getVrsteRestorana() ?></textarea>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="form-group ">
                                <label for="input-email">Email</label>
                                <input type="email" id="input-email" class="form-control" name="email"
                                       value="<?= $this->korisnik->getEmail() ?>" placeholder="Unesite Vaš email"
                                       required/>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label for="input-slika-lokala">Učitaj novu sliku lokala</label>
                                <input type="file" id="input-slika-lokala" name="lokacijaSlika">
                                <p class="help-block">Izaberite sliku</p>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group ">
                                <label for="input-slika-rasporeda">Učitaj novu sliku stolova</label>
                                <input type="file" id="input-slika-rasporeda" name="rasporedSlika">
                                <p class="help-block">Izaberite sliku</p>
                            </div>
                        </div>
                        <div class="horizontal-line "></div>
                        <div class="col-xs-4">
                            <div class="form-group ">
                                <label for="input-radi-od-radni-dan">Radnim danom:</label>
                                <input type="text" id="input-radi-od-radni-dan" class="form-control timepicker"
                                       placeholder="Radi od" name="roR"
                                       value="<?= $this->korisnik->getRadiOdRadniDan() ?>"/>
                                <br>
                                <input type="text" id="input-radi-do-radni-dan" class="form-control timepicker"
                                       placeholder="Radi do" name="rdR"
                                       value="<?= $this->korisnik->getRadiDoRadniDan() ?>"/>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="form-group ">
                                <label for="input-radi-od-radni-dan">Subotom: </label>
                                <input type="text" id="input-radi-od-radni-dan" class="form-control timepicker"
                                       placeholder="Radi od" name="ros"
                                       value="<?= $this->korisnik->getRadiOdSubota() ?>"/>
                                <br>
                                <input type="text" id="input-radi-do-radni-dan" class="form-control timepicker"
                                       placeholder="Radi do" name="rds"
                                       value="<?= $this->korisnik->getRadiDoSubota() ?>"/>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="form-group ">
                                <label for="input-radi-od-radni-dan">Nedjeljom:</label>
                                <input type="text" id="input-radi-od-radni-dan" class="form-control timepicker"
                                       placeholder="Radi od" name="ron"
                                       value="<?= $this->korisnik->getRadiOdNedjelja() ?>"/>
                                <br>
                                <input type="text" id="input-radi-do-radni-dan" class="form-control timepicker"
                                       placeholder="Radi do" name="rdn"
                                       value="<?= $this->korisnik->getRadiDoNedjelja() ?>"/>
                            </div>
                        </div>
                        <div class="horizontal-line "></div>
                        <div class="col-xs-4">
                            <div class="form-group ">
                                <label for="input-odobren-admin"> Admin odobrio </label>
                                <input type="checkbox" id="input-odobren-admin" class=" form-control"
                                       disabled <?= $this->korisnik->getJePrihvacenAdmin() ? "checked" : "" ?>/>
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="form-group ">
                                <label for="input-odobren-inspekcija"> Inspekcija odobrila </label>
                                <input type="checkbox" id="input-odobren-inspekcija" class="form-control"
                                       disabled <?= $this->korisnik->getJePrihvacenInspekcija() ? "checked" : "" ?>/>
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class=" text-right">
                                <label> Pohrani izmjene </label>
                                <button type="submit" class="btn btn-success btn-pohrani-izmjene-profila">
                                    <span class="glyphicon glyphicon-ok"></span>
                                    Spremi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class=" col-xs-5">
                    <?php $this->pogledRasporedStolova->generirajUredi(false);?>
                </div>
                <div class="col-xs-12">
                    <div class="horizontal-line "></div>
                </div>
                <div class="col-xs-12">
                    <?php $this->generirajUpravljanjeKonobarima(); ?>


                </div>
            </div>

            <div class="row">

                <div class="col-xs-12">
                    <?php $this->pogledJelovnik->generirajUredi(false);?>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     *
     */
    private function genGostinjski()
    {
        ?>
        <div class="container">
            <div class="page-header text-center">
                <h1>
                    <?= $this->korisnik->getNadimak(); ?>
                    <small>korisnik</small>

                </h1>
            </div>
            <?php if ($this->err): ?>
                <div class="alert alert-danger col-xs-7">
                    <strong>Greška! </strong><?= $this->err ?>
                </div>
            <?php endif;?>
            <div class="row" style="...">
                <div class="row" style="padding-bottom: 20px;border-bottom: 1px solid #e0e0e0">
                    <div class="gost-uredi-podatke col-xs-7">

                        <form class="form" action="/profil.php" method="post" enctype="multipart/form-data">
                            <div class="form-group col-xs-12">
                                <label for="input-ime-prezime"> Ime i prezime </label>
                                <input type="text" id="input-ime-prezime" class="form-control" name="ime"
                                       value="<?= $this->korisnik->getImePrezime() ?>" placeholder="Unesite ime" required/>
                            </div>
                            <div class="form-group col-xs-12">
                                <label for="input-email"> Email </label>
                                <input type="email" id="input-email" class="form-control" name="email"
                                       value="<?= $this->korisnik->getEmail() ?>" placeholder="Unesite Vaš email"
                                       required/>
                            </div>
                            <div class="form-group col-xs-12">
                                <label for="input-telefon"> Broj telefona </label>
                                <input type="text" id="input-telefon" class="form-control" name="telefon"
                                       value="<?= $this->korisnik->getTelefon() ?>"/>
                            </div>
                            <div class="form-group col-xs-12">
                                <label for="input-kartica"> Broj kartice </label>
                                <input type="text" id="input-kartica" class="form-control" name="kartica"
                                       value="<?= $this->korisnik->getBrojkartice() ?>"/>
                            </div>
                            <div class="col-xs-4 text-right">
                                <label> Pohrani izmjene </label>
                                <button type="submit" class="btn btn-success btn-pohrani-izmjene-profila">
                                    <span class="glyphicon glyphicon-ok"></span>
                                    Spremi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <div class="row">

                <div class="col-xs-12">
                    <?php $this->pogledRezerviranja->generiraj();?>
                </div>
                <div class="col-xs-12 pogled-kritika">
                    <?php $this->pogledKritika->generiraj();?>
                </div>
           </div>
        </div>
    <?php
    }


    /**
     *
     */
    private function genInspekcijski()
    {
        ?>
        <div class="container">
            <div class="page-header text-center">
                <h1>
                    Inspekcija
                </h1>
            </div>
            <?php if ($this->err): ?>
                <div class="alert alert-danger col-xs-7">
                    <strong>Greška! </strong><?= $this->err ?>
                </div>
            <?php endif;?>
            <div class="row">
                <div class="col-xs-12">
                    <?php $this->generirajNeodobreneUgostitelje();?>
                </div>
                <div class="col-xs-12">
                    <?php $this->pogledKritika->generiraj();?>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     *
     */
    private function genAdministratorski()
    {
        ?>


        <div class="container">
            <div class="page-header text-center">
                <h1>
                    Administrator
                </h1>

            </div>
            
            <?php if ($this->err): ?>
                <div class="alert alert-danger col-xs-7">
                    <strong>Greška! </strong><?= $this->err ?>
                </div>
            <?php endif;?>
            <div class="row">
                <div class="col-xs-12">
                    <?php $this->generirajNeodobreneUgostitelje();?>
                </div>
            </div>


        </div>
    <?php
    }

    protected function generirajJSdodatno()
    {
        /**
         * generiraj određeni JS
         *
         * @throws Exception
         */
        switch ($this->idVrsta)
        {
            case Korisnik::GOST:
                $this->pogledRezerviranja->generirajJSdodatno();
                break;
            case Korisnik::UGOSTITELJ:
                ?>
                <script>
                    function ispravanUnosKonobara(korisnickoIme, lozinka) {
                        if (korisnickoIme.length <= 0) {
                            bootbox.alert('Neispravan unos korisničkog imena konobara');
                            return false;
                        }
                        if (korisnickoIme.length <= 0) {
                            bootbox.alert('Neispravan unos lozinke konobara');
                            return false;
                        }
                        return true;
                    }

                    $(document).ready(function () {
                        $('.ugostitelj-uredi-podatke input.timepicker').timepicker({
                            'lang': TIMEPICKER_LANG,
                            'timeFormat': 'G:i'
                        });


                        $('.btn-dodaj-konobar').click(function () {
                            var korisnickoIme = $('#InputKonobarKorisnickoIme').val();
                            var lozinka = $("#InputKonobarLozinka").val();
                            if (!ispravanUnosKonobara(korisnickoIme, lozinka))
                                return;
                            posaljiPost({
                                akcija: 'dodajKonobar',
                                korisnickoIme: korisnickoIme,
                                lozinka: lozinka
                            }, function (msg) {
                                if (msg == 'OK') {
                                    location.reload();
                                    return;
                                }
                                bootbox.alert(msg);
                            });
                        });
                        $('.btn-izbrisi-konobar').click(function () {
                            var id = $(this).data('id');
                            posaljiPost({
                                akcija: 'izbrisiKonobar',
                                id: id
                            }, function (msg) {
                                if (msg == 'OK') {
                                    location.reload();
                                    return;
                                }
                                bootbox.alert(msg);
                            });
                        });
                    });
                </script>
                <?php
                $this->pogledJelovnik->generirajJSdodatno();
                $this->pogledRasporedStolova->generirajJSdodatno();
                break;
            case Korisnik::ADMIN:
                ?>
                <script>
                    $('.btn-prihvati-ugostitelja').click(function (event) {
                        event = event || window.event;
                        event.stopPropagation();
                        event.preventDefault();

                        var id = $(this).data('id');
                        posaljiPost({
                            akcija: 'prihvatiUgostitelja',
                            id: id
                        }, osvjeziOk);
                    });
                    $('.btn-izbrisi-ugostitelja').click(function (event) {
                        event = event || window.event;
                        event.stopPropagation();
                        event.preventDefault();

                        var id = $(this).data('id');
                        posaljiPost({
                            akcija: 'izbrisiUgostitelja',
                            id: id
                        }, osvjeziOk);
                    });

                </script>
                <?php
                break;
            case Korisnik::INSPEKCIJA:
                break;
            case Korisnik::KONOBAR:
                break;
            default:
                throw new Exception("Nepoznata vrsta korisnika");
                break;
        }
    }

    private function generirajUpravljanjeKonobarima()
    {
        $konobari = $this->korisnik->dohvatiKonobare();

        ?>
        <div class="text-center" style="margin-bottom: 30px">
            <h3>
                Konobari
            </h3>
        </div>
        <div class="<?= count($konobari) > 0 ? "lista-blok-holder" : "" ?>">
            <?php
            foreach ($konobari as $konobar): ?>
                <div class="row lista-blok" style="padding: 10px 15px">
                    <div class="col-xs-10">
                        <span class="glyphicon glyphicon-user"></span>
                        <span><?= $konobar['korisnicko_ime'] ?></span>
                    </div>
                    <div class=" col-xs-2">
                        <?php
                        $this->generirajGumb(
                            'btn-danger btn-izbrisi-konobar one_full" data-id="' . $konobar['id'] . '"',
                            'glyphicon-remove',
                            'IZBRIŠI'

                        ); ?>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
        <div class="row" style="margin: 20px 0;">
            <div class="col-xs-5">
                <input class="form-control" type="text" placeholder="Unesi korisničko ime konobara" name="korisnickoIme"
                       id="InputKonobarKorisnickoIme">
            </div>
            <div class="col-xs-5">
                <input class="form-control" type="text" id="InputKonobarLozinka" placeholder="Unesi lozinku konobara"
                       name="lozinka">
            </div>
            <div class="col-xs-2">
                <?php
                $this->generirajGumb(
                    'btn-success btn-dodaj-konobar one_full',
                    'glyphicon-plus',
                    'DODAJ'
                );?>
            </div>
        </div>
    <?php
    }

    private function genKonobarski()
    {
        ?>
        <div class="col-xs-12 text-center">
                    <?php $this->generirajGumb('btn btn-primary','glyphicon-edit','REZERVIRAJ',null,'/rezervacija.php?kreiraj='.$this->korisnik->getIdUgostitelj()); ?>
        </div>
        <br>
        <?php
        $this->pogledRezerviranja->generiraj();
    }

    private function generirajNeodobreneUgostitelje()
    {
        $neodobreniUgostitelji = $this->korisnik->dohvatiNeodobreneUgostitelje();
        $jeAdmin = $this->korisnik->getIdVrsta() == Korisnik::UGOSTITELJ;
        ?>
        <div class="text-center" style="margin-bottom: 30px">
            <h3>
                Odobravanje ugostitelja
            </h3>
        </div>
        <div class="one_full" style="padding: 10px; line-height: 24px; font-weight: bold">
            <div class="col-xs-1">
                ID
            </div>
            <div class="col-xs-3">
                <?= $jeAdmin ? "Korisničko ime" : "Ime restorana" ?>
            </div>
            <div class="col-xs-4">
                <?= $jeAdmin ? "Ime restorana" : "Adresa" ?>
            </div>
        </div>
        <div class="lista-blok-holder one_full">
            <?php
            foreach ($neodobreniUgostitelji as $ugostitelj)
                $this->generirajBlokUgostitelja($ugostitelj);
            ?>
        </div>
        <div class="one_full" style="padding: 40px;">
            
        </div>
    <?php
    }

    /**
     * @param string[] $ugostitelj
     */
    private function generirajBlokUgostitelja($ugostitelj)
    {
        $jeAdmin = $this->korisnik->getIdVrsta() == Korisnik::ADMIN;
        ?>
        <a href="/ugostitelj.php?id=<?= $ugostitelj['id_ugostitelj'] ?>" style="color:inherit">
            <div class="lista-blok lista-blok-clickable one_full">
                <div class="col-xs-1">
                    <?= $ugostitelj['id_ugostitelj'] ?>
                </div>
                <div class="col-xs-3">
                    <?= $jeAdmin ? $ugostitelj['ime_restoran'] : $ugostitelj['ime_restoran'] ?>
                </div>
                <div class="col-xs-<?= $jeAdmin ? 4 : 6 ?>">
                    <?= $jeAdmin ? $ugostitelj['adresa'] : $ugostitelj['adresa'] ?>
                </div>
                <?php if ($jeAdmin): ?>
                    <?php
                    if (!$ugostitelj['prihvacen_admin']):?>
                        <div class="col-xs-2">
                            <?php
                            $this->generirajGumb(
                                'btn-success btn-prihvati-ugostitelja one_full',
                                'glyphicon-ok',
                                'PRIHVATI',
                                'data-id="' . $ugostitelj['id'] . '"'
                            ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="col-xs-2 col-xs-offset-<?=($ugostitelj['prihvacen_admin'])?2:0  ?>">
                        <?php
                        $this->generirajGumb(
                            'btn-danger btn-izbrisi-ugostitelja one_full',
                            'glyphicon-remove',
                            'IZBRIŠI',
                            'data-id="' . $ugostitelj['id'] . '"'
                        ); ?>
                    </div>

                <?php endif;?>
            </div>
        </a>
    <?php
    }

}