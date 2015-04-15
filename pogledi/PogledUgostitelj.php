<?php



defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledKritika.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledJelovnik.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledRasporedStolova.php";

/**
 * Class PogledUgostitelj
 */
class PogledUgostitelj extends Pogled
{

    /**
     * @var Ugostitelj
     */
    private $ugostitelj;

    /**
     * @var PogledKritika
     */
    private $pogledKritika;

    /**
     * @var PogledRasporedStolova
     */
    private $pogledRasporedStolova;

    /**
     * @var PogledJelovnik
     */
    private $pogledJelovnik;

    /**
     * @param Ugostitelj $ugostitelj
     */
    function __construct($ugostitelj)
    {
        $this->ugostitelj = $ugostitelj;
        $this->pogledKritika = new PogledKritika($ugostitelj);
        $this->pogledRasporedStolova = new PogledRasporedStolova($ugostitelj);
        $this->pogledJelovnik = new PogledJelovnik($ugostitelj, Session::dohvatiTrenutnogKorisnika());

    }

    public function generiraj()
    {
        $ugostitelj = $this->ugostitelj;
        ?>

        <div class="parallax-window" data-parallax="scroll" style="margin-top: -50px"
             data-image-src="<?= $ugostitelj->getUrlSlikeLokala() ?>"></div>

        <div class="pogled-ugostitelj container">
            <div class="one_full" style="min-height: 119px">
                <div class="page-header  text-center"
                     style="z-index: 200; width: 100%; background: #ffffff;border-bottom: 1px solid #ddd;">
                    <h1>
                        <?= $this->ugostitelj->getImeRestoran(); ?>
                        <small>
                            <?= $this->ugostitelj->getAdresa() ?>
                        </small>
                    </h1>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <blockquote class="one_full">
                        <p class="lead"><?= str_replace("\n", '<br>', $this->ugostitelj->getOpis()) ?></p>
                    </blockquote>
                    <br><br>

                    <div class="one_full">
                        <div class="well well-sm" style="margin-top: 25px;font-size: 16px">
                            <b>Vrsta restorana: </b>
                            <?= $this->ugostitelj->getVrsteRestorana() ?>
                            <br> <b>Adresa: </b>
                            <?= $this->ugostitelj->getAdresa() ?>
                            <br> <b>E-mail: </b>
                            <?= $this->ugostitelj->getEmail() ?>
                            <br><b>Radnim danom: </b>
                            <?= substr($this->ugostitelj->getRadiOdRadniDan(),0,-3) ?> - <?= substr($this->ugostitelj->getRadiDoRadniDan(
                            ),0,-3) ?>
                            <br> <b>Subotom: </b>
                            <?= substr($this->ugostitelj->getRadiOdSubota(),0,-3) ?> - <?= substr($this->ugostitelj->getRadiDoSubota(),0,-3) ?>
                            <br> <b>Nedjeljom: </b>
                            <?= substr($this->ugostitelj->getRadiOdNedjelja(),0,-3) ?> - <?= substr($this->ugostitelj->getRadiDoNedjelja(
                            ),0,-3) ?>


                        </div>
                    </div>


                </div>
                <div class="col-xs-6">
                    <p class="one_full" style="margin-bottom: 20px">
                        <a href="<?= Session::jePrijavljen() ? "/rezervacija.php?kreiraj=".$this->ugostitelj->getId()  : "/prijava.php" ?>"
                           class="btn btn btn-primary one_full">
                            <span class="glyphicon glyphicon glyphicon-edit"></span>
                            <span class="lead">REZERVIRAJ</span>
                        </a>
                    </p>
                    <?php $this->pogledKritika->generirajOcjene(); ?>
                </div>
            </div>
            <div class="col-xs-6 col-xs-offset-6"></div>
            <div role="tabpanel" class="row" style="margin-bottom: 40px">


                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    <?php if ($this->pogledKritika->isInspekcijaProgovorila()): ?>
                        <li role="presentation" class="active">
                            <a href="#recenzije-tab" aria-controls="home" role="tab" data-toggle="tab">Recenzije</a>
                        </li><?php endif; ?>
                    <li role="presentation"
                        class="<?= $this->pogledKritika->isInspekcijaProgovorila() ? "" : "active"; ?>">
                        <a href="#jelovnik-tab" aria-controls="profile" role="tab" data-toggle="tab">Jelovnik</a>
                    </li>
                    <li role="presentation">
                        <a href="#raspored-tab" aria-controls="messages" role="tab" data-toggle="tab">Raspored stolova
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content" style="margin-top: 20px">
                    <div role="tabpanel" class="tab-pane fade in active" id="recenzije-tab">
                        <?php $this->pogledKritika->generiraj() ?>

                    </div>
                    <div role="tabpanel" class="tab-pane fade " id="jelovnik-tab">
                        <?php $this->pogledJelovnik->generirajPrikazi(false) ?>

                    </div>
                    <div role="tabpanel" class="tab-pane fade " id="raspored-tab">
                        <?php $this->pogledRasporedStolova->generirajPrikazi(false) ?>

                    </div>
                </div>

            </div>
        </div>
    <?php }

    protected function generirajJSdodatno()
    {
        ?>
        <style>
            .pogled-ugostitelj .affix {
                top: 0;
                left: 0;
                z-index: 1000;
            }
        </style>
        <script>
            $(".pogled-ugostitelj .page-header").affix({offset: {top: 580}});
        </script>
        <script>
            var ugostitelj = <?= $this->ugostitelj->getId() ?>;
        </script>
        <?php
        $this->pogledKritika->generirajJSdodatno();
    }
}

?>