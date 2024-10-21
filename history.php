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
            padding: 20px;
            border-radius: 8px;
            margin: 10px 0;
            text-align: center;
        }

        .info-box p {
            font-size: 18px;
            margin: 10px 0;
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
        ">Water Management System User Interface</h1>
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

        

<?php
// Pagination setup
$recordsPerPage = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startFrom = ($page - 1) * $recordsPerPage;

// Query to fetch paginated records
$sql = "SELECT transactions.id, users.username, transactions.requested_amount, transactions.approved_amount, transactions.status, transactions.channel 
        FROM transactions 
        JOIN users ON transactions.user_id = users.id 
        WHERE transactions.status = 'approved' 
         ORDER BY transactions.id DESC 
        LIMIT $startFrom, $recordsPerPage ";
$result = $conn->query($sql);

// Query to get the total number of records for pagination controls
$totalSql = "SELECT COUNT(*) AS total FROM transactions WHERE status = 'approved' ";
$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<div class="container" style="margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; max-width: 1000px;">
    <h2>Transaction History</h2>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr style="background-color: #007BFF; color: white;">
            <th style="padding: 10px; border: 1px solid #ddd;">Transaction ID</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Username</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Requested Amount</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Channel</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['id']; ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['username']; ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['requested_amount']; ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['channel']; ?></td>
            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['status']; ?></td>
        </tr>
        <?php } ?>
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
    margin-bottom: 50px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
" onclick="window.location.href='admin_logout.php';">
    Logout
</button>
<center>
<footer style="
        background-color: #007BFF; 
        color: white; 
        margin-top: 20px;
        padding: 20px; 
        text-align: center; 
        font-family: Arial, sans-serif; 
        position: fixed; 
        bottom: 0; 
        width: 75%; 
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
            Admin Panel | Version 1.0
        </p>
    </footer>


</center>
</html>
