<?php
session_start();

// Check if the user is logged in and is a rentee
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Rentee') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a rentee
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Check if booking ID is provided
if (!isset($_GET['bookingID'])) {
    header("Location: rentee_dashboard.php");
    exit();
}

$bookingID = $_GET['bookingID'];
$userID = $_SESSION['user'];

// Fetch booking details
$booking_sql = "SELECT b.*, p.title, p.location, p.price, p.photos 
                FROM Booking b 
                JOIN Has h ON b.bookingID = h.bookingID 
                JOIN Property p ON h.propertyID = p.propertyID 
                WHERE b.bookingID = ? AND h.userID = ?";
$stmt = $conn->prepare($booking_sql);
$stmt->bind_param("ii", $bookingID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: rentee_dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Successful - House Rent for Bachelors</title>
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
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .success-icon {
            color: #4CAF50;
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .booking-details {
            margin-top: 2rem;
            text-align: left;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .property-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }
        
        .btn:hover {
            background-color: #45a049;
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
    </style>
</head>
<body>
    <header>
        <h1>Booking Successful</h1>
    </header>
    
    <div class="container">
        <div class="success-icon">✓</div>
        <h2>Your booking has been confirmed!</h2>
        <p>Thank you for booking with House Rent for Bachelors. Your booking details are below.</p>
        
        <div class="booking-details">
            <img src="uploads/<?php echo htmlspecialchars($booking['photos']); ?>" alt="Property Image" class="property-image">
            
            <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['location']); ?></p>
            <p><strong>Price:</strong> ৳<?php echo htmlspecialchars($booking['price']); ?></p>
            <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($booking['payment_method']); ?></p>
            <p><strong>Payment Status:</strong> 
                <span class="status-<?php echo strtolower($booking['payment_status']); ?>">
                    <?php echo htmlspecialchars($booking['payment_status']); ?>
                </span>
            </p>
            
            <?php if ($booking['payment_status'] === 'Pending'): ?>
                <p>Please complete your payment to finalize your booking.</p>
            <?php elseif ($booking['payment_status'] === 'Paid'): ?>
                <p>Your payment has been received. You can move in as per the agreement.</p>
            <?php endif; ?>
        </div>
        
        <a href="rentee_dashboard.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html> 