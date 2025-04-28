<?php
session_start();

if (isset($_SESSION["user"])) {
    $username1 = $_SESSION["user"];
    require_once "DBconnect.php";

    // Fetch the account type of the logged-in user
    $sql = "SELECT * FROM user WHERE userID = '$username1'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

    // Redirect based on account type
    if ($user["account_type"] == "rentee") {
        header("Location: rentee_dashboard.php");
    } else if ($user["account_type"] == "renter") {
        header("Location: renter_dashboard.php");
    } else if ($user["account_type"] == "admin") {
        header("Location: admin_dashboard.php");
    }
    exit();
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
            <div class="nav_logo">
                <!-- Removed the bold "House Rent" text -->
                <!-- <h1><a href="index.php">House Rent</a></h1> -->
            </div>
        </nav>
    </header>
    <main>
        <section class="login">
            <div class="login_box">
                <h1>Login</h1>
                <?php
                if (isset($_POST["login"])) {
                    $username = $_POST["username"];
                    $password = $_POST["password"];

                    // Hardcoded admin credentials
                    if ($username === "admin_yoshi" && $password === "1234") {
                        $_SESSION["user"] = $username;
                        $_SESSION["admin_logged_in"] = true;
                        header("Location: admin_dashboard.php");
                        exit();
                    }

                    require_once "DBconnect.php";

                    // Check if the user exists in the database
                    $sql = "SELECT * FROM user WHERE userID = '$username'";
                    $result = mysqli_query($conn, $sql);
                    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    if ($user) {
                        // Verify the password
                        if (password_verify($password, $user["password"])) {
                            $_SESSION["user"] = $username;

                            // Redirect based on account type
                            if ($user["account_type"] == "rentee") {
                                header("Location: rentee_dashboard.php");
                            } else if ($user["account_type"] == "renter") {
                                header("Location: renter_dashboard.php");
                            } else if ($user["account_type"] == "admin") {
                                header("Location: admin_dashboard.php");
                            }
                            exit();
                        } else {
                            echo "<div style='color: red; text-align: center;'>Password does not match</div>";
                        }
                    } else {
                        echo "<div style='color: red; text-align: center;'>User does not exist</div>";
                    }
                }
                ?>
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
            </div>
        </section>
    </main>
</body>
</html>
