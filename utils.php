<?php
require_once "settings.php";

/**
 * Function used to sanitize a given string or string array
 *
 *  Using stripslashes, strip_tags and htmlentities PHP functions
 *
 * @param $var string|string[] string or string array variable to be sanitize
 * @return string|string[] sanitized variable
 */
function sanitizeString(array|string $var): array|string
{
    if (is_string($var)) {
        $var = stripslashes($var);
        $var = strip_tags($var);
        return htmlentities($var);
    } else if (is_array($var)) {
        foreach($var as $k => $v){
            $var[$k] = stripslashes($v);
            $var[$k] = strip_tags($v);
            $var[$k] = htmlentities($v);
        }
        return $var;
    }else{
        return "";
    }
}


/**
 * Unset all the keys of $_SESSION or $_COOKIE array excepts the ones specified in param
 * @param string|string[]|null keys to be kept
 */
function unsetAllExcept($keys)
{
    foreach (USE_COOKIE ? $_COOKIE : $_SESSION as $key => $value) {
        if (is_array($keys) and !in_array($key, $keys) or is_string($keys) and $key != $keys or $keys == null) {
            remove($key);
        }
    }
}


/**
 * Save value whether in $_COOKIE or in $_SESSION depending on the
 * value of the USE_COOKIE constant
 * @param string $key corresponds to the name of the cookie or $_SESSION key
 * @param string $value stored at specific key inside SESSION or COOKIE
 */
function set(string $key, string $value)
{
    if (USE_COOKIE) {
        setcookie($key, $value, COOKIE_OPTIONS);
    } else {
        $_SESSION[$key] = $value;
    }
}


/**
 * @param string $key the variable to be checked
 * @return bool whether the current key is set in the used method of authentication
 * (in $_COOKIE or $_SESSION)
 */
function exists(string $key): bool
{
    if (USE_COOKIE) {
        return isset($_COOKIE[$key]);
    } else {
        return isset($_SESSION[$key]);
    }
}

/**
 * Return the value stored at the specific key
 *
 * Make sure that the key does exists first by using exists function
 *
 * @param string $key key at which the value is stored
 * @return string the value stored at a specific $key in whether $_COOKIE or $_SESSION variable
 * depending on current method of authentication
 */
function read(string $key): ?string
{
    if (USE_COOKIE) {
        return $_COOKIE[$key];
    } else {
        return $_SESSION[$key];
    }
}

/**
 * Unset a specific cookie or a session data
 * Cookie are unset by setting an expiration date in the past
 *
 * @param string $key variable to be unset
 */
function remove(string $key)
{
    if (USE_COOKIE) {
        $_COOKIE . setcookie($_COOKIE["key"], "", 1);
    } else {
        unset($_SESSION[$key]);
    }
}