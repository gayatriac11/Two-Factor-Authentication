<?php
// Database connection to otp_system
$conn = new mysqli("localhost", "root", "", "otp_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if POST data is set
if (!isset($_POST['mobile']) || !isset($_POST['otp'])) {
    die("Error: Mobile number or OTP not provided.");
}

// Validate and sanitize input
$mobile = $conn->real_escape_string($_POST['mobile']);
$otp = $conn->real_escape_string($_POST['otp']);

// Fetch the OTP and expiry from the database
$query = "SELECT otp, otp_expiry FROM users WHERE mobileNo = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stored_otp = $row['otp'];
    $otp_expiry = $row['otp_expiry'];

    // Check if the OTP matches and is not expired
    if ($otp === $stored_otp) {
        if (strtotime($otp_expiry) > time()) {
            echo "OTP verified successfully!";
        } else {
            echo "OTP has expired. Please request a new one.";
        }
    } else {
        echo "Invalid OTP. Please try again.";
    }
} else {
    echo "Mobile number not found in OTP database. Please request an OTP first.";
}

$stmt->close();
$conn->close();
?>
