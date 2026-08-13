<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'employee';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Kill other stuck transactions first
$procs = $conn->query("SHOW PROCESSLIST");
if ($procs) {
    while ($row = $procs->fetch_assoc()) {
        if ($row['Id'] != $conn->thread_id && $row['Info'] !== null && strpos($row['Info'], 'LOCK') !== false) {
            $conn->query("KILL " . $row['Id']);
        }
    }
}

$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);

// Ensure department exists
$conn->query("INSERT IGNORE INTO tbl_department (department_id, department_name, created_at) VALUES (1, 'Administration', NOW())");

// Check if admin employee exists
$check = $conn->query("SELECT employee_id FROM tbl_employee WHERE employee_email = 'admin@example.com' LIMIT 1");

if ($check && $check->num_rows > 0) {
    $emp = $check->fetch_assoc();
    $employee_id = $emp['employee_id'];
    echo "Found existing admin employee (ID: $employee_id)<br>";
} else {
    $conn->query("INSERT IGNORE INTO tbl_employee (employee_name, employee_email, employee_phone, employee_salary, department_id, status, created_at) VALUES ('Admin User', 'admin@example.com', '9999999999', 100000, 1, 'Active', NOW())");
    $employee_id = $conn->insert_id;
    if ($employee_id == 0) {
        $r = $conn->query("SELECT employee_id FROM tbl_employee WHERE employee_email = 'admin@example.com'");
        $employee_id = $r->fetch_assoc()['employee_id'];
    }
    echo "Created admin employee (ID: $employee_id)<br>";
}

// Check if user exists
$user_check = $conn->query("SELECT user_id FROM tbl_users WHERE username = 'admin' LIMIT 1");

if ($user_check && $user_check->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE tbl_users SET password = ?, role = 'Admin', status = 'Active' WHERE username = 'admin'");
    $stmt->bind_param("s", $hashed_password);
    $stmt->execute();
    $stmt->close();
    echo "Admin password updated!<br>";
} else {
    $stmt = $conn->prepare("INSERT INTO tbl_users (employee_id, username, password, role, status, created_at) VALUES (?, 'admin', ?, 'Admin', 'Active', NOW())");
    $stmt->bind_param("is", $employee_id, $hashed_password);
    $stmt->execute();
    $stmt->close();
    echo "Admin user created!<br>";
}

echo "<br><b>Username: admin</b><br>";
echo "<b>Password: admin123</b><br>";
echo "<br><a href='http://localhost:7328/employee_management/auth/login'>Go to Login</a>";

$conn->close();
?>