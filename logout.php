<?php
    session_start();
    require('./config.php');

    if(!isset($_SESSION['token'])){
        header('Location: login.php');
        exit;
    }

    $client = new Google\Client();
    $client->setAccessToken($_SESSION['token']);

    $client->revokeToken();

    $_SESSION = array();

    if(ini_get("session.use_cookies")){
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    header("Location: login.php");
    exit;