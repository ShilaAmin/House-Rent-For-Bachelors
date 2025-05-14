<?php
session_start();

// Check if the user is logged in and is a rentee
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Rentee') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a rentee
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Fetch properties based on search criteria or show all properties
$where_clauses = [];
$params = [];
$types = "";

// Check if location is provided
if (!empty($_GET['location'])) {
    $where_clauses[] = "location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}

// Check if min price is provided
if (!empty($_GET['min_price'])) {
    $where_clauses[] = "price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}

// Check if max price is provided
if (!empty($_GET['max_price'])) {
    $where_clauses[] = "price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

// Check if gender is provided
if (!empty($_GET['gender'])) {
    $where_clauses[] = "gender = ?";
    $params[] = $_GET['gender'];
    $types .= "s";
}

// Build the query
$sql = "SELECT * FROM property WHERE remaining > 0";
if (!empty($where_clauses)) {
    $sql .= " AND " . implode(" AND ", $where_clauses);
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch user's bookings
$userID = $_SESSION['user'];
$bookings_sql = "SELECT b.*, p.title, p.location, p.photos, p.price 
                FROM Booking b 
                JOIN Has h ON b.bookingID = h.bookingID 
                JOIN Property p ON h.propertyID = p.propertyID 
                WHERE h.userID = ?
                ORDER BY b.booking_date DESC";
$stmt_bookings = $conn->prepare($bookings_sql);
$stmt_bookings->bind_param("i", $userID);
$stmt_bookings->execute();
$bookings_result = $stmt_bookings->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/rentee_dashboard.css">
    <title>Rentee Dashboard</title>
    <style>
        .bookings {
            margin-top: 2rem;
        }
        
        .bookings h3 {
            margin-bottom: 1rem;
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
        
        .cancel-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
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
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1>Rentee Dashboard</h1>
            </div>
            <ul class="nav_links">
                <li><a href="logout.php"><button class="dropbtn1">Log Out</button></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <section class="search">
            <h3>Search Properties</h3>
            <form method="GET" action="rentee_dashboard.php" class="search_form">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" placeholder="Enter location">

                <label for="min_price">Min Price:</label>
                <input type="number" id="min_price" name="min_price" placeholder="Min price">

                <label for="max_price">Max Price:</label>
                <input type="number" id="max_price" name="max_price" placeholder="Max price">

                <label for="gender">Gender:</label>
                <select id="gender" name="gender">
                    <option value="">Any</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <button type="submit" class="dropbtn1">Search</button>
            </form>
        </section>

        <section class="properties">
            <h3>Available Properties</h3>
            <div class="properties_grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($property = mysqli_fetch_assoc($result)): ?>
                        <div class="property_card">
                            <img src="uploads/<?php echo htmlspecialchars($property['photos']); ?>" alt="Property Image" class="property_image">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p>Location: <?php echo htmlspecialchars($property['location']); ?></p>
                            <p>Description: <?php echo htmlspecialchars($property['description']); ?></p>
                            <p>Price: ৳<?php echo htmlspecialchars($property['price']); ?></p>
                            <p>Slots: <?php echo htmlspecialchars($property['remaining']); ?> / <?php echo htmlspecialchars($property['capacity']); ?></p>
                            <?php if ($property['remaining'] > 0): ?>
                                <a href="checkout.php?propertyID=<?php echo $property['propertyID']; ?>" class="dropbtn1">Book Now</a>
                            <?php else: ?>
                                <button type="button" class="dropbtn1" disabled>No Slots Available</button>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No properties available for rent at the moment.</p>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="bookings">
            <h3>My Bookings</h3>
            <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                    <div class="booking-card">
                        <img src="uploads/<?php echo htmlspecialchars($booking['photos']); ?>" alt="Property Image" class="booking-image">
                        <div class="booking-details">
                            <h4><?php echo htmlspecialchars($booking['title']); ?></h4>
                            <p>Location: <?php echo htmlspecialchars($booking['location']); ?></p>
                            <p>Price: ৳<?php echo htmlspecialchars($booking['price']); ?></p>
                            <p>Booking Date: <?php echo htmlspecialchars($booking['booking_date']); ?></p>
                            <p>Payment Method: <?php echo htmlspecialchars($booking['payment_method']); ?></p>
                            <p>Payment Status: 
                                <span class="status-<?php echo strtolower($booking['payment_status']); ?>">
                                    <?php echo htmlspecialchars($booking['payment_status']); ?>
                                </span>
                            </p>
                            <?php if ($booking['payment_status'] === 'Pending'): ?>
                                <form method="POST" action="cancel_booking.php">
                                    <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
                                    <button type="submit" name="cancel_booking" class="cancel-btn">Cancel Booking</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You have no bookings yet.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>