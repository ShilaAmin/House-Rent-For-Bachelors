<?php
session_start();

// Check if the user is logged in and is a renter
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Renter') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a renter
    exit();
}

require_once 'DBconnect.php'; // Include database connection

// Check if userID parameter is set
if (!isset($_GET['userID'])) {
    header("Location: renter_bookings.php");
    exit();
}

$renteeID = $_GET['userID'];

// Get rentee details
$sql = "SELECT * FROM User WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $renteeID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: renter_bookings.php");
    exit();
}

$rentee = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentee Details - House Rent for Bachelors</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 2rem;
        }
        
        .profile-name {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .profile-info {
            margin-bottom: 2rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
        }
        
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .back-button {
            margin-top: 1rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1>Rentee Details</h1>
            </div>
            <ul class="nav_links">
                <li><a href="renter_bookings.php" class="dropbtn1">Back to Bookings</a></li>
                <li><a href="renter_dashboard.php" class="dropbtn1">Dashboard</a></li>
                <li><a href="logout.php" class="dropbtn1">Log Out</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="container">
        <div class="profile-header">
            <img src="uploads/<?php echo htmlspecialchars($rentee['picture']); ?>" alt="Profile Picture" class="profile-picture">
            <div>
                <h2 class="profile-name"><?php echo htmlspecialchars($rentee['name']); ?></h2>
                <p><?php echo ucfirst(htmlspecialchars($rentee['account_type'])); ?></p>
            </div>
        </div>
        
        <div class="profile-info">
            <div class="info-item">
                <span class="label">User ID:</span>
                <span><?php echo htmlspecialchars($rentee['userID']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Phone Number:</span>
                <span><?php echo htmlspecialchars($rentee['phone_no']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Email:</span>
                <span><?php echo htmlspecialchars($rentee['email']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Gender:</span>
                <span><?php echo ucfirst(htmlspecialchars($rentee['gender'])); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Location:</span>
                <span><?php echo htmlspecialchars($rentee['location']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">NID:</span>
                <span><?php echo htmlspecialchars($rentee['nid']); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Verification:</span>
                <span><?php echo ($rentee['verified'] == 1) ? 'Verified' : 'Not Verified'; ?></span>
            </div>
        </div>
        
        <a href="renter_bookings.php" class="dropbtn1 back-button">Back to Bookings</a>
    </div>
</body>
</html> 