<?php
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

$checkout_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    if (isset($_POST['quantity']) && isset($_POST['customer_name'])) {
        $quantities = $_POST['quantity'];
        $customer_name = $_POST['customer_name']; // Retrieve customer name

        // Prepare and bind the SQL statement
        $stmt = $db_connection->prepare("INSERT INTO orderdetails (customer_name, order_id, item_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");

        // Check if the statement was prepared successfully
        if ($stmt === false) {
            die("Error preparing statement: " . $db_connection->error);
        }

        // Bind parameters
        $stmt->bind_param("sisidd", $customer_name, $order_id, $item_name, $quantity, $price, $total);

        // Get the next order ID
        $order_id_query = "SELECT MAX(order_id) AS max_order_id FROM orderdetails";
        $result = $db_connection->query($order_id_query);
        if ($result) {
            $row = $result->fetch_assoc();
            $order_id = $row['max_order_id'] + 1;
        } else {
            $order_id = 1; // Default to 1 if no orders exist
        }

        $items = [
            "Monggo with Rice" => 40,
            "Afritada with Rice" => 55,
            "Menudo with Rice" => 55,
            "Ginataan with Rice" => 50,
            "Dinuguan with Rice" => 55,
            "Bicol Express with Rice" => 65,
            "Adobo with Rice" => 55,
            "Water" => 10,
            "Softdrinks" => 20,
            "Extra Rice" => 15,
            "Soup" => 10,
            
            "Monggo" => 50,
            "Afritada" => 70,
            "Menudo" => 70,
            "Ginataan" => 60,
            "Dinuguan" => 65,
            "Bicol Express" => 75,
            "Adobo" => 70,
            "Water" => 10,
            "Softdrinks" => 20,
            "Rice" => 15,
            "Soup" => 10,
        ];

        // Loop through each item in the order
        foreach ($quantities as $item => $quantity) {
            if ($quantity > 0) {
                // Set the values for the SQL statement
                $item_name = $item;
                $price = isset($items[$item_name]) ? $items[$item_name] : 0;
                $total = $price * $quantity; // Calculate total price

                // Execute the statement
                $stmt->execute();
            }
        }

        // Close the statement
        $stmt->close();

        // Set checkout success flag
        $checkout_success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }

    .checkout-message {
        text-align: center;
        margin-bottom: 20px;
    }

    .checkout-button {
        padding: 10px 20px;
        font-size: 16px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        text-decoration: none;
    }

    .checkout-button:hover {
        background-color: #0056b3;
    }
</style>
</head>
<body>

<div class="container">
    <?php if ($checkout_success): ?>
        <div class="checkout-message">
            <h1>Checkout Successful!</h1>
            <p>Your order has been successfully processed.</p>
        </div>
        <a href="Test.php" class="checkout-button">Back to Test</a>
    <?php else: ?>
        <h1>Checkout</h1>
        <!-- Display ordered items -->
        <?php if (isset($_POST['quantity']) && is_array($_POST['quantity'])): ?>
            <h2>Ordered Items:</h2>
            <ul>
            <?php foreach ($_POST['quantity'] as $item => $quantity): ?>
                <?php if ($quantity > 0): ?>
                    <li><?php echo htmlspecialchars($item); ?>: <?php echo htmlspecialchars($quantity); ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
