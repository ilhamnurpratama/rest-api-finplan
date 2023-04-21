<?php
// Retrieve the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Replace these placeholders with your actual database connection details
$servername = "localhost"; // Server name
$username = "fin_plan_admin"; // Username
$password = "Testpassword1!"; // Password
$dbname = "fin_plan"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check data in data base
function dataExistsInDatabase($uid) {
    // Connect to your database (replace with your own database connection code)
    $conn = mysqli_connect("localhost", "username", "password", "database_name");

    // Check if connection was successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare and execute a database query to check if $uid already exists
    $query = "SELECT COUNT(*) FROM fin_plan.user WHERE `uid` = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $uid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Close the database connection
    mysqli_close($conn);

    // If count is greater than 0, $uid already exists, return true; otherwise, return false
    return $count > 0;
}


// Extract data from the POST request
$uid = $data['uid'];
$firstName = $data['firstName'];
$lastName = $data['lastName'];
$email = $data['email'];
$password = $data['password'];

// Prepare and bind the SQL statement
$stmt = $conn->prepare("INSERT INTO fin_plan.user (uid, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $uid, $firstName, $lastName, $email, $password);

$response = array(); // Create an array to store the response data

try {
    // Execute the statement
    if ($stmt->execute()) {
        $response['statusCode'] = '00';
        $response['statusDesc'] = "Data inserted successfully";
    } else {
        $response['statusCode'] = '500';
        $response['statusDesc'] = "Error inserting data: " . $stmt->error;
    }
} catch (Exception $e) {
    if (!isset($data['uid']) || empty($data['uid']) || !isset($data['firstName']) || empty($data['firstName']) || !isset($data['email']) || empty($data['email']) || !isset($data['password']) || empty($data['password']) ) {
        $response['statusCode'] = '01';
        $response['statusDesc'] = "Missing mandatory parameter";
    } elseif (mysqli_errno($conn) == 1062) {
        $response['statusCode'] = '02';
        $response['statusDesc'] = "Duplicate entry";
    } else {
        $response['statusCode'] = '500';
        $response['statusDesc'] = "Error inserting data: " . $e->getMessage();
    }
}

// Convert the response array to JSON
$jsonResponse = json_encode($response);

// Set the appropriate response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Output the JSON response
echo $jsonResponse;

?>