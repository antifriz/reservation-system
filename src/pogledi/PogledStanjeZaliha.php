<?php

/**
 * Class PogledStanjeZaliha
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledStatistika.php";

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";
require_once DOCUMENT_ROOT . "/podaci/narucivanje/Namirnica.php";
require_once DOCUMENT_ROOT . "/podaci/narucivanje/StanjeZaliha.php";
require_once DOCUMENT_ROOT . "/podaci/narucivanje/Nabava.php";

/**
 * Class PogledStanjeZaliha
 */
class PogledStanjeZaliha extends Pogled
{
    /**
     * @var Ugostitelj|Dobavljac
     */
    private $korisnik;

    /**
     * @var PogledStatistika
     */
    private $pogledStatistika;

    /**
     * @var string|null
     */
    private $err;

    /**
     * @param $korisnik
     */
    function __construct($korisnik, $err)
    {
        $this->korisnik = $korisnik;
        $this->pogledStatistika = new PogledStatistika($korisnik);
    }

    public function generiraj()
    {
        ?>
        <div id="pogled-stanja-zaliha" class="container">
            <?php

            if (Session::jeUgostitelj())
            {
                $this->pogledStatistika->generiraj();

                $stanje = $this->korisnik->dohvatiStanjeZaliha();

                $namirnice = $stanje->getNamirnice();

                $this->generirajNamirnice($namirnice);
            }

            $nabave = $this->korisnik->dohvatiNabave();

            $this->generirajNabave($nabave);
            ?>
        </div>
    <?php
    }


    /**
     * @param Namirnica $namirnica // naziv, id, kolicina
     */
    private function generirajNamirnicu($namirnica, $moze_uredi = true)
    {
        ?>
        <div class="lista-blok col-xs-12">
            <div class="hidden"><?= $namirnica->getId() ?> ?></div>

            <div class="col-xs-3">
                <span><?= $namirnica->getNaziv(); ?></span>
            </div>
            <div class="col-xs-<?= $moze_uredi ? 2 : 9 ?> text-right">
                <span
                    class="<?= $moze_uredi ? "editable-span" : "" ?>" <?= $moze_uredi ? "contenteditable" : "" ?>><?= $namirnica->getKolicina(
                    ); ?></span>
                <span> kn</span>
            </div>
            <?php if ($moze_uredi): ?>
                <div class="col-xs-2">
                    <?php
                    $this->generirajGumb(
                        'btn-primary btn-uredi-namirnicu one_full" data-id="' . $namirnica->getId() . '"',
                        'glyphicon-floppy-disk',
                        'SPREMI',
                        ''
                    ); ?>
                </div>
                <div class="col-xs-2">
                    <?php
                    $this->generirajGumb(
                        'btn-danger btn-izbrisi-namirnicu one_full" data-id="' . $namirnica->getId() . '"',
                        'glyphicon-remove',
                        'IZBRIŠI',
                        ''
                    ); ?>
                </div>
                <div class="col-xs-1 " style="height: 100%;">
                    <div class="center-block" style="border-left: 1px dashed #777; height: 34px; width: 0;"></div>
                </div>
                <div class="col-xs-2">
                    <div class=" input-group">
                            <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-minus"
                                    onclick="changeKolicina(this,-1,0); ">
                                <span class="glyphicon glyphicon-minus"></span>
                            </button>
                            </span>
                        <input type="text" class="naruci-vrijednost form-control " value="0" style="text-align: center"
                               data-id="<?= $namirnica->getId() ?>">
                            <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-plus" onclick="changeKolicina(this,1,0); ">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                            </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * @param Namirnica[] $namirnice
     * @param bool        $moze_uredi
     * @param bool        $ima_zaglavlje
     */
    private function generirajNamirnice($namirnice, $moze_uredi = true, $ima_zaglavlje = true)
    {
        $error = $this->err;
        ?>
        <?php if ($ima_zaglavlje):
        $dobavljac = $this->korisnik->getDobavljac();
        $dobavljaci = Dobavljac::dohvatiDobavljace();?>
        <div class="page-header text-center">
            <h1>
                Stanje zaliha
            </h1>
            <strong> Dobavljač: </strong>

            <div class="dropdown" style="display: inline-block;margin-left: 10px;" data-id="<?= $dobavljac->getId() ?>">
                <button class="btn btn-default dropdown-toggle" style="min-width: 200px" type="button"
                        id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                    <span><?= $dobavljac->getNadimak() ?></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" style="width: 100%" role="menu" aria-labelledby="dropdownMenu1">
                    <?php foreach ($dobavljaci as $d): ?>
                        <li role="presentation">
                            <a role="menuitem" tabindex="-1"
                               href="?noviDobavljac=<?= $d['id'] ?>"><?= $d['korisnicko_ime'] ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php if ($error): ?>
                <div class=" col-xs-12">
                    <div class=" alert alert-danger">
                        <strong>Greška </strong><?php echo $error; ?>
                    </div>
                </div>
            <?php endif;?>
        </div>
    <?php endif;?>
        <div class="lista-namirnica lista-blok-holder row" style="margin-bottom: 10px">
            <?php
            foreach ($namirnice as $namirnica)
                $this->generirajNamirnicu($namirnica, $moze_uredi);
            ?>
        </div>
        <?php if ($moze_uredi): ?>
        <div class="row" style="padding: 10px;">
            <div class="col-xs-3">
                <input class="form-control" type="text" placeholder="Unesi naziv namirnice" id="InputNamirnicaNaziv">
            </div>

            <div class="col-xs-2">

                <input class="form-control" type="text" id="InputNamirnicaVrijednost"
                       placeholder="Unesi vrijednost (kn)">
            </div>
            <div class="col-xs-2">
                <?php
                $this->generirajGumb(
                    'btn-success btn-dodaj-namirnicu one_full',
                    'glyphicon-plus',
                    'DODAJ'
                ); ?>
            </div>
            <div class=" col-xs-offset-3 col-xs-2">

                <?php
                $this->generirajGumb(
                    'btn-primary btn-naruci-namirnice one_full',
                    'glyphicon-open',
                    'NARUČI'
                ); ?>
            </div>
        </div>
    <?php endif;?>
    <?php
    }

    /**
     * @param $nabave
     */
    private function generirajNabave($nabave)
    {
        ?>
        <div class="page-header text-center">
            <h1>
                Aktivne nabave
            </h1>
        </div>
        <div class="lista-blok-holder row" style="margin-bottom: 10px">
            <?php
            foreach ($nabave as $nabava)
                $this->generirajNabavu($nabava);
            ?>
        </div>
    <?php
    }

    /**
     * @param Nabava $nabava
     *
     */
    private function generirajNabavu($nabava)
    {
        ?>
        <div class="row" style="margin-bottom: 10px">
            <div class="lista-blok col-xs-12" style="border-bottom:1px solid #ddd">
                <div class="hidden"><?= $nabava->getId() ?></div>

                <div class="col-xs-<?= ($this->korisnik->getIdVrsta() == Korisnik::DOBAVLJAC) ? 4 : 6 ?>">
                    <span>Nabava #<?= $nabava->getId(); ?></span>
                </div>
                <div class="col-xs-3 text-right">
                    <span><?= $nabava->getStatusOpis(); ?></span>
                </div>
                <?php

                if ($this->korisnik->getIdVrsta() == Korisnik::DOBAVLJAC): ?>
                    <div class="col-xs-2">
                        <?php

                        if ($nabava->getStatus() == Nabava::STATUS_NARUCENO)
                            $this->generirajGumb(
                                'btn-success btn-posalji-nabavu one_full" data-id="' . $nabava->getId() . '"',
                                'glyphicon-ok',
                                'POŠALJI'
                            );
                        ?>
                    </div>
                <?php endif; ?>
                <div class="col-xs-2">
                    <?php
                    if ($nabava->getStatus() == Nabava::STATUS_ISPORUCENO)
                        $this->generirajGumb(
                            'btn-success btn-opskrbi-nabavom one_full" data-id="' . $nabava->getId() . '"',
                            'glyphicon-save',
                            'OPSKRBI'
                        );
                    else if ($this->korisnik->getIdVrsta() == Korisnik::DOBAVLJAC and $nabava->getStatus(
                        ) == Nabava::STATUS_NARUCENO
                    )
                        $this->generirajGumb(
                            'btn-danger btn-odbij-nabavu one_full" data-id="' . $nabava->getId() . '"',
                            'glyphicon-remove',
                            'ODBIJ'
                        );
                    ?>
                </div>
                <div class="col-xs-1">
                    <?php
                    if ($this->korisnik->getIdVrsta() == Korisnik::UGOSTITELJ)
                        $this->generirajGumb(
                            'btn-warning btn-prosiri one_full',
                            'glyphicon-plus',
                            ''
                        );?>
                </div>
            </div>
            <div class="col-xs-12 namirnice" style=" width: 100%;min-height: 1px;background: #ddd;<?= ($this->korisnik->getIdVrsta() == Korisnik::UGOSTITELJ)?"display:none":"" ?> ?>display: none;">
                <div class="lista-blok-header" style="margin: 0 0 0 -15px">
                    <?= Ugostitelj::dohvati($nabava->getIdUgostitelj())->getImeRestoran() ?>
                </div>
                <?php $this->generirajNamirnice($nabava->getNaruceno(), $moze_uredi = false, $ima_zaglavlje = false);?>
            </div>
        </div>
    <?php
    }


    /**
     *
     */
    protected function generirajJSdodatno()
    {
        if ($this->korisnik->getIdVrsta() == Korisnik::UGOSTITELJ)
            $this->pogledStatistika->generirajJSdodatno();
        ?>
        <script>
            $(function () {
                $('.btn-prosiri').click(function () {
                    $(this).closest('.row').find('.namirnice').slideToggle();
                    $(this).find('.glyphicon').toggleClass('glyphicon-plus glyphicon-minus');
                });
                $('.btn-opskrbi-nabavom').click(function () {
                    posaljiPost({
                        akcija: "opskrbiNabavom",
                        id: $(this).data('id')
                    }, osvjeziOk);
                });
                $('.btn-posalji-nabavu').click(function () {
                    posaljiPost({
                        akcija: "posaljiNabavu",
                        id: $(this).data('id')
                    }, osvjeziOk);
                });
                $('.btn-odbij-nabavu').click(function () {
                    posaljiPost({
                        akcija: "odbijNabavu",
                        id: $(this).data('id')
                    }, osvjeziOk);
                });
                $('.btn-izbrisi-namirnicu').click(function () {
                    posaljiPost({
                        akcija: "izbrisiNamirnicu",
                        id: $(this).data('id')
                    }, osvjeziOk);
                });
                $('.btn-dodaj-namirnicu').click(function () {
                    var naziv = $("#InputNamirnicaNaziv").val();
                    var vrijednost = $("#InputNamirnicaVrijednost").val();
                    if (naziv.length <= 0 || !isInt(vrijednost)) {
                        bootbox.alert("Neispravan unos namirnice");
                        return;
                    }
                    posaljiPost({
                        akcija: "dodajNamirnicu",
                        naziv: naziv,
                        vrijednost: vrijednost
                    }, osvjeziOk);
                });
                $('.btn-uredi-namirnicu').click(function () {
                    var root = $(this).closest(".lista-blok");
                    var vrijednost = root.find(".editable-span").html().trim();
                    if (!isInt(vrijednost)) {
                        bootbox.alert("Neispravan unos namirnice");
                        return;
                    }
                    posaljiPost({
                        akcija: "urediNamirnicu",
                        id: $(this).data('id'),
                        vrijednost: parseInt(vrijednost)
                    }, osvjeziOk);
                });
                $('.btn-naruci-namirnice').click(function () {
                    var root = $("#pogled-stanja-zaliha").find(".lista-namirnica");

                    var narudzba = [];
                    root.find(".naruci-vrijednost").each(function () {
                        var id = $(this).data('id');
                        var vrijednost = $(this).val();
                        if (!isInt(vrijednost)) {
                            bootbox.alert("Nije unešena ispravna količina");
                            return;
                        }
                        if (vrijednost > 0)
                            narudzba.push(id + ':' + vrijednost);
                    });
                    if (narudzba.length <= 0) {
                        bootbox.alert("Nije izabrano ni jedno jelo za naručivanje");
                        return;
                    }

                    var narudzbaImploded = narudzba.join('|');

                    posaljiPost({
                        akcija: "naruciNamirnice",
                        namirnice: narudzbaImploded
                    }, osvjeziOk);
                });
            });
        </script>
    <?php
    }

}

