<?php
session_start();

// Check if the user is logged in and is a renter
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Renter') {
    header("Location: login.php");
    exit();
}

require_once 'DBconnect.php';

// Fetch property details for editing
if (isset($_GET['propertyID'])) {
    $propertyID = $_GET['propertyID'];
    $sql = "SELECT * FROM property WHERE propertyID = ? AND renterID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $propertyID, $_SESSION['user']);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();

    if (!$property) {
        echo "<script>alert('Property not found or you do not have permission to edit this property.');</script>";
        header("Location: renter_dashboard.php");
        exit();
    }
} else {
    header("Location: renter_dashboard.php");
    exit();
}

// Handle property update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_property'])) {
    $title = $_POST['title'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $remaining = $_POST['remaining'];
    $gender = $_POST['gender'];

    // Update property details in the database
    $sql = "UPDATE property SET title = ?, location = ?, description = ?, price = ?, capacity = ?, remaining = ?, gender = ? WHERE propertyID = ? AND renterID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdiisis", $title, $location, $description, $price, $capacity, $remaining, $gender, $propertyID, $_SESSION['user']);

    if ($stmt->execute()) {
        echo "<script>alert('Property updated successfully!');</script>";
        header("Location: renter_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Error updating property: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/renter_dashboard.css">
    <title>Edit Property</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1>Edit Property</h1>
            </div>
        </nav>
    </header>

    <main>
        <section class="edit_property">
            <h3>Edit Property Details</h3>
            <form method="POST" action="edit_property.php?propertyID=<?php echo $propertyID; ?>">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>

                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($property['description']); ?></textarea>

                <label for="price">Price (৳):</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($property['price']); ?>" required>

                <label for="capacity">Capacity (Slots):</label>
                <input type="number" id="capacity" name="capacity" value="<?php echo htmlspecialchars($property['capacity']); ?>" required>

                <label for="remaining">Remaining Slots:</label>
                <input type="number" id="remaining" name="remaining" value="<?php echo htmlspecialchars($property['remaining']); ?>" required>

                <label for="gender">Preferred Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Any" <?php echo (isset($property['gender']) && $property['gender'] == 'Any') ? 'selected' : ''; ?>>Any</option>
                    <option value="Male" <?php echo (isset($property['gender']) && $property['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($property['gender']) && $property['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                </select>

                <button type="submit" name="update_property" class="dropbtn1">Update Property</button>
            </form>
        </section>
    </main>
</body>
</html>