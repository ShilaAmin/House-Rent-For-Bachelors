<?php
include '../frontend/register.html'; // Include the HTML file at the top
require_once 'DBconnect.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Handle file upload
    $picture = $_FILES['picture']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($picture);

    if (!move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
        echo "Error uploading picture.";
        exit;
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO users (userID, phone_no, email, name, gender, location, picture, password, nid, verified, account_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssis", $userID, $phone_no, $email, $name, $gender, $location, $picture, $hashedPassword, $nid, $verified, $account_type);

    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: index.php"); // Redirect to the homepage after successful registration
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>