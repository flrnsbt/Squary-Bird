<?php

require_once "db_credentials.php";

/**
 * Used to store some basics constants to use for authentication and websites functions
 */


/**
 * Root Directory
 */
const ROOT_PATH = "http://localhost/";


/**
 * Website's architecture (all the pages)
 */

const WEBSITETREE = ["home" => "Home", "contact-us" => "Contact Us"];


/**
 * Timeout in seconds after which the session will automatically logout
 */
const SESSION_TIMEOUT = 3600;

/**
 * Timeout in seconds after which the log-in attempt counter will reset
 */
const WAITING_TIME_TOO_MANY_REQUEST = 60;


/**
 * Default cookie setting used for this website
 */
const COOKIE_OPTIONS = array(
    "expires" => 0,
    "path" => ROOT_PATH,
    "domain" => "",
    "secure" => false,
    "httponly" => false,
);

/**
 *  constant used to inform if cookies authentication is used,
 * set to FALSE by default to use SESSION based authentication (more convenient and safer)
 *
 * define is used instead of const, because this constant value can change during the running time
 */
define("USE_COOKIE", false);


/**
 * function to easily manage our mySQL connection to make it accessible from everywhere,
 *
 * The function simply check if the static variable holding our mysql connection is declared,
 * instantiate it if not, and then return it
 * @return mixed|mysqli current mySQL connection
 */
function db () {
    static $conn;
    if ($conn===NULL){
        $conn = new mysqli('localhost',$db_user,
            $db_password, $db_name);
    }
    return $conn;
}