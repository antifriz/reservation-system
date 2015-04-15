<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 26.12.14.
 * Time: 23:47
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
defined('MAIL_TEMPLATE_DIR') or define('MAIL_TEMPLATE_DIR', DOCUMENT_ROOT."/sastavnice/mail-templates/");


/**
 * Class Mail
 */
class Mail {
    const SERVER_NAME = "mali.zmaj.me";
    const ORIGIN_MAIL_ADDRESS = "noreply@mali.zmaj.me";
    const ADMIN_MAIL = "nirujnavi+maliali@gmail.com";

    /**
     * @param $email
     * @param $subject
     * @param $msg
     *
     * @return bool
     */
    private static function sendMail($email,$subject,$msg)
    {
        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/plain; charset=iso-8859-1\r\n";
        $header .= "From: ". self::SERVER_NAME . " <" . self::ORIGIN_MAIL_ADDRESS . ">\r\n";

        $message = $msg;

        $message = wordwrap($message, 70);

        return mail($email,$subject,$message,$header);
    }

    /**
     * @param $name
     *
     * @return string
     */
    private static function getTemplate($name)
    {
        return file_get_contents(MAIL_TEMPLATE_DIR.$name);
    }

    /**
     * @param $template_name
     * @param $arr
     *
     * @return mixed|string
     */
    private static function createMessage($template_name,$arr)
    {
        $msg = self::getTemplate($template_name);

        $default = [
            '%WEBSTRANICA%' => self::SERVER_NAME
            ,'%ADMIN%' => self::ADMIN_MAIL
        ];

        $arr = $arr + $default;

        foreach($arr as $key => $value)
            $msg = str_replace($key,$value,$msg);

        return $msg;
    }

    /**
     * @param $p
     */
    public static function sendContactForm($p)
    {
        $replacement = [
            '%PORUKA%' => $p['message'],
            '%IME%' => $p['name'],
            '%PREZIME%'=> $p['surname'],
            '%EMAIL%' => $p['email']
        ];

        self::sendMail(
            self::ADMIN_MAIL
            ,"Kontakt poruka"
            ,self::createMessage('nova-kontakt-poruka.txt',$replacement)
        );

        self::sendMail(
            $p['email']
            ,"Automatska poruka"
            ,self::createMessage('poslali-ste-kontakt-poruku.txt',$replacement)
        );
    }

    /**
     * @param $data
     *
     * @return string
     */
    private static function stringifyNaruceno($data)
    {
        return html_entity_decode(json_encode($data, JSON_PRETTY_PRINT),'UTF-8');
    }

    /**
     * @param $ID
     * @param $Data
     * @param $adresa
     * @param $poslovnica
     * @param $zahtjevi
     */
    public static function sendNarudzba($ID, $Data, $adresa, $poslovnica, $zahtjevi)
    {
        require_once DOCUMENT_ROOT."/modules/Session.php";

        $user = Session::dohvatiTrenutnogKorisnika();

        $replacement = [
            '%IME%' => $user['fname'],
            '%PREZIME%' => $user['lname'],
            '%EMAIL%' => $user['email'],
            '%TELEFON%' => $user['phone'],
            '%ADRESA_DOSTAVE%' => $adresa,
            '%DODATNI_ZAHTJEVI%' => $zahtjevi,
            '%ID_NARUDZBE%' => $ID,
            '%NARUCENO%' => self::stringifyNaruceno($Data)
        ];

        self::sendMail(
            self::ADMIN_MAIL
            ,"Narudžba #".$ID
            ,self::createMessage('nova-narudzba.txt',$replacement)
        );

        self::sendMail(
            $user['email']
            ,"Naručeno #".$ID." [automatska poruka}"
            ,self::createMessage('poslali-ste-novu-narudzbu.txt',$replacement)
        );
    }


    /**
     * @param $user
     */
    public static function sendUserAdd($user)
    {
        $replacement = [
            '%IME%' => $user['fname'],
            '%PREZIME%' => $user['lname'],
            '%EMAIL%' => $user['email'],
            '%ADRESA%' => $user['address'],
            '%TELEFON%' => $user['phone']
        ];

        self::sendMail(
            $user['email']
            ,"Uspješna registracija [automatska poruka]"
            ,self::createMessage('uspjesna-registracija.txt',$replacement)
        );

    }

    /**
     * @param      $stat
     * @param      $id
     * @param null $odbijenica
     */
    public static function sendNarudzbaStatus($stat, $id, $odbijenica=null)
    {
        $replacement = [
            '%ID_NARUDZBA%'=> $id,
            '%ODBIJENICA%' => $odbijenica?" uz slijedeći razlog:\n".$odbijenica:""
        ];

        require_once DOCUMENT_ROOT."/modules/Narudzba.php";

        $nar = new Narudzba($id,false);

        require_once DOCUMENT_ROOT."/modules/User.php";

        $user = User::get($nar->UserId);


        $template = 'narudzba-';
        switch($stat)
        {
            case 3:
                $template.='prihvaceno.txt';
                $status = "Prihvaćeno";
                break;
            case 4:
                $template.='odbijeno.txt';
                $status = "Odbijeno";
                break;
            case 5:
                $template.='isporuceno.txt';
                $status = "Isporučeno";
                break;
            default:
                return;
        }

        self::sendMail(
            $user['email']
            ,$status." #".$id." [automatska poruka]"
            ,self::createMessage($template,$replacement)
        );
    }


}