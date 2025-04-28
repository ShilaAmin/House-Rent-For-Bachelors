<?php
session_start();
include("DBconnect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css"> <!-- Link to style.css -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
    <title>House Rent for Bachelors - Welcome</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <a href="index.php">
                    <img src="frontend/images/logo.png" alt="House Rent for Bachelors" class="logo">
                </a>
            </div>
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="welcome">
            <div class="welcome_box">
                <h1>Welcome to House Rent for Bachelors</h1>
                <p>Find your perfect rental space. Explore a variety of options tailored for bachelors.</p>
                <a href="properties_page.php"><button class="dropbtn1">Explore All Properties</button></a>
            </div>
        </section>

        <section class="featured_properties">
            <h2>Featured Properties</h2>
            <div class="properties_grid">
                <?php
                $sql = "SELECT * FROM property LIMIT 4"; // Fetch 4 featured properties
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='property_card'>";
                        echo "<img src='uploads/" . htmlspecialchars($row['photos']) . "' alt='Property Image' class='property_image'>";
                        echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                        echo "<p>Location: " . htmlspecialchars($row['location']) . "</p>";
                        echo "<p>Price: ৳" . htmlspecialchars($row['price']) . "</p>";
                        echo "<a href='view_property.php?id=" . $row['propertyID'] . "'><button class='dropbtn1'>View Details</button></a>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No featured properties available.</p>";
                }
                ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 House Rent for Bachelors. All rights reserved.</p>
    </footer>
</body>
</html>
