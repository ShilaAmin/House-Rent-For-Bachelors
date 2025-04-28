<?php
session_start();
require_once 'DBconnect.php'; // Database connection

$errorMessage = ""; // Initialize an error message variable
$successMessage = ""; // Initialize a success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $userID = $_POST['userID'];
    $phone_no = $_POST['phone_no'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $location = $_POST['location'];
    $password = $_POST['password'];
    $nid = $_POST['nid'];
    $account_type = $_POST['account_type'];
    $verified = 0; // Default value for verification status

    // Check if the username already exists
    $checkStmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $checkStmt->bind_param("s", $userID);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $errorMessage = "UserID already exists.";
        $checkStmt->close();
    } else {
        $checkStmt->close();

        // Handle file upload
        $picture = $_FILES['picture']['name'];
        $target_dir = "uploads/"; // Corrected path
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
        }
        $target_file = $target_dir . basename($picture);

        if (!move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
            echo "<script>alert('Error uploading picture.');</script>";
            exit;
        }

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert data into the database
        $stmt = $conn->prepare("INSERT INTO user (userID, phone_no, email, name, gender, location, picture, password, nid, verified, account_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssis", $userID, $phone_no, $email, $name, $gender, $location, $picture, $hashedPassword, $nid, $verified, $account_type);

        if ($stmt->execute()) {
            $successMessage = "Registration successful! Please log in.";
            header("Location: register.php?showLogin=true"); // Redirect to show login form
            exit();
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }

    $conn->close();
}

// Handle login functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM user WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);

        if (password_verify($password, $user_data['password'])) {
            $_SESSION['user_id'] = $user_data['userID'];
            $_SESSION['email'] = $user_data['email'];
            header("Location: index.php");
            exit();
        } else {
            $errorMessage = "Invalid email or password.";
        }
    } else {
        $errorMessage = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register or Login</title>
    <link rel="stylesheet" href="frontend/register.css"> <!-- Link to the CSS file -->
</head>
<body>
    <?php if (!empty($errorMessage)): ?>
        <script>
            alert("<?php echo $errorMessage; ?>");
        </script>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <script>
            alert("<?php echo $successMessage; ?>");
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['showLogin']) && $_GET['showLogin'] === 'true'): ?>
        <!-- Login Form -->
        <div class="form-container">
            <h2>Login</h2>
            <form method="POST">
                <input type="email" name="email" placeholder="Enter Email" required><br>
                <input type="password" name="password" placeholder="Enter Password" required><br>
                <button type="submit" name="login">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    <?php else: ?>
        <!-- Registration Form -->
        <div class="form-container">
            <h2>Register</h2>
            <form action="register.php" method="POST" enctype="multipart/form-data">
                <label for="userID">User ID: (give a unique username)</label>
                <input type="text" id="userID" name="userID" required>
                <br>
                <label for="phone_no">Phone Number:</label>
                <input type="text" id="phone_no" name="phone_no" required>
                <br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <br>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <br>
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <br>
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
                <br>
                <label for="picture">Picture:</label>
                <input type="file" id="picture" name="picture" accept="image/*" required>
                <br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <br>
                <label for="nid">NID:</label>
                <input type="text" id="nid" name="nid" required>
                <br>
                <label for="account_type">Account Type:</label>
                <select id="account_type" name="account_type" required>
                    <option value="rentee">Rentee</option>
                    <option value="renters">Renter</option>
                    <option value="admin">Admin</option>
                </select>
                <br>
                <button type="submit" name="register">Register</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>