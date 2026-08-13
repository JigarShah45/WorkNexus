<?php
$conn = new mysqli('localhost', 'root', '', 'employee');
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit(1);
}

$result = $conn->query("SHOW TABLES LIKE 'tbl_notifications'");
if ($result->num_rows > 0) {
    echo "TABLE_EXISTS";
    $count = $conn->query("SELECT COUNT(*) as cnt FROM tbl_notifications")->fetch_assoc();
    echo "\nROWS: " . $count['cnt'];
} else {
    echo "TABLE_MISSING";
}

$conn->close();
