<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

require_once 'DBconnect.php';

if (isset($_GET['propertyID'])) {
    $propertyID = $_GET['propertyID'];
    $sql = "SELECT photos FROM property WHERE propertyID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = "uploads/" . $row['photos'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // Delete the image file
        }
        $sql_delete_image = "UPDATE property SET photos = NULL WHERE propertyID = ?";
        $stmt_delete = $conn->prepare($sql_delete_image);
        $stmt_delete->bind_param("i", $propertyID);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}
?>