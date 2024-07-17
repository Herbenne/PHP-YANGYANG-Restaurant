<?php
session_start();

// Check if admin is not logged in, redirect to admin login
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Database connection
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

// Function to get report data
function getReportData($db_connection, $interval) {
    $report_data = [
        'revenue' => 0,
        'sales' => 0,
        'best_sellers' => []
    ];

    // Define the interval based on the parameter
    switch ($interval) {
        case 'DAY':
            $interval_value = '1 DAY';
            break;
        case 'WEEK':
            $interval_value = '1 WEEK';
            break;
        case 'MONTH':
            $interval_value = '1 MONTH';
            break;
        default:
            return $report_data; // Return empty data if the interval is not valid
    }

    // Prepare and execute revenue query
    $sql_revenue = "SELECT SUM(quantity * price) AS total_revenue FROM orderdetails WHERE order_datetime >= DATE_SUB(NOW(), INTERVAL $interval_value)";
    if ($result_revenue = $db_connection->query($sql_revenue)) {
        $report_data['revenue'] = $result_revenue->fetch_assoc()['total_revenue'] ?? 0;
    } else {
        echo "Error in revenue query: " . $db_connection->error;
    }

    // Prepare and execute sales query
    $sql_sales = "SELECT COUNT(DISTINCT order_id) AS total_sales FROM orderdetails WHERE order_datetime >= DATE_SUB(NOW(), INTERVAL $interval_value)";
    if ($result_sales = $db_connection->query($sql_sales)) {
        $report_data['sales'] = $result_sales->fetch_assoc()['total_sales'] ?? 0;
    } else {
        echo "Error in sales query: " . $db_connection->error;
    }

    // Prepare and execute best sellers query
    $sql_best_sellers = "SELECT item_name, SUM(quantity) AS total_quantity FROM orderdetails WHERE order_datetime >= DATE_SUB(NOW(), INTERVAL $interval_value) GROUP BY item_name ORDER BY total_quantity DESC LIMIT 5";
    if ($result_best_sellers = $db_connection->query($sql_best_sellers)) {
        $report_data['best_sellers'] = $result_best_sellers->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error in best sellers query: " . $db_connection->error;
    }

    return $report_data;
}

$report_intervals = ['DAY' => 'daily', 'WEEK' => 'weekly', 'MONTH' => 'monthly'];
$reports = [];

foreach ($report_intervals as $interval => $label) {
    $reports[$label] = getReportData($db_connection, $interval);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            padding: 20px;
            width: 80%;
            max-width: 1200px;
        }

        h2 {
            text-align: center;
            margin-bottom: 40px;
        }

        .report-section {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 40px;
            width: 100%;
        }

        .report-section h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .report-table, .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .report-table th {
            background-color: #f2f2f2;
        }

        .report-table td {
            background-color: #fbfbfb;
        }

        .report-table th, .report-table td {
            text-align: center;
        }

        .best-sellers-title {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Reports</h2>
        <?php foreach ($reports as $label => $report): ?>
            <div class="report-section">
                <h3><?php echo ucfirst($label); ?> Report</h3>
                <table class="report-table">
                    <tr>
                        <th>Total Revenue</th>
                        <td><?php echo number_format($report['revenue'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Total Sales</th>
                        <td><?php echo $report['sales']; ?></td>
                    </tr>
                </table>
                <h4 class="best-sellers-title">Best Sellers</h4>
                <table class="report-table">
                    <tr>
                        <th>Item Name</th>
                        <th>Total Quantity Sold</th>
                    </tr>
                    <?php foreach ($report['best_sellers'] as $item): ?>
                        <tr>
                            <td><?php echo $item['item_name']; ?></td>
                            <td><?php echo $item['total_quantity']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
