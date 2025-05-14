<?php
$servername = "localhost";
$username = "root";     // Default XAMPP username
$password = "";         // Default XAMPP password
$dbname = "house_rent_for_bachelors";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Check if MySQL is running
    $mysql_running = false;
    if (function_exists('exec')) {
        exec('netstat -an | findstr ":3306"', $output);
        $mysql_running = !empty($output);
    }
    
    die("Database Error: " . $e->getMessage() . "<br><br>" .
        "Please check:<br>" .
        "1. Is XAMPP running?<br>" .
        "2. Is MySQL started in XAMPP Control Panel?" . 
        ($mysql_running ? "" : " (MySQL appears to be stopped)") . "<br>" .
        "3. Does the database 'house_rent_for_bachelors' exist?<br>");
}
//echo "Connected successfully!";
?>
