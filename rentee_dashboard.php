<?php
session_start();

// Check if the user is logged in and is a rentee
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Rentee') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a rentee
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Fetch properties available for rent
$sql = "SELECT * FROM property WHERE remaining > 0";
$result = mysqli_query($conn, $sql);

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_property'])) {
    $propertyID = $_POST['propertyID'];

    // Check if slots are available
    $check_slots_sql = "SELECT remaining FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($check_slots_sql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result_slots = $stmt->get_result();
    $property = $result_slots->fetch_assoc();

    if ($property['remaining'] > 0) {
        // Decrease the remaining slot count by 1
        $update_slots_sql = "UPDATE property SET remaining = remaining - 1 WHERE propertyID = ?";
        $stmt_update = $conn->prepare($update_slots_sql);
        $stmt_update->bind_param("i", $propertyID);
        $stmt_update->execute();

        // Add booking record (optional: create a `bookings` table to track bookings)
        echo "<script>alert('Booking successful!');</script>";
    } else {
        echo "<script>alert('No slots available for this property.');</script>";
    }
}
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
            <p>Browse available properties below:</p>
        </section>

        <section class="properties">
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
                            <form method="POST" action="rentee_dashboard.php">
                                <input type="hidden" name="propertyID" value="<?php echo $property['propertyID']; ?>">
                                <?php if ($property['remaining'] > 0): ?>
                                    <button type="submit" name="book_property" class="dropbtn1">Book Now</button>
                                <?php else: ?>
                                    <button type="button" class="dropbtn1" disabled>No Slots Available</button>
                                <?php endif; ?>
                            </form>
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