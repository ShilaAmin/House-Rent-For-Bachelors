<?php
session_start();
require_once 'DBconnect.php'; // Include database connection

// Fetch all properties from the database
$sql = "SELECT * FROM property";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css">
    <title>All Properties</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1><a href="index.php">House Rent for Bachelors</a></h1>
            </div>
            <ul class="nav_links">
                <li><a href="index.php"><button class="dropbtn1">Home</button></a></li>
                <li><a href="logout.php"><button class="dropbtn1">Log Out</button></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="all_properties">
            <h2>All Properties</h2>
            <div class="properties_grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($property = mysqli_fetch_assoc($result)): ?>
                        <div class="property_card">
                            <img src="uploads/<?php echo htmlspecialchars($property['photos']); ?>" alt="Property Image" class="property_image">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p>Location: <?php echo htmlspecialchars($property['location']); ?></p>
                            <p>Description: <?php echo htmlspecialchars($property['description']); ?></p>
                            <p>Price: ৳<?php echo htmlspecialchars($property['price']); ?></p>
                            <p>Slots: <?php echo htmlspecialchars($property['remaining']); ?> / <?php echo htmlspecialchars($property['capacity']); ?></p>
                            <p>Gender Preference: <?php echo htmlspecialchars($property['gender'] ?? 'Any'); ?></p>
                            <a href="view_property.php?id=<?php echo $property['propertyID']; ?>" class="dropbtn1">View Details</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No properties available at the moment.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 House Rent for Bachelors. All rights reserved.</p>
    </footer>
</body>
</html>