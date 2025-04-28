<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once('DBconnect.php');

// Check if the userID is provided in the query string
if (isset($_GET['userID'])) {
    $userID = mysqli_real_escape_string($conn, $_GET['userID']);

    // Delete the user from the database
    $sql_delete_user = "DELETE FROM user WHERE userID = '$userID'";
    $result_delete_user = mysqli_query($conn, $sql_delete_user);

    if (mysqli_affected_rows($conn) > 0) {
        echo "<script>alert('User deleted successfully.'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error deleting user: " . mysqli_error($conn) . "'); window.location.href='admin_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('No user ID provided.'); window.location.href='admin_dashboard.php';</script>";
}

mysqli_close($conn);
?>