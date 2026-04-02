    <?php
    include 'config.php';

    header('Content-Type: application/json');

    $response = ['success' => false, 'message' => ''];

    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        $response['message'] = 'Please fill in your name, email, and message.';
        echo json_encode($response);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }

    // Create contact_messages table if not exists
    $createTable = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        subject VARCHAR(200) DEFAULT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $conn->query($createTable);

    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Message sent successfully! We will get back to you within 24 hours.';
        
        // Optional: Send email notification to admin
        // mail("admin@bloodconnect.com", "New Contact Message from $name", $message, "From: $email");
    } else {
        $response['message'] = 'Failed to send message. Please try again.';
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
    ?>
