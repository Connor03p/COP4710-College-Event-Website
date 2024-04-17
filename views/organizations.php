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
    
    if (isset($_GET['id']))
    {
        include $dir['views'] . '/organizations/view.php';
    }
    else
    {
        include $dir['views'] . '/organizations/list.php';
        return;
    }

