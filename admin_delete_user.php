<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Admin') {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once 'DBconnect.php';

// Check if the userID is provided in the query string
if (isset($_GET['userID'])) {
    $userID = $_GET['userID'];

    // Delete the user from the database using prepared statement
    $sql = "DELETE FROM user WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userID);

    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user: " . $conn->error;
    }

    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

mysqli_close($conn);
?>