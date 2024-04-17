<?php
    $queryText = [];
    $query = "
        SELECT 
            E.id, E.title, E.summary, E.event_start, E.event_end, 
            L.description AS location_name, L.latitude, L.longitude 
        FROM 
            events E
        LEFT JOIN 
            locations L ON E.location_id = L.id
        LEFT JOIN 
            public_events PE ON E.id = PE.event_id
        LEFT JOIN 
            private_events UE ON E.id = UE.event_id AND UE.university_id = (SELECT university_id FROM university_members WHERE user_id = ?)
        LEFT JOIN 
            rso_events RE ON E.id = RE.event_id AND RE.rso_id IN (SELECT rso_id FROM rso_members WHERE user_id = ?)
        WHERE 
            (
                PE.event_id IS NOT NULL
                OR UE.event_id IS NOT NULL
                OR RE.event_id IS NOT NULL
            )
        ";
    $params = [];
    $params[] = $_SESSION['user']['id'];
    $params[] = $_SESSION['user']['id'];

    if (isset($_GET['submit']))
    {
        $restoreInput = $_GET;

        if (isset($_GET['visibility']) && !empty($_GET['visibility']))
        {
            switch ($_GET['visibility'])
            {
                case "Public":
                    $query .= " AND PE.event_id IS NOT NULL";
                    break;

                case "University":
                    $query .= " AND UE.event_id IS NOT NULL";
                    break;

                case "Organization":
                    $query .= " AND RE.event_id IS NOT NULL";
                    break;
            }
        }

        if (isset($_GET['search']) && !empty($_GET['search']))
        {
            $query .= " AND (title LIKE ? OR summary LIKE ?)";
            $params[] = "%" . $_GET['search'] . "%";
            $params[] = "%" . $_GET['search'] . "%";
        }

        if (isset($_GET['category']) && !empty($_GET['category']))
        {
            $query .= " AND category = ?";
            $params[] = $_GET['category'];
        }

        if (isset($_GET['timeframe']) && !empty($_GET['timeframe']) && isset($_GET['date']) && !empty($_GET['date']))
        {
            $query .= " AND (event_start BETWEEN ? AND ? OR event_end BETWEEN ? AND ?)";

            if ($_GET['timeframe'] == "Day")
            {
                if (isset($_GET['date']) && !empty($_GET['date']))
                {
                    $date = new DateTime($_GET['date']);
                    $start = $date->format("Y-m-d");
                    $end = $date->format("Y-m-d");
                }
            }
            else if ($_POST['timeframe'] == "Week")
            {
                if (isset($_GET['date']) && !empty($_GET['date']))
                {
                    $date = new DateTime($_POST['date']);
                    $start = $date->format("Y-m-d");
                    $date->modify("+6 days");
                    $end = $date->format("Y-m-d");
                }
            }
            else if ($_GET['timeframe'] == "Month")
            {
                if (isset($_GET['date']) && !empty($_GET['date']))
                {
                    $date = new DateTime($_GET['date']);
                    $start = $date->format("Y-m-01");
                    $end = $date->format("Y-m-t");
                }
            }

            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
        }
    }

    $query .= " ORDER BY event_start ASC";

    $queryText[] = $query;
    $query = $conn->prepare($query);
    if (!empty($params))
        $query->bind_param(str_repeat("s", count($params)), ...$params);
    $query->execute();
    $data_events = $query->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View all events</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="<?=$dir['domain']?>/style.css">
    <style>
        #filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            max-width: 100%;
        }

        #filter-container>* {
            flex-grow: 1;
            width: unset;
            min-width: 0;
        }
    </style>
</head>

<body>
    <main>
        <form id="filter-container">
            <input type="search" name="search" id="filter-search" placeholder="Search Events"
                value="<?=isset($restoreInput['search']) ? $restoreInput['search'] : ""?>"
            >
            
            <select id="filter-privacy" name="visibility">
                <option value="" disabled selected hidden>Visibility</option>
                <option value="All">All</option>
                <option value="Public"
                    <?=(isset($restoreInput['visibility']) && $restoreInput['visibility'] == "Public") ? "selected" : ""?>
                >Public</option>
                <option value="University"
                    <?=(isset($restoreInput['visibility']) && $restoreInput['visibility'] == "University") ? "selected" : ""?>
                >University</option>
                <option value="Organization"
                    <?=(isset($restoreInput['visibility']) && $restoreInput['visibility'] == "Organization") ? "selected" : ""?>
                >Organization</option>
            </select>
            <script>
                const filterPrivacy = document.getElementById("filter-privacy");
                filterPrivacy.onchange = function()
                {
                    if (filterPrivacy.value == "All")
                    {
                        filterPrivacy.value = "";
                    }
                }
            </script>
            
            <?php
                $query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'events' AND COLUMN_NAME = 'category'";
                $query = $conn->prepare($query);
                $query->execute();
                $result = $query->get_result()->fetch_assoc();
                $filter_categories = explode("','", substr($result['COLUMN_TYPE'], 6, -2));
            ?>
            <select id="filter-category" name="category">
                <option value="" disabled selected hidden>Category</option>
                <option value="All">All</option>
                <?php foreach ($filter_categories as $category): ?>
                    <option value="<?=$category?>"
                        <?=(isset($restoreInput['category']) && $restoreInput['category'] == $category) ? "selected" : ""?>
                    ><?=$category?></option>
                <?php endforeach; ?>
            </select>
            <script>
                const filterCategory = document.getElementById("filter-category");
                filterCategory.onchange = function()
                {
                    if (filterCategory.value == "All")
                    {
                        filterCategory.value = "";
                    }
                }
            </script>
            
            <select id="filter-timeframe" name="timeframe">
                <option value="Day" selected>Day</option>
                <option value="Week">Week</option>
                <option value="Month">Month</option>
            </select>
            <input id="filter-date" type="date" name="date"
                value="<?=isset($restoreInput['date']) ? $restoreInput['date'] : ""?>"
            >
            
            <input type="submit" name="submit" id="filter-button" value="Search" />
        </form>


        <div id="EventList-All">
            <?php if ($data_events->num_rows > 0): ?>
                <?php
                while ($row = $data_events->fetch_assoc()) {
                    echo "<a href='" . $dir['domain'] . "/events?id=" . $row['id'] . "'>";
                    echo "  <section class='event'>";
                    echo "      <h3 class='event-title'>" . $row['title'] . "</h3>";
                    echo "      <div class='details-container'>";

                    // Display the event date
                    if (isset($row['event_start']) || isset($row['event_end']))
                    {
                        echo "          <div class='detail'>";
                        echo file_get_contents($dir['img'] . "calendar-icon.svg");
                        echo "              <p>";
                            if(isset($row['event_start']))
                            {
                                $startTime = date("F j, g:ia", strtotime($row['event_start']));
                                echo ($startTime);
                            }   
                            if(isset($row['event_end']))
                            {
                                // If the end date is more than 1 day after the start date, display the end date
                                if (date("Y-m-d", strtotime($row['event_start'])) != date("Y-m-d", strtotime($row['event_end'])))
                                {
                                    $endTime = date("F j, g:ia", strtotime($row['event_end']));
                                }
                                else
                                {
                                    $endTime = date("g:ia", strtotime($row['event_end']));
                                }
                                echo " - " . ($endTime);
                            }
                        echo "              </p>";
                        echo "          </div>";
                    }

                    // Display the event location
                    if (isset($row['location_name']))
                    {
                        echo "          <div class='detail'>";
                        echo file_get_contents($dir['img'] . "location-icon.svg");
                        echo "              <p class='event-location'>" . $row['location_name'] . "</p>";
                        echo "          </div>";
                    }
                    
                    echo "      </div>";
                    echo "      <div class='event-summary'>" . $row['summary'] . "</div>";
                    echo "  </section>";
                    echo "</a>";
                }
                ?>
            <?php else: ?>
                <div id="no-events">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                        style="display:block; margin: auto; max-width: 8rem;"
                        fill="var(--highEmphasis)"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path
                            d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM174.6 384.1c-4.5 12.5-18.2 18.9-30.7 14.4s-18.9-18.2-14.4-30.7C146.9 319.4 198.9 288 256 288s109.1 31.4 126.6 79.9c4.5 12.5-2 26.2-14.4 30.7s-26.2-2-30.7-14.4C328.2 358.5 297.2 336 256 336s-72.2 22.5-81.4 48.1zM144.4 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" />
                    </svg>
                    <h3 style="text-align: center">No events found</h3>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const search = document.getElementById("filter-search");
        const tags = document.getElementById("filter-tags");
        const timeframe = document.getElementById("filter-timeframe");
        const date = document.getElementById("filter-date");

        // Test is the week/month input types are supported
        const test = document.createElement("input");
        try {
            test.type = "week";
        }
        catch (e) {
            console.log(e.description);
        }

        if (test.type === "text")
            timeframe.hidden = true;


        timeframe.addEventListener("change", function () {
            if (timeframe.value == "Day") {
                date.type = "date";
            }
            else if (timeframe.value == "Week") {
                date.type = "week";
            }
            else if (timeframe.value == "Month") {
                date.type = "month";
            }
        });

    </script>

</body>

</html>