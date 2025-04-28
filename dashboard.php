<!-- filepath: c:\Users\USER\Downloads\download\htdocs\house_rent_for_bachelors\dashboard.php -->
<?php
session_start();
include("DBconnect.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the account type of the logged-in user
$userID = $_SESSION['user_id'];
$sql = "SELECT account_type FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $account_type = $user['account_type'];
} else {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css"> <!-- Link to style.css -->
    <title>Dashboard</title>
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
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="dashboard">
            <?php if ($account_type == 'admin'): ?>
                <h2>Admin Dashboard</h2>
                <p>Welcome, Admin! Here you can manage users, properties, and more.</p>
                <a href="manage_users.php"><button class="dropbtn1">Manage Users</button></a>
                <a href="manage_properties.php"><button class="dropbtn1">Manage Properties</button></a>
            <?php elseif ($account_type == 'renter'): ?>
                <h2>My Properties</h2>
                <p>Welcome, Renter! Here are the properties you have listed:</p>
                <?php
                $sql = "SELECT * FROM property WHERE renterID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $userID);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($property = $result->fetch_assoc()) {
                        echo "<div class='property_card'>";
                        echo "<h3>" . htmlspecialchars($property['title']) . "</h3>";
                        echo "<p>Location: " . htmlspecialchars($property['location']) . "</p>";
                        echo "<p>Price: ৳" . htmlspecialchars($property['price']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No properties listed yet.</p>";
                }
                ?>
            <?php elseif ($account_type == 'guest'): ?>
                <h2>Available Properties</h2>
                <p>Welcome, Guest! Here are the available properties:</p>
                <?php
                $sql = "SELECT * FROM property WHERE remaining > 0";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($property = mysqli_fetch_assoc($result)) {
                        echo "<div class='property_card'>";
                        echo "<h3>" . htmlspecialchars($property['title']) . "</h3>";
                        echo "<p>Location: " . htmlspecialchars($property['location']) . "</p>";
                        echo "<p>Price: ৳" . htmlspecialchars($property['price']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No available properties at the moment.</p>";
                }
                ?>
            <?php else: ?>
                <p>Invalid account type.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>