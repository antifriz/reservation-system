<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 20.11.14.
 * Time: 16:29
 */

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT."/sastavnice/DBfacade.php"; # TODO: nepotrebno, pogledaj dohvati poslovnice!!!
require_once DOCUMENT_ROOT."/sastavnice/Session.php";


/**
 * Class User
 */
abstract class User {
    private $id;
    private $nickname;
    private $email;

    private static $dbTableName = 'app_user';

    const MSG_EMAIL_NOT_FOUND = 'Ne postoji korisnik s takvom Email adresom.';
    const MSG_PASSWORD_WRONG = 'Šifra je pogrešna.';
    const MSG_EMAIL_INVALID = 'Email nije ispravan.';
    const MSG_PASSWORD_INVALID = 'Šifra nije ispravno unešena.';
    const MSG_EMAIL_TAKEN = 'Email zauzet.';
    const MSG_INVALID_INPUT = 'Neispravan unos.';

    const MSG_USER_TYPE_NOT_FOUND = 'User type not found.';


    const CUSTOMER_ID = 0;
    const CUSTOMER_HOME_PAGE = "/dnevna-ponuda.php";

    const POSLOVNICA_ID = 1;
    const POSLOVNICA_HOME_PAGE = "/poslovnica";

    const ADMIN_ID = 2;
    const ADMIN_HOME_PAGE = "/admin";


    /**
     * @param $typ_id
     *
     * @return string
     */
    public static function getHomePage($typ_id)
    {
        switch($typ_id)
        {
            case self::ADMIN_ID:
                return self::ADMIN_HOME_PAGE;
            case self::POSLOVNICA_ID:
                return self::POSLOVNICA_HOME_PAGE;
            case self::CUSTOMER_ID:
            default:
                return self::CUSTOMER_HOME_PAGE;
        }
    }

    /**
     * @param $row
     */
    public static function redirectToHomePage($row)
    {
        $home_page = $row?self::getHomePage($row['typeof']):"/";

        Session::preusmjeriNaStranicu($home_page);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public static function get($id)
    {
        $rows = DBfacade::receiveRowsByColumnId(self::$dbTableName, 'id', $id);

        return $rows -> fetch();
    }

    /**
     * @param $email
     * @param $password
     *
     * @return mixed
     * @throws Exception
     */
    public static function getLoginData($email, $password)
    {
        $column_hashed_password = 'hashed_password';
        $column_salt = 'salt';

        $rows = DBfacade::receiveRowsByColumnId(self::$dbTableName, 'email', $email);

        $row = $rows -> fetch();

        if(!$row)
            throw new Exception(self::MSG_EMAIL_NOT_FOUND);

        if(!self::checkPassword($password, $row[$column_hashed_password], $row[$column_salt]))
            throw new Exception(self::MSG_PASSWORD_WRONG);

        unset($row[$column_hashed_password]);
        unset($row[$column_salt]);

        return $row;
    }

    /**
     * @param $data
     *
     * @throws Exception
     */
    public static function add($data) // throws Exception
    {
        // generating salt and hashed password to check against
        $salt = self::generateSalt();
        $hashedPassword = self::hashPassword($data['pass'], $salt);

        // check if email is valid
        if(!self::isValidEmail($data['email']))
            throw new Exception(self::MSG_EMAIL_INVALID);

        // check if password is valid
        if(!self::isValidPassword($data['pass']))
            throw new Exception(self::MSG_PASSWORD_INVALID);

        // check if email exists
        if (!DBfacade::existsKey(self::$dbTableName,'email',$data['email']))
            throw new Exception(self::MSG_EMAIL_TAKEN);

        // create querry
        $querry = "
            call dodaj_korisnika(:fname,:lname,:email,:pass,:address,:phone,:hashed_password,:salt);
        ";

        $params = array_merge($data,[
            'hashed_password' => $hashedPassword,
            'salt' => $salt
        ]);

        try{
            DBfacade::send($querry, $params);
        }
        catch(Exception $e)
        {
            throw new Exception(self::MSG_INVALID_INPUT);
        }

        require_once DOCUMENT_ROOT."/modules/Mail.php";

        Mail::sendUserAdd($data);

        Session::login($data['email'],$data['pass']);
    }

    /**
     * @param $pass
     * @param $salt
     *
     * @return string
     */
    private static function hashPassword($pass, $salt)
    {
        $result = $pass;
        for($round = 0; $round < 65536; $round++)
        {
            $result = hash('sha256', $result.$pass. $salt);
        }
        return $result;
    }

    /**
     * @return string
     */
    private static function generateSalt()
    {
        return dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
    }

    // checks if email is of a right format
    /**
     * @param $email
     *
     * @return mixed
     */
    private static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }


    // checks if password is of a right format
    /**
     * @param $password
     *
     * @return bool
     */
    private static function isValidPassword($password)
    {
        // TODO
        return true;
    }

    // checks password against hash
    /**
     * @param $password
     * @param $hash
     * @param $salt
     *
     * @return bool
     */
    private static function checkPassword($password, $hash, $salt)
    {
        return (self::hashPassword($password,$salt) === $hash);
    }

    /**
     * @param $row
     *
     * @return string
     */
    public static function getFullName($row)
    {
        return $row['fname'] . ' ' . $row['lname'];
    }

    /**
     * @return array
     */
    public static function getAllUsers()
    {
        return DBfacade::receive('SELECT * FROM app_user order by typeof desc,registration_timestamp desc ',[])->fetchAll();
    }
}