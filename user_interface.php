<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "water_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id ORDER BY id ASC" ;
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$username = $row['username'];
$balance = $row['balance'];
$user_id = $row['id'];

$id_value = ($user_id == 1) ? 'flowRate1' : 'flowRate2'; 

// Number of transactions per page
$transactions_per_page = 4;

// Get the current page number from the query string, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);

// Calculate the starting point for the SQL query
$start = ($current_page - 1) * $transactions_per_page;

// Query to get the total number of transactions
$sql_total = "SELECT COUNT(*) AS total_transactions FROM transactions WHERE status = 'approved'";
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_transactions = $row_total['total_transactions'];

// Query to get the transactions for the current page
$sql = "SELECT transactions.id, users.username, transactions.requested_amount, transactions.approved_amount, transactions.status, transactions.channel 
        FROM transactions 
        JOIN users ON transactions.user_id = users.id 
        WHERE transactions.status = 'approved'
        ORDER BY transactions.timestamp DESC
        LIMIT $start, $transactions_per_page";

$transactions = $conn->query($sql);

// Calculate the total number of pages
$total_pages = ceil($total_transactions / $transactions_per_page);



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['request_amount'])) {
        // Water request handling
        $request_amount = $_POST['request_amount'];
        // Convert liters to Naira
        $required_naira = $request_amount * 10; 
        if ($required_naira <= $balance) {
            // Insert transaction with channel set to user_id
            $sql = "INSERT INTO transactions (user_id, requested_amount, status, channel) 
                    VALUES ($user_id, $request_amount, 'pending', $user_id)";
            if ($conn->query($sql) === TRUE) {
                echo "Request submitted successfully.";

                

            } else {
                echo "Error submitting request: " . $conn->error;
            }
        } else {
            echo "Insufficient balance.";
        }
    } elseif (isset($_POST['add_amount'])) {
        // Balance update handling
        $add_amount = $_POST['add_amount'];
        // Convert Naira to liters
        $liters_to_add = $add_amount / 10;
        $sql = "UPDATE users SET balance = balance + $add_amount WHERE id = $user_id";
        if ($conn->query($sql) === TRUE) {
            echo "Balance updated successfully.";
            // Refresh balance after update
            $sql = "SELECT balance FROM users WHERE id = $user_id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $balance = $row['balance'];
        } else {
            echo "Error updating balance: " . $conn->error;
        }
    }
}

// Fetch transaction history
$sql = "SELECT * FROM transactions WHERE user_id = $user_id ORDER BY id DESC limit 4" ;
$transactions = $conn->query($sql);


?>

<!DOCTYPE html>
<html>
<head>
    <title>User Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 500px;
            margin-top: 50px;
            
        }
        h1 {
            background-color: #007BFF;
            color: white;
            padding: 15px;
            margin: 0;
            text-align: center;
        }
        h2 {
            color: black;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            color: #555;
        }
        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        .logout {
            margin-top: 20px;
            text-align: center;
        }
        .logout a {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        h1 {
            background-color: #007BFF;
            color: black;
            padding: 15px;
            margin: 0;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        button {
            background-color: blue; 
    color: white; 
    border: none; 
    border-radius: 5px; 
    padding: 10px 20px; 
    font-size: 16px; 
    font-weight: bold; 
    cursor: pointer; 
    transition: background-color 0.3s ease, transform 0.3s ease; 
    display: inline-block; 
    text-align: center;
    text-decoration: none;
    margin-top: 20px;
    
    margin-bottom: 50px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
" onclick="window.location.href='logout.php';"
        }

        #pumpOn {
            background-color: green;
        }

        #pumpOff {
            background-color: red;
        }

        #status {
            font-size: 18px;
            margin: 20px;
            text-align: center;
        }

        .disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .info-box {
            background-color: #007BFF;
            color: white;
            padding: 40px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }

        .info-box p {
            font-size: 18px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<title>User Interface</title>

</head>
<body>
<div style="
        display: flex;
        align-items: center;
        gap: 15px;
        justify-content: center;
        margin: 40px auto;
        padding: 20px;
        background-color: #007BFF;
        color: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        max-width: 800px;
    ">
        <img src="water.jpeg" alt="Logo" style="
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
        " />
        <h1 style="
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
        ">Water Management System User Interface</h1>
    </div>
    
   

       

    <div class="container">
    <div style="
    background-color: #007BFF; 
    color: white; 
    padding: 10px 20px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    box-shadow: 0 4px 8px rgba(0,0,0,0.2); 
    font-family: Arial, sans-serif;
">
    <div style="
        font-size: 18px; 
        font-weight: bold;
    ">
        User Interface
    </div>
    <div>
        
        <button style="
            background-color: #FF4C4C; 
            color: white; 
            border: none; 
            border-radius: 10px; 
            padding: 10px 20px; 
            font-size: 12px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background-color 0.3s ease, transform 0.3s ease; 
            display: inline-block;
        " onclick="window.location.href='logout.php';">
            Logout
        </button>
    </div>
</div>
        <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
        <p id= "balance" >Account Balance: <?php echo number_format($balance, 2); ?> Naira (<?php echo number_format($balance / 10, 2); ?> Liters) 
        <span style="background-color:none ;text-align: right;">

        <div style="width: 100%; border: 0px solid #ccc; padding: 10px; box-sizing: border-box;">
        <p style="text-align: right;">Price: #10 per Litre</p>
        </div>
        </p></span>
        
        

        <div class="container">
      
        <div class="info-box">
            <h2>Approved Water-flow Rate </h2>
            <p id="flowRate">Fetching water level...</p>
            <p id="<?php if ($user_id==1) {echo 'flowRate1';}elseif($user_id==2){
                echo 'flowRate2';} ?> ">Fetching water level...</p>
        <p id="<?php echo htmlspecialchars($id_value); ?>">Fetching water flow rate...</p>
        </div>
        
    </div>
        <center><h2>Request Water</h2></center>
        <form method="POST">
            <label for="request_amount" style="color:black">Enter amount of water to request (in liters):</label>
            <input type="number" name="request_amount" required>
            <button type="submit">Request Water</button>
        </form>
        
        <center><h2 style="margin-top:50px;">Add Funds to Balance</h2></center>
        <form method="POST">
            <label for="add_amount">Enter amount to add (in Naira):</label>
            <input type="number" name="add_amount" step="0.01" required>
            <button type="submit">Add Funds</button>
        </form>
        
        <?php
// Pagination setup
$recordsPerPage = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startFrom = ($page - 1) * $recordsPerPage;

// Query to fetch paginated records
$sql = "SELECT transactions.id, users.username, transactions.requested_amount, transactions.approved_amount, transactions.status, transactions.channel,transactions.timestamp 
        FROM transactions 
        JOIN users ON transactions.user_id = users.id 
         
         ORDER BY transactions.id DESC 
        LIMIT $startFrom, $recordsPerPage ";
$result = $conn->query($sql);

// Query to get the total number of records for pagination controls
$totalSql = "SELECT COUNT(*) AS total FROM transactions WHERE user_id=$user_id  ";
$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
?>



       <center><h2 style="margin-top:50px;">Transaction History</h2></center> 
        <table>
            <tr>
                <th>Requested Amount (Liters)</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php if ($transactions->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['requested_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No transactions found.</td>
                </tr>
            <?php endif; ?>
        </table>
        <div style="text-align: center;">
        <?php if ($page > 1) { ?>
            <a href="?page=<?php echo $page - 1; ?>" style="padding: 10px; border: 1px solid #ddd; background-color: #007BFF; color: white; text-decoration: none; border-radius: 4px;">Previous</a>
        <?php } ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <a href="?page=<?php echo $i; ?>" style="padding: 10px; border: 1px solid #ddd; background-color: #007BFF; color: white; text-decoration: none; border-radius: 4px; margin: 0 5px;"><?php echo $i; ?></a>
        <?php } ?>
        
        <?php if ($page < $totalPages) { ?>
            <a href="?page=<?php echo $page + 1; ?>" style="padding: 10px; border: 1px solid #ddd; background-color: #007BFF; color: white; text-decoration: none; border-radius: 4px;">Next</a>
        <?php } ?>
    </div>  

        <button style="
    background-color: #FF4C4C; 
    color: white; 
    border: none; 
    border-radius: 5px; 
    padding: 10px 20px; 
    font-size: 16px; 
    font-weight: bold; 
    cursor: pointer; 
    transition: background-color 0.3s ease, transform 0.3s ease; 
    display: inline-block; 
    
    text-decoration: none;
    margin-top: 20px;
    text-align: right;
    margin-bottom: 50px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
" onclick="window.location.href='logout.php';">
    Logout
</button>
    </div>
</body>

<script>
function fetchData() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "user_interface.php", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    document.getElementById('balance').innerText = 'Current Water Level: ' + response.$balance;
                }
            };
            xhr.send();
        }

        // Fetch data every 3 seconds
        setInterval(fetchData, 3000);

        // Initial data fetch
        fetchData();

        
        setInterval(() => {
            fetch('http://192.168.142.27/data').then(response => response.json()).then(data => {
                document.getElementById("flowRate1").innerHTML = data.flowRate1
                document.getElementById("flowRate2").innerHTML = data.flowRate2
                document.getElementById("flowRate1").innerHTML = data.flowRate
                document.getElementById("waterLevel") = data.water_level
            }) 
        }, 2000);
       


        document.addEventListener('DOMContentLoaded', function() {
        // Get HTML elements
        const flowRateElement = document.getElementById('flowRate');
        const waterLevelElement = document.getElementById('waterLevel');
        
        // Fetch user_id from PHP or session
        const user_id = <?php echo json_encode($user_id); ?>; // Replace with your actual method to get user_id

        // URL for fetching data
        const dataUrl = 'http://192.168.142.27/data'; // Replace with your ESP32 URL

        fetch(dataUrl)
            .then(response => response.json())
            .then(data => {
                // Default message if data is not available
                let flowRateText = 'No data available';
                
                // Check user_id and set flowRateText accordingly
                if (user_id === 1 && data.flowRate1) {
                    flowRateText = `${data.flowRate1} Liters/min`;
                } else if (user_id === 2 && data.flowRate2) {
                    flowRateText = `${data.flowRate2} Liters/min`;
                }

                // Update HTML elements with fetched data
                flowRateElement.textContent = flowRateText;
                waterLevelElement.textContent = `${data.water_level} Liters`;
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                flowRateElement.textContent = 'Error fetching data';
                waterLevelElement.textContent = 'Error fetching data';
            });
    });
        </script>

<footer style="
        background-color: #007BFF; 
        color: white; 
        margin-top: 20px;
        padding: 20px; 
        text-align: center; 
        font-family: Arial, sans-serif; 
        position: fixed; 
        bottom: 0; 
         margin-left: 250px;
        width: 70%; 
        box-shadow: 0 -4px 8px rgba(0,0,0,0.2);
    ">
        <p style="
            margin: 0; 
            font-size: 14px;
        ">
            &copy; 2024 Finial Year Project. All Rights Reserved.
        </p>
        <p style="
            margin: 5px 0 0; 
            font-size: 12px;
        ">
            User Interface | Version 1.0
        </p>
    </footer>



</html>
