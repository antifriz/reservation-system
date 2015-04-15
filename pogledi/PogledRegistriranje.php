<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";

/**
 * Class PogledRegistriranje
 */
class PogledRegistriranje extends Pogled
{
    /**
     * @var
     */
    private $err;

    /**
     * @var bool
     */
    private $jeGostinjski;

    /**
     * @param string|null $err
     */
    function __construct($err, $je_gostinjski)
    {
        $this->err = $err;
        $this->jeGostinjski = $je_gostinjski;
    }

    public function generiraj()
    {
        $error = $this->err;
        $je_gostinjski = $this->jeGostinjski;
        ?>
        <div class="container">
            <div class="page-header">
                <h1>Registracija <?= $je_gostinjski ? "gosta" : "ugostitelja" ?></h1>
            </div>
            <div class="row" style="padding-bottom: 20px;border-bottom: 2px solid #e0e0e0">
                <div class="regugostitelj col-xs-7">
                    <form class="form" action="" method="post">
                        <?php if ($error): ?>
                            <div class=" col-xs-12">
                                <div class=" alert alert-danger">
                                    <strong>Greška </strong><?php echo $error; ?>
                                </div>
                            </div>
                        <?php endif;?>
                        <div class="form-group col-xs-12">
                            <label for="input-korisnicko_ime"> Korisničko ime:</label>
                            <input autofocus type="text" id="input-korisnicko_ime" class="form-control" name="username" value="" required/>
                        </div>
                        <div class="form-group col-xs-12">
                            <label for="input-lozinka"> Lozinka: </label>
                            <input pattern=".{5,}" type="password" id="input-lozinka" class="form-control" name="password" value="" required/>
                        </div>
                        <div class="col-xs-12">
                            <div class="horizontal-line "></div>
                        </div>
                        <?php
                        $je_gostinjski ? $this->generirajGostinjski() : $this->generirajUgostiteljski() ?>
                        <br> <br>

                        <div class="col-xs-4 text-right">
                            <button type="submit" class="btn btn-success btn-regugostitelj">
                                <span class="glyphicon glyphicon-ok"></span>
                                Registriraj
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php
    }

    private function generirajGostinjski()
    {
        ?>
        <div class="form-group col-xs-12">
            <label for="input-ime"> Ime i prezime:</label>
            <input type="text" id="input-ime" class="form-control" name="ime" value="" required/>
        </div>
        <div class="form-group col-xs-12">
            <label for="input-email"> E-mail adresa: </label>
            <input type="email" id="input-email" class="form-control" name="email" value="" required/>
        </div>
    <?php
    }

    private function generirajUgostiteljski()
    {
        ?>

        <div class="form-group col-xs-12">
            <label for="input-ime_restoran"> Ime restorana:</label>
            <input type="text" id="input-ime_restoran" class="form-control" name="ime_restoran" value="" required/>
        </div>
        <div class="form-group col-xs-12">
            <label for="input-adresa"> Adresa:</label>
            <input type="text" id="input-adresa" class="form-control" name="adresa" value="" required/>
        </div>
        <div class="form-group col-xs-12">
            <label for="input-email"> E-mail adresa: </label>
            <input type="email" id="input-email" class="form-control" name="email" value="" required/>
        </div>
    <?php
    }

    protected function generirajJSdodatno()
    {
    }

}

