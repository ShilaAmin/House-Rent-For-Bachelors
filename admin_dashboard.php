<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['account_type'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

require_once('DBconnect.php');

// Fetch all users
$sql_users = "SELECT * FROM user";
$result_users = mysqli_query($conn, $sql_users);

// Fetch all properties
$sql_properties = "SELECT * FROM property";
$result_properties = mysqli_query($conn, $sql_properties);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="frontend/style.css"> <!-- Link to your CSS file -->
    <title>Admin Dashboard</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo">
                <h1><a href="admin_dashboard.php">Admin Dashboard</a></h1>
            </div>
            <ul class="nav_link">
                <li><a href="logout.php"><button class="dropbtn1">Log Out</button></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="admin_section">
            <div class="admin_box">
                <h1>Admin Dashboard</h1>

                <!-- Manage Users -->
                <h2>Manage Users</h2>
                <table class="admin_table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Location</th>
                            <th>Verified</th>
                            <th>Account Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_users) > 0) {
                            while ($row = mysqli_fetch_assoc($result_users)) {
                                echo "<tr>";
                                echo "<td>" . $row['userID'] . "</td>";
                                echo "<td>" . $row['name'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['phone_no'] . "</td>";
                                echo "<td>" . $row['gender'] . "</td>";
                                echo "<td>" . $row['location'] . "</td>";
                                echo "<td>" . ($row['verified'] ? 'Yes' : 'No') . "</td>";
                                echo "<td>" . $row['account_type'] . "</td>";
                                echo "<td><a href='admin_delete_user.php?userID=" . $row['userID'] . "' onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Manage Properties -->
                <h2>Manage Properties</h2>
                <table class="admin_table">
                    <thead>
                        <tr>
                            <th>Property ID</th>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_properties) > 0) {
                            while ($row = mysqli_fetch_assoc($result_properties)) {
                                echo "<tr>";
                                echo "<td>" . $row['propertyID'] . "</td>";
                                echo "<td>" . $row['title'] . "</td>";
                                echo "<td>" . $row['location'] . "</td>";
                                echo "<td>" . $row['price'] . "</td>";
                                echo "<td><a href='admin_delete_property.php?propertyID=" . $row['propertyID'] . "' onclick=\"return confirm('Are you sure you want to delete this property?');\">Delete</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No properties found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

<?php
mysqli_close($conn);
?>