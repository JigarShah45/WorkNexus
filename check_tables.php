<?php
$conn = new mysqli('localhost', 'root', '', 'employee');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Show existing tables
echo "<h3>Existing Tables:</h3>";
$result = $conn->query("SHOW TABLES");
$tables = array();
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
    echo "{$row[0]}<br>";
}

echo "<br><h3>Missing Tables:</h3>";
$required = array(
    'tbl_permissions',
    'tbl_role_permissions',
    'tbl_user_permissions',
    'tbl_audit_trail',
    'tbl_user_logs',
    'tbl_shifts',
    'tbl_employee_shifts',
    'tbl_attendance',
    'tbl_leave_requests',
    'tbl_client_meetings',
    'tbl_meeting_employees',
    'tbl_meeting_files',
    'tbl_salary_hikes'
);

$missing = array();
foreach ($required as $table) {
    if (!in_array($table, $tables)) {
        $missing[] = $table;
        echo "<span style='color:red'>MISSING: {$table}</span><br>";
    } else {
        echo "<span style='color:green'>EXISTS: {$table}</span><br>";
    }
}

if (empty($missing)) {
    echo "<br><b style='color:green'>All tables exist!</b>";
}

$conn->close();
?>
