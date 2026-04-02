<?php
include 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Get form data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$name = trim($first_name . ' ' . $last_name);
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$age = $_POST['age'] ?? '';
$blood_group = $_POST['blood_group'] ?? '';
$district = $_POST['district'] ?? '';
$state = $_POST['state'] ?? '';
$pin_code = $_POST['pin_code'] ?? '';
$last_donation_month = $_POST['last_donation_month'] ?? '';
$last_donation_year = $_POST['last_donation_year'] ?? '';

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($phone) || empty($blood_group) || empty($district)) {
    $response['message'] = 'Please fill all required fields';
    echo json_encode($response);
    exit;
}

// Build last donation date
$last_donation_date = null;
if ($last_donation_month && $last_donation_year && $last_donation_month != 'Month' && $last_donation_year != 'Year') {
    $last_donation_date = date('Y-m-d', strtotime("1 $last_donation_month $last_donation_year"));
}

// Generate random password (8 characters)
$temp_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
$hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

// Start transaction
$conn->begin_transaction();

try {
    // Insert into donors table
    $sql = "INSERT INTO donors (name, blood_group, city, phone, email, address, age, state, pin_code, last_donation_date, available) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", 
        $name, $blood_group, $district, $phone, $email, $address, $age, $state, $pin_code, $last_donation_date
    );
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    
    $donor_id = $stmt->insert_id;
    $stmt->close();
    
    // Create user account with auto-generated password
    $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role, donor_id) VALUES (?, ?, ?, 'donor', ?)");
    $userStmt->bind_param("sssi", $name, $email, $hashed_password, $donor_id);
    
    if (!$userStmt->execute()) {
        throw new Exception($userStmt->error);
    }
    $userStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = "Registration successful! Your login password is: " . $temp_password;
    $response['password'] = $temp_password; // Optional: for debugging
    
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'Registration failed: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>