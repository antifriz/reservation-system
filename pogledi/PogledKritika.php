<?php


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";

/**
 * Class PogledKritika
 */
class PogledKritika extends Pogled
{
    /**
     * @var array
     */
    private $kritike;

    /**
     * @var mixed
     */
    private $ocjene;

    /**
     * @var Ugostitelj|Gost
     */
    private $korisnik;


    /**
     * @var bool
     */
    private $jeUgostitelj;


    /**
     * @var bool
     */
    private $inspekcijaProgovorila;

    /**
     * @param Ugostitelj|Gost|Inspekcija $korisnik
     *
     * @throws Exception
     */
    function __construct($korisnik)
    {
        $this->korisnik = $korisnik;

        $this->jeUgostitelj = $korisnik->getIdVrsta() == Korisnik::UGOSTITELJ;


        $this->kritike = $korisnik->dohvatiRecenzije();

        if ($this->jeUgostitelj)
        {
            $this->ocjene = $korisnik->dohvatiOcjena();

            $this->inspekcijaProgovorila = ($this->ocjene['inspekcija_ocjena'] > 1);
        }

        if (($this->korisnik->getIdVrsta() == Korisnik::GOST) || (($this->korisnik->getIdVrsta(
                ) == Korisnik::INSPEKCIJA))
        )
        {
            $this->ocjene = $korisnik->dohvatiOcjene();


        }


    }


    public function generiraj()
    {

        ?>
        <div class="pogled-kritika">
            <?php
            if (!$this->jeUgostitelj)
            {

                $this->generirajKritike($this->kritike);
            }
            elseif ($this->inspekcijaProgovorila)
            {
                if (Session::jeInspekcija() or Session::jeGost() or !Session::jePrijavljen())
                    $this->generirajUnos();
                $this->generirajKritike($this->kritike);
            }
            elseif (Session::jeInspekcija())
            {
                $this->generirajUnos();
            }
            else
            {
                ?>

                <div class="col-xs-offset-2 col-xs-10 ">
                    <h3 class="media-sub-header reviews">Komentiranje nije dostupno</h3>

                </div>

                <div class="col-xs-offset-2 col-xs-10">
                    <div class="horizontal-line"></div>
                </div>
            <?php

            }
            ?>

        </div>
    <?php


    }


    private function generirajUnos()
    {
        ?>
        <div class="form-horizontal">
            <div class="col-xs-offset-2 col-xs-10 ">
                <h3 class="media-sub-header reviews">Ostavite svoju recenziju</h3>

            </div>
            <div class="form-group">
                <label for="postaviOcjenu" class="col-sm-2 control-label">Ocjena</label>

                <div class="col-sm-2" style="width: 170px;">
                    <div class="form-control" id="postaviOcjenu" style="cursor:pointer;">
                        <a>
                            <span class="glyphicon glyphicon-star" data-id="1"></span>
                        </a>
                        <a>
                            <span class="glyphicon glyphicon-star" data-id="2"></span>
                        </a>
                        <a>
                            <span class="glyphicon glyphicon-star" data-id="3"></span>
                        </a>
                        <a>
                            <span class="glyphicon glyphicon-star" data-id="4"></span>
                        </a>
                        <a>
                            <span class="glyphicon glyphicon-star-empty" data-id="5"></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="addComment" class="col-sm-2 control-label">Komentar</label>

                <div class="col-sm-10">
                                            <textarea class="form-control" name="addComment" id="addComment"
                                                      rows="5"></textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button class="btn btn-success btn-circle text-uppercase" id="submitComment">
                        <span class="glyphicon glyphicon-send"></span>
                        Pošalji
                    </button>
                </div>
            </div>
        </div>
        <div class="col-xs-offset-2 col-xs-10">
            <div class="horizontal-line"></div>
        </div>

    <?php
    }

    public function generirajJSdodatno()
    {



        ?>
            <script>
                function osvjeziKritiku(err) {
                    if (err == 'OK') {
                        location.reload();
                        return
                    }
                    bootbox.alert(err);
                }


                $(function () {

                    var spanovi = $("#postaviOcjenu").find(">a>span");
                    spanovi.click(function () {
                        var span = $(this);
                        var id = parseInt(span.data('id'));

                        spanovi.each(function () {
                            var to = $(this);
                            var isBigger = parseInt(to.data('id')) > id;
                            to.addClass(isBigger ? 'glyphicon-star-empty' : 'glyphicon-star');
                            to.removeClass(isBigger ? 'glyphicon-star' : 'glyphicon-star-empty');
                        });
                    });
                    function procitajOcjenu() {
                        var maxStar = 1;
                        spanovi.each(function () {
                            var to = $(this);
                            var id = parseInt(to.data('id'));
                            maxStar = id > maxStar && to.hasClass('glyphicon-star') ? id : maxStar;
                        });
                        return maxStar;
                    }

                    $("#submitComment").click(function () {

                        var data = {
                            akcija: "kreirajRecenziju",
                            ugostitelj: ugostitelj,
                            tekst: $('#addComment').val(),
                            ocjena: procitajOcjenu()
                        };


                        if ((data['tekst']).length > <?php if(Session::dohvatiTrenutnogKorisnika() and Session::dohvatiTrenutnogKorisnika()->getIdVrsta()==Korisnik::INSPEKCIJA) echo 1000; else echo 500; ?>) {
                            bootbox.alert("Komentar mora biti ispod " + <?php if(Session::dohvatiTrenutnogKorisnika() and Session::dohvatiTrenutnogKorisnika()->getIdVrsta()==Korisnik::INSPEKCIJA) echo 1000; else echo 500; ?> + " znakova!");
                            return;

                        }


                        posaljiPost(data, osvjeziKritiku);
                    });

                });

            </script>
        <?php

    }

    /**
     * @param $kritika
     */
    private function generirajKritiku($kritika)
    {
        $date = explode('-', $kritika['datum']);
        $ocjena = $kritika['ocjena'];
        ?>
        <li class="media">
            <div class="col-sm-2 ">
                <div class="media-object center-block">
                    <h1 class="rating-num">
                        <?= $ocjena ?></h1>

                    <div class="rating">
                        <span class="glyphicon glyphicon-star<?= $ocjena < 0.5 ? '-empty' : '' ?>"></span>
                        <span class="glyphicon glyphicon-star<?= $ocjena < 1.5 ? '-empty' : '' ?>"></span>
                        <span class="glyphicon glyphicon-star<?= $ocjena < 2.5 ? '-empty' : '' ?>"></span>
                        <span class="glyphicon glyphicon-star<?= $ocjena < 3.5 ? '-empty' : '' ?>"></span>
                        <span class="glyphicon glyphicon-star<?= $ocjena < 4.5 ? '-empty' : '' ?>"></span>
                    </div>
                </div>
            </div>
            <div class="media-body col-sm-10">
                <div class="well well-lg">
                    <h4 class="media-heading text-uppercase reviews"><?= $kritika['nadimak'] ?></h4>
                    <ul class="media-date text-uppercase reviews list-inline">
                        <li class="dd"><?= $date[0] ?></li>
                        <li class="mm"><?= $date[1] ?></li>
                        <li class="aaaa"><?= $date[2] ?></li>
                    </ul>
                    <p class="media-comment">
                        <?= $kritika['tekst'] ?>
                    </p>

                </div>
            </div>
        </li>
    <?php
    }

    /**
     * @param $kritike
     */
    private function generirajKritike($kritike)
    {
        /*  $data=$this->ocjene;*/
        ?>
        <?php if ($this->jeUgostitelj): ?>

        <div class="col-xs-offset-2 col-xs-10 ">
            <h3 class="media-sub-header reviews">Recenzije<?= $this->jeUgostitelj ? ' korisnika' : '' ?></h3>
        </div>
    <?php else: ?>
        <div class="page-header text-center">
            <h3>
                Recenzije
                <?php if (count($this->kritike) <= 0): ?>
                    <small> nema recenzija</small><?php endif; ?>
            </h3>
        </div>

        <div class="col-xs-offset-2 col-xs-10">
            <?php $this->generirajOcjenuCijelu(); ?>
        </div>




    <?php endif; ?>
        <div class="one_full">

            <ul class="media-list">
                <?php
                foreach ($kritike as $kritika)
                {
                    if ($kritika['id_vrsta'] != Korisnik::INSPEKCIJA)
                        $this->generirajKritiku($kritika);
                }
                ?>
            </ul>
        </div>
        <div class="col-xs-offset-2 col-xs-10">
            <div class="horizontal-line"></div>
        </div>
    <?php
    }


    /**
     */
    public function generirajOcjenuCijelu()
    {
        $data = $this->ocjene;
        ?>
        <div class="one_full">
            <div class="well well-sm">
                <div class="row">
                    <div class="col-xs-12 col-md-4 text-center">
                        <?php $this->generirajOcjenuGlavniDio($data['ukupna_ocjena'], $data['broj_ocjena']) ?>
                    </div>
                    <div class="col-xs-12 col-md-8">
                        <?php $this->generirajOcjenuStatistika($data); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * @param      $ocjena
     * @param null $brojOcjena
     */
    private function generirajOcjenuGlavniDio($ocjena, $brojOcjena = null)
    {

        ?>
        <h1 class="rating-num">
            <?= $ocjena < 1 ? "-.-" : $ocjena; ?></h1>

        <div class="rating">
            <span class="glyphicon glyphicon-star<?= $ocjena >= 0.5 ? "" : "-empty" ?>"></span><span
                class="glyphicon glyphicon-star<?= $ocjena >= 1.5 ? "" : "-empty" ?>">
                            </span>
            <span class="glyphicon glyphicon-star<?= $ocjena >= 2.5 ? "" : "-empty" ?>"></span><span
                class="glyphicon glyphicon-star<?= $ocjena >= 3.5 ? "" : "-empty" ?>"></span><span
                class="glyphicon glyphicon-star<?= $ocjena >= 4.5 ? "" : "-empty" ?>"></span>
        </div>
        <div>
            <?php if ($brojOcjena): ?>
                <span class="glyphicon glyphicon-user"></span>
                <?= $brojOcjena ?> ukupno
            <?php else: ?>
                <div style="height: 20px;"></div>
            <?php endif; ?>

        </div>
    <?php
    }

    public function generirajOcjenuInspekcijeCijelu()
    {
        $data = $this->ocjene;
        ?>

        <div class="one_full">
            <div class="well well-sm">
                <div class="row">
                    <div class="col-xs-12 col-md-4 text-center">
                        <?php $this->generirajOcjenuGlavniDio($data['inspekcija_ocjena'] . ".0") ?>
                    </div>

                    <div class="col-xs-12 col-md-8 text-center">
                        <?php $this->generirajOcjenuOdobrilaInspekcija($data['inspekcija_ocjena']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <blockquote style="text-align: justify">
                            <p class="lead" ><?= str_replace("\n", '<br>', $data['inspekcija_tekst']) ?></p>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * @param $data
     */
    private function generirajOcjenuStatistika($data)
    {
        ?>
        <div class="row rating-desc">
            <div class="col-xs-3 col-md-3 text-right">
                <span class="glyphicon glyphicon-star"></span>
                5
            </div>
            <div class="col-xs-8 col-md-9">
                <div class="progress progress-striped">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="20"
                         aria-valuemin="0" aria-valuemax="100" style="width: <?= $data['udio_5'] ?>%"></div>
                </div>
            </div>
            <!-- end 5 -->
            <div class="col-xs-3 col-md-3 text-right">
                <span class="glyphicon glyphicon-star"></span>
                4
            </div>
            <div class="col-xs-8 col-md-9">
                <div class="progress">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="20"
                         aria-valuemin="0" aria-valuemax="100" style="width: <?= $data['udio_4'] ?>%"></div>
                </div>
            </div>
            <!-- end 4 -->
            <div class="col-xs-3 col-md-3 text-right">
                <span class="glyphicon glyphicon-star"></span>
                3
            </div>
            <div class="col-xs-8 col-md-9">
                <div class="progress">
                    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="20" aria-valuemin="0"
                         aria-valuemax="100" style="width: <?= $data['udio_3'] ?>%"></div>
                </div>
            </div>
            <!-- end 3 -->
            <div class="col-xs-3 col-md-3 text-right">
                <span class="glyphicon glyphicon-star"></span>
                2
            </div>
            <div class="col-xs-8 col-md-9">
                <div class="progress">
                    <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="20"
                         aria-valuemin="0" aria-valuemax="100" style="width: <?= $data['udio_2'] ?>%">
                        <span class="sr-only"></span>
                    </div>
                </div>
            </div>
            <!-- end 2 -->
            <div class="col-xs-3 col-md-3 text-right">
                <span class="glyphicon glyphicon-star"></span>
                1
            </div>
            <div class="col-xs-8 col-md-9">
                <div class="progress">
                    <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="80"
                         aria-valuemin="0" aria-valuemax="100" style="width: <?= $data['udio_1'] ?>%">
                        <span class="sr-only"></span>
                    </div>
                </div>
            </div>
            <!-- end 1 -->
        </div>
    <?php
    }

    /**
     * @param string[] $ocjena
     */
    private function generirajOcjenuOdobrilaInspekcija($ocjena)
    {

        ?>

        <div
            class=" btn btn-<?= $ocjena > 1 ? "success" : "danger" ?> btn-no-hover-<?= $ocjena > 1 ? "success" : "danger" ?>"
            style="margin-top: 34px;">
            <span class="lead"><span
                    class="glyphicon glyphicon-<?= $ocjena > 1 ? "ok" : "remove" ?>"></span> <?= $ocjena > 1 ? "" : "NIJE " ?>
                ODOBRILA INSPEKCIJA</span>
        </div>
    <?php
    }

    public function generirajOcjene()
    {
        if($this->ocjene['inspekcija_ocjena'] == 1)
            $this->generirajOcjenuInspekcijeCijelu();

        if ($this->inspekcijaProgovorila)
        {
            if ($this->ocjene['ukupna_ocjena'] >= 1)
                $this->generirajOcjenuCijelu();
            $this->generirajOcjenuInspekcijeCijelu();
        }
        else
        {
            ?>
            <div style="min-height: 1px"></div>
        <?php
        }
    }

    /**
     * @return boolean
     */
    public function isInspekcijaProgovorila()
    {
        return $this->inspekcijaProgovorila;
    }
}

