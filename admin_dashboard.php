<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

require_once('DBconnect.php');

// Fetch all users
$sql_users = "SELECT * FROM user";
$result_users = mysqli_query($conn, $sql_users);

// Fetch all properties
$sql_properties = "SELECT * FROM property";
$result_properties = mysqli_query($conn, $sql_properties);

// Handle property deletion
if (isset($_GET['delete_property'])) {
    $propertyID = $_GET['delete_property'];

    $sql_delete_property = "DELETE FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($sql_delete_property);
    $stmt->bind_param("i", $propertyID);

    if ($stmt->execute()) {
        echo "<script>alert('Property deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting property: " . $conn->error . "');</script>";
    }

    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $userID = $_GET['delete_user'];

    $sql_delete_user = "DELETE FROM user WHERE userID = ?";
    $stmt = $conn->prepare($sql_delete_user);
    $stmt->bind_param("s", $userID);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting user: " . $conn->error . "');</script>";
    }

    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

// Handle property image editing
if (isset($_GET['edit_image'])) {
    $propertyID = $_GET['edit_image'];

    // Fetch the current image for the property
    $sql_image = "SELECT photos FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($sql_image);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentImage = $row['photos'];
    } else {
        echo "<script>alert('Property not found.');</script>";
        header("Location: admin_dashboard.php");
        exit();
    }
    $stmt->close();

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
                    echo "<script>alert('Image updated successfully.');</script>";
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    echo "<script>alert('Error updating image: " . $conn->error . "');</script>";
                }
                $stmt_update->close();
            } else {
                echo "<script>alert('Error uploading the new image.');</script>";
            }
        } else {
            echo "<script>alert('Please select a valid image file.');</script>";
        }
    }

    // Handle the form submission for deleting the image
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
        $targetDir = "uploads/";

        // Delete the old image file if it exists
        if (!empty($currentImage) && file_exists($targetDir . $currentImage)) {
            unlink($targetDir . $currentImage);
        }

        // Update the database to remove the image reference
        $sql_delete_image = "UPDATE property SET photos = NULL WHERE propertyID = ?";
        $stmt_delete = $conn->prepare($sql_delete_image);
        $stmt_delete->bind_param("i", $propertyID);

        if ($stmt_delete->execute()) {
            echo "<script>alert('Image deleted successfully.');</script>";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Error deleting image: " . $conn->error . "');</script>";
        }
        $stmt_delete->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/admin_dashboad.css"> <!-- Link to the Admin Dashboard CSS -->
    <title>Admin Dashboard</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1><a href="admin_dashboard.php">Admin Dashboard</a></h1>
            </div>
            <ul class="nav_link">
                <li><a href="logout.php"><button class="dropbtn1">Log Out</button></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="admin_section">
            <div class="admin_box">
                <!-- Manage Users -->
                <h2>Manage Users</h2>
                <table class="admin_table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Location</th>
                            <th>Verified</th>
                            <th>Account Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_users) > 0) {
                            while ($row = mysqli_fetch_assoc($result_users)) {
                                echo "<tr>";
                                echo "<td>" . $row['userID'] . "</td>";
                                echo "<td>" . $row['name'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['phone_no'] . "</td>";
                                echo "<td>" . $row['gender'] . "</td>";
                                echo "<td>" . $row['location'] . "</td>";
                                echo "<td>" . ($row['verified'] ? 'Yes' : 'No') . "</td>";
                                echo "<td>" . $row['account_type'] . "</td>";
                                echo "<td><a href='admin_dashboard.php?delete_user=" . $row['userID'] . "' onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Manage Properties -->
                <h2>Manage Properties</h2>
                <table class="admin_table">
                    <thead>
                        <tr>
                            <th>Property ID</th>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Remaining Slots</th>
                            <th>Capacity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_properties) > 0) {
                            while ($row = mysqli_fetch_assoc($result_properties)) {
                                echo "<tr>";
                                echo "<td>" . $row['propertyID'] . "</td>";
                                echo "<td>" . $row['title'] . "</td>";
                                echo "<td>" . $row['location'] . "</td>";
                                echo "<td>" . $row['price'] . "</td>";
                                echo "<td>" . $row['remaining'] . "</td>";
                                echo "<td>" . $row['capacity'] . "</td>";
                                echo "<td>
                                    <a href='admin_dashboard.php?delete_property=" . $row['propertyID'] . "' onclick=\"return confirm('Are you sure you want to delete this property?');\">Delete</a> |
                                    <a href='admin_dashboard.php?edit_image=" . $row['propertyID'] . "'>Edit/Delete Image</a>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No properties found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Edit/Delete Property Image -->
                <?php if (isset($_GET['edit_image'])): ?>
                    <h2>Edit/Delete Image for Property ID: <?php echo htmlspecialchars($propertyID); ?></h2>
                    <?php if (!empty($currentImage)): ?>
                        <p>Current Image:</p>
                        <img src="uploads/<?php echo htmlspecialchars($currentImage); ?>" alt="Current Property Image" width="200">
                    <?php else: ?>
                        <p>No image currently available for this property.</p>
                    <?php endif; ?>
                    <form action="admin_dashboard.php?edit_image=<?php echo htmlspecialchars($propertyID); ?>" method="POST" enctype="multipart/form-data">
                        <label for="new_image">Select New Image:</label>
                        <input type="file" id="new_image" name="new_image" accept="image/*">
                        <br><br>
                        <button type="submit" name="update_image">Update Image</button>
                        <button type="submit" name="delete_image" onclick="return confirm('Are you sure you want to delete this image?');">Delete Image</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
</body>
</html>

<?php
mysqli_close($conn);
?>