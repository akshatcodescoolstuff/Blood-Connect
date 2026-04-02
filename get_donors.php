<?php
include 'config.php';

header('Content-Type: application/json');

$sql = "SELECT id, name, blood_group, city, phone, available FROM donors";
$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>