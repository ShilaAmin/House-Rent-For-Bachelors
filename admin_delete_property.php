<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once('DBconnect.php');

// Check if the propertyID is provided in the query string
if (isset($_GET['propertyID'])) {
    $propertyID = mysqli_real_escape_string($conn, $_GET['propertyID']);

    // Delete the property from the database
    $sql_delete_property = "DELETE FROM property WHERE propertyID = '$propertyID'";
    $result_delete_property = mysqli_query($conn, $sql_delete_property);

    if (mysqli_affected_rows($conn) > 0) {
        echo "<script>alert('Property deleted successfully.'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error deleting property: " . mysqli_error($conn) . "'); window.location.href='admin_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request. No property ID provided.'); window.location.href='admin_dashboard.php';</script>";
}

mysqli_close($conn);
?>