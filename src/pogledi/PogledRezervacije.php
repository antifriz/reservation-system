<?php

/**
 *
 */
/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledJelovnik.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledRasporedStolova.php";
require_once DOCUMENT_ROOT . "/podaci/rezerviranje/Rezervacija.php";

/**
 * Class PogledRezervacije
 */
class PogledRezervacije extends Pogled
{

    /**
     * @var Rezervacija
     */
    private $rezervacija;


    /**
     * @var Ugostitelj
     */
    private $ugostitelj;
    /**
     * @var Gost
     */
    private $gost;

    /**
     * @var PogledJelovnik
     */
    private $pogledJelovnik;

    /**
     * @var PogledRasporedStolova
     */
    private $pogledRasporedStolova;


    /**
     * @param $rezervacija Rezervacija
     */
    public function __construct($rezervacija)
    {
        $this->rezervacija = $rezervacija;

        $ugostitelj = $rezervacija->getUgostitelj();
        $gost = $rezervacija->getGost();

        $this->ugostitelj = $ugostitelj;
        $this->gost = $gost;

        $this->pogledJelovnik = new PogledJelovnik($ugostitelj, $gost);
        $this->pogledRasporedStolova = new PogledRasporedStolova($ugostitelj, $gost);
//todo: razlikuj konobara i gosta u vidu smije li sat vremena prije (i u proslost) uredivat narudzbu!
    }

    /**
     *
     */
    public function generiraj()
    {

        ?>


        <div class="rezervacija">

            <div class="osnovno">
                <?php $this->generirajOsnovno(); ?>
            </div>
            <div class="raspored-stolova">
                <div class="container">
                    <?php $this->pogledRasporedStolova->generirajNaruci(false); ?>
                </div>
            </div>
            <div class="jelovnik">

                <div class="container">
                    <?php $this->pogledJelovnik->generirajNaruci(false); ?>
                </div>
            </div>
            <div class="rezerviraj">
                <div class="container">
                    <?php $this->generirajRezerviraj(); ?>
                </div>
            </div>
        </div>
    <?php

    }

    /**
     *
     */
    private function generirajOsnovno()
    {
        ?>
        <div class="parallax-window" data-parallax="scroll" style="margin-top: -100px"
             data-image-src="<?= $this->ugostitelj->getUrlSlikeLokala() ?>"></div>
        <div class="container ">

            <div class="page-header text-center ">
                <h1>
                    <a href="/ugostitelj.php?id=<?= $this->ugostitelj->getId() ?>"><?= $this->ugostitelj->getImeRestoran(
                        ); ?></a>
                    <small><?= $this->ugostitelj->getAdresa() ?></small>
                </h1>
                <br> Rezervacija #<?= $this->rezervacija->getId() ?>
            </div>
            <div class="one_full date-interval" style="text-align: center">
                <div class="form-group one_fourth">

                    <label for="broj-osoba">Broj osoba</label>

                    <div class="input-group">
                    <span class="input-group-btn">
                    <button type="button"
                            class="btn btn-default btn-number"
                            onclick="changeKolicina(this,-1,1); ">
                        <span class="glyphicon glyphicon-minus"></span>
                    </button>
                    </span>
                        <input id="broj-osoba" type="text" class="form-control text-center" value="1">
                    <span class="input-group-btn">
                    <button type="button"
                            class="btn btn-default btn-number"
                            onclick="changeKolicina(this,1,1); ">
                        <span class="glyphicon glyphicon-plus"></span>
                    </button>
                    </span>
                    </div>
                </div>

                <div class="form-group one_fourth">
                    <label for="date-interval-date">Datum</label>
                    <input type="text" id="date-interval-date" class="form-control text-center"/>
                </div>
                <div class="form-group one_fourth">
                    <label for="date-interval-time-start">Dolazak</label>
                    <input type="text" id="date-interval-time-start" class="form-control text-center"/>
                </div>
                <div class="form-group one_fourth last">
                    <label for="date-interval-time-end">Odlazak</label>
                    <input type="text" id="date-interval-time-end" class="form-control text-center"/>
                </div>
            </div>
            <div class="three_fourth">
            <span class="one_full alert alert-danger" id="alert-zauzet-termin" style="display: none">
                <strong>Zauzeto </strong>pokušajte drugi termin
            </span>
            <span class="one_full alert alert-success" id="alert-slobodan-termin" style="display: none">
                <strong>Slobodno </strong>izabrani termin je slobodan
            </span>
            </div>
            <div class="one_fourth last" style="padding: 7px 0;margin-bottom: 30px">
                <?php $this->generirajGumb(
                    "btn-primary btn-provjeri-dostupnost one_full",
                    "glyphicon-calendar",
                    "PROVJERI DOSTUPNOST",
                    null
                )?>
            </div>
        </div>

    <?php
    }

    /**
     *
     */
    private function generirajRezerviraj()
    {
        ?>
        <div class="text-center">
            <?php $this->generirajGumb(
                "btn-success btn-rezerviraj",
                "glyphicon-ok",
                $this->rezervacija->jeNovokreirana() ? "REZERVIRAJ" : "OSVJEŽI REZERVACIJU",
                null
            )?>
        </div>
    <?php
    }

    /**
     *
     */
    protected function generirajJSdodatno()
    {
        $this->pogledRasporedStolova->generirajJSdodatno();
        $this->pogledJelovnik->generirajJSdodatno();
        ?>
        <script>
            window.rezervacijaId = <?= $this->rezervacija->getId() ?>;
            window.ugostitelj = <?= $this->ugostitelj->getId() ?>;
            window.jeNova = <?= $this->rezervacija->jeNovokreirana()?'true':'false'?>;
            window.vrijemePocetak = <?= $this->rezervacija->getVrijemePocetak() ?>;
            window.vrijemeKraj = <?=$this->rezervacija->getVrijemeKraj() ?>;
            window.rezerviranaJela = <?= $this->rezervacija->dohvatiJela() ?>;
            window.rezerviranaMjesta = <?= $this->rezervacija->dohvatiStolove() ?>;

        </script>
        <script type="text/javascript" src="/js/datepair.js"></script>
        <script>
            function checkTimeIntervalInput(epochStart, epochEnd, alert) {
                try {
                    var epochDiff = epochEnd - epochStart;

                    if (epochDiff <= 0) {
                        if (alert)bootbox.alert('Molimo izaberite vrijeme dolaska koje prethodi vremenu odlaska.');
                        return false;
                    }
                    if (epochStart < new Date().getTime()) {
                        if (alert)bootbox.alert('Rezervacija je nažalost moguća samo u budućem vremenu.');
                        return false;
                    }
                }
                catch (e) {
                    if (alert)bootbox.alert('Neispravan unos termina' + epochStart + epochEnd);
                    return false;
                }
                return true;
            }
            function dohvatiKolikoMaksimalnoStaneLjudiUTomTerminu() {
                var cnt = 0;
                $("[name='odabraniStolovi']").each(function () {
                    if (!$(this).is(':disabled')) {

                        var blok = $(this).closest(".lista-blok");
                        cnt += parseInt(blok.find('.stol-kapacitet').html().trim());
                    }
                });
                return cnt;
            }
            function dohvatiBrojGostiju(alert) {
                var brojGostiju = $("#broj-osoba").val();

                if (!isInt(brojGostiju)) {
                    if (alert)bootbox.alert("Neispravan broj gostiju");
                    return null;
                }
                return brojGostiju;
            }

            function maxBrojGostijuZaStolovima(izabraniStolovi) {
                var cnt = 0;
                izabraniStolovi.forEach(function (element) {
                    cnt += element.kapacitet;
                });
                console.log(cnt);
                return cnt;
            }


            function dohvatiStanuLiOsobeUTomTerminu() {
                var brojGostiju = dohvatiBrojGostiju(false);
                console.log(brojGostiju, dohvatiKolikoMaksimalnoStaneLjudiUTomTerminu());
                return isInt(brojGostiju) && brojGostiju <= dohvatiKolikoMaksimalnoStaneLjudiUTomTerminu();
            }
            function dohvatiDostupnostStolovaUTerminuSuccess(stolovi) {
                var automatskiOdabir = !$("#pogled-raspored-stolova-switch").is(':checked');
                for (var x = 0; x < stolovi.length; x++) {
                    var stol = stolovi[x];
                    var sw = $("#odabraniStolovi-" + stol.id_stol);
                    var jeDostupan = stol.dostupnost == '1';
                    if (!jeDostupan)
                        sw.bootstrapSwitch('state', false);
                    else if (automatskiOdabir)
                        sw.bootstrapSwitch('state', stol.prijedlog == '1');

                    sw.bootstrapSwitch('disabled', !jeDostupan);
                }
                if (dohvatiStanuLiOsobeUTomTerminu()) {
                    $("#alert-slobodan-termin").show();
                    $("#alert-zauzet-termin").hide();
                }
                else {
                    $("#alert-zauzet-termin").show();
                    $("#alert-slobodan-termin").hide();
                }
            }
            function dohvatiDostupnostStolovaUTerminu(timeStart, timeEnd, brojGostiju) {
                var data = {
                    akcija: "dohvatiDostupnostStolovaUTerminu",
                    vrijemePocetak: timeStart,
                    vrijemeKraj: timeEnd,
                    brojGostiju: brojGostiju,
                    rezervacijaId: rezervacijaId,
                    idUgostitelj: ugostitelj
                };
                posaljiPost(data, dohvatiDostupnostStolovaUTerminuSuccess);

            }
            function dohvatiEpoch(dateString, timeString) {
                try {
                    dateString = dateString.split('.');
                    timeString = timeString.split(':');

                    var time = new Date(dateString[2], dateString[1] - 1, dateString[0], timeString[0], timeString[1], 0, 0);

                    return time.getTime();
                }
                catch (e) {
                    return null;
                }
            }
            function masterDostupnostStolova(alert, async) {

                var brojGostiju = dohvatiBrojGostiju(alert);
                if (brojGostiju === null) return false;

                var vrijeme = dohvatiVrijeme(alert);
                if (vrijeme === null) return false;

                if (async) dohvatiDostupnostStolovaUTerminu(vrijeme.pocetak, vrijeme.kraj, brojGostiju);

                return true;
            }

            function dohvatiVrijeme(alert) {
                var dateString = $("#date-interval-date").val();
                var timeStartString = $("#date-interval-time-start").val();
                var timeEndString = $("#date-interval-time-end").val();

                var vrijeme = {
                    pocetak: dohvatiEpoch(dateString, timeStartString),
                    kraj: dohvatiEpoch(dateString, timeEndString)
                };

                if (!checkTimeIntervalInput(vrijeme.pocetak, vrijeme.kraj, alert))
                    return null;

                return vrijeme;
            }


            function ispitajStolove(alert) {
                var izabraniStolovi = dohvatiIzabraneStolove(true);

                if (!izabraniStolovi)
                    return true;

                var brojGostiju = dohvatiBrojGostiju(true);
                if (!brojGostiju) return;

                if (izabraniStolovi.length > brojGostiju) {
                    if (alert)bootbox.alert("Izabrali ste više stolova nego ljudi, izaberite manji broj stolova.");
                    return false;
                }

                var staneGostiju = maxBrojGostijuZaStolovima(izabraniStolovi);

                if (staneGostiju < brojGostiju) {
                    if (alert)bootbox.alert("Izabrali ste premali broj stolova u odnosu na broj ljudi.");
                    return false;
                }
                return true;
            }


            function rezerviraj() {

                if (!masterDostupnostStolova(true, false))
                    return;

                var vrijeme = dohvatiVrijeme(false);
                var brojGostiju = dohvatiBrojGostiju(false);

                var izabraniStolovi = dohvatiIzabraneStolove();

                var stoloviImploded = [];
                if (izabraniStolovi) {
                    izabraniStolovi.forEach(function (item) {
                        stoloviImploded.push(item.id);
                    });
                    stoloviImploded = stoloviImploded.join('|');
                }

                var izabranaJela = dohvatiJelovnikRezervacije();


                var jelaImploded = [];
                if (izabranaJela) {
                    izabranaJela.forEach(function (item) {
                        if (item.kolicina > 0)
                            jelaImploded.push(item.id + ":" + item.kolicina);
                    });
                    jelaImploded = jelaImploded.join('|');
                }

                var data = {
                    akcija: "rezerviraj",
                    rezervacijaId: rezervacijaId,
                    vrijemePocetak: vrijeme.pocetak,
                    vrijemeKraj: vrijeme.kraj,
                    izabraniStolovi: stoloviImploded,
                    brojGostiju: brojGostiju,
                    jela: jelaImploded
                };
                posaljiPost(data, rezerviranoCallback);
            }

            function rezerviranoCallback(msg) {
                if (msg == 'OK') {
                    bootbox.alert('Rezervacija uspješna, neiscrpno Vas isčekujemo!', function () {
                        window.location = "/profil.php#rezerviranja";
                    });
                    return;
                }
                bootbox.alert(msg);
            }

            function dohvatiIzabraneStolove() {

                var izabrani = [];
                $("[name='odabraniStolovi']").each(function () {
                    if ($(this).is(':checked')) {
                        var blok = $(this).closest(".lista-blok");
                        var id = blok.children('.hidden').html().trim();
                        var kapacitet = parseInt(blok.find('.stol-kapacitet').html().trim());
                        izabrani.push({id: id, kapacitet: kapacitet});
                    }
                });
                return izabrani;
            }

            function dohvatiJelovnikRezervacije() {
                var jela = [];

                if (!$("#pogled-jelovnik-switch").is(':checked'))
                    return jela;

                $("#pogled-jelovnik").find(".lista-blok").each(function () {
                    var jeloId = parseInt($(this).children('div.hidden').html().trim());
                    var cijenaJela = parseFloat($(this).find('span.jelo-cijena').html().trim());
                    var kolicinaJela = parseInt($(this).find('input.jelovnik-jelo-kolicina').val());


                    var vecPostoji = false;
                    jela.forEach(function (jelo) {
                        if (jelo.id === jeloId) {
                            jelo.kolicina += kolicinaJela;
                            vecPostoji = true;
                        }
                    });
                    if (!vecPostoji)
                        jela.push({id: jeloId, cijena: cijenaJela, kolicina: kolicinaJela});
                });
                return jela;
            }
            function izracunajUkupnaCijena() {
                var jelovnik = dohvatiJelovnikRezervacije();
                var cijena = 0;
                jelovnik.forEach(function (jelo) {
                    console.log(jelo);
                    cijena += jelo.cijena * jelo.kolicina;
                });
                return cijena.toFixed(2);
            }

            function updateUkupnaCijena() {
                $("#jelovnik-ukupna-cijena").html(izracunajUkupnaCijena().toString());
            }

            function changeJelovnikKolicina(node, add, minVal) {
                var d = $(node).closest('div');
                var plus = d.find('.btn-plus');
                var minus = d.find('.btn-minus');
                var i = d.find('input');
                var val = parseInt(i.val()) + parseInt(add);
                if (val < minVal) {
                    return;
                }
                plus.addClass(val > 0 ? 'btn-success' : 'btn-default');
                plus.removeClass(val <= 0 ? 'btn-success' : 'btn-default');
                minus.addClass(val > 0 ? 'btn-danger' : 'btn-default');
                minus.removeClass(val <= 0 ? 'btn-danger' : 'btn-default');

                i.css('font-weight', val > 0 ? 'bold' : 'normal');

                i.val(val);

                updateUkupnaCijena();
            }

            function getDateTime(timestamp) {
                var now = new Date(timestamp);
                var year = now.getFullYear();
                var month = now.getMonth() + 1;
                var day = now.getDate();
                var hour = now.getHours();
                var minute = now.getMinutes();
                if (month.toString().length == 1) {
                    month = '0' + month;
                }
                if (day.toString().length == 1) {
                    day = '0' + day;
                }
                if (hour.toString().length == 1) {
                    hour = '0' + hour;
                }
                if (minute.toString().length == 1) {
                    minute = '0' + minute;
                }
                return day + "-" + month + "-" + year + ' ' + hour + ':' + minute;
            }

            $(function () {


                $(".date-interval").click(function () {
                    masterDostupnostStolova(false, true);
                });

                $(".btn-provjeri-dostupnost").click(function () {
                    masterDostupnostStolova(true, true);
                });
                $(".btn-rezerviraj").click(function () {

                    if (!masterDostupnostStolova(true, false))
                        return;
                    if (!ispitajStolove(true))
                        return;

                    var vrijeme = dohvatiVrijeme(false);

                    var stolovi = dohvatiIzabraneStolove();
                    var brojStolova = stolovi ? stolovi.length : "nisu izabrani";
                    var brojGostiju = dohvatiBrojGostiju(false);

                    bootbox.dialog({
                        message: "Potvrđujem svoju rezervaciju" +
                        "<br>Dolazak: " + getDateTime(vrijeme.pocetak) +
                        "<br>Odlazak: " + getDateTime(vrijeme.kraj) +
                        "<br>Broj osoba:" + brojGostiju +
                        "<br>Broj stolova:" + brojStolova,
                        title: "Rezervacija",
                        buttons: {
                            main: {
                                label: "Odustani",
                                className: "btn-default",
                                callback: function () {
                                }
                            },
                            success: {
                                label: "Rezerviraj",
                                className: "btn-success",
                                callback: rezerviraj
                            }
                        }
                    });
                });


                if (!jeNova) {
                    $("#pogled-raspored-stolova-switch").bootstrapSwitch('state', true);
                    var x;
                    if (rezerviranaMjesta && rezerviranaMjesta.length > 0) {
                        $("#pogled-raspored-stolova").find('input').bootstrapSwitch('state', false);
                        for (x = 0; x < rezerviranaMjesta.length; x++) {
                            var stol = rezerviranaMjesta[x];
                            var sw = $("#odabraniStolovi-" + stol.id_stol);
                            sw.bootstrapSwitch('state', true);
                        }
                    }
                    if (rezerviranaJela && rezerviranaJela.length > 0) {
                        $("#pogled-jelovnik-switch").bootstrapSwitch('state', true);
                        var j = $("#pogled-jelovnik");
                        j.find('input').val(0);
                        for (x = 0; x < rezerviranaJela.length; x++) {
                            var jelo = rezerviranaJela[x];
                            changeJelovnikKolicina(j.find('#vrste-jela *[data-id="' + jelo.id_jelo + '"]'),jelo.kolicina,0);
                        }
                    }
                }
                else {
                    $("#pogled-raspored-stolova-switch").bootstrapSwitch('state', false);

                    $("#pogled-jelovnik-switch").bootstrapSwitch('state', false);
                }
                console.log(rezerviranaMjesta, rezerviranaJela);

                masterDostupnostStolova(false, true);


            });
        </script>
    <?php
    }
}
