<?php
    session_start();
    require_once $_SERVER["DOCUMENT_ROOT"] . '\php\database.php';
    global $conn;

    // Get first part of url
    $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $request = explode("/", $request)[1];
    
    $dir = array(
        'root' => $_SERVER["DOCUMENT_ROOT"] . '/',
        'views' => $_SERVER["DOCUMENT_ROOT"] . '/views/',
        'php' => $_SERVER["DOCUMENT_ROOT"] . '/php/',
        'img' => $_SERVER["DOCUMENT_ROOT"] . '/img/'
    );    

    // Check if logged in
    if (!isset($_SESSION['user']))
    {
        switch($request)
        {
            case '':
                require $dir['views'] . 'login.php';
                return;
            case 'login':
                require $dir['views'] . 'login.php';
                return;
            case 'signup':
                require $dir['views'] . 'signup.php';
                return;
            default:
                header("Location: http://cop4710/");
                return;
        }
    }

    switch ($request)
    {
        case '':
            require $dir['views'] . 'dashboard.php';
            break;

        case 'signup':
            require $dir['views'] . 'signup.php';
            break;
        
        case 'register':
            require $dir['views'] . 'register.php';
            break;
        
        case 'dashboard':
            require $dir['views'] . 'dashboard.php';
            break;
        
        case 'universities':
            require $dir['views'] . 'universities.php';
            break;

        case 'organizations':
            require $dir['views'] . 'organizations.php';
            break;

        case 'events':
            require $dir['views'] . 'events.php';
            break;
        
        case 'logout' || 'login':
            unset($_SESSION["user"]);
            header("Location: http://cop4710/");
            break;

        default:
            http_response_code(404);
            require $dir['views'] . '404.php';
            break;
    }
