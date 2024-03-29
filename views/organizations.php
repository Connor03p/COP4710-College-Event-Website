<?php
    global $dir;
    require_once $dir['php'] . 'database.php';
    global $conn;

    // Check if url ends with "/create"
    if (substr($_SERVER['REQUEST_URI'], -7) == '/create')
    {
        include $dir['views'] . '/organizations/create.php';
        return;
    }
    
    $rso_id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    if (isset($rso_id))
    {
        $rso_id = explode("=", $rso_id)[1];
        include $dir['views'] . '/organizations/view.php';
    }
    else
    {
        include $dir['views'] . '/organizations/list.php';
        return;
    }

