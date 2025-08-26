<?php
// Database connection to both databases
$conn_auth = new mysqli("localhost", "root", "", "auth_system");
$conn_otp = new mysqli("localhost", "root", "", "otp_system");

if ($conn_auth->connect_error) {
    die("Connection failed to auth_system: " . $conn_auth->connect_error);
}
if ($conn_otp->connect_error) {
    die("Connection failed to otp_system: " . $conn_otp->connect_error);
}

// Get the mobile number from the request
$mobile = $_POST['mobile'];

// Check if the mobile number exists in the auth_system database
$query_auth = "SELECT * FROM users WHERE mobileNo = ?";
$stmt_auth = $conn_auth->prepare($query_auth);
$stmt_auth->bind_param("s", $mobile);
$stmt_auth->execute();
$result_auth = $stmt_auth->get_result();

if ($result_auth->num_rows > 0) {
    // Mobile number exists in auth_system, proceed to generate and send OTP

    // Generate a 6-digit OTP
    $otp = rand(100000, 999999);
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes")); // OTP expires in 10 minutes

    // Check if the mobile number already exists in the otp_system database
    $query_otp = "SELECT * FROM users WHERE mobileNo = ?";
    $stmt_otp = $conn_otp->prepare($query_otp);
    $stmt_otp->bind_param("s", $mobile);
    $stmt_otp->execute();
    $result_otp = $stmt_otp->get_result();

    if ($result_otp->num_rows > 0) {
        // Update OTP and expiry for the existing user in otp_system
        $query_update = "UPDATE users SET otp = ?, otp_expiry = ? WHERE mobileNo = ?";
        $stmt_update = $conn_otp->prepare($query_update);
        $stmt_update->bind_param("sss", $otp, $otp_expiry, $mobile);
        $stmt_update->execute();
    } else {
        // Insert a new user with the OTP into otp_system
        $query_insert = "INSERT INTO users (mobileNo, otp, otp_expiry) VALUES (?, ?, ?)";
        $stmt_insert = $conn_otp->prepare($query_insert);
        $stmt_insert->bind_param("sss", $mobile, $otp, $otp_expiry);
        $stmt_insert->execute();
    }

    // Fast2SMS API integration to send physical SMS
    $apiKey = "ZceFlVQY1kRp503ygC2KN4aMuWwPAbHSU9jI87inTXrDELzm6qBKrh0uOt2dRgbsUAainEl9JIFoyDPN"; // Replace with your Fast2SMS API Key
    $message = "Your OTP is $otp. It is valid for 10 minutes. Please do not share it with anyone.";
    $route = "q"; // Use 'q' for transactional messages
    $sender_id = "FSTSMS";

    // Send OTP via Fast2SMS
    $url = "https://www.fast2sms.com/dev/bulkV2?authorization=$apiKey&route=$route&sender_id=$sender_id&message=" . urlencode($message) . "&language=english&numbers=$mobile";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo "Error: $err";
    } else {
        echo "OTP sent successfully to $mobile";
    }
} else {
    echo "Mobile number does not exist in the auth_system database. Please register first.";
}

// Close connections
$conn_auth->close();
$conn_otp->close();
?>
