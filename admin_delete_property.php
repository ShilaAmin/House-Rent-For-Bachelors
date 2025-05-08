<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Admin') {
    header("Location: login.php"); // Redirect to login page if not logged in or not an admin
    exit();
}

require_once 'DBconnect.php';

// Check if the propertyID is provided in the query string
if (isset($_GET['propertyID'])) {
    $propertyID = $_GET['propertyID'];

    // Delete the property from the database using prepared statement
    $sql = "DELETE FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyID);

    if ($stmt->execute()) {
        echo "Property deleted successfully.";
    } else {
        echo "Error deleting property: " . $conn->error;
    }

    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

mysqli_close($conn);
?>