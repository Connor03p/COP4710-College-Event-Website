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
            height: 2.2rem;
            max-width: 2.2rem;
        }
    </style>
</head>

<body>
    <main>
        <form id="filter-container">
            <input type="search" id="filter-search" placeholder="Search Organizations">
            <select id="filter-tags">
                <option value="" disabled selected hidden>Tags</option>
                <option value="tag1">Tag1</option>
                <option value="tag2">Tag2</option>
                <option value="tag3">Tag3</option>
            </select>
            <input type="image" name="filter" id="filter-button"
                src='data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>'>
            </input>
        </form>


        <div id="EventList-All">
            <?php
            $sql = "SELECT O.id, O.name, O.summary FROM organizations O WHERE university_id = ? ORDER BY O.date_created DESC LIMIT 10";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['university']['id']);
            $stmt->execute();
            $data_events = $stmt->get_result();
            ?>

            <?php if ($data_events->num_rows > 0): ?>
                <?php
                while ($row = $data_events->fetch_assoc()) {
                    echo "<a style='text-decoration: none;' href='/organizations?id=" . $row['id'] . "'>";
                    echo "    <section class='rso'>";
                    echo "        <h3 class='rso-name'>" . $row['name'] . "</h3>";
                    echo "        <p class='rso-description'>" . $row['summary'] . "</p>";
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

        <br>

        <a style="text-decoration: none;" href="organizations/create">
            <section>
                <h3>Create an Organization</h3>
                <p>Start a new organization to get involved with your university community.</p>
            </section>
        </a>

        <?php include $dir['views'] . 'footer.php'; ?>
    </main>

    <script>
        const search = document.getElementById("filter-search");
        const tags = document.getElementById("filter-tags");
    </script>
</body>

</html>