<?php
session_start();

// Check if admin is not logged in, redirect to admin login
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Database connection and query to retrieve orders
$user = 'root';
$pass = ''; // Change this to the actual password if it's not empty
$db = 'testdb';
$port = 3307;

// Create a new mysqli object and establish the connection
$db_connection = new mysqli('localhost', $user, $pass, $db, $port);

// Check if there are any connection errors
if ($db_connection->connect_error) {
    die("Connection failed: " . $db_connection->connect_error);
}

// Function to delete order list for a specific order ID from database
function cancelOrderList($db_connection, $order_id) {
    $sql = "DELETE FROM orderdetails WHERE order_id = ?";
    $stmt = $db_connection->prepare($sql);
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Function to update order status to Paid and insert into paid orders table
function markOrderAsPaid($db_connection, $order_id) {
    $sql_update = "UPDATE orderdetails SET status='Paid' WHERE order_id=?";
    $stmt_update = $db_connection->prepare($sql_update);
    $stmt_update->bind_param("i", $order_id);
    
    // Retrieve order details to calculate total amount
    $sql_select = "SELECT SUM(quantity * price) AS total_amount FROM orderdetails WHERE order_id=?";
    $stmt_select = $db_connection->prepare($sql_select);
    $stmt_select->bind_param("i", $order_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $row = $result->fetch_assoc();
    $total_amount = $row['total_amount'];
    
    // Insert order ID and total amount into paid_orders table
    $sql_insert = "INSERT INTO paid_orders (order_id, total_amount) VALUES (?, ?)";
    $stmt_insert = $db_connection->prepare($sql_insert);
    $stmt_insert->bind_param("id", $order_id, $total_amount);
    
    $db_connection->begin_transaction();
    if ($stmt_update->execute() && $stmt_insert->execute()) {
        $db_connection->commit();
        return true;
    } else {
        $db_connection->rollback();
        return false;
    }
}

// Check if cancel button is clicked for a specific order
if(isset($_POST['cancel'])) {
    $order_id = $_POST['order_id'];
    if(cancelOrderList($db_connection, $order_id)) {
        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit;
    }
}

// Check if paid button is clicked
if(isset($_POST['paid'])) {
    $order_id = $_POST['order_id'];
    if(markOrderAsPaid($db_connection, $order_id)) {
        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit;
    }
}

// Retrieve orders from the database
$sql = "SELECT * FROM orderdetails";
$result = $db_connection->query($sql);

// Process the retrieved orders as needed
$orders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[$row['order_id']][] = $row; // Group orders by order_id
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard
    </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            display: flex;
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #333;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-x: hidden;
            transition: width 0.5s;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .container {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .sidebar-toggle {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding-top: 60px;
            }
            .container {
                margin-left: 0;
                width: 100%;
            }
            .sidebar-toggle {
                display: block;
                font-size: 30px;
                position: absolute;
                top: 0;
                left: 0;
                cursor: pointer;
                color: #333;
                padding: 15px;
            }
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .button-container button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }

        .button-container button.cancel {
            background-color: #dc3545;
            color: #fff;
        }

        .button-container button.paid {
            background-color: #28a745;
            color: #fff;
        }

        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }

            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .button-container button {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-toggle" onclick="toggleSidebar()">&#9776;</div>
    <div class="sidebar" id="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_stocks.php">Manage Stocks</a>
        <a href="admin_reports.php">Revenue</a>
        <a href="admin_logout.php">Logout</a>
    </div>
    <div class="container">
        <h2>Welcome to Admin Dashboard</h2>
        
        <?php foreach ($orders as $order_id => $order_items): ?>
            <h3>Order ID: <?php echo $order_id; ?></h3>
            <p>Order Date & Time: <?php echo $order_items[0]['order_datetime']; ?></p> <!-- Display order date and time -->
            <table>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
                <?php 
                    $total_price = 0; // Initialize total price for this order
                    foreach ($order_items as $item): 
                        $total_price += $item['quantity'] * $item['price']; // Calculate total price for each item
                ?>
                    <tr>
                        <td><?php echo $item['item_name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['price']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <!-- Display Total Amount for this order -->
            <table>
                <tr>
                    <th>Total Amount</th>
                    <td><?php echo number_format($total_price, 2); ?></td>
                </tr>
            </table>
            <div class="button-container">
                <form method="post">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <button type="submit" class="paid" name="paid">Paid</button>
                </form>
                <form method="post">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <button type="submit" class="cancel" name="cancel">Cancel</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            if (sidebar.style.width === "250px") {
                sidebar.style.width = "0";
            } else {
                sidebar.style.width = "250px";
            }
        }
    </script>
</body>
</html>
