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

    require_once $dir['php'] . 'database.php';
    global $conn;

    try
    {
        $sql = "SELECT * FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $rso_id);
        $stmt->execute();
        $rso = $stmt->get_result();
        $rso = $rso->fetch_assoc();

        if ($rso == null)
        {
            echo 'Event not found.';
        }
        else
        {
            $data = array(
                'title' => $rso['title'],
                'date_start' => $rso['date_start'],
                'date_end' => $rso['date_end'],
                'location_id' => $rso['location_id'],
                'description' => $rso['description']
            );
            include $dir['views'] . 'student/events/view.php';
        }
    }
    catch (Exception $e)
    {
        echo 'Error: ' . $e->getMessage();
    }