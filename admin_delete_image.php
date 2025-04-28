<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once('DBconnect.php');

// Check if required parameters are provided
if (isset($_GET['propertyID']) && isset($_GET['image'])) {
    $propertyID = mysqli_real_escape_string($conn, $_GET['propertyID']);
    $image = mysqli_real_escape_string($conn, $_GET['image']);

    // Fetch the image path from the database
    $sql_fetch = "SELECT image_path FROM property_images WHERE propertyID = '$propertyID' AND image_path = '$image'";
    $result_fetch = mysqli_query($conn, $sql_fetch);

    if (mysqli_num_rows($result_fetch) > 0) {
        $row = mysqli_fetch_assoc($result_fetch);
        $image_path = $row['image_path'];

        // Delete the image record from the database
        $sql_delete = "DELETE FROM property_images WHERE propertyID = '$propertyID' AND image_path = '$image'";
        if (mysqli_query($conn, $sql_delete)) {
            // Delete the image file from the server
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            echo "<script>alert('Image deleted successfully.'); window.location.href='admin_edit_images.php?propertyID=$propertyID';</script>";
        } else {
            echo "<script>alert('Error deleting image: " . mysqli_error($conn) . "'); window.location.href='admin_edit_images.php?propertyID=$propertyID';</script>";
        }
    } else {
        echo "<script>alert('Image not found.'); window.location.href='admin_edit_images.php?propertyID=$propertyID';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='admin_dashboard.php';</script>";
}

mysqli_close($conn);
?>