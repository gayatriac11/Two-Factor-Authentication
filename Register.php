<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auth_system";

// Establish connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get form input
$Username = trim($_POST['username']);
$Mobile = trim($_POST['mobileNo']);
$Password = trim($_POST['pass']);

// Password validation criteria
$uppercase = preg_match('/[A-Z]/', $Password);
$lowercase = preg_match('/[a-z]/', $Password);
$number = preg_match('/[0-9]/', $Password);
$specialChars = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $Password);
$minLength = 8;
$maxLength = 15;

// Check password length
if (strlen($Password) < $minLength || strlen($Password) > $maxLength) {
    echo "<script>alert('Password length must be between ${minLength} and ${maxLength} characters.');</script>";
    exit; // Stop script execution
}
// Check if password meets the criteria
elseif (!$lowercase || !$number || !$uppercase || !$specialChars) {
    echo "<script>alert('Password is weak. It must include uppercase letters, lowercase letters, numbers, and special characters.');</script>";
    exit; // Stop script execution
}

if (!preg_match('/^\+?[0-9]{10,15}$/', $Mobile)) {
    die("<script>alert('Invalid mobile number format.');</script>");
}
// Hash the password before storing
$hashedPassword = password_hash($Password, PASSWORD_BCRYPT);

// Prepare the SQL statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO `users` (`username`, `mobileNo`, `password`) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $Username, $Mobile, $hashedPassword);

// Execute the statement and check the result
if ($stmt->execute()) {
    echo "<script>alert('Registration successful! Redirecting to OTP verification...');</script>";
    echo "<script>window.location.href = 'OTP_Verification.html';</script>";
} else {
    echo "<script>alert('Error: Unable to register user.');</script>";
}

// Close the prepared statement and connection
$stmt->close();
mysqli_close($conn);

?>
