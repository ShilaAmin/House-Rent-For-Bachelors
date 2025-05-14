<?php
require_once 'DBconnect.php';

// Check if the gender column already exists
$result = $conn->query("SHOW COLUMNS FROM property LIKE 'gender'");
$exists = $result->num_rows > 0;

if (!$exists) {
    // Add the gender column to the property table
    $sql = "ALTER TABLE property ADD COLUMN gender VARCHAR(10) DEFAULT 'Any'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Gender column added successfully to the property table.";
    } else {
        echo "Error adding gender column: " . $conn->error;
    }
} else {
    echo "Gender column already exists in the property table.";
}

// Close the connection
$conn->close();
?> 