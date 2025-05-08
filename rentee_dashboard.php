<?php
session_start();

// Check if the user is logged in and is a rentee
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Rentee') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a rentee
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Fetch properties based on search criteria or show all properties
$where_clauses = [];
$params = [];
$types = "";

// Check if location is provided
if (!empty($_GET['location'])) {
    $where_clauses[] = "location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}

// Check if min price is provided
if (!empty($_GET['min_price'])) {
    $where_clauses[] = "price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}

// Check if max price is provided
if (!empty($_GET['max_price'])) {
    $where_clauses[] = "price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

// Check if gender is provided
if (!empty($_GET['gender'])) {
    $where_clauses[] = "gender = ?";
    $params[] = $_GET['gender'];
    $types .= "s";
}

// Build the query
$sql = "SELECT * FROM property WHERE remaining > 0";
if (!empty($where_clauses)) {
    $sql .= " AND " . implode(" AND ", $where_clauses);
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

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
    <link rel="stylesheet" href="frontend/rentee_dashboard.css">
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
        <section class="search">
            <h3>Search Properties</h3>
            <form method="GET" action="rentee_dashboard.php" class="search_form">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" placeholder="Enter location">

                <label for="min_price">Min Price:</label>
                <input type="number" id="min_price" name="min_price" placeholder="Min price">

                <label for="max_price">Max Price:</label>
                <input type="number" id="max_price" name="max_price" placeholder="Max price">

                <label for="gender">Gender:</label>
                <select id="gender" name="gender">
                    <option value="">Any</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <button type="submit" class="dropbtn1">Search</button>
            </form>
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
</body>
</html>