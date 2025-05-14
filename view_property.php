<?php
session_start();
require_once 'DBconnect.php'; // Include database connection

// Check if property ID is provided
if (!isset($_GET['id'])) {
    header("Location: properties_page.php");
    exit();
}

$propertyID = $_GET['id'];

// Fetch property details
$sql = "SELECT p.*, u.name as renter_name, u.phone_no as renter_phone 
        FROM property p 
        JOIN user u ON p.renterID = u.userID 
        WHERE p.propertyID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: properties_page.php");
    exit();
}

$property = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css">
    <title><?php echo htmlspecialchars($property['title']); ?> - Property Details</title>
    <style>
        .property-details {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }
        
        .property-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .property-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .property-description {
            grid-column: span 2;
            margin-top: 1rem;
        }
        
        .book-button {
            grid-column: span 2;
            margin-top: 1rem;
        }
        
        .info-item {
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .info-item strong {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .property-info {
                grid-template-columns: 1fr;
            }
            
            .property-description, .book-button {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1><a href="index.php">House Rent for Bachelors</a></h1>
            </div>
            <ul class="nav_links">
                <li><a href="properties_page.php"><button class="dropbtn1">All Properties</button></a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="<?php echo $_SESSION['account_type'] === 'Rentee' ? 'rentee_dashboard.php' : 'renter_dashboard.php'; ?>">
                        <button class="dropbtn1">Dashboard</button></a>
                    </li>
                    <li><a href="logout.php"><button class="dropbtn1">Log Out</button></a></li>
                <?php else: ?>
                    <li><a href="login.php"><button class="dropbtn1">Log In</button></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="property-details">
            <img src="uploads/<?php echo htmlspecialchars($property['photos']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
            
            <h2><?php echo htmlspecialchars($property['title']); ?></h2>
            
            <div class="property-info">
                <div class="info-item">
                    <strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?>
                </div>
                
                <div class="info-item">
                    <strong>Price:</strong> ৳<?php echo htmlspecialchars($property['price']); ?>
                </div>
                
                <div class="info-item">
                    <strong>Availability:</strong> <?php echo htmlspecialchars($property['remaining']); ?> out of <?php echo htmlspecialchars($property['capacity']); ?> slots
                </div>
                
                <div class="info-item">
                    <strong>Gender Preference:</strong> <?php echo htmlspecialchars($property['gender'] ?? 'Any'); ?>
                </div>
                
                <div class="info-item">
                    <strong>Renter:</strong> <?php echo htmlspecialchars($property['renter_name']); ?>
                </div>
                
                <div class="info-item">
                    <strong>Contact:</strong> <?php echo htmlspecialchars($property['renter_phone']); ?>
                </div>
                
                <div class="property-description">
                    <strong>Description:</strong>
                    <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
                
                <?php if (isset($_SESSION['user']) && $_SESSION['account_type'] === 'Rentee' && $property['remaining'] > 0): ?>
                    <div class="book-button">
                        <a href="checkout.php?propertyID=<?php echo $property['propertyID']; ?>">
                            <button class="dropbtn1">Book Now</button>
                        </a>
                    </div>
                <?php elseif (!isset($_SESSION['user'])): ?>
                    <div class="book-button">
                        <a href="login.php?redirect=view_property.php?id=<?php echo $property['propertyID']; ?>">
                            <button class="dropbtn1">Login to Book</button>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 House Rent for Bachelors. All rights reserved.</p>
    </footer>
</body>
</html> 