<?php

/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 20.11.14.
 * Time: 17:10
 */
class DBfacade
{


    // Define database parameters
    /**
     * @var string
     */
    private static $username = "root";
    /**
     * @var string
     */
    private static $password = "jurinjekralj";
    /**
     * @var string
     */
    private static $host = "localhost";
    /**
     * @var string
     */
    private static $dbname = "njam";

    /**
     * @var array
     */
    private static $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];

    // database object


    /**
     * @var $db PDO
     */
    private static $db;

    // checks if there is already certain key in certain column of table

    /**
     * @param $table
     * @param $column
     * @param $key
     *
     * @return bool
     */
    public static function existsKey($table, $column, $key)
    {
        $querry = "
            SELECT {$column} FROM {$table}
            WHERE {$column} = :ky
        ";

        $params = [
            'ky' => $key
        ];

        $res = self::receive($querry, $params);

        return ($res && !$res->fetch());
    }

    /**
     * @param $table
     * @param $column
     * @param $id_column
     * @param $id_val
     * @param $value
     *
     * @return bool
     */
    public static function updateValue($table, $column, $id_column, $id_val, $value)
    {
        $querry = "
            UPDATE {$table}
            SET {$column} = :val
            WHERE {$$id_column} = :id
        ";

        $params = [
            'id' => $id_val,
            'val' => $value
        ];

        $res = self::receive($querry, $params);

        return ($res && !$res->fetch());
    }


    /**
     * @param $table
     * @param $id_column
     * @param $id_val
     *
     * @return PDOStatement
     */
    public static function receiveRowsByColumnId($table, $id_column, $id_val)
    {
        $querry = "
            SELECT * FROM {$table}
            WHERE {$id_column} = :id
        ";

        $params = [
            'id' => $id_val
        ];

        return self::receive($querry, $params);
    }

    /**
     * @param $query
     * @param $params
     *
     * @return PDOStatement
     */
    public static function receive($query, $params) // throws PDOException (fetched)
    {
            if (!self::$db)
                self::initDB();

            $convmap = [0x80, 0xffff, 0, 0xffff];
            foreach ($params as $key => $value)
                if ($value != null)
                    $params[$key] = mb_encode_numericentity($value, $convmap, 'UTF-8');

            // Execute the query against the database
            $stmt = self::$db->prepare($query);
            $stmt->execute($params);


        // $stmt->fetch() fetches rows
        return $stmt;
    }

    /**
     * @param string $querry
     * @param array  $params
     *
     * @return array
     */
    public static function receiveAll($querry, $params)
    {
        return self::receive($querry, $params)->fetchAll();
    }

    /**
     * @param $query
     * @param $params
     *
     */
    public static function send($query, $params) // throws PDOException
    {
        self::receive($query, $params);
    }

    /**
     *
     */
    private static function initDB() // throws PDOException
    {
        $host = self::$host;
        $dbname = self::$dbname;
        $username = self::$username;
        $password = self::$password;
        $options = self::$options;

        $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options);

        // Throw exception if something fails
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Set format of returned rows
        // Array will have string indexes, where the string value represents the name of the column in your database.
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::$db = $db;
    }
}
