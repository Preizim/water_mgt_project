<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "water_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Function to fetch approved amount for a user
function getApprovedAmount($conn, $user_id) {
  
  return 0;
}





if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $approved_amount = htmlspecialchars($_GET['approved_amount']);
    $transaction_id = htmlspecialchars($_GET['transaction_id']);
    $channel = htmlspecialchars($_GET['processTransactions']);
    $user_id = htmlspecialchars($_GET['user_id']);

    // Create response array
    $response = array(
        "channel" => $channel,
        "approved_amount" => $approved_amount
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options esp ip address    
    curl_setopt($ch, CURLOPT_URL, "http://192.168.175.27/approve");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($response));

    // Execute cURL request and get the response
    $result = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
      echo $result;
     ?>
  <div class="redirect">
      You will be redirected shortly...
      <script>
          setTimeout(function() {
              window.location.href = 'admin_panel.php'; // The page you want to redirect to
          }, 3000); // 2 seconds delay
      </script>
  </div>
        <?php
       

        $sql = "SELECT * FROM transactions WHERE user_id = $user_id AND status = 'pending' LIMIT 1";
$result = $conn->query($sql);

if($result== true){
if ($row = $result->fetch_assoc()) {
  $transaction_id = $row['id'];
  $requested_amount = $row['requested_amount'];
  $user_id = $row['user_id'];
  $approved_amount = $row['approved_amount'] ?: $requested_amount;

  $sql = "SELECT balance FROM users WHERE id = $user_id";
  $user_result = $conn->query($sql);
  $user_row = $user_result->fetch_assoc();

  if ($user_row['balance'] >= $approved_amount) {
    $liters_to_add = $approved_amount * 10;
    $new_balance = $user_row['balance'] - $liters_to_add;
    $sql = "UPDATE users SET balance = $new_balance WHERE id = $user_id";
    $conn->query($sql);
    $sql = "UPDATE transactions SET status = 'approved' WHERE id = $transaction_id";
    $conn->query($sql);

    return $approved_amount;
    header("Location: user_interface.php");
  } else {
    $sql = "UPDATE transactions SET status = 'rejected' WHERE id = $transaction_id";
    $conn->query($sql);
    return 0;
  }
  
  }

  
    }



    }

    // Close cURL session
    curl_close($ch);

    
  
  // Call the function to get the status
  //getEsp32Status(); 

} else {
    echo "<p>No data submitted.</p>";
}


?>


