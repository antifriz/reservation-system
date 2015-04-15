<?php


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Gost.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Ugostitelj.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/Jelovnik.php";
require_once DOCUMENT_ROOT . "/podaci/ugostiteljstvo/RasporedStolova.php";

/**
 * Class PogledJelovnik
 */
class   PogledJelovnik extends Pogled
{

    /**
     * @var Ugostitelj
     */
    private $ugostitelj;
    /**
     * @var Gost
     */
    private $gost;
    /**
     * @var Jelovnik
     */
    private $jelovnik;


    /**
     * @var
     */
    private $tipPogleda;


    /**
     * @param $ugostitelj Ugostitelj
     * @param $gost       Gost
     */
    public function __construct($ugostitelj, $gost)
    {
        $this->ugostitelj = $ugostitelj;
        $this->gost = $gost;
        $this->jelovnik = $ugostitelj->dohvatiJelovnik();

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

    /**
     *
     */
    protected function generiraj()
    {

        switch ($this->tipPogleda)
        {
            case POGLED_NARUCI:
                ?>
                <div class="one_full thumbnail" style="padding: 10px">
                    <div class="pull-right">
                        <input type="checkbox" id="pogled-jelovnik-switch">
                    </div>
            <span style="line-height: 34px;font-size:20px">
                Želim izabrati jelovnik
            </span>
                </div>
                <?php
                break;
            case POGLED_UREDI:
                ?>
                <div class="text-center" style="margin-bottom: 30px">
                    <h3>
                        Jelovnik
                    </h3>
                </div>
                <?php
                break;
            default:
                break;
        }
        ?>

        <div id="pogled-jelovnik"
             class="tabs one_full " <?= ($this->tipPogleda == POGLED_NARUCI) ? 'style="display:none;"' : "" ?>>
            <ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
                <li class="active">
                    <a href="#vrste-jela" data-toggle="tab">Po vrstama jela</a>
                </li>
                <li class="">
                    <a href="#ponude-jela" data-toggle="tab">Po ponudama jela</a>
                </li>
            </ul>
            <div class="tab-content one_full">
                <div class="tab-pane fade in one_full active" id="vrste-jela">
                    <?php $this->generirajJelovnikPoVrstama(); ?>
                </div>
                <div class="tab-pane fade in one_full " id="ponude-jela">
                    <?php $this->generirajJelovnikPoPonudama(); ?>
                </div>

            </div>
            <?php

            if ($this->tipPogleda == POGLED_NARUCI):?>
                <div class="lead text-center one_full" style="margin-top: 20px">
                    Ukupna cijena: <strong>
                        <span id="jelovnik-ukupna-cijena">0.00</span>
                        kn</strong>
                </div>
            <?php endif;
            //$arr = array('ime_jela'=>'mate','jelo_cijena'=>"3.00");
            //if ($this->tipPogleda == POGLED_NARUCI)
            //    self::generirajNaruceno(array(new Jelo($arr),new Jelo($arr),new Jelo($arr)));
            ?>
        </div>

        <div class="modal fade" id="ponudaJelaModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content one_full">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Izaberi ponude jela</h4>
                    </div>
                    <div class="modal-body one_full">
                        <div class="hidden">

                        </div>
                        <div class="lista-blok-holder one_full">
                            <?php
                            $ponude = $this->jelovnik->getPonudeJela();
                            foreach ($ponude as $ponuda):
                                ?>
                                <div class="lista-blok one_full">
                                    <div class="two_third">
                                        <?= mb_strtoupper($ponuda->getNaziv(), 'UTF-8') ?>
                                    </div>
                                    <div class="one_third last text-right">
                                        <input type="checkbox" name="odabranePonudeJela"
                                               value="<?= $ponuda->getId() ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer one_full">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary btn-pohrani">Pohrani promjene</button>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     *
     */
    private function generirajJelovnikPoVrstama()
    {
        ?>
        <div class="jelovnik-po-vrstama one_full">
            <?php

            $jelaPoVrstama = $this->jelovnik->getJelaPoVrstama();

            foreach ($jelaPoVrstama as $vrsta => $jela):


                $vrsta = explode('|', $vrsta);
                $vrsta_naziv = $vrsta[0];
                $vrsta_id = $vrsta[1];
                if($this->tipPogleda != POGLED_UREDI and count($jela)<=0)
                    continue;
                ?>
                <div class="lista-blok-whole">
                    <?php
                    $this->generirajJelaBloka($vrsta_id, $vrsta_naziv, $jela, false);
                    switch ($this->tipPogleda)
                    {
                        case POGLED_UREDI:
                            $this->generirajUrediBlok($vrsta_id);
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
            endforeach;?>
        </div>
    <?php

    }

    /**
     * @param        $id
     * @param        $naziv
     * @param Jelo[] $jela
     * @param        $editableNaslov
     */
    private function generirajJelaBloka($id, $naziv, $jela, $editableNaslov)
    {
        ?>
        <div class="lista-blok-header one_full">
            <span class="<?= $editableNaslov ? "editable-span" : "" ?>"
                  contenteditable="<?= $editableNaslov ? "true" : "false" ?>"><?= mb_strtoupper(
                    $naziv,
                    'UTF-8'
                ) ?></span>

            <div class="pull-right"><?php
                if ($editableNaslov)
                {
                    $this->generirajGumb(
                        'btn-primary btn-spremi-ponuda-jela" data-id="' . $id . '"',
                        "glyphicon-floppy-disk",
                        "SPREMI",
                        null
                    );
                    $this->generirajGumb(
                        'btn-danger btn-izbrisi-ponuda-jela" data-id="' . $id . '"',
                        "glyphicon-remove",
                        "IZBRIŠI",
                        null
                    );

                }
                ?>
            </div>
        </div>
        <div class="hidden"><?= $id ?></div>
        <div class="one_full lista-blok-holder">
            <?php
            foreach ($jela as $jelo)
                $this->generirajJelo($jelo);
            ?>
        </div>
    <?php
    }

    /**
     * @param Jelo $jelo
     */
    private function generirajJelo($jelo)
    {
        ?>
        <div class="one_full lista-blok">
            <div class="hidden"><?= $jelo->getId() ?></div>
            <!--<div style="position:absolute;top: 0; bottom:0; margin-top: auto;margin-bottom: auto; left: -20px;"><span class="glyphicon glyphicon-arrow-right"></span></div>
            !-->
            <div class="three_fourth">
                <div class="one_half">
                    <span class="jelo-naziv <?= $this->tipPogleda == POGLED_UREDI ? "editable-span" : "" ?>"
                          contenteditable="<?= $this->tipPogleda == POGLED_UREDI ? "true" : "false" ?>"><?= $jelo->getNaziv(
                        ); ?></span>
                </div>
                <div class="one_half last text-right">
                    <span class="jelo-cijena <?= $this->tipPogleda == POGLED_UREDI ? "editable-span" : "" ?>"
                          contenteditable="<?= $this->tipPogleda == POGLED_UREDI ? "true" : "false" ?>"><?= $jelo->getCijena(
                        ); ?></span>
                    <span> kn</span>
                </div>
            </div>
            <div class="one_fourth last text-right">
                <?php
                switch ($this->tipPogleda)
                {
                    case POGLED_UREDI:
                        $this->generirajGumb(
                            'btn-primary btn-spremi-jelo" data-toggle="modal" data-target="#ponudaJelaModal" data-vrsta="' . $jelo->getVrsta(
                            ) . '" data-new="false"',
                            "glyphicon-floppy-disk",
                            "SPREMI",
                            null
                        );
                        $this->generirajGumb(
                            "btn-danger btn-izbrisi-jelo",
                            "glyphicon-remove",
                            "IZBRIŠI",
                            null
                        );
                        break;
                    case POGLED_NARUCI:
                        ?>
                        <div class=" input-group">
                            <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-minus"
                                    onclick="changeJelovnikKolicina(this,-1,0); ">
                                <span class="glyphicon glyphicon-minus"></span>
                            </button>
                            </span>
                            <input type="text" class="form-control jelovnik-jelo-kolicina" value="0"
                                   style="text-align: center" data-id="<?= $jelo->getId() ?>">
                            <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-plus"
                                    onclick="changeJelovnikKolicina(this,1,0); ">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                            </span>
                        </div>
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

    /**
     * @param $vrsta_id
     */
    private function generirajUrediBlok($vrsta_id)
    {
        ?>
        <!--<div class="lead text-center">
            Dodaj jelo
        </div>!-->
        <div class="one_full" style="padding: 20px 10px">

            <input class="form-control input-naziv-jelo one_half" type="text" placeholder="Unesi naziv jela"
                   name="naziv" required>
            <input class="form-control input-cijena-jelo one_half last" type="text" id="InputCijenaJelo"
                   placeholder="Unesi cijenu jela" name="cijena" required>
        </div>

        <div class="text-center">
            <?php
            $this->generirajGumb(
                'btn-success btn-dodaj-jelo" data-toggle="modal" data-target="#ponudaJelaModal" data-vrsta="' . $vrsta_id . '" data-new="true" ',
                'glyphicon-plus',
                'DODAJ',
                null
            );?>
        </div>
    <?php
    }

    /**
     *
     */
    private function generirajJelovnikPoPonudama()
    {
        ?>
        <div class="one_full">
            <?php

            $ponude = $this->jelovnik->getPonudeJela();

            foreach ($ponude as $ponuda):
                $ponuda_naziv = $ponuda->getNaziv();
                $ponuda_id = $ponuda->getId();
                $jela = $ponuda->getJela();

                ?>
                <div class="lista-blok-whole">
                    <?php $this->generirajJelaBloka(
                        $ponuda_id,
                        $ponuda_naziv,
                        $jela,
                        $this->tipPogleda == POGLED_UREDI
                    ); ?>
                </div>
            <?php
            endforeach;

            if ($this->tipPogleda == POGLED_UREDI):?>

                <div class="one_full ponuda-dodaj">
                    <div class="one_full" style="padding: 20px 10px">

                        <input class="form-control input-naziv-ponuda-jela one_full" type="text"
                               placeholder="Unesi naziv ponude jela" name="naziv-ponuda-jela" required>
                    </div>

                    <div class="text-center">
                        <?php
                        $this->generirajGumb(
                            'btn-success btn-dodaj-ponuda-jela"',
                            'glyphicon-plus',
                            'DODAJ',
                            null
                        );?>
                    </div>
                </div>
            <?php

            endif; ?>
        </div>

    <?php

    }

    /**
     *
     */
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
                    function posaljiJelo(data) {
                        posaljiPost(data, osvjeziJelo);
                    }
                    function osvjeziJelo(err) {
                        if (!err || err.length <= 0)
                            location.reload();
                        bootbox.alert(err);
                    }
                </script>
                <script>
                    var jelaPonude = <?= json_encode($this->jelovnik->getJelaPoPonudama());
                    ?>;


                    function ispravanUnosJela(naziv, cijena) {
                        if (naziv.length <= 0) {
                            bootbox.alert('Neispravan unos naziva jela');
                            return false;
                        }
                        console.log(cijena);
                        if (!isInt(cijena) || cijena < 0 || cijena >= 10000) {
                            bootbox.alert('Neispravan unos cijene jela');
                            return false;
                        }
                        return true;
                    }
                    function isUPonudi(k, id) {
                        console.log(k, jelaPonude[k], id);
                        return jelaPonude[k].indexOf(id.toString()) > -1;
                    }
                    function podesiModal(modal, id) {
                        $(modal).find('.hidden').html(id);


                        for (var k in jelaPonude) {
                            if (jelaPonude.hasOwnProperty(k)) {
                                $(modal).find('[value=' + k + ']').bootstrapSwitch('state', id != null && isUPonudi(k, id))
                            }
                        }

                    }


                    $("#ponudaJelaModal").on('show.bs.modal', function (event) {
                        event = event || window.event;

                        var button = $(event.relatedTarget);
                        var isNew = button.data('new');

                        var naziv, cijena, id = null, data, blok;

                        if (isNew) {
                            blok = $(button).closest('.lista-blok-whole');
                            naziv = blok.find(".input-naziv-jelo").val();
                            cijena = Number(blok.find(".input-cijena-jelo").val());
                        }
                        else {
                            blok = $(button).closest('.lista-blok');
                            naziv = blok.find('.jelo-naziv').html();
                            cijena = Number(blok.find('.jelo-cijena').html());
                            id = parseInt(blok.find('.hidden').html());
                        }


                        if (ispravanUnosJela(naziv, cijena)) {
                            var modal = $(this);

                            modal.data('id', id);
                            modal.data('cijena', cijena);
                            modal.data('naziv', naziv);
                            modal.data('vrsta', button.data('vrsta'));


                            podesiModal(modal, id);
                            return true;
                        }
                        return false;
                    });
                    $(".btn-pohrani").click(function (event) {

                            var modal = $("#ponudaJelaModal");

                            var checkboxes = modal.find('input:checked');
                            var ids = [];
                            checkboxes.each(function () {
                                ids.push(this.value);
                            });

                            var id = modal.data('id');
                            var naziv = modal.data('naziv');
                            var cijena = modal.data('cijena');
                            var vrsta = modal.data('vrsta');
                            var data = {
                                ime_jela: naziv,
                                cijena_jela: cijena,
                                ugostitelj: ugostitelj,
                                ponude: ids,
                                id_vrsta_jela: vrsta
                            };

                            if (id) {
                                data.akcija = 'urediJelo';
                                data.id_jelo = id;
                            }
                            else
                                data.akcija = 'dodajJelo';

                            console.log(data);

                            posaljiJelo(data);

                            modal.modal('hide');
                        }
                    )
                    ;

                    $(".btn-izbrisi-jelo").click(function (event) {
                        event = event || window.event;
                        event.preventDefault();

                        var blok = $(this).closest('.lista-blok');

                        var id = parseInt(blok.find('.hidden').html());

                        var data = {
                            akcija: 'izbrisiJelo', id_jelo: id, ugostitelj: ugostitelj
                        };
                        posaljiJelo(data);
                    });

                    $(".btn-izbrisi-ponuda-jela").click(function (event) {
                        event = event || window.event;
                        event.preventDefault();

                        var blok = $(this).closest('.lista-blok-whole');

                        var id = parseInt(blok.children('.hidden').html());

                        var data = {
                            akcija: 'izbrisiPonudaJela', id_ponuda: id, ugostitelj: ugostitelj
                        };
                        posaljiJelo(data);
                    });
                    $(".btn-spremi-ponuda-jela").click(function (event) {
                        event = event || window.event;
                        event.preventDefault();

                        var blok = $(this).closest('.lista-blok-whole');

                        var id = parseInt(blok.children('.hidden').html());
                        var naziv = blok.find('.lista-blok-header>span').html();

                        if (!naziv || naziv.length <= 0) {
                            bootbox.alert('Neispravan naziv ponude');
                            return;
                        }

                        var data = {
                            akcija: 'urediPonudaJela', id_ponuda: id, naziv_ponuda: naziv, ugostitelj: ugostitelj
                        };
                        posaljiJelo(data);
                    });
                    $(".btn-dodaj-ponuda-jela").click(function (event) {
                        event = event || window.event;
                        event.preventDefault();

                        var blok = $(this).closest('.ponuda-dodaj');

                        var naziv = blok.find('.input-naziv-ponuda-jela').val();


                        if (!naziv || naziv.length <= 0) {
                            bootbox.alert('Neispravan naziv ponude');
                            return;
                        }

                        var data = {
                            akcija: 'dodajPonudaJela', naziv_ponuda: naziv, ugostitelj: ugostitelj
                        };
                        posaljiJelo(data);
                    });
                </script>
                <?php
                break;
            case POGLED_NARUCI:
                ?>
                <script>
                    $('#pogled-jelovnik-switch').on('switchChange.bootstrapSwitch', function (event, state) {
                        var pogled = $('#pogled-jelovnik');
                        if (state)
                            pogled.slideDown();
                        else
                            pogled.slideUp();
                    });
                </script>
            <?php
            default:
                break;
        }
    }

    /**
     * @param Jelo[] $jela
     */
    private static function generirajNaruceno($jela)
    {
        if (!$jela or count($jela) <= 0)
            return;
        ?>
        <div class="lista-blok-whole">
            <div class="lista-blok-header text-center one_full">
                NARUČENO
            </div>
            <div class="lista-blok-holder">
                <?php
                foreach ($jela as $jelo):
                    ?>
                    <div class="lista-blok one_full">
                        <div class="one_half">
                            <?= $jelo->getNaziv() ?>
                        </div>
                        <div class="one_fourth">
                            <?= $jelo->getCijena() ?>
                        </div>
                        <div class="one_fourth last">
                            <?= $jelo->getKolicina() ?>
                        </div>

                    </div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
    <?php
    }

}

?>