<?php
    global $dir;
    require_once $dir['php'] . 'database.php';
    global $conn;

    if (!isset($_SESSION['user']))
    {
        header('location: index.php');   // if not set the user is sendback to login page.
    }

    // Check if url ends with "/create"
    if (substr($_SERVER['REQUEST_URI'], -7) == '/create')
    {
        include $dir['views'] . '/events/create.php';
        return;
    }

    $rso_id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    if (isset($rso_id))
    {
        include $dir['views'] . 'events/view.php';
        return;
    }
    else
    {
        include $dir['views'] . 'events/list.php';
        return;
    }