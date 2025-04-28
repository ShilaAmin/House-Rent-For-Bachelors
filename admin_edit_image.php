<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once "DBconnect.php";

// Validate query parameters
if (!isset($_GET['propertyID'])) {
    echo "<script>alert('Invalid request. No property ID provided.'); window.location.href='admin_dashboard.php';</script>";
    exit();
}

$propertyID = mysqli_real_escape_string($conn, $_GET['propertyID']);

// Fetch property details
$sql_property = "SELECT * FROM property WHERE propertyID = '$propertyID'";
$result_property = mysqli_query($conn, $sql_property);
$property = mysqli_fetch_assoc($result_property);

if (!$property) {
    echo "<script>alert('Property not found.'); window.location.href='admin_dashboard.php';</script>";
    exit();
}

// Fetch images for the property
$sql_images = "SELECT * FROM property_images WHERE propertyID = '$propertyID'";
$result_images = mysqli_query($conn, $sql_images);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="frontend/style.css" />
    <title>Edit Property Images</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1><a href="admin_dashboard.php">Admin Dashboard</a></h1>
            </div>
            <ul class="nav_link">
                <li><a href="admin_dashboard.php">Home</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="property-images">
            <div class="property-images_box">
                <h1>Images for <?php echo $property['title']; ?></h1>
                <table class="property-images_table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_images) > 0) {
                            while ($row = mysqli_fetch_assoc($result_images)) {
                                $image_path = $row["image_path"];
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($image_path)) { ?>
                                    <img src="<?php echo $image_path; ?>" alt="Property Image" width="150" height="100" />
                                <?php } else { ?>
                                    <p>Image not found</p>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="admin_delete_image.php?propertyID=<?php echo $propertyID; ?>&image=<?php echo $image_path; ?>" onclick="return confirm('Are you sure you want to delete this image?');">Delete</a>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='2'>No images found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>