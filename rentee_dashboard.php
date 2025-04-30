<?php
session_start();

// Check if the user is logged in and is a rentee
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'rentee') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a rentee
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Fetch properties available for rent
$sql = "SELECT * FROM property WHERE status = 'available'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css"> <!-- Link to your CSS file -->
    <title>Rentee Dashboard</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1>Rentee Dashboard</h1>
            </div>
            <ul class="nav_links">
                <li><a href="logout.php"><button class="dropbtn1">Log Out</button></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="welcome">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
            <p>Here are the properties available for rent:</p>
        </section>

        <section class="properties">
            <div class="properties_grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($property = mysqli_fetch_assoc($result)): ?>
                        <div class="property_card">
                            <img src="uploads/<?php echo htmlspecialchars($property['photos']); ?>" alt="Property Image" class="property_image">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p>Location: <?php echo htmlspecialchars($property['location']); ?></p>
                            <p>Price: ৳<?php echo htmlspecialchars($property['price']); ?></p>
                            <a href="view_property.php?id=<?php echo $property['propertyID']; ?>"><button class="dropbtn1">View Details</button></a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No properties available for rent at the moment.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 House Rent for Bachelors. All rights reserved.</p>
    </footer>
</body>
</html>