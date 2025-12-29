<?php
require 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow CORS for testing/frontend
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['username']) && isset($data['password'])) {
        $username = $conn->real_escape_string($data['username']);
        $password = $data['password'];

        $sql = "SELECT * FROM users WHERE (name = '$username' OR email = '$username') AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Don't send password back
            unset($user['password']);
            echo json_encode(['status' => 'success', 'message' => 'Login successful', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing username or password']);
    }
} else {
     echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
