<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'auth_system';

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$username = trim($_POST['username']);
$password_entered = trim($_POST['pass']);

// Check if username or password is empty
if (empty($username) || empty($password_entered)) {
    echo "<script>alert('Username or Password cannot be empty');</script>";
    exit;
}

// Fetch mobileNo using username
$sql = "SELECT mobileNo FROM `users` WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters and execute
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists in the database
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $mobileNo = $row['mobileNo'];

    // Fetch the hashed password using mobileNo
    $sql_password = "SELECT password FROM `users` WHERE mobileNo = ? LIMIT 1";
    $stmt_password = $conn->prepare($sql_password);

    if (!$stmt_password) {
        die("Error preparing password statement: " . $conn->error);
    }

    $stmt_password->bind_param("s", $mobileNo);
    $stmt_password->execute();
    $result_password = $stmt_password->get_result();

    if ($result_password->num_rows === 1) {
        $row_password = $result_password->fetch_assoc();
        $storedHashedPassword = $row_password['password'];

        // Verify the entered password against the hashed password
        if (password_verify($password_entered, $storedHashedPassword)) {
            echo "<script>
                    alert('Valid Password. Login Successful!');
                    window.location.href = 'Biometric_Verification.html';
                  </script>";
        } else {
            echo "<script>alert('Invalid password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Password not found for the user.');</script>";
    }

    // Close the password statement
    $stmt_password->close();
} else {
    echo "<script>alert('User does not exist.');</script>";
}

// Close the connection
$stmt->close();
$conn->close();
?>
