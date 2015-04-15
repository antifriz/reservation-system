<?php


defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Ugostitelj.php";

/**
 * Class PogledPretrage
 */
class PogledPretrage extends Pogled
{

    /**
     *
     */
    public function generiraj()
    {
        ?>
        <div id="pogled-pretrage" class="one_full">
            <div class="container ">
                <div class="row">
                    <div class="two_third">
                        <?php $this->generirajRezultatePretrage(); ?>
                    </div>
                    <div id="pretrazivac-holder" class="one_third last">
                        <?php $this->generirajKontrolerPretrage(); ?>
                    </div>
                </div>
            </div>
        </div>

    <?php
    }

    protected function generirajJSdodatno()
    {
        ?>
        <script id="restoran-blok" type="text/template">
            <div class="col-xs-6">
                <a href="/ugostitelj.php?id=%ID%">
                    <div class="wrapper-16-9">
                        <div class="main" style="background-image: url(%URL%)">

                            <div class="main-description-head">
                                <span>%IME%</span>
                                <span class="pull-right">%KORISNIK%</span>
                            </div>

                            <div class="main-description-body">
                                <p class="lead" style="font-size: 16px;text-align: left">
                                    <strong>Adresa </strong>%ADRESA%<br> <strong>Vrste </strong>%VRSTE%<br> <strong>Ocjena
                                        korisnika </strong>%KORISNIK%<br> <strong>Ocjena inspekcije </strong>%INSPEKCIJA%
                                </p>

                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </script>
        <script>
            var pretrazivacHolder = $('#pretrazivac-holder');
            var pretrazivac = pretrazivacHolder.children('#pretrazivac');

            var toptop = $('.top-top');

            function updatePretrazivacWidth() {
                pretrazivac.innerWidth(pretrazivacHolder.width());
            }

            function updatePretrazivacTop() {
                pretrazivac.css('top', toptop.height() + 100);

            }


            var inputImeRestoran = $("#input-ime-restoran");
            var inputVrstaRestoran = $("#input-vrsta-restoran");
            var inputPoOcjeniInspekcije = $("#input-po-ocjeni-inspekcije");
            var sliderInspekcijaMax = $("#sliderInspekcijaMax");
            var sliderInspekcijaMin = $("#sliderInspekcijaMin");
            var inputPoOcjeniKorisnik = $("#input-po-ocjeni-korisnika");
            var sliderKorisnikMax = $("#sliderKorisnikMax");
            var sliderKorisnikMin = $("#sliderKorisnikMin");

            function updateSlider(number, value) {

                var minValue, maxValue;

                if (number == 1) {
                    sliderInspekcijaMax.find('.price').html(value.toFixed(1));
                    minValue = sliderInspekcijaMin.slider("option", "value");
                    console.log(minValue, sliderInspekcijaMax);
                    if (value < minValue) {
                        sliderInspekcijaMin.find('.price').html(value.toFixed(1));
                        sliderInspekcijaMin.slider('value', value);
                    }

                } else if (number == 2) {
                    sliderInspekcijaMin.find('.price').html(value.toFixed(1));
                    maxValue = sliderInspekcijaMax.slider("option", "value");
                    console.log(maxValue, sliderInspekcijaMin);

                    if (value > maxValue) {
                        sliderInspekcijaMax.find('.price').html(value.toFixed(1));
                        sliderInspekcijaMax.slider('value', value);
                    }
                }
                else if (number == 3) {
                    sliderKorisnikMax.find('.price').html(value.toFixed(1));
                    minValue = sliderKorisnikMin.slider("option", "value");
                    console.log(minValue, sliderKorisnikMax);
                    if (value < minValue) {
                        sliderKorisnikMin.find('.price').html(value.toFixed(1));
                        sliderKorisnikMin.slider('value', value);
                    }

                } else if (number == 4) {
                    sliderKorisnikMin.find('.price').html(value.toFixed(1));
                    maxValue = sliderKorisnikMax.slider("option", "value");
                    console.log(maxValue, sliderKorisnikMin);

                    if (value > maxValue) {
                        sliderKorisnikMax.find('.price').html(value.toFixed(1));
                        sliderKorisnikMax.slider('value', value);
                    }
                }
            }

            function pretragaCallback(rezultati) {
                console.log(rezultati);
                var holder = $("#rezultati-holder");
                holder.html("");
                var template = $("#restoran-blok").html();

                for (var x = 0; x < rezultati.length; x++) {
                    var rezultat = rezultati[x];
                    var childRaw = template
                        .replace(/%URL%/g, rezultat.url_slike_lokala)
                        .replace(/%ID%/g, rezultat.id_ugostitelj)
                        .replace(/%IME%/g, rezultat.ime_restoran)
                        .replace(/%ADRESA%/g, rezultat.adresa)
                        .replace(/%VRSTE%/g, (rezultat.vrste_restoran != null ? rezultat.vrste_restoran : ""))
                        .replace(/%KORISNIK%/g, rezultat.ocjena_korisnik ? rezultat.ocjena_korisnik : "-.-")
                        .replace(/%INSPEKCIJA%/g, rezultat.ocjena_inspekcija ? rezultat.ocjena_inspekcija : "-.-");
                    holder.append($(childRaw));
                }
                $(".wrapper-16-9").hover(function () {
                    var mdh = $(this).find(".main-description-head");
                    var mdb = $(this).find(".main-description-body");
                    var height = Math.max($(this).height() - mdh.outerHeight() - mdb.outerHeight(), 0);

                    mdh.animate({marginTop: height}, 250, 'swing');
                }, function () {
                    var mdh = $(this).find(".main-description-head");
                    var mdb = $(this).find(".main-description-body");
                    var height = $(this).height() - mdh.outerHeight();

                    mdh.animate({marginTop: height}, 250, 'swing');
                });
            }
            function updateRezultate() {
                var imaOcjenaKorisnik = inputPoOcjeniKorisnik.is(':checked');
                var imaOcjenaInspekcije = inputPoOcjeniInspekcije.is(':checked');


                posaljiPost({
                    akcija: "pretragaRestorana",
                    dio_naziva_restorana: inputImeRestoran.val(),
                    vrsta_restorana: inputVrstaRestoran.val(),
                    ocjena_inspekcija_manja_od: imaOcjenaInspekcije ? sliderInspekcijaMax.slider("option", "value") : null,
                    ocjena_inspekcija_veca_od: imaOcjenaInspekcije ? sliderInspekcijaMin.slider("option", "value") : null,
                    ocjena_korisnik_manja_od: imaOcjenaKorisnik ? sliderKorisnikMax.slider("option", "value") : null,
                    ocjena_korisnik_veca_od: imaOcjenaKorisnik ? sliderKorisnikMin.slider("option", "value") : null,
                    sortiranje: $("#sortiranje").data('id')
                }, pretragaCallback);
            }
            $(function () {
                $().width(function () {
                    updatePretrazivacWidth();
                });


                $(window).scroll(function () {
                    updatePretrazivacTop();
                });

                $(".dropdown ul li a").click(function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var dropdown = $(this).closest(".dropdown");
                    dropdown.data('id', $(this).data('id'));

                    dropdown.find("button>span:first-child").html($(this).html().trim());
                    $("body").trigger("click");

                    updateRezultate();
                });

                sliderInspekcijaMax.slider({
                    animate: false,
                    value: 5,
                    min: 1,
                    max: 5,
                    step: 0.1,
                    slide: function (event, ui) {
                        updateSlider(1, ui.value);
                    },
                    change: updateRezultate
                });
                sliderInspekcijaMin.slider({
                    animate: false,
                    value: 2,
                    min: 1,
                    max: 5,
                    step: 0.1,
                    slide: function (event, ui) {
                        updateSlider(2, ui.value);
                    },
                    change: updateRezultate
                });
                sliderKorisnikMax.slider({
                    animate: false,
                    value: 5,
                    min: 1,
                    max: 5,
                    step: 0.1,
                    slide: function (event, ui) {
                        updateSlider(3, ui.value);
                    },
                    change: updateRezultate
                });
                sliderKorisnikMin.slider({
                    animate: false,
                    value: 2,
                    min: 1,
                    max: 5,
                    step: 0.1,
                    slide: function (event, ui) {
                        updateSlider(4, ui.value);
                    },
                    change: updateRezultate
                });

                inputPoOcjeniInspekcije.on('switchChange.bootstrapSwitch', function () {
                    $("#ocjena-inspekcije-holder").slideToggle(300);
                    updateRezultate();
                });

                inputPoOcjeniKorisnik.on('switchChange.bootstrapSwitch', function () {
                    $("#ocjena-korisnik-holder").slideToggle(300);
                    updateRezultate();
                });

                inputImeRestoran.keyup(updateRezultate);
                inputVrstaRestoran.keyup(updateRezultate);


                $(".btn-pretrazi").click(updateRezultate);


                updatePretrazivacWidth();
                updateRezultate();
            });
        </script>
    <?php
    }


    private function generirajRezultatePretrage()
    {
        /**
         * @see Ugostitelj::dohvatiUgostiteljeUzUvjete
         */
        $rows = Ugostitelj::dohvatiUgostiteljeUzUvjete();

        ?>
        <div id="rezultati-holder" class="one_full">
            <?php
            /**
             * todo: ne korisitit <center> ni <br>
             */
            foreach ($rows as $row)
            {
                $this->generirajBlokUgostitelja($row);
            }
            ?>
        </div>
    <?php
    }

    /**
     * @param array $row
     */
    private function generirajBlokUgostitelja($row)
    {
        $naziv = $row['ime_restoran'];
        $url_slike_lokala = $row['url_slike_lokala'];
        $adresa = $row['adresa'];
        $id = $row['id_ugostitelj'];
        ?>
        <div class="col-xs-6">
            <a href="/ugostitelj.php?id=<?= $id ?>">
                <div class="wrapper-16-9">
                    <div class="main" style="background-image: url(<?= $url_slike_lokala; ?>)">

                        <div class="main-description-head">
                            <span><?= $naziv; ?></span>
                            <span
                                class="pull-right"><?= $row['ocjena_korisnik'] ? $row['ocjena_korisnik'] : "-.-" ?></span>
                        </div>

                        <div class="main-description-body">
                            <p class="lead" style="font-size: 16px;text-align: left">
                                <strong>Adresa </strong><?= $adresa; ?><br>
                                <strong>Vrste </strong><?= $row['vrste_restoran']; ?><br> <strong>Ocjena
                                    korisnika </strong><?= $row['ocjena_korisnik']; ?><br> <strong>Ocjena
                                    inspekcije </strong><?= $row['ocjena_inspekcija']; ?></p>

                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php
    }

    private function generirajKontrolerPretrage()
    {
        ?>
        <div id="pretrazivac" class="thumbnail affix" style="padding: 0 10px;">
            <h3 class="text-center">
                Pretraži
            </h3>

            <div class="form-group col-xs-12">
                <label for="input-ime-restoran">Po imenu restorana</label>
                <input type="text" id="input-ime-restoran" class="form-control" name="imeRestoran" value=""
                       placeholder="Unesite ime restorana" required/>
            </div>
            <div class="form-group col-xs-12">
                <label for="input-vrsta-restoran">Po vrsti restorana</label>
                <input type="text" id="input-vrsta-restoran" class="form-control" name="imeRestoran" value=""
                       placeholder="Unesite vrstu restorana"/>
            </div>
            <div class="form-inline col-xs-12">
                <label for="input-po-ocjeni-inspekcije " style="line-height: 34px">Po ocjeni inspekcije</label>

                <div class="pull-right">

                    <input type="checkbox" id="input-po-ocjeni-inspekcije" class="form-control right"/>
                </div>
            </div>
            <div id="ocjena-inspekcije-holder" class="form-group col-xs-offset-1 col-xs-10" style="display: none;">
                <label style="font-weight: normal">Ocjena inspekcije između</label>

                <div class=" col-xs-12 ui-slider-holder">
                    <div class="col-xs-12">
                        <div id="sliderInspekcijaMax"
                             class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"
                             aria-disabled="false">
                            <a class="ui-slider-handle ui-state-default ui-corner-all" href="#" style="left: 57%;">
                                <label>
                                    <span class="glyphicon glyphicon-chevron-left"></span>
                                    <span class="price">5.0</span>
                                    <span class="glyphicon glyphicon-chevron-right"></span>
                                </label></a>
                        </div>
                    </div>
                </div>
                <div class=" col-xs-12 ui-slider-holder">

                    <div class="col-xs-12 ">
                        <div id="sliderInspekcijaMin"
                             class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"
                             aria-disabled="false">
                            <a class="ui-slider-handle ui-state-default ui-corner-all" href="#" style="left: 57%;">
                                <label>
                                    <span class="glyphicon glyphicon-chevron-left"></span>
                                    <span class="price">2.0</span>
                                    <span class="glyphicon glyphicon-chevron-right "></span>
                                </label></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-inline col-xs-12">
                <label for="input-po-ocjeni-korisnika " style="line-height: 34px">Po ocjeni korisnika</label>

                <div class="pull-right">

                    <input type="checkbox" id="input-po-ocjeni-korisnika" class="form-control right"/>
                </div>
            </div>
            <div id="ocjena-korisnik-holder" class="col-xs-offset-1 col-xs-10  form-group " style="display: none;">
                <label style="font-weight: normal">Ocjena korisnika između</label>

                <div class="ui-slider-holder">
                    <div class="col-xs-12">
                        <div id="sliderKorisnikMax"
                             class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"
                             aria-disabled="false">
                            <a class="ui-slider-handle ui-state-default ui-corner-all" href="#" style="left: 57%;">
                                <label>
                                    <span class="glyphicon glyphicon-chevron-left"></span>
                                    <span class="price">5.0</span>
                                    <span class="glyphicon glyphicon-chevron-right"></span>
                                </label></a>
                        </div>
                    </div>
                </div>
                <div class=" col-xs-12 ui-slider-holder">

                    <div class="col-xs-12 ">
                        <div id="sliderKorisnikMin"
                             class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"
                             aria-disabled="false">
                            <a class="ui-slider-handle ui-state-default ui-corner-all" href="#" style="left: 57%;">
                                <label>
                                    <span class="glyphicon glyphicon-chevron-left"></span>
                                    <span class="price">2.0</span>
                                    <span class="glyphicon glyphicon-chevron-right "></span>
                                </label></a>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-inline col-xs-12">
                <label for="sortiranje" style="line-height: 34px">Sortiranje</label>

                <div class="pull-right">


                    <div class="dropdown" id="sortiranje" data-id="nasumicno">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"
                                aria-expanded="true">

                            <span> Nasumično</span>

                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                            <li role="presentation">
                                <a href="" data-id="nasumicno" role="menuitem" tabindex="-1">Nasumično</a>
                            </li>
                            <li role="presentation">
                                <a href="" data-id="ocjenaKorisnikD" role="menuitem" tabindex="-1">Po ocjeni korisnika
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="" data-id="ocjenaInspekcijaD" role="menuitem" tabindex="-1">Po ocjeni
                                    inspekcije
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="" data-id="abecednoA" role="menuitem" tabindex="-1">Abecedno</a>
                            </li>

                        </ul>

                    </div>


                </div>
                <br>


                <div class="col-xs-12 text-center" style="margin-bottom: 20px"></div>

                <br>

                <div class="col-xs-12 text-center" style="margin-bottom: 20px">


                    <?php



                    $this->generirajGumb(
                        "btn btn-primary btn-pretrazi",
                        "glyphicon glyphicon-search",       //ovo neeee treeebaaaa"!!!!!!
                        "PRETRAŽI",
                        null
                    )
                    ?>
                </div>
            </div>

        </div>


    <?php
    }

}

