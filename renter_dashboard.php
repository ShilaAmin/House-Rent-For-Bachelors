<?php
session_start();

// Check if the user is logged in and is a renter
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Renter') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a renter
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Handle property addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    $propertyID = $_POST['propertyID'];
    $title = $_POST['title'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $gender = $_POST['gender'];
    $remaining = $capacity; // Initially, remaining slots are equal to capacity
    $renterID = $_SESSION['user'];

    // Handle file upload
    $photo = $_FILES['photo']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($photo);

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        // Insert property into the database
        $sql = "INSERT INTO property (propertyID, photos, location, description, title, renterID, remaining, capacity, price, gender) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiidss", $propertyID, $photo, $location, $description, $title, $renterID, $remaining, $capacity, $price, $gender);

        if ($stmt->execute()) {
            echo "<script>alert('Property added successfully!');</script>";
        } else {
            echo "<script>alert('Error adding property: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error uploading photo.');</script>";
    }
}

// Fetch properties listed by the renter
$renterID = $_SESSION['user'];
$sql = "SELECT * FROM property WHERE renterID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $renterID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/renter_dashboard.css">
    <title>Renter Dashboard</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1>Renter Dashboard</h1>
            </div>
            <ul class="nav_links">
                <li><a href="logout.php"><button class="dropbtn1">Log Out</button></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="welcome">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
            <p>Manage your properties below or add a new one:</p>
        </section>

        <section class="add_property">
            <h3>Add New Property</h3>
            <form method="POST" action="renter_dashboard.php" enctype="multipart/form-data">
                <label for="propertyID">Property ID:</label>
                <input type="text" id="propertyID" name="propertyID" required>

                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>

                <label for="price">Price (৳):</label>
                <input type="number" id="price" name="price" step="0.01" required>

                <label for="capacity">Capacity (Slots):</label>
                <input type="number" id="capacity" name="capacity" required>

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <label for="photo">Photo:</label>
                <input type="file" id="photo" name="photo" accept="image/*" required>

                <button type="submit" name="add_property" class="dropbtn1">Add Property</button>
            </form>
        </section>

        <section class="properties">
            <h3>Your Listed Properties</h3>
            <div class="properties_grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($property = mysqli_fetch_assoc($result)): ?>
                        <div class="property_card">
                            <img src="uploads/<?php echo htmlspecialchars($property['photos']); ?>" alt="Property Image" class="property_image">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p>Location: <?php echo htmlspecialchars($property['location']); ?></p>
                            <p>Description: <?php echo htmlspecialchars($property['description']); ?></p>
                            <p>Price: ৳<?php echo htmlspecialchars($property['price'],2); ?></p>
                            <p>Slots: <?php echo htmlspecialchars($property['remaining']); ?> / <?php echo htmlspecialchars($property['capacity']); ?></p>
                            <p>Gender: <?php echo htmlspecialchars($property['gender']); ?></p>
                            <a href="edit_property.php?propertyID=<?php echo $property['propertyID']; ?>" class="dropbtn1">Edit</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>You have not listed any properties yet.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>


</body>
</html>