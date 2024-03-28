<?php
    $query = "SELECT e.id, e.title, e.summary
    FROM events e
    LEFT JOIN public_events pe ON e.id = pe.event_id
    LEFT JOIN private_events pv ON e.id = pv.event_id
    LEFT JOIN rso_events re ON e.id = re.event_id
    LEFT JOIN university_students us ON pv.university_id = us.university_id
    LEFT JOIN rso_students rs ON re.rso_id = rs.rso_id
    LEFT JOIN students s ON us.student_id = s.user_id OR rs.student_id = s.user_id
    WHERE pe.event_id IS NOT NULL OR pv.university_id IS NULL OR rs.rso_id IS NULL OR s.user_id = ?
    ";
    $query = $conn->prepare($query);
    $query->bind_param("i", $_SESSION['user']['university_id']);
    $query->execute();
    $data_events = $query->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="style.css">
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

        #filter-button {
            height: 2.2rem;
            width: 2.2rem;
        }
    </style>
</head>

<body>
    <main>
        <form id="filter-container">
            <input type="search" id="filter-search" placeholder="Search Events">
            <select id="filter-tags">
                <option value="" disabled selected hidden>Tags</option>
                <option value="tag1">Tag1</option>
                <option value="tag2">Tag2</option>
                <option value="tag3">Tag3</option>
            </select>
            <select id="filter-timeframe">
                <option value="Day" selected>Day</option>
                <option value="Week">Week</option>
                <option value="Month">Month</option>
            </select>
            <input id="filter-date" type="date">
            <input type="image" name="filter" id="filter-button"
                src='data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="var(--link)" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>'>
            </input>
        </form>


        <div id="EventList-All">
            <?php if ($data_events->num_rows > 0): ?>
                <?php
                while ($row = $data_events->fetch_assoc()) {
                    echo "<a href='http://cop4710/events?id=" . $row['id'] . "'>";
                    echo "  <section>";
                    echo "      <h3 class='event-title'>" . $row['title'] . "</h3>";
                    echo "      <p class='event-summary'>" . $row['summary'] . "</p>";
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

        <?php include $dir['views'] . 'footer.php'; ?>
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