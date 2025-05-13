<?php
require_once "DBconnect.php"; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if the user exists in the database
    $sql = "SELECT * FROM user WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user["password"])) {
            session_start();
            $_SESSION["user"] = $username;
            $_SESSION["account_type"] = $user["account_type"];
            $_SESSION['user_picture'] = $user['picture']; // Assuming 'picture' is the column name in the database

            // Redirect based on account type
            if ($user["account_type"] == "Rentee") {
                header("Location: rentee_dashboard.php");
            } else if ($user["account_type"] == "Renter") {
                header("Location: renter_dashboard.php");
            } else if ($user["account_type"] == "Admin") {
                header("Location: admin_dashboard.php");
            }
            exit();
        } else {
            $errorMessage = "Password does not match.";
        }
    } else {
        $errorMessage = "User does not exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="frontend/login.css" />
    <title>House Rent Login</title>
</head>
<body>
    <header>
        <nav>
            <div class="nav_logo"></div>
        </nav>
    </header>
    <main>
        <section class="login">
            <div class="login_box">
                <h1>Login</h1>
                <?php if (!empty($errorMessage)): ?>
                    <div style="color: red; text-align: center;"><?php echo $errorMessage; ?></div>
                <?php endif; ?>
                <form class="login_form" action="login.php" method="post">
                    <input
                      type="text"
                      id="username"
                      name="username"
                      placeholder="Enter User ID"
                      required
                    />
                    <input
                      type="password"
                      id="password"
                      name="password"
                      placeholder="Enter Password"
                      required
                    />
                    <input type="submit" value="Login" name="login" />
                </form>
                <p style="text-align: center; margin-top: 10px;">
                    Don't have an account? <a href="register.php" style="color: blue; text-decoration: underline;">Register here</a>.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
