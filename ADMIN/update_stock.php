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

// Get menu item ID and stock quantity from the form
$item_id = $_POST['item_id'];
$stock_quantity = $_POST['stock_quantity'];

// Prepare and execute SQL statement to update stock quantity
$sql = "UPDATE menu SET stock = stock + ? WHERE item_id = ?";
$stmt = $db_connection->prepare($sql);
$stmt->bind_param("ii", $stock_quantity, $item_id);

if ($stmt->execute()) {
    echo "Stock quantity updated successfully.";
} else {
    echo "Error updating stock quantity: " . $stmt->error;
}

// Close the database connection
$stmt->close();
$db_connection->close();
?>
