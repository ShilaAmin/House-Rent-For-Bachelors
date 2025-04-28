<?php
session_start();
include("DBconnect.php");

// Handle Admin Login
$errorMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded admin credentials
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit();
    } else {
        $errorMessage = "Invalid username or password.";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle Delete User
if (isset($_GET['delete_user']) && isset($_SESSION['admin_logged_in'])) {
    $userID = $_GET['delete_user'];
    $sql = "DELETE FROM user WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Handle Delete Property
if (isset($_GET['delete_property']) && isset($_SESSION['admin_logged_in'])) {
    $propertyID = $_GET['delete_property'];
    $sql = "DELETE FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}
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
    <!-- Navigation Bar -->
    <header>
        <nav>
            <div class="nav_logo">
                <a href="index.php">
                    <img src="frontend/images/logo.png" alt="House Rent for Bachelors" class="logo">
                </a>
            </div>
            <ul>
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="index.php?logout=true">Logout</a></li>
                <?php else: ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <?php if (isset($_SESSION['admin_logged_in'])): ?>
        <!-- Admin Dashboard -->
        <section class="admin_dashboard">
            <h1>Admin Dashboard</h1>
            <!-- Admin content here -->
        </section>
    <?php else: ?>
        <!-- Welcome Page -->
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
    <?php endif; ?>

    <footer>
        <p>&copy; 2025 House Rent for Bachelors. All rights reserved.</p>
    </footer>
</body>
</html>
