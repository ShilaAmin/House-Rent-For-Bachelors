<?php
session_start();
include("DBconnect.php");

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $propertyID = intval($_GET['id']);
    $sql = "SELECT * FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();
    } else {
        echo "Property not found.";
        exit();
    }
} else {
    echo "Invalid property ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css"> <!-- Link to style.css -->
    <title>Property Details</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <a href="index.php">
                    <img src="frontend/images/logo.png" alt="House Rent for Bachelors" class="logo">
                </a>
            </div>
        </nav>
    </header>

    <main>
        <section class="property_details">
            <h2><?php echo htmlspecialchars($property['title']); ?></h2>
            <img src="uploads/<?php echo htmlspecialchars($property['photos']); ?>" alt="Property Image" class="property_image">
            <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($property['description']); ?></p>
            <p><strong>Price:</strong> ৳<?php echo htmlspecialchars($property['price']); ?></p>
            <p><strong>Remaining:</strong> <?php echo htmlspecialchars($property['remaining']); ?></p>
            <p><strong>Capacity:</strong> <?php echo htmlspecialchars($property['capacity']); ?></p>
        </section>
    </main>
</body>
</html>
