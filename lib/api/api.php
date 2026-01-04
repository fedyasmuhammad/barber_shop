<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database configuration
$host = "localhost";
$db_name = "barber_shop";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Fetch all appointments
    $sql = "SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time DESC";
    $result = $conn->query($sql);
    
    $appointments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Convert id to integer
            $row['id'] = (int)$row['id'];
            $appointments[] = $row;
        }
    }
    
    echo json_encode([
        "success" => true,
        "data" => $appointments
    ]);
    
} elseif ($method == 'POST') {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['action'])) {
        $action = $data['action'];
        
        if ($action == 'create') {
            // Create new appointment
            $client_name = $conn->real_escape_string($data['client_name']);
            $phone_number = $conn->real_escape_string($data['phone_number']);
            $service = $conn->real_escape_string($data['service']);
            $appointment_date = $conn->real_escape_string($data['appointment_date']);
            $appointment_time = $conn->real_escape_string($data['appointment_time']);
            
            $sql = "INSERT INTO appointments (client_name, phone_number, service, appointment_date, appointment_time) 
                    VALUES ('$client_name', '$phone_number', '$service', '$appointment_date', '$appointment_time')";
            
            if ($conn->query($sql) === TRUE) {
                echo json_encode([
                    "success" => true,
                    "message" => "Appointment created successfully",
                    "id" => $conn->insert_id
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error: " . $conn->error
                ]);
            }
            
        } elseif ($action == 'delete') {
            // Delete appointment
            $id = intval($data['id']);
            
            // Verify ID exists
            if ($id <= 0) {
                echo json_encode([
                    "success" => false,
                    "message" => "Invalid appointment ID"
                ]);
                $conn->close();
                exit;
            }
            
            $sql = "DELETE FROM appointments WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                echo json_encode([
                    "success" => true,
                    "message" => "Appointment deleted successfully"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error: " . $conn->error
                ]);
            }
            
        } elseif ($action == 'update') {
            // Update appointment
            $id = intval($data['id']);
            $client_name = $conn->real_escape_string($data['client_name']);
            $phone_number = $conn->real_escape_string($data['phone_number']);
            $service = $conn->real_escape_string($data['service']);
            $appointment_date = $conn->real_escape_string($data['appointment_date']);
            $appointment_time = $conn->real_escape_string($data['appointment_time']);
            
            $sql = "UPDATE appointments 
                    SET client_name = '$client_name',
                        phone_number = '$phone_number',
                        service = '$service',
                        appointment_date = '$appointment_date',
                        appointment_time = '$appointment_time'
                    WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                echo json_encode([
                    "success" => true,
                    "message" => "Appointment updated successfully"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error: " . $conn->error
                ]);
            }
        }
    }
}

$conn->close();
?>