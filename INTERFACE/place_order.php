<?php
// place_order.php

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Your existing validation and form data retrieval code here

    // Database connection parameters
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

    // Start a transaction to ensure data consistency
    $db_connection->begin_transaction();

    // Prepare and execute SQL statement to update stock in the menu table and insert order details into the orderdetails table
    // Note: You need to adjust this part based on your database structure and how you retrieve the item price
    foreach ($_POST["quantity"] as $item_name => $quantity) {
        // Check if quantity is greater than 0
        if ($quantity > 0) {
            // Retrieve the price and current stock for the item from the menu table
            $sql_select = "SELECT price, stock FROM menu WHERE item_name = ?";
            $stmt_select = $db_connection->prepare($sql_select);
            $stmt_select->bind_param("s", $item_name);
            $stmt_select->execute();
            $stmt_select->bind_result($price, $stock);
            $stmt_select->fetch();
            $stmt_select->close();

            // Calculate total price for the item
            $total = $price * $quantity;

            // Update stock in the menu table
            $new_stock = $stock - $quantity;
            $sql_update_stock = "UPDATE menu SET stock = ? WHERE item_name = ?";
            $stmt_update_stock = $db_connection->prepare($sql_update_stock);
            $stmt_update_stock->bind_param("is", $new_stock, $item_name);
            $stmt_update_stock->execute();
            $stmt_update_stock->close();

            // Insert order details into the orderdetails table
            $sql_insert_order = "INSERT INTO orderdetails (customer_name, order_id, item_name, quantity, price, total, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert_order = $db_connection->prepare($sql_insert_order);
            $stmt_insert_order->bind_param("sisiiis", $customer_name, $order_id, $item_name, $quantity, $price, $total, $status);
            $stmt_insert_order->execute();
            $stmt_insert_order->close();
        }
    }

    // Commit the transaction
    $db_connection->commit();

    // Close the database connection
    $db_connection->close();

    // Redirect back to the student meal page after successful submission
    header("Location: student_meal.php");
    exit();
} else {
    // If the form is not submitted, redirect back to the student meal page
    header("Location: student_meal.php");
    exit();
}
?>