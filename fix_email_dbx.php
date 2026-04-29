<?php
// fix_email_db.php
// Script to remove UNIQUE constraint on email column

include 'dbconn.php'; 

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Checking indexes on tb_user...\n";

// Find the index name for the UNIQUE constraint on email
$sql_check = "SHOW INDEX FROM tb_user WHERE Column_name = 'email' AND Non_unique = 0";
$result = $conn->query($sql_check);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $keyName = $row['Key_name'];
        echo "Found UNIQUE index: " . $keyName . "\n";
        
        $sql_drop = "ALTER TABLE tb_user DROP INDEX `" . $keyName . "`";
        if ($conn->query($sql_drop)) {
            echo "Successfully dropped index '" . $keyName . "'.\n";
        } else {
            echo "Error dropping index: " . $conn->error . "\n";
        }
    }
} else {
    echo "No UNIQUE index found on 'email' column.\n";
    // Check if maybe it's hitting a primary key? Unlikely for email.
}

// Optional: Make email nullable if it isn't
$sql_alter = "ALTER TABLE tb_user MODIFY email VARCHAR(255) NULL";
if ($conn->query($sql_alter)) {
    echo "Successfully altered email column to allow NULL.\n";
} else {
    echo "Error altering email column: " . $conn->error . "\n";
}

echo "Done.\n";
?>
