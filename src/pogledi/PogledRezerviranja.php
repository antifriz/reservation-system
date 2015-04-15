<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/podaci/rezerviranje/Rezervacija.php";

/**
 * Class PogledRezerviranja
 */
class PogledRezerviranja extends Pogled
{

    /**
     * @var Gost|Konobar
     */
    private $korisnik;

    /**
     * @var array
     */
    private $rezervacije;

    /**
     * @var bool
     */
    private $jeGost;

    /**
     * @param $korisnik
     *
     * @throws Exception
     * @throws UnexpectedValueException
     */
    function __construct($korisnik)
    {
        $this->korisnik = $korisnik;
        $this->jeGost = ($this->korisnik->getIdVrsta() == Korisnik::GOST);

        if ($this->jeGost)
        {
            $gost = new Gost($this->korisnik->getId());
            $this->rezervacije = $gost->dohvatiRezervacije();
        }
        elseif ($this->korisnik->getIdVrsta() == Korisnik::KONOBAR)
        {
            $ugostitelj = new Ugostitelj($this->korisnik->getIdUgostitelj());

            $this->rezervacije = $ugostitelj->dohvatiRezervacije();

        }
        else
            throw new UnexpectedValueException();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function generiraj()
    {

        ?>
        <div class="container" id="rezerviranja" style="margin-bottom: 40px">
            <div class="row lista-blok-whole">
                <div class=" row">
                    <div class="page-header text-center">
                        <h3>
                            Rezervacije
                            <?php if(count($this->rezervacije)<=0):?><small> nema rezervacija</small><?php endif;?>
                        </h3>
                    </div>
                    <?php if (count($this->rezervacije) > 0): ?>
                        <div class="col-xs-2">
                            <span class="lead" style="font-size: 16px;padding-left: 41px">
                                <strong>REDNI BROJ</strong>
                            </span>
                        </div>
                        <div class="col-xs-4">
                            <span class="lead" style="font-size: 16px;padding-left: 33px">
                                <strong><?= $this->jeGost ? "RESTORAN" : "O GOSTU" ?></strong>
                            </span>
                        </div>
                        <div class="col-xs-2">
                            <span class="lead" style="font-size: 16px;padding-left: 25px">
                                <strong>BROJ OSOBA</strong>
                            </span>
                        </div>
                        <div class="col-xs-4">
                            <span class="lead" style="font-size: 16px;padding-left: 21px">
                                <strong>REZERVIRANI TERMIN</strong>
                            </span>
                        </div>
                    <?php endif;?>
                </div>
                <?php if (count($this->rezervacije) > 0): ?>

                    <div class="lista-blok-holder">
                        <?php

                        $this->generirajRezervacije();
                        ?>
                    </div>
                <?php endif;?>

            </div>
        </div>
    <?php
    }

    protected function generirajJSdodatno()
    {
        ?>
        <script>
            $(function () {
                $('.btn-izbrisi').click(function (event) {
                    event = event || window.event;
                    event.stopPropagation();
                    event.preventDefault();


                    var id = $(this).data('id');
                    posaljiPost({
                            akcija: 'izbrisiRezervaciju',
                            rezervacijaId: id
                        }
                        , function (msg) {
                            if (msg === 'OK') {
                                location.reload();
                                return;
                            }
                            bootbox.alert('Neuspjelo brisanje rezervacije');
                        });
                });
            })

        </script>
    <?php
    }

    /**
     * @param $rezervacija
     *//*
    private function generirajRezervacijaGost($rezervacija)
    {
        ?>
        <div class="lista-blok lista-blok-clickable row">
            <a href="rezervacija.php?id=<?= $rezervacija['id_rezervacija'] ?>" style="color:inherit;">

                <div class="col-xs-2">
                    <span class="glyphicon glyphicon-flag text-primary"></span> <?= $rezervacija['id_rezervacija'] ?>

                </div>
                <div class="col-xs-4">
                    <div class="hidden"><?= $rezervacija['id_ugostitelj'] ?></div>
                    <span class="glyphicon glyphicon-user text-primary"></span>
                    <strong><?= $rezervacija['ime_restoran'] ?></strong>
                </div>
                <div class="col-xs-2">
                    <span class="glyphicon glyphicon-tags text-primary"></span> <?= $rezervacija['broj_osoba'] ?>
                </div>
                <div class="col-xs-4">
                    <span class="glyphicon glyphicon-time text-primary"></span> <?= date(
                        "d.m.Y H:i",
                        $rezervacija['vrijeme_pocetak']
                    ) . " - " . date(
                        "H:i",
                        $rezervacija['vrijeme_kraj']
                    ) ?>
                </div>
                <div class="col-xs-offset-2 col-xs-4 small">
                    <span class="glyphicon glyphicon-map-marker text-primary"></span> <?= $rezervacija['adresa'] ?>
                </div>
                <div class="col-xs-6">
                    <div class="pull-right">
                        <?php $this->generirajGumb(
                            "btn-danger btn-izbrisi one_full",
                            "glyphicon-remove",
                            "IZBRIŠI",
                            "data-id='" . $rezervacija['id_rezervacija'] . "'"
                        )?>
                    </div>

                </div>
                <div class="col-xs-12 text-center">

                </div>
            </a>

        </div>
    <?php
    }*/

    /**
     * @param $rezervacija
     */
    private function generirajRezervacija($rezervacija)
    {
        $fact = 5;

        $relPoc = $rezervacija['relPocetak'];

        $relKraj = $rezervacija['relKraj'];
        $boja = 'success';
        if ($relPoc < 0)
            $boja = 'danger';
        if ($relKraj > 101)
            $boja = 'info';
        if ($relPoc >= 100 - $fact)
            $relPoc = 100 - $fact;
        if ($relPoc < 0)
            $relPoc = 0;
        if ($relKraj > 100)
            $relKraj = 100;
        if ($relKraj < $fact)
            $relKraj = $fact;

        $relDulj = $relKraj - $relPoc;

        if ($rezervacija['vrijeme_pocetak'] < time())
            $boja = 'warning';
        if ($rezervacija['vrijeme_kraj'] < time())
            $boja = 'danger';
        ?>
        <div class="lista-blok lista-blok-clickable row">
            <a href="/rezervacija.php?id=<?= $rezervacija['id_rezervacija'] ?>" style="color:inherit;">
                <div class="col-xs-2">
                    <span class="glyphicon glyphicon-flag text-primary"></span> <?= $rezervacija['id_rezervacija'] ?>

                </div>

                <div class="col-xs-4">
                    <?php if ($this->jeGost): ?>
                        <div class="hidden"><?= $rezervacija['id_ugostitelj'] ?></div>
                        <span class="glyphicon glyphicon-user text-primary"></span>
                        <strong><?= $rezervacija['ime_restoran'] ?></strong>
                    <?php else: ?>
                        <div class="hidden"><?= $rezervacija['id_gost'] ?></div>
                        <span class="glyphicon glyphicon-user text-primary"></span>
                        <strong><?= empty($rezervacija['ime_prezime'])?"Konobar":$rezervacija['ime_prezime'] ?></strong>
                    <?php endif;?>
                </div>
                <div class="col-xs-2">
                    <span class="glyphicon glyphicon-tags text-primary"></span> <?= $rezervacija['broj_osoba'] ?>
                </div>
                <div class="col-xs-4">
                    <span class="glyphicon glyphicon-time text-primary"></span> <?= date(
                        "d.m.Y H:i",
                        $rezervacija['vrijeme_pocetak']
                    ) . " - " . date(
                        "H:i",
                        $rezervacija['vrijeme_kraj']
                    ) ?>
                </div>
                <?php if ($this->jeGost): ?>
                    <div class="col-xs-offset-2 col-xs-4 small">
                        <span class="glyphicon glyphicon-map-marker text-primary"></span> <?= $rezervacija['adresa'] ?>
                    </div>
                    <div class="col-xs-offset-4 col-xs-2">
                        <?php $this->generirajGumb(
                            "btn-danger btn-izbrisi one_full",
                            "glyphicon-remove",
                            "IZBRIŠI",
                            "data-id='" . $rezervacija['id_rezervacija'] . "'"
                        ) ?>
                    </div>
                <?php else: ?>
                    <div class="col-xs-offset-2 col-xs-4 small">
                        <span
                            class="glyphicon glyphicon-credit-card text-primary"></span> <?= empty($rezervacija['br_kartice'])?"-":$rezervacija['br_kartice'] ?>
                        <br>
                        <span
                            class="glyphicon glyphicon-phone-alt text-primary"></span> <?= empty($rezervacija['br_telefona'])?"-":$rezervacija['br_telefona'] ?>

                    </div>
                    <div class="col-xs-6" style="padding-top:20px">

                        <div class="progress bottom " style="background: #fcf8e3">
                            <div class="progress-bar" style="width: <?= $relPoc ?>%;visibility:hidden ;"></div>
                            <div class="progress-bar progress-bar-<?= $boja ?> progress-bar-striped active"
                                 role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                 style="width: <?= $relDulj ?>%;border-radius: 7px"></div>
                        </div>
                    </div>
                    <div class="col-xs-12 text-center">

                    </div>
                <?php endif;?>
                </a>
        </div>
    <?php
    }

    /**
     * @throws Exception
     * @throws UnexpectedValueException
     *
     */
    private function generirajRezervacije()
    {
        $rezervacije = $this->rezervacije;

        foreach ($rezervacije as $rezervacija)
            $this->generirajRezervacija($rezervacija);
    }
}