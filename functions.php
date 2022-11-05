<?php
include_once 'settings.php';
include_once 'utils.php';
header("Content-type: application/json");
session_start();

if (isset($_GET['f'])) {
    switch ($_GET['f']) {
        case "login":
            logIn();
            break;
        case "register":
            register();
            break;
        case "loadPage":
            loadPage();
            break;
        case "addScore":
            addScore();
            break;
        case "highScore":
            highScore();
            break;
        case "getRank":
            getRank();
            break;
        case "logout":
            logOut();
            break;
        case "getUserInfo":
            getUserInfo();
            break;
        case "updateAccount":
            updateAccount();
            break;
        case "deleteAccount":
            deleteAccount();
            break;
        case "sendEmail":
            sendEmail();
            break;
    }
} else {
    exit;
}

function logIn()
{
    $result = array();
    //  Get the client IP address to prevent hijacking
    $ip = $_SERVER['REMOTE_ADDR'];

    // reset if the login attempts counter is timed out (WAITING_TIME_TOO_MANY_REQUEST
    // defined in settings.php by default to 60s)
    if (exists('logged_time_stamp') and intval(read('logged_time_stamp')) + WAITING_TIME_TOO_MANY_REQUEST < time()) {
        session_unset();
    }
    // check if more than 5 wrong log-in attempts were made
    if (exists('attempts') and read('attempts') >= 5) {
        $result["type"] = "error";
        $result['data'] = "Too many login requests. Please try again in " . intval(read('logged_time_stamp')) + WAITING_TIME_TOO_MANY_REQUEST - time() . " seconds.";
    } else if (isset($_POST['loginType']) and isset($_POST[$_POST['loginType']]) and isset($_POST['password'])) {
        $password = sanitizeString($_POST['password']);
        $loginType = sanitizeString($_POST["loginType"]);
        $identifier = sanitizeString($_POST[$_POST['loginType']]);
        //  store auth log-in timestamp to be used for timeout function
        set("logged_time_stamp", time());

        if ($stmt = db()->prepare('SELECT user_id, username, password FROM users WHERE ' . $loginType . ' = ?')) {
            $stmt->bind_param('s', $identifier);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $username, $passwd);
                $stmt->fetch();
                if (password_verify($password, $passwd)) {
                    //Set 'username' SESSION key to $username value
                    set("user_id", $id);
                    set("username", $username);
                    //  Set 'ip' SESSION key with hashed $ip value
                    set("ip", hash(algo: "sha512", data: $ip));
                    //  Save hashed HTTP user agent into SESSION 'user_agent'
                    set("user_agent", hash(algo: "sha512", data: $_SERVER['HTTP_USER_AGENT']));
                    $result["type"] = "success";
                } else {
                    // Incorrect password
                    $result["type"] = "error";
                    $result['data'] = "Invalid credentials";
                    increaseLoginAttemptCounter();
                }
            } else {
                // Incorrect username
                $result["type"] = "error";
                $result['data'] = "Invalid credentials";
                increaseLoginAttemptCounter();
            }
            $stmt->close();
        }
    } else {
        $result["type"] = "error";
        $result['data'] = "Please fill in username and password input fields";
    }
    echo json_encode($result);
    die();
}

function increaseLoginAttemptCounter()
{
    if (!exists('attempts')) {
        set('attempts', 1);
    } else {
        set('attempts', intval(read('attempts')) + 1);
    }
}


/**
 * Function used to perform the registration of a new user
 * Use of AJAX to perform the registration with all the needed data set into the $_POST array
 *
 * Errors would be returned if the request couldn't be executed, if an error was met while
 * inserting the new user into the database table "users", with message set to "Unknown Error" to limit the understanding
 * of our system to the user and thus limits the security threats
 *
 * An error would also be returned if at least one of the followings fields is already presents in the database :
 * Username, Email or Phone
 *
 * Session is filled in with username and id of the user, and success is returned if the registration was done successfully
 */

function register()
{
    $result = array();
    //  Get the client IP address to prevent hijacking
    $ip = $_SERVER['REMOTE_ADDR'];

    if (
        isset($_POST['username']) and isset($_POST['password']) and isset($_POST['phone']) and isset($_POST['email']) and
        isset($_POST['name']) and isset($_POST['surname']) and isset($_POST['country_id']) and isset($_POST['city_name']) and
        isset($_POST['city_postal_code'])
    ) {
        $password = sanitizeString($_POST['password']);
        $username = sanitizeString($_POST['username']);
        $phone = sanitizeString($_POST['phone']);
        $email = sanitizeString($_POST['email']);
        $name = sanitizeString($_POST['name']);
        $surname = sanitizeString($_POST['surname']);
        $country = sanitizeString($_POST['country_id']);
        $city = sanitizeString($_POST['city_name']);
        $zipcode = sanitizeString($_POST['city_postal_code']);

        if ($stmt = db()->prepare('SELECT username, email, phone FROM users WHERE username = ? OR email = ? OR phone = ?')) {
            $stmt->bind_param('sss', $username, $email, $phone);
            $stmt->execute();
            $stmt = $stmt->get_result();
            if ($stmt->num_rows !== 0) {
                $stmt = mysqli_fetch_all($stmt, MYSQLI_ASSOC);
                $result['type'] = "error";
                $errors = array();
                for ($i = 0; $i < count($stmt); $i++) {
                    if ($stmt[$i]['username'] === $username) {
                        $errors["username"]  = "Username already used";
                    }
                    if ($stmt[$i]['email'] === $email) {
                        $errors["email"]  = "Email already used";
                    }
                    if ($stmt[$i]['phone'] === $phone) {
                        $errors["phone"]  = "Phone already used";
                    }
                }
                $result['data'] = $errors;
            } else {
                $password_hash = password_hash($password, algo: PASSWORD_BCRYPT);
                $stmt = db()->prepare("INSERT INTO users(username, password, phone, email, name, surname, country_id, city_name, city_postal_code) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssisi", $username, $password_hash, $phone, $email, $name, $surname, $country, $city, $zipcode);
                if ($stmt->execute()) {
                    // I store the user id in the SESSION array at the key "user_id",
                    // and not by using session_id() or Cookies, to be safer and prevent others user to easily
                    // access an user account by guessing and trying ids
                    set("user_id", db()->insert_id);
                    set("username", $username);
                    //  Set 'ip' SESSION key with hashed $ip value
                    set("ip", hash(algo: "sha512", data: $ip));
                    //  Save hashed HTTP user agent into SESSION 'user_agent'
                    set("user_agent", hash(algo: "sha512", data: $_SERVER['HTTP_USER_AGENT']));

                    set("logged_time_stamp", time());
                    $result['type'] = "success";
                } else {
                    $result['type'] = "error";
                    $result['data'] = "An unknown error occurred. Please try again later.";
                }
            }
        } else {
            $result['type'] = "error";
            $result['data'] = "An unknown error occurred. Please try again later.";
        }
    } else {
        $result['type'] = "error";
        $result['data'] = "Please fill all the fields out";
    }
    echo json_encode($result);
    die();
}

function logOut()
{
    session_unset();
    session_destroy();
    echo json_encode(array("success"));
    die();
}


function loadPage()
{
    if (!empty($_GET['content'])) {
        $page = "pages/" . sanitizeString($_GET['content']) . ".php";
        if (file_exists($page)) {
            include($page);
        } else {
            include("pages/404.php");
        }
    } else {
        include("pages/home.php");
    }
}

function addScore()
{
    if (isset($_POST['score'])) {
        $score = sanitizeString($_POST['score']);
        if (exists('user_id')) {
            $stmt = db()->prepare("INSERT INTO user_score(user_id, score) VALUES(?, ?)");
            $stmt->bind_param("ss", read('user_id'), $score);
            if ($stmt->execute()) {
                $result['type'] = "success";
            } else {
                $result['type'] = "error";
                $result['data'] = "An unknown error occurred. Please try again later.";
            }
        } else {
            set("high_score", $score);
            $result['type'] = "success";
            $result['data'] = "Not logged in";
        }
    } else {
        $result['type'] = "error";
        $result['data'] = "An unknown error occurred. Please try again later.";
    }
    echo json_encode($result);
}

function highScore()
{
    if (exists('user_id')) {
        if ($results = db()->query("SELECT score FROM user_score WHERE user_id = " . read('user_id') . " ORDER BY score DESC LIMIT 1")) {
            $result['type'] = "success";
            $result['data'] = mysqli_fetch_object($results);
        } else {
            $result['type'] = "error";
            $result['data'] = "An unknown error occurred. Please try again later.";
        }
    } else {
        if (!exists('high_score')) {
            $result['type'] = "error";
            $result['data'] = "Not logged in";
        } else {
            $result['type'] = "success";
            $result['data'] = read('high_score');
        }
    }
    echo json_encode($result);
}

function getRank()
{
    if (isset($_POST['score'])) {
        $score = (int)sanitizeString($_POST['score']);
        $stmt = db()->prepare("SELECT COUNT(*)+1 FROM user_score WHERE score > ?");
        $stmt->bind_param("i", $score);
        if ($stmt->execute()) {
            $stmt = $stmt->get_result();
            $result['type'] = "success";
            $result['data'] = mysqli_fetch_row($stmt)[0];
        } else {
            $result['type'] = "error";
            $result['data'] = "An unknown error occurred. Please try again later.";
        }
    } else {
        $result['type'] = "error";
        $result['data'] = "An unknown error occurred. Please try again later.";
    }
    echo json_encode($result);
}

function getUserInfo()
{
    if (exists('user_id')) {
        if ($results = db()->query("SELECT username, phone, email, name, surname, country_id, city_name, city_postal_code FROM users WHERE user_id = " . read('user_id'))) {
            if (($results = mysqli_fetch_assoc($results)) !== null) {
                $result['type'] = "success";
                $result['data'] = $results;
            } else {
                $result['type'] = "error";
                $result['data'] = "An unknown error occurred.";
            }
        } else {
            $result['type'] = "error";
            $result['data'] = "An unknown error occurred.";
        }
    } else {
        $result['type'] = "error";
        $result['data'] = "You are offline";
    }
    echo json_encode($result);
}

function sendEmail()
{
    if (isset($_POST['email']) && isset($_POST['message']) && isset($_POST['subject'])) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: <" . sanitizeString($_POST['email']) . ">";
        $mail_status = mail("1810220005@students.stamford.edu", sanitizeString($_POST['subject']), sanitizeString($_POST['message']), $headers);
        if ($mail_status) {
            $result['type'] = "success";
        } else {
            $result['type'] = "error";
            $result['data'] = "An unknown error occurred";
        }
    } else {
        $result['type'] = "error";
        $result['data'] = "Please fill out all the fields";
    }
    echo json_encode($result);
}

function updateAccount()
{
    $result = array();
    if (isset($_POST["data"])) {
        $data = sanitizeString($_POST["data"]);
        $query = "";
        if ($uniqueKeys = array_intersect_key($data, array_flip(array("username", "phone", "email")))) {
            $i = 0;
            foreach ($uniqueKeys as $k => $v) {
                $query .= $k . "= '" . $v . "'";
                if (++$i < count($uniqueKeys)) {
                    $query .= " OR ";
                }
            }
            $results = db()->query("SELECT username, phone, email FROM users WHERE " . $query);
            $results = mysqli_fetch_all($results, MYSQLI_ASSOC);
            if (count($results) > 0) {
                foreach ($results as $v) {
                    $result["error"] = array();
                    foreach (array_keys(array_intersect($v, $uniqueKeys)) as $k) {
                        array_push($result["error"], $k);
                        unset($data[$k]);
                    }
                }
            }
        }
        if (!empty($data)) {
            $query = "";
            $i = 0;
            foreach ($data as $k => $v) {
                $query .= $k . "= '" . $v . "'";
                if (++$i < count($data)) {
                    $query .= " , ";
                }
            }
            if (db()->query("UPDATE users SET " . $query . "  WHERE user_id = " . read("user_id"))) {
                $result["data"] = $data;
            } else {
                $result["error"] = 2;
            }
        }
    } else {
        $result["error"] = 1;
    }
    echo json_encode($result);
}

function deleteAccount()
{
    $result = array();
    if (isset($_POST["confirm-password"])) {
        $password = sanitizeString($_POST["confirm-password"]);
        $stmt = db()->query("SELECT password FROM users WHERE user_id = " . read("user_id"));
        if ($psswd = mysqli_fetch_row($stmt)) {
            if (password_verify($password, $psswd[0])) {
                if(db()->query("DELETE FROM users WHERE user_id = " . read("user_id"))){
                    logOut();
                }else{
                    $result["error"] = "An unknown error occured. Please try again later." . db()->error;
                }
            } else {
                $result["error"] = "Incorrect Password";
            }
        } else {
            $result["error"] = "An unknown error occured. Please try again later.";
        }
    } else {
        $result["error"] = "Please enter your password";
    }
    echo json_encode($result);
}
