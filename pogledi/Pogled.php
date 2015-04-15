<?php
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";


/**
 * Class Pogled
 */
abstract class Pogled
{

    /**
     * @var Korisnik
     */
    protected $subjekt = null;
    /**
     * @var Korisnik
     */
    protected $objekt = null;

    /**
     * @return mixed
     */
    protected abstract function generiraj();


    /*
     *
     * generiraj -> generirajheader + generirajbody + generirajfooter
     *
     * u htmlu ne koristiti <center> i slicne, sve raditi s divovima, npr <div class="text-center">ovo ce bit na sredini</div>
     *
     * bootstrap + shortcodes koje cu vam sad stavit u /js i /css
     *
     *
     */

    /**
     *
     */
    public function generirajOkvir()
    {
        $this->generirajZaglavlje();
        $this->generiraj();
        $this->generirajPodnozje();
    }

    protected function generirajGlavu()
    {
        ?>
        <!DOCTYPE html>
        <html lang="hr">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
            <title>Njam</title>

            <!-- Bootstrap -->
            <link href="/css/bootstrap.min.css" rel="stylesheet">

            <!-- ovdje je glavni css fajl -->
            <link href="/css/style.css" rel="stylesheet">

            <!-- ovdje je css fajl za responsive layout-->
            <link href="/css/style-responsive.css" rel="stylesheet">
            <link href="/css/style-shortcodes.css" rel="stylesheet">

            <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn't work if you view the page via file:// --><!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
            <style>

            </style>
        </head>
    <?php
    }


    protected function generirajZaglavlje()
    {
        $this->generirajGlavu();
        ?>
        <body>
        <div id="header">
            <div class="top-top navbar-fixed-top "
                 style="height: 150px;background: rgb(66,139,202);color: rgb(255,255,255) ; overflow: hidden">
                <div style="position:absolute;bottom:0;width: 100%">
                    <div class="h1 text-center">
                        njam.zmaj.me
                    </div>
                    <div class="lead text-center">
                        Ručati doma je stvar prošlosti...
                    </div>
                </div>
            </div>
            <!-- Navigation -->
            <nav class="navbar  navbar-fixed-top " role="navigation" style="margin-top: 0px; background-color:#262626">
                <div class="container">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header " style=" width: 100px; min-height: 1px">
                        <button type="button" class="navbar-toggle" data-toggle="collapse"
                                data-target="#bs-example-navbar-collapse-1">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="/"
                           style="position:absolute; top: 0px;background: url(/media/slike/njam-logo.png) no-repeat center; background-size: contain; height: 0px; width: 240px">
                            <img src="" alt="">
                        </a>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse navbar-inverse" id="bs-example-navbar-collapse-1"
                         style="padding-left: 200px; background-color:#262626">
                        <ul class="nav navbar-nav">
                            <li>
                                <a href="/">Početna</a>
                            </li>

                            <?php if (Session::jePrijavljen()): ?>
                                <li>
                                    <a href="/profil.php">Profil (<?= Session::dohvatiTrenutnogKorisnika()->getNadimak(
                                        ) ?>)
                                    </a>
                                </li>
                                <li>
                                    <a href="/odjava.php">Odjavi se</a>
                                </li>

                            <?php else: ?>
                                <li>
                                    <a href="/prijava.php">Prijavi se</a>
                                </li>
                                <li>
                                    <a href="/reggost.php">Registracija gosta</a>
                                </li>
                                <li>
                                    <a href="/regugostitelj.php">Registracija ugostitelja</a>
                                </li>
                            <?php endif; ?>
                            <?php if (Session::jeAdmin()): ?>
                                <li>
                                    <a href="/dnevnik.php">Dnevnik</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <!-- /.navbar-collapse -->
                </div>
                <!-- /.container -->
            </nav>
            <div style="height: 250px">

            </div>
        </div>
    <?php
    }

    protected function generirajPodnozje()
    {
        ?>
        <div id="footer" class="one_full"
             style="padding-top: 20px; padding-bottom: 20px; background:#262626; color:#aaa">
            <!--
                        <div class="container text-center" >
                            <img src="/media/images/logo-mali-vektor-white-309-245.png" style="max-width: 175px">
                        </div>!-->
            <div class="container">


                <div class="one_third">
                    <h3>Kontaktirajte nas</h3>

                    <p><b>Krumpiri</b></p>

                    <p>
                        <span class="glyphicon glyphicon-map-marker">&nbsp;</span>
                        FER, Unska 3
                    </p>

                    <p>
                        <span class="glyphicon glyphicon-phone-alt">&nbsp;</span>
                        +385 (0)1 *** ***
                    </p>
                    <p>
                        <span class="glyphicon glyphicon-envelope">&nbsp;</span>
                        <a href="mailto:nirujnavi+web@gmail.com">krumpiri@zmaj.me</a>
                    </p>

                    <div class="email">
                        <p>
                            <a href="mailto:nirujnavi+web@gmail.com"></a>
                        </p>
                    </div>
                </div>
                <div class="one_third">

                    <h3>Tko smo mi?</h3>

                    <p>Lovro Filipović</p>

                    <p>David Geček</p>

                    <p>Andrea Gradečak</p>

                    <p>Nika Jukić</p>

                    <p>Ivan Jurin</p>

                    <p>Igor Kramarić</p>

                </div>
                <div class="one_third last">
                    <h3>Web stranica</h3>

                    <p>Web-stranica:
                        <a href="mailto:nirujnavi+web@gmail.com">Krumpiri</a>
                    </p>

                </div>
            </div>
            <div class="container text-center"></div>

        </div>
        <?php $this->generirajJS(); ?>
        </body>
        </html>
    <?php
    }

    protected function generirajJS()
    {
        ?>
        <script src="/js/jquery-1.11.1.min.js"></script>

        <script src="/js/bootstrap.min.js"></script>
        <script src="https://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>

        <script src="/js/bootbox.min.js"></script>
        <script src="/js/bootstrap-switch.js"></script>

        <script src="/js/parallax.min.js"></script>


        <script type="text/javascript" src="/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="/js/jquery.timepicker.min.js"></script>



        <script src="/js/allInclusive.js"></script>

        <script>
            var navbar = $(".navbar");
            var zmajcek = $(".navbar-brand");
            var toptop = $(".top-top");
            function updateHeaderDisplay() {
                var top = window.pageYOffset || document.documentElement.scrollTop;
                if (top < 300) {
                    // navbar.animate({height: (150 - top / 2)}, 500);
                    navbar.css('margin-top', 150 - top / 2);
                    toptop.css('height', 150 - top / 2);

                    zmajcek.css('height', 250 - top / 1.75);
                    zmajcek.css('top', -150 + top / 2);
                }
                else {
                    zmajcek.css('height', 250 - 300 / 1.75);
                    zmajcek.css('top', 0);
                    toptop.css('height', 0);

                    navbar.css('margin-top', 0);
                }
            }
            $(function () {
                updateHeaderDisplay();

                $(window).scroll(function () {
                    updateHeaderDisplay();
                });
            });

        </script>

        <?php $this->generirajJSdodatno() ?>
    <?php
    }

    /**
     * @param $classes
     * @param $icon
     * @param $text
     * @param $atributi
     */
    protected function generirajGumb($classes, $icon, $text, $atributi = null, $href = null)
    {
        if ($atributi == null)
            $atributi = "";
        ?>
        <<?= $href ? 'a href="' . $href . '"' : 'button' ?> class="btn <?php echo $classes ?>"
                style="margin: 0 5px" <?php echo $atributi ?>>
            <span class="glyphicon <?php echo $icon ?>"></span>
            <span><?php echo $text ?></span>
        </<?= $href ? 'a' : 'button' ?>>
    <?php
    }

    protected abstract function generirajJSdodatno();

}

