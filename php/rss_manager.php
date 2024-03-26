<?php
    require_once __DIR__ . '\..\php\database.php';
    global $conn;
    global $dir;

    if (isset($_SESSION['university']))
    {
        $query = "SELECT * FROM `feeds` WHERE university_id = ?;";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['university']['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                if ($row['last_read'] < date('Y-m-d H:i:s', strtotime('-1 hour'))) // if the last read is more than an hour ago
                {
                    $query = "UPDATE `feeds` SET last_read = ? WHERE id = ?;";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("si", date('Y-m-d H:i:s'), $row['id']);
                    $stmt->execute();
                    $stmt->close();
                    ReadRSS($dir['root'] . 'feed.rss');
                }
            }
        }
    }



    function ReadRSS($url)
    {
        $content = file_get_contents($url);
        $x = new SimpleXmlElement($content);

        echo "<ul>";

        foreach($x->channel->item as $entry)
        {
            $event_title = $entry->title;
            $event_description = $entry->description;
            $event_link = $entry->link;
        }
    }