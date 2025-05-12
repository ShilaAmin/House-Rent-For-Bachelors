<?php
session_start();

// Check if the user is logged in and is a rentee
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Rentee') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a rentee
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Check if booking ID is provided
if (!isset($_POST['bookingID'])) {
    header("Location: rentee_dashboard.php");
    exit();
}

$bookingID = $_POST['bookingID'];
$userID = $_SESSION['user'];

// Verify that the booking belongs to the current user and is in 'Pending' status
$check_sql = "SELECT b.*, h.propertyID 
              FROM Booking b 
              JOIN Has h ON b.bookingID = h.bookingID 
              WHERE b.bookingID = ? AND h.userID = ? AND b.payment_status = 'Pending'";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $bookingID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Either the booking doesn't exist, doesn't belong to this user, or is not in 'Pending' status
    $_SESSION['error_message'] = "Unable to cancel booking. It may have already been processed.";
    header("Location: rentee_dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();
$propertyID = $booking['propertyID'];

// Start transaction
$conn->begin_transaction();

try {
    // Update booking status to 'Cancelled'
    $update_sql = "UPDATE Booking SET payment_status = 'Cancelled' WHERE bookingID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $bookingID);
    $stmt->execute();
    
    // Increase the remaining slots for the property
    $update_property_sql = "UPDATE Property SET remaining = remaining + 1 WHERE propertyID = ?";
    $stmt = $conn->prepare($update_property_sql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    $_SESSION['success_message'] = "Booking cancelled successfully.";
} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();
    $_SESSION['error_message'] = "Error cancelling booking: " . $e->getMessage();
}

// Redirect back to the rentee dashboard
header("Location: rentee_dashboard.php");
exit();
?> 