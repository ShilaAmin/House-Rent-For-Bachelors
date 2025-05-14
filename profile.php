<?php
session_start();
require_once 'DBconnect.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['user'];
$errorMessage = "";
$successMessage = "";

// Fetch user details
$sql = "SELECT * FROM user WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newUserID = $_POST['userID'];
    $phone_no = $_POST['phone_no'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Handle file upload for profile picture
    $picture = $user['picture']; // Default to the current picture
    if (!empty($_FILES['picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['picture']['name']);
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
            $picture = $_FILES['picture']['name'];
        } else {
            $errorMessage = "Error uploading picture.";
        }
    }

    // Update user details in the database
    $sql = "UPDATE user SET userID = ?, phone_no = ?, email = ?, name = ?, password = ?, picture = ? WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $newUserID, $phone_no, $email, $name, $hashedPassword, $picture, $userID);

    if ($stmt->execute()) {
        $_SESSION['user'] = $newUserID; // Update session with new userID
        $successMessage = "Profile updated successfully!";
        header("Refresh:0"); // Refresh the page to show updated details
    } else {
        $errorMessage = "Error updating profile: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="frontend/profile.css"> <!-- Link to CSS -->
</head>
<body>
    <div class="profile-container">
        <h2>Your Profile</h2>
        <?php if (!empty($errorMessage)): ?>
            <div class="error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <div class="success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <label for="userID">User ID:</label>
            <input type="text" id="userID" name="userID" value="<?php echo htmlspecialchars($user['userID']); ?>" readonly>

            <label for="phone_no">Phone Number:</label>
            <input type="text" id="phone_no" name="phone_no" value="<?php echo htmlspecialchars($user['phone_no']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter new password">

            <label for="picture">Profile Picture:</label>
            <input type="file" id="picture" name="picture" accept="image/*">
            <img src="uploads/<?php echo htmlspecialchars($user['picture']); ?>" alt="Profile Picture" class="profile-picture">

            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>
    <ul class="navigation-menu">
        <li><a href="profile.php"><button class="dropbtn1">Profile</button></a></li>
    </ul>
</body>
</html>