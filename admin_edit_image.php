<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'DBconnect.php';

if (isset($_GET['propertyID'])) {
    $propertyID = $_GET['propertyID'];

    // Fetch the current image for the property
    $sql = "SELECT photos FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentImage = $row['photos'];
    } else {
        echo "Property not found.";
        exit();
    }
    $stmt->close();
}

// Handle the form submission for updating the image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_image'])) {
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $newImageName = $_FILES['new_image']['name'];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($newImageName);

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFile)) {
            // Delete the old image file if it exists
            if (!empty($currentImage) && file_exists($targetDir . $currentImage)) {
                unlink($targetDir . $currentImage);
            }

            // Update the database with the new image name
            $sql_update_image = "UPDATE property SET photos = ? WHERE propertyID = ?";
            $stmt_update = $conn->prepare($sql_update_image);
            $stmt_update->bind_param("si", $newImageName, $propertyID);

            if ($stmt_update->execute()) {
                echo "Image updated successfully.";
                header("Location: admin_dashboard.php");
                exit();
            } else {
                echo "Error updating image: " . $conn->error;
            }
            $stmt_update->close();
        } else {
            echo "Error uploading the new image.";
        }
    } else {
        echo "Please select a valid image file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css"> <!-- Link to your CSS file -->
    <title>Edit Property Image</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1>Edit Property Image</h1>
            </div>
        </nav>
    </header>
    <main>
        <section class="edit_image_section">
            <h2>Edit Image for Property ID: <?php echo htmlspecialchars($propertyID); ?></h2>
            <?php if (!empty($currentImage)): ?>
                <p>Current Image:</p>
                <img src="uploads/<?php echo htmlspecialchars($currentImage); ?>" alt="Current Property Image" width="200">
            <?php else: ?>
                <p>No image currently available for this property.</p>
            <?php endif; ?>
            <form action="admin_edit_image.php?propertyID=<?php echo htmlspecialchars($propertyID); ?>" method="POST" enctype="multipart/form-data">
                <label for="new_image">Select New Image:</label>
                <input type="file" id="new_image" name="new_image" accept="image/*" required>
                <br><br>
                <button type="submit" name="update_image">Update Image</button>
            </form>
        </section>
    </main>
</body>
</html>