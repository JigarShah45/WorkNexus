<?php
$conn = new mysqli("localhost", "root", "", "employee");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$imageFolder = "employee_images/";

$stmt = $conn->prepare("SELECT employee_id, employee_name FROM tbl_employee WHERE profile_image IS NULL");
$stmt->execute();
$result = $stmt->get_result();

$imported = 0;
$skipped = 0;

while ($row = $result->fetch_assoc()) {
    $id = $row['employee_id'];
    $name = trim($row['employee_name']);
    $imageName = str_replace(' ', '_', $name) . ".jpg";
    $imagePath = $imageFolder . $imageName;

    if (file_exists($imagePath)) {
        $imageData = file_get_contents($imagePath);
        $update = $conn->prepare("UPDATE tbl_employee SET profile_image=? WHERE employee_id=?");
        $null = NULL;
        $update->bind_param("bi", $null, $id);
        $update->send_long_data(0, $imageData);
        if ($update->execute()) {
            echo " Imported: $name (ID $id) -> $imageName<br>";
            $imported++;
        } else {
            echo " Error importing: $name<br>";
        }
        $update->close();
    } else {
        echo " Not found: $imageName<br>";
        $skipped++;
    }
}

$stmt->close();
$conn->close();

echo "<br><h2>Import Completed</h2>";
echo "<p>Imported: $imported | Skipped: $skipped</p>";
?>
