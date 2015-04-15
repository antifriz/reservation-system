<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/pogledi/PogledKorisnik.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Korisnik.php";


/**
 * ne ulogiran nema tu šta raditi, preusmjeri ga na početnu stranicu
 */
Session::akoNijeUlogiranPreusmjeri();

/**
 * dohvati trenutnog korisnika
 */
$trenutniKorisnik = Session::dohvatiTrenutnogKorisnika();

$err = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    switch ($trenutniKorisnik->getIdVrsta())
    {
        case Korisnik::UGOSTITELJ:
            try
            {

                /**
                 * @param $file
                 *
                 * @return null|string
                 */
                function getUploadedUrl($file)
                {
                    // Undefined | Multiple Files | $_FILES Corruption Attack
                    // If this request falls under any of them, treat it invalid.
                    if (!isset($file['error']) || is_array($file['error'])
                    )
                    {
                        return null;//throw new RuntimeException('Invalid parameters.');
                    }

                    // Check $_FILES['upfile']['error'] value.
                    switch ($file['error'])
                    {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            return null;//throw new RuntimeException('No file sent.');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            return null;//throw new RuntimeException('Exceeded filesize limit.');
                        default:
                            return null;//throw new RuntimeException('Unknown errors.');
                    }

                    // You should also check filesize here.
                    if ($file['size'] > 1000000)
                    {
                        return null;//throw new RuntimeException('Exceeded filesize limit.');
                    }

                    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                    // Check MIME Type by yourself.
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    if (false === $ext = array_search(
                            $finfo->file($file['tmp_name']),
                            [
                                'jpg' => 'image/jpeg',
                                'png' => 'image/png',
                                'gif' => 'image/gif',
                            ],
                            true
                        )
                    )
                    {
                        return null;//throw new RuntimeException('Invalid file format.');
                    }

                    // You should name it uniquely.
                    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                    // On this example, obtain safe unique name from its binary data.
                    $newLocation = sprintf(
                        '/media/slike/%s.%s',
                        sha1_file($file['tmp_name']),
                        $ext
                    );
                    if (!move_uploaded_file(
                        $file['tmp_name'],
                        DOCUMENT_ROOT . $newLocation
                    )
                    )
                    {
                        return null;//throw new RuntimeException('Failed to move uploaded file.');
                    }

                    return $newLocation;
                }


                $urlLokacija = getUploadedUrl($_FILES['lokacijaSlika']);
                if (!$urlLokacija)
                    $urlLokacija = $trenutniKorisnik->getUrlSlikeLokala();

                $urlRasporeda = getUploadedUrl($_FILES['rasporedSlika']);
                if (!$urlRasporeda)
                    $urlRasporeda = $trenutniKorisnik->getUrlSlikeStolova();


                $_POST['lokacijaSlika'] = $urlLokacija;
                $_POST['rasporedSlika'] = $urlRasporeda;

                $trenutniKorisnik->osvjezi($_POST);

            }
            catch (Exception $e)
            {
                $err = $e->getMessage();
            }
            break;
        case Korisnik::GOST:
            try
            {
                $trenutniKorisnik->osvjezi($_POST);
            }
            catch (Exception $e)
            {
                $err = $e->getMessage();
            }
            break;
        default:
            Session::preusmjeriNaPocetnuStranicu();

    }
}

/**
 * generiraj pogled
 */
(new PogledKorisnik($trenutniKorisnik, $err))->generirajOkvir();
