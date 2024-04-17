<?php
    $queryText = [];
    $query = "SELECT O.id, O.name, O.summary, I.name file_name FROM organizations O LEFT JOIN images I ON I.id = O.image_id WHERE university_id = ?";
    $params = [];
    $params[] = $_SESSION['user']['university_id'];

    if (isset($_GET['submit']))
    {
        if (isset($_GET['search']) && !empty($_GET['search']))
        {
            $restoreInput = $_GET;
            $search = $_GET['search'];
            $query .= " AND (O.name LIKE ? OR O.summary LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
    }

    $query .= " ORDER BY O.active DESC, O.name ASC";
    
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
    <title>Browse RSOs</title>
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
            flex-grow: 0;
        }
    </style>
</head>

<body>
    <main>
        <form id="filter-container">
            <input type="search" name="search" id="filter-search" placeholder="Search Organizations"
                value="<?php echo isset($restoreInput['search']) ? $restoreInput['search'] : ''; ?>" /
            >
            <input type="submit" name="submit" id="filter-button" value="Search" />
        </form>


        <div id="EventList-All">
            <?php if ($data_events->num_rows > 0): ?>
                <?php
                while ($row = $data_events->fetch_assoc()) {
                    echo "<a style='text-decoration: none;' href='/organizations?id=" . $row['id'] . "'>";
                    echo "    <section class='rso'>";
                    if ($row['file_name'] != null)
                    echo "        <img src='" . $dir["uploads"] . $row['file_name'] . "' alt='RSO Logo' class='rso-logo'>";
                    echo "        <div>";
                    echo "          <h3 class='rso-name'>" . $row['name'] . "</h3>";
                    echo "          <div class='rso-description'>" . $row['summary'] . "</div>";
                    echo "        </div>";
                    echo "    </section>";
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
                    <h3 style="text-align: center">No organizations found</h3>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const search = document.getElementById("filter-search");
        const tags = document.getElementById("filter-tags");
    </script>
</body>

</html>