<?php
session_start();
include("db.php");

// Check if renter is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please login first");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $price = intval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $capacity = intval($_POST['capacity']);
    $remaining = intval($_POST['remaining']);
    $renterID = $_SESSION['user_id']; // who posted

    // Handle image upload
    $photoName = $_FILES['photo']['name'];
    $photoTmp = $_FILES['photo']['tmp_name'];
    $photoFolder = "images/" . basename($photoName);

    if (move_uploaded_file($photoTmp, $photoFolder)) {
        // Insert into property table
        $sql = "INSERT INTO property (photos, location, description, title, renterID, remaining, capacity, price) 
                VALUES ('$photoName', '$location', '$description', '$title', '$renterID', '$remaining', '$capacity', '$price')";
        if (mysqli_query($conn, $sql)) {
            echo "Property Posted Successfully!";
        } else {
            echo "Error posting property: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading photo.";
    }
}
?>

<!-- Rental Post Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Property</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Post New Property</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Property Title" required><br><br>
    <input type="text" name="location" placeholder="Location" required><br><br>
    <input type="number" name="price" placeholder="Price" required><br><br>
    <textarea name="description" placeholder="Description" required></textarea><br><br>
    <input type="number" name="capacity" placeholder="Capacity (Total)" required><br><br>
    <input type="number" name="remaining" placeholder="Remaining Spaces" required><br><br>
    
    <label>Upload Property Photo:</label><br>
    <input type="file" name="photo" accept="image/*" required><br><br>
    
    <button type="submit">Post Property</button>
</form>

</body>
</html>
