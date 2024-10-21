<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
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

$admin_id = $_SESSION['admin_id'];

// Handle transaction updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['transaction_id'])) {
        $transaction_id = $_POST['transaction_id'];
        $action = $_POST['action'];
        $approved_amount = $_POST['approved_amount'];
        $channel = $_POST['channel'];
        
        // Update transaction status and approved amount
        $sql = "UPDATE transactions SET status='$action', approved_amount='$approved_amount', channel='$channel' WHERE id=$transaction_id";
        $conn->query($sql);

        if ($action == 'approved') {
            // Update user balance
            $sql = "SELECT user_id, requested_amount FROM transactions WHERE id=$transaction_id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];
            $requested_amount = $row['requested_amount'];

            $sql = "UPDATE users SET balance = balance - $requested_amount WHERE id=$user_id";
            $conn->query($sql);
        }
    } elseif (isset($_POST['control_pump'])) {
        // Handle pump control if needed
    }
}

// Fetch admin details
$sql = "SELECT username FROM admins WHERE id=$admin_id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$admin_name = $row['username'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            background-color: #007BFF;
            color: white;
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
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
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
        .container {
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
        }
        .info-box {
            background-color: #007BFF;
            color: white;
            padding: 40px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .info-box h2 {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box p {
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
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
        ">Water Management System Admin Panel</h1>
    </div>
    <div class="container">
        <h2>Welcome, <?php echo strtoupper(htmlspecialchars($admin_name)); ?><span style="
            font-size: 14px; 
            margin-left: 600px;
            color: black;
        " id="date">Date :  
            <!-- JavaScript will insert the date here -->
    </span></h2>
        
        

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
        My Admin Panel
    </div>
    <div>
    <a href="admin_panel.php" style="
            color: white; 
            text-decoration: none; 
            font-size: 12px; 
            padding: 10px 20px; 
            border-radius: 5px; 
            margin: 0 10px;
            background-color: #0056b3; 
            transition: background-color 0.3s;
        " onmouseover="this.style.backgroundColor='#003d7a'" onmouseout="this.style.backgroundColor='#0056b3'">
            Home
        </a>
        <a href="history.php" style="
            color: white; 
            text-decoration: none; 
            padding: 10px 20px; 
            margin: 0 10px; 
            font-size: 12px;
            border-radius: 5px; 
            background-color: #0056b3;
            transition: background-color; 
            display: inline-block;
        " onmouseover="this.style.backgroundColor='#003d7a'" onmouseout="this.style.backgroundColor='#007BFF'"
        
        >
            History
        </a>
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
        " onclick="window.location.href='admin_logout.php';">
            Logout
        </button>
    </div>
</div>

        <!-- Pump Control Section -->
        <h2 style="text-align: center; margin-top: 20px">Pump Control</h2>
        <div style="text-align: center; ">
            <button id="pumpOn" onclick="controlPump('on')">Turn Pump ON</button>
            <button id="pumpOff" onclick="controlPump('off')">Turn Pump OFF</button>
        </div>
        <p id="status"></p>
    </div>
    
        <!-- Information Boxes -->
         
    <div class="container" style="margin- left:200px;">
    <center><h2>Water Level Monitoring</h2></center>
        <div class="info-box">
        <center> <h3>Current Water Level</h3>
            <p id="waterLevel"></p></center>
        </div>
        <div style="margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; max-width: 1000px;">
        <center><h2>Flow rate</h2></center>
        <div style="display: flex; justify-content: space-between; gap: 20px;">
        

    
            <div style="flex: 1; background-color: #007BFF; color: white; padding: 20px; border-radius: 8px; text-align: left; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
          
                <h4>User Name: Tolani</h4>
                
                <h4 id="totalLitres1">Litres processing</h4>
                
               
            </div>
              
            <div style="flex: 1; background-color: #007BFF; color: white; padding: 20px; border-radius: 8px; text-align: left; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
          
                <h4 >User Name: Book </h4>
                
                <h4 id="">Litres processing</h4>
                
                
            </div>
        </div>
    </div>
        
    
        

    <div style="margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; max-width: 1000px;">
        <center><h2>User Information</h2></center>
        <div style="display: flex; justify-content: space-between; gap: 20px;">
        <?php
                $sql = "SELECT 
                users.username,
                users.balance,
                users.email,
                COALESCE(SUM(transactions.requested_amount), 0) AS cumulative_approved_water 
            FROM 
                users
            LEFT JOIN 
                transactions ON users.id = transactions.user_id WHERE status ='approved' 
            GROUP BY 
                users.id";
    
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {?>


    
            <div style="flex: 1; background-color: #007BFF; color: white; padding: 20px; border-radius: 8px; text-align: left; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
          
                <h4>User Name: <?php echo $row['username']; ?></h4>
                
                <h4>User Balance: #<?php echo $row['balance'];?></h4>
                
                <h4>User Email: <?php echo $row['email'];?></h4>
               
               <h4>Cumulative Approved Water: <?php echo $row['cumulative_approved_water'] ?> Litres</h4>
                
               <?php ?>
            </div>
              <?php }} ?>
            
        </div>
    </div>




    <div class="container">
       <center><h2>Transaction Awaiting Approval</h2></center> 
        <table>
            <tr>
                <th>Transaction ID</th>
                <th>Username</th>
                <th>Requested Amount</th>
                <th>Approved Amount</th>
                <th>Channel</th>
                <th>Status</th>
              
            </tr>
            <?php 
            $sql = "SELECT *
            FROM transactions 
            JOIN users ON transactions.user_id = users.id WHERE status = 'pending'";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                
                $user_id= $row['user_id'];
                ?>
            
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['requested_amount']; ?></td>
                <td>
                    <form action="water_management.php" method="GET" style="color: green">
                        <input type="number" name="approved_amount" min="0" max="<?php echo $row['requested_amount']; ?>" step="0.01" required>
                        <input type="hidden" name="transaction_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="action" value="approved">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <input type="hidden" name="processTransactions" value="<?php echo $row['channel']; ?>">
                        <button type="submit" style="background-color: green">Approve</button>
                    </form>
                </td>
                <td><?php echo $row['channel']; ?></td>
                <td><?php echo $row['status']; ?></td>
                
            </tr>
            <?php } ?>
        </table>
    </div>

    

    <script>
        function controlPump(action) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "/PRAISE/control_pump.php?action=" + action, true);
            xhr.onload = function () {
                document.getElementById('status').innerText = xhr.responseText;

                // Update button states based on response
                if (xhr.responseText.includes("Pump turned on")) {
                    document.getElementById('pumpOn').classList.add('disabled');
                    document.getElementById('pumpOff').classList.remove('disabled');
                } else if (xhr.responseText.includes("Pump turned off")) {
                    document.getElementById('pumpOn').classList.remove('disabled');
                    document.getElementById('pumpOff').classList.add('disabled');
                }
            };
            xhr.send();
        }

        setInterval(() => {
            fetch('http://192.168.175.27/data').then(response => response.json()).then(data => {
                document.getElementById("totalLitres1").innerHTML = data.totalLitres1
                document.getElementById("totalLitres2").innerHTML = data.totalLitres2
                document.getElementById("waterLevel") = data.water_level
            }) 
        }, 2000);

        //fetchwater level
        function fetchWaterLevel() {
      fetch('http://192.168.175.27/water_level') // Replace with your ESP32 IP address and route
        .then(response => response.json())
        .then(data => {
          document.getElementById('waterLevel').textContent = data.waterLevel.toFixed(2) + '%';
        })
        .catch(error => {
          console.error('Error fetching water level:', error);
        });
    }

    // Fetch water level every 3 seconds
    setInterval(fetchWaterLevel, 3000);

    // Initial fetch
    fetchWaterLevel();
        
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Set current date in the date element
        document.getElementById('date').innerText = formatDate(new Date());
    </script>
</body>

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
    text-align: center;
    text-decoration: none;
    margin-top: 20px;
    margin-left: 800px;
    margin-bottom: 100px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
" onclick="window.location.href='admin_logout.php';">
    Logout
</button>
<center>
<footer style="
        background-color: #007BFF; 
        color: white; 
        margin-top: 20px;
        margin-left: 150px;
        padding: 20px; 
        text-align: center; 
        font-family: Arial, sans-serif; 
        position: fixed; 
        bottom: 0; 
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



</center>
</html>
