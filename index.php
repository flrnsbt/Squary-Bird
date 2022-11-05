<?php

require_once "HTML.php";
require_once "utils.php";
require_once "settings.php";

/**
 * ITE220 - Web Development II
 * Assignment 1 Week 8 - Florian Sabate
 *
 * Login (Username, Email, Phone) and Registration website using MySQL database
 * Most functionalities work with or without Javascript
 *
 * Database configuration in settings.php
 *
 */

ini_set('session.gc_maxlifetime', 3600); //session timeout set to 1h

session_start();

$username = "Guest";
if (exists('user_id')) {
    if (!isLoginSessionExpired()) {
        if (isSameClient()) {
            $userId = read('user_id');
            $username = read('username');
        }
    }
}

//Instantiate the HomePage HTML object
$homePage = new HTML();

//render the header of the page
$homePage->header();
echo "<script src='js/jquery-3.6.0.min.js'></script>";
echo "<script src='js/main_script.js'></script>"; 
echo '<script src="js/game_components.js"></script>';
echo '<script src="js/game.js"></script>';
echo '<link rel="stylesheet" href="style/style.css">';
//open the body HTML tag
$homePage->startBody();

$homePage->noScript();
$homePage->navigationBar(isset($userId), WEBSITETREE);
$homePage->sideBar();

echo "<div id='pageContent'></div>";

//close the body HTML tag
$homePage->endBody();

//close the <html> tag
$homePage->footer();


/**
 * Custom function to check whether the current login auth is expired or not
 * from time out constant value "SESSION_TIMEOUT" set in settings.php
 *
 * used instead of "auth.gc_maxlifetime" because more reliable and less cost-intensive
 * @return bool TRUE if the auth is expired, FALSE otherwise
 */
function isLoginSessionExpired(): bool
{
    if (exists("logged_time_stamp")) {
        if (((time() - intval(read('logged_time_stamp'))) > SESSION_TIMEOUT)) {
            logOut();
            return true;
        }
    }
    return false;
}

/**
 * Custom function to check whether the current client user is the same both checking IP address
 * and HTTP User Agent
 *
 * @return bool TRUE if the client is the same (if IP and the HTTP_USER_AGENT
 * are the same as the ones stored), FALSE otherwise
 *
 */
function isSameClient(): bool
{
    if (exists('ip')) {
        if (read('ip') != hash('sha512', $_SERVER['REMOTE_ADDR']) or read('user_agent') != hash('sha512', $_SERVER['HTTP_USER_AGENT'])) {
            logOut();
            return false;
        }
    }
    return true;
}

/**
 * Logout the current user and add logout reason feedback if provided
 * @param string|null Reason of log out
 */
function logOut()
{
    session_unset();
    session_destroy();
    header("Location: index.php");
}
