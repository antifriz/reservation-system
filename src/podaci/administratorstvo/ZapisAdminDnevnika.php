<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 12/16/14
 * Time: 6:47 PM
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";

defined('DNEVNIK_PUTANJA') or define('DNEVNIK_PUTANJA', DOCUMENT_ROOT . "/podaci/administratorstvo/dnevnik.txt");

/**
 * Class ZapisAdminDnevnika
 */
class ZapisAdminDnevnika
{
    /**
     * @param string $opisAkcije
     */
    public static function zapisi($opisAkcije)
    {
        date_default_timezone_set("Europe/Zagreb");
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.'.$micro, $t) );
        $vremenskaOznaka = $date->format("Y-m-d H-i-s.u");

        $user=Session::dohvatiTrenutnogKorisnika();
        if($user==null)
            $userId="neregistrirani korisnik";

        else
            $userId = $user->getId();

        $zapis = $vremenskaOznaka . " - " . $userId . " -> " . $opisAkcije . "\n";
// Write the contents to the file,
// using the FILE_APPEND flag to append the content to the end of the file
// and the LOCK_EX flag to prevent anyone else writing to the file at the same time
        file_put_contents(DNEVNIK_PUTANJA, $zapis, FILE_APPEND | LOCK_EX);
    }
}