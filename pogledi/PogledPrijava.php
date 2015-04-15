<?php

/**
 *
 */
defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";

/**
 * Class PogledLogin
 */
class PogledPrijava extends Pogled
{
    /**
     * @var
     */
    private $err;

    /**
     * @param string|null $err
     */
    function __construct($err)
    {
        $this->err = $err;
    }


    /**
     *
     */
    public function generiraj()
    {
        $error = $this->err;

        ?>
        <div class="container" style="min-height: 50%">
            <div class="page-header">
                <h1>Prijavi se</h1>
            </div>

            <div class="row" style="padding-bottom: 20px;
            ">
                <div class="prijava col-xs-7">
                    <?php if($error): ?>
                        <div class=" col-xs-12">
                            <div class=" alert alert-danger">
                                <strong>Greška </strong><?php echo $error; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <form class="form" action="../prijava.php" method="post" enctype="multipart/form-data">
                        <div class="form-group col-xs-12">
                            <label for="input-username"> Korisničko ime: </label>
                            <input autofocus type="text" id="input-username" class="form-control" name="username" />
                        </div>
                        <div class="form-group col-xs-12">
                            <label for="input-password"> Lozinka: </label>
                            <input type="password" id="input-password" class="form-control"  name="password" />
                        </div>
                        <div class="col-xs-4 text-right">
                            <button type="submit" class="btn btn-success btn-prijavi">
                                <span class="glyphicon glyphicon-ok"></span>
                                Prijava
                            </button>
                        </div>
                        <div class="col-xs-12">
                            <div style="margin: 10px 20px 20px 10px;border-bottom: 2px solid #e0e0e0"></div>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    <?php
    }

    /**
     *
     */
    protected function generirajJSdodatno()
    {
        // TODO: Implement generirajJSdodatno() method.
    }
}

?>