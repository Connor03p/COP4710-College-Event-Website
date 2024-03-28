<?php
    global $dir;
    require_once $dir['php'] . 'database.php';
    global $conn;

    if (!isset($_SESSION['user']))
    {
        header('location: index.php');   // if not set the user is sendback to login page.
    }

    $rso_id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    if (isset($rso_id))
    {
        $rso_id = explode("=", $rso_id)[1];
    }
    else
    {
        include $dir['views'] . 'events/list.php';
        return;
    }