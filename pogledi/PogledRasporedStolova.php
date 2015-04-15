<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";

require_once DOCUMENT_ROOT . "/podaci/korisnici/Gost.php";

require_once DOCUMENT_ROOT . "/podaci/korisnici/Ugostitelj.php";

require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/RasporedStolova.php";

require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Stol.php";


/**
 * Class PogledRasporedStolova
 */
class PogledRasporedStolova extends Pogled
{
    /**
     * @var Ugostitelj
     */
    private $ugostitelj;


    /**
     * @var RasporedStolova
     */
    private $rasporedStolova;


    /**
     * @var
     */
    private $tipPogleda;


    /**
     * @param Ugostitelj $ugostitelj
     */
    public function __construct($ugostitelj)
    {
        $this->ugostitelj = $ugostitelj;

        $this->rasporedStolova = $ugostitelj->dohvatiRasporedStolova();
    }


    /**
     *
     */
    public function generiraj()
    {
        $slika_rasporeda = $this->ugostitelj->getUrlSlikeStolova();
        if ($this->tipPogleda == POGLED_NARUCI):
            ?>
            <div class="one_full thumbnail" style="padding: 10px">
                <div class="pull-right">
                    <input type="checkbox" id="pogled-raspored-stolova-switch">
                </div>
            <span style="line-height: 34px;font-size:20px">
                Želim izabrati mjesto sjedenja
            </span>
            </div>
        <?php endif;?>
        <div id="pogled-raspored-stolova"
             class="one_full" <?= ($this->tipPogleda == POGLED_NARUCI) ? 'style="display:none;"' : "" ?>>
            <div class="one_full text-center">
                <img class="img-thumbnail " src="<?= $slika_rasporeda ?>" alt="Nedostaje slika rasporeda stolova"
                     style="margin: 20px auto; min-width: 50%">
            </div>
            <div class="one_full">
                <div class="one_half">
                    <div class="one_half">
                        <span class="lead" style="font-size: 16px">
                            <strong>REDNI BROJ</strong>
                        </span>
                    </div>
                    <div class="one_half last text-center">
                        <span class="lead" style="font-size: 16px">
                            <strong>KAPACITET</strong>
                        </span>
                    </div>
                </div>
            </div>
            <div class="one_full lista-blok-holder">
                <?php
                $stolovi = $this->rasporedStolova->getStolovi();
                foreach ($stolovi as $stol)
                    $this->generirajStol($stol);
                ?>
            </div>
            <?php
            switch ($this->tipPogleda)
            {
                case POGLED_UREDI:
                    $this->generirajUrediBlok();
                    break;
                case POGLED_NARUCI:
                    break;
                case POGLED_PRIKAZI:
                default:
                    break;
            }
            ?>
        </div>

    <?php
    }

    /**
     * @param Stol $stol
     */

    private function generirajStol($stol)
    {
        ?>
        <div class="one_full lista-blok">
            <div class="hidden"><?= $stol->getId() ?></div>
            <div class="one_half">
                <div class="one_half" style="padding-left: 10px">
                    #<span class="stol-redni-broj"
                           contenteditable="<?= $this->tipPogleda == POGLED_UREDI ? "true" : "false" ?>">
                        <?= $stol->getRedniBroj() ?>
                    </span>
                </div>
                <div class="one_half last text-center">
                    <span class="stol-kapacitet"
                          contenteditable="<?= $this->tipPogleda == POGLED_UREDI ? "true" : "false" ?>">
                        <?= $stol->getKapacitet(); ?>
                    </span>
                </div>
            </div>
            <div class="one_half last text-right">
                <?php
                switch ($this->tipPogleda)
                {
                    case POGLED_UREDI:
                        $this->generirajGumb(
                            "btn-primary btn-spremi-stol",
                            "glyphicon-floppy-disk",
                            "SPREMI",
                            null
                        );
                        $this->generirajGumb(
                            "btn-danger btn-izbrisi-stol",
                            "glyphicon-remove",
                            "OBRIŠI",
                            null
                        );
                        break;
                    case POGLED_NARUCI:
                        ?>
                        <input type="checkbox" name="odabraniStolovi" id="odabraniStolovi-<?= $stol->getId() ?>">
                        <?php
                        break;
                    case POGLED_PRIKAZI:
                    default:
                        break;
                }
                ?>
            </div>
        </div>
    <?php
    }


    private function generirajUrediBlok()
    {
        ?>
        <!--<div class="lead text-center">
            Dodaj stol
        </div>!-->
        <div class="one_full" style="padding: 20px 20px">
            <div class="three_fourth">
                <div class="one_half">
                    <input class=" form-control" type="number" id="InputRedniBrojStol" placeholder="Unesi redni broj"
                           name="rbr" min="1" step="1" required>
                </div>
                <div class="one_half last">
                    <input class="form-control" type="number" id="InputKapacitetStol" placeholder="Unesi kapacitet"
                           name="kapacitet" min="1" step="1" required>

                </div>
            </div>
            <div class=" one_fourth last text-center">
                <?php
                $this->generirajGumb(
                    'btn-success btn-dodaj-stol one_full',
                    'glyphicon-plus',
                    'DODAJ',
                    null
                );?>
            </div>
        </div>

    <?php
    }


    public function generirajJSdodatno()
    {
        switch ($this->tipPogleda)
        {
            case POGLED_UREDI:
                ?>
                <script>
                    var ugostitelj = <?php echo $this->ugostitelj->getId()?>;
                </script>
                <script>
                    var kontroler = "/kontroler.php";
                    function posaljiStol(data) {
                        posaljiPost(data, osvjeziStol);
                    }
                    function osvjeziStol(err) {
                        if (!err || err.length <= 0)
                            location.reload();
                        bootbox.alert(err);
                    }
                </script>
                <script>

                    function ispravanUnosStola(rbr, kapacitet) {
                        if (!isInt(rbr) || rbr < 1) {
                            bootbox.alert('Neispravan unos rednog broja stola');
                            return false;
                        }
                        if (!isInt(kapacitet) || kapacitet < 1) {
                            bootbox.alert('Neispravan unos kapaciteta stola');
                            return false;
                        }
                        return true;
                    }
                    $(".btn-dodaj-stol").click(function (event) {
                        event = event || window.event;
                        event.preventDefault();

                        var rbr = parseInt($("#InputRedniBrojStol").val());
                        var kapacitet = parseInt($("#InputKapacitetStol").val());

                        if (!ispravanUnosStola(rbr, kapacitet))
                            return;

                        var data = {
                            akcija: 'dodajStol', rbr_stol: rbr, kapacitet: kapacitet, ugostitelj: ugostitelj
                        };
                        posaljiStol(data);
                    });
                    $(".btn-spremi-stol").click(function (event) {
                        event = event || window.event;
                        event.preventDefault();

                        var blok = $(this).closest('.lista-blok');

                        var id = parseInt(blok.find('.hidden').html());
                        var rbr = blok.find('.stol-redni-broj').html();
                        var kapacitet = parseInt(blok.find('.stol-kapacitet').html());

                        if (!ispravanUnosStola(rbr, kapacitet))
                            return;

                        var data = {
                            akcija: 'urediStol',
                            id_stol: id,
                            rbr_stol: rbr,
                            kapacitet: kapacitet,
                            ugostitelj: ugostitelj
                        };
                        posaljiStol(data);
                    });
                    $(".btn-izbrisi-stol").click(function (event) {
                        event = event || window.event;
                        event.preventDefault();

                        var blok = $(this).closest('.lista-blok');

                        var id = Number(blok.find('.hidden').html());

                        var data = {
                            akcija: 'izbrisiStol', id_stol: id, ugostitelj: ugostitelj
                        };
                        posaljiStol(data);
                    });
                </script>
                <?php
                break;
            case POGLED_NARUCI:
                ?>
                <script>
                    $(function () {
                        var swPogledRasporedStolova = $('#pogled-raspored-stolova-switch');
                        swPogledRasporedStolova.bootstrapSwitch('state', false);
                        swPogledRasporedStolova.on('switchChange.bootstrapSwitch', function (event, state) {
                            var pogled = $('#pogled-raspored-stolova');
                            if (state)
                                pogled.slideDown();
                            else
                                pogled.slideUp();
                        });
                    });

                </script>
            <?php
            default:
                break;
        }
    }

    /**
     * @param $ima_okvir
     */
    public function generirajNaruci($ima_okvir)
    {
        $this->tipPogleda = POGLED_NARUCI;
        $ima_okvir ? $this->generirajOkvir() : $this->generiraj();
    }

    /**
     * @param $ima_okvir
     */
    public function generirajPrikazi($ima_okvir)
    {
        $this->tipPogleda = POGLED_PRIKAZI;
        $ima_okvir ? $this->generirajOkvir() : $this->generiraj();
    }

    /**
     * @param $ima_okvir
     */
    public function generirajUredi($ima_okvir)
    {
        $this->tipPogleda = POGLED_UREDI;
        $ima_okvir ? $this->generirajOkvir() : $this->generiraj();
    }


}