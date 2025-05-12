<?php
session_start();

// Check if the user is logged in and is a renter
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Renter') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a renter
    exit();
}

require_once 'DBconnect.php'; // Include database connection

$renterID = $_SESSION['user'];

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingID = $_POST['bookingID'];
    $newStatus = $_POST['status'];
    
    // Update booking status
    $update_sql = "UPDATE Booking SET payment_status = ? WHERE bookingID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $newStatus, $bookingID);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $success_message = "Booking status updated successfully.";
    } else {
        $error_message = "Failed to update booking status.";
    }
}

// Fetch all bookings for properties owned by this renter
$bookings_sql = "SELECT b.*, p.title, p.location, p.photos, p.price, u.name as rentee_name, u.phone_no as rentee_phone 
                FROM Booking b 
                JOIN Has h ON b.bookingID = h.bookingID 
                JOIN Property p ON h.propertyID = p.propertyID 
                JOIN User u ON h.userID = u.userID
                WHERE p.renterID = ?
                ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($bookings_sql);
$stmt->bind_param("i", $renterID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - House Rent for Bachelors</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        header {
            background-color: #333;
            color: white;
            padding: 1rem;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav_links {
            list-style: none;
            display: flex;
        }
        
        .nav_links li {
            margin-left: 1rem;
        }
        
        .dropbtn1 {
            background-color: #4CAF50;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }
        
        .bookings-list {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }
        
        .booking-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
        }
        
        .booking-image {
            width: 150px;
            height: 100px;
            object-fit: cover;
            margin-right: 1rem;
        }
        
        .booking-details {
            flex: 1;
        }
        
        .booking-actions {
            width: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        
        .status-paid {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .status-cancelled {
            color: #f44336;
            font-weight: bold;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #dff0d8;
            border: 1px solid #d0e9c6;
            color: #3c763d;
        }
        
        .alert-danger {
            background-color: #f2dede;
            border: 1px solid #ebcccc;
            color: #a94442;
        }
        
        select, button {
            padding: 0.5rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1>Manage Bookings</h1>
            </div>
            <ul class="nav_links">
                <li><a href="renter_dashboard.php" class="dropbtn1">Dashboard</a></li>
                <li><a href="logout.php" class="dropbtn1">Log Out</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="bookings-list">
            <h2>Bookings for Your Properties</h2>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($booking = mysqli_fetch_assoc($result)): ?>
                    <div class="booking-card">
                        <img src="uploads/<?php echo htmlspecialchars($booking['photos']); ?>" alt="Property Image" class="booking-image">
                        
                        <div class="booking-details">
                            <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['location']); ?></p>
                            <p><strong>Price:</strong> ৳<?php echo htmlspecialchars($booking['price']); ?></p>
                            <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($booking['payment_method']); ?></p>
                            <p><strong>Rentee:</strong> <?php echo htmlspecialchars($booking['rentee_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['rentee_phone']); ?></p>
                            <p><strong>Payment Status:</strong> 
                                <span class="status-<?php echo strtolower($booking['payment_status']); ?>">
                                    <?php echo htmlspecialchars($booking['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="booking-actions">
                            <form method="POST" action="">
                                <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
                                <select name="status">
                                    <option value="Pending" <?php echo ($booking['payment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Paid" <?php echo ($booking['payment_status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                    <option value="Cancelled" <?php echo ($booking['payment_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="dropbtn1">Update Status</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No bookings found for your properties.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 