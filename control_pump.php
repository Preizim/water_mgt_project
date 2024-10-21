<?php
// control.php

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $action = $_POST['control_pump'];
    
//     // Validate action
//     if ($action === 'ON' || $action === 'OFF') {
//         $esp32Ip = 'http://192.168.137.1/PRAISE/control_pump'; // Replace with your ESP32 endpoint
        
//         $data = array('action' => $action);
//         $options = array(
//             'http' => array(
//                 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//                 'method'  => 'POST',
//                 'content' => http_build_query($data),
//             ),
//         );
//         $context  = stream_context_create($options);
//         $result = file_get_contents($esp32Ip, false, $context);

//         if ($result === FALSE) {
//             echo "Failed to communicate with ESP32.";
//         } else {
//             echo "Command sent to ESP32: " . htmlspecialchars($result);
//         }
//     } else {
//         echo "Invalid action.";
//     }
// } else {
//     echo "Invalid request method.";
// }






$esp32_ip = 'http://192.168.175.27'; // Replace with your ESP32 IP address

// Function to send a command to the ESP32
function sendCommandToESP32($action) {  
    global $esp32_ip;

    $url = $esp32_ip . '/control?action=' . $action;

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute the cURL session
    $response = curl_exec($ch);
    
    if ($response === FALSE) {
        $error = curl_error($ch);
        curl_close($ch);
        return 'cURL Error: ' . $error;
    }

    curl_close($ch);
    return $response;
}

// Check if an action is set
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'on' || $action == 'off') {
        // Send the action to the ESP32
        $response = sendCommandToESP32($action);
        echo 'Pump turned ' . $action . '. Response from ESP32: ' . $response;
    } else {
        echo 'Invalid action.';
    }
} else {
    echo 'No action specified.';
}
?>






