<?php
session_start();

// Check if the user is logged in and is a rentee
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Rentee') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a rentee
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Check if property ID is provided
if (!isset($_GET['propertyID'])) {
    header("Location: rentee_dashboard.php");
    exit();
}

$propertyID = $_GET['propertyID'];
$userID = $_SESSION['user'];

// Fetch property details
$property_sql = "SELECT * FROM property WHERE propertyID = ?";
$stmt = $conn->prepare($property_sql);
$stmt->bind_param("i", $propertyID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: rentee_dashboard.php");
    exit();
}

$property = $result->fetch_assoc();

// Fetch rentee details to check gender
$rentee_sql = "SELECT gender FROM user WHERE userID = ?";
$stmt = $conn->prepare($rentee_sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$rentee_result = $stmt->get_result();
$rentee = $rentee_result->fetch_assoc();

// Check if rentee's gender matches the property's gender preference
if (isset($property['gender']) && $property['gender'] !== 'Any' && $property['gender'] !== $rentee['gender']) {
    $_SESSION['error_message'] = "This property is only available for " . $property['gender'] . " rentees.";
    header("Location: rentee_dashboard.php");
    exit();
}

// Process payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get payment details
    $payment_method = $_POST['payment_method'];
    $booking_date = date('Y-m-d');
    $payment_status = 'Pending'; // Initially set as pending
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert into Booking table
        $booking_sql = "INSERT INTO Booking (booking_date, payment_method, payment_status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($booking_sql);
        $stmt->bind_param("sss", $booking_date, $payment_method, $payment_status);
        $stmt->execute();
        
        // Get the booking ID
        $bookingID = $conn->insert_id;
        
        // Insert into Has table (relationship between booking, property, and user)
        $has_sql = "INSERT INTO Has (bookingID, propertyID, userID) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($has_sql);
        $stmt->bind_param("iii", $bookingID, $propertyID, $userID);
        $stmt->execute();
        
        // Update property's remaining slots
        $update_sql = "UPDATE Property SET remaining = remaining - 1 WHERE propertyID = ? AND remaining > 0";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $propertyID);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            // No slots available
            throw new Exception("No slots available for this property.");
        }
        
        // If payment method is "Cash", set payment status as "Pending"
        // If payment method is "Online", simulate successful payment and set status as "Paid"
        if ($payment_method === "Online") {
            $update_status_sql = "UPDATE Booking SET payment_status = 'Paid' WHERE bookingID = ?";
            $stmt = $conn->prepare($update_status_sql);
            $stmt->bind_param("i", $bookingID);
            $stmt->execute();
        }
        
        // Commit the transaction
        $conn->commit();
        
        // Redirect to success page
        header("Location: booking_success.php?bookingID=" . $bookingID);
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - House Rent for Bachelors</title>
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
        }
        
        .property-details {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
        .property-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            margin-right: 1rem;
        }
        
        .property-info {
            flex: 1;
        }
        
        .payment-form {
            margin-top: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        select, input[type="text"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .btn:hover {
            background-color: #45a049;
        }
        
        .error {
            color: red;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Checkout</h1>
    </header>
    
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="error">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>
        
        <div class="property-details">
            <img src="uploads/<?php echo htmlspecialchars($property['photos']); ?>" alt="Property Image" class="property-image">
            <div class="property-info">
                <h2><?php echo htmlspecialchars($property['title']); ?></h2>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                <p><strong>Price:</strong> ৳<?php echo htmlspecialchars($property['price']); ?></p>
                <p><strong>Available Slots:</strong> <?php echo htmlspecialchars($property['remaining']); ?> of <?php echo htmlspecialchars($property['capacity']); ?></p>
                <p><strong>Gender Preference:</strong> <?php echo htmlspecialchars($property['gender'] ?? 'Any'); ?></p>
            </div>
        </div>
        
        <div class="payment-form">
            <h3>Payment Details</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="payment_method">Payment Method:</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="Cash">Cash on Arrival</option>
                        <option value="Online">Online Payment (Credit/Debit Card)</option>
                        <option value="bKash">bKash</option>
                        <option value="Nagad">Nagad</option>
                    </select>
                </div>
                
                <div id="online_payment_fields" style="display: none;">
                    <div class="form-group">
                        <label for="card_number">Card Number:</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                    </div>
                    
                    <div class="form-group">
                        <label for="card_holder">Card Holder Name:</label>
                        <input type="text" id="card_holder" name="card_holder" placeholder="John Doe">
                    </div>
                    
                    <div class="form-group" style="display: flex; gap: 1rem;">
                        <div style="flex: 1;">
                            <label for="expiry_date">Expiry Date:</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                        </div>
                        <div style="flex: 1;">
                            <label for="cvv">CVV:</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123">
                        </div>
                    </div>
                </div>
                
                <div id="mobile_payment_fields" style="display: none;">
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number:</label>
                        <input type="text" id="mobile_number" name="mobile_number" placeholder="01XXXXXXXXX">
                    </div>
                </div>
                
                <button type="submit" class="btn">Complete Booking</button>
            </form>
        </div>
    </div>
    
    <script>
        // Show/hide payment fields based on selected payment method
        document.getElementById('payment_method').addEventListener('change', function() {
            const onlineFields = document.getElementById('online_payment_fields');
            const mobileFields = document.getElementById('mobile_payment_fields');
            
            if (this.value === 'Online') {
                onlineFields.style.display = 'block';
                mobileFields.style.display = 'none';
            } else if (this.value === 'bKash' || this.value === 'Nagad') {
                onlineFields.style.display = 'none';
                mobileFields.style.display = 'block';
            } else {
                onlineFields.style.display = 'none';
                mobileFields.style.display = 'none';
            }
        });
    </script>
</body>
</html> 