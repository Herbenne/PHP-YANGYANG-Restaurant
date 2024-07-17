<?php
// student_meal.php

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

// Function to fetch menu items from the database
function fetchMenuItems($db_connection) {
    $sql = "SELECT item_id, item_name, price, image, stock FROM menu";
    $result = $db_connection->query($sql);
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

// Fetch menu items
$menuItems = fetchMenuItems($db_connection);

// Close the database connection
$db_connection->close();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Re-establish the database connection
    $db_connection = new mysqli('localhost', $user, $pass, $db, $port);

    // Start a transaction to ensure data consistency
    $db_connection->begin_transaction();

    // Get the highest order_id and increment it by 1 for the new order
    $sql_get_order_id = "SELECT IFNULL(MAX(order_id), 0) + 1 AS new_order_id FROM orderdetails";
    $result = $db_connection->query($sql_get_order_id);
    $row = $result->fetch_assoc();
    $new_order_id = $row['new_order_id'];

    // Prepare and execute SQL statement to update stock in the menu table and insert order details into the orderdetails table
    foreach ($_POST["quantity"] as $item_id => $quantity) {
        // Skip items with a quantity of 0
        if ($quantity == 0) {
            continue;
        }

        // Retrieve the price and current stock for the item from the menu table
        $sql_select = "SELECT price, stock FROM menu WHERE item_id = ?";
        $stmt_select = $db_connection->prepare($sql_select);
        $stmt_select->bind_param("i", $item_id);
        $stmt_select->execute();
        $stmt_select->bind_result($price, $stock);
        $stmt_select->fetch();
        $stmt_select->close();

        // Calculate total price for the item
        $total = $price * $quantity;

        // Update stock in the menu table
        $new_stock = $stock - $quantity;
        $sql_update_stock = "UPDATE menu SET stock = ? WHERE item_id = ?";
        $stmt_update_stock = $db_connection->prepare($sql_update_stock);
        $stmt_update_stock->bind_param("ii", $new_stock, $item_id);
        $stmt_update_stock->execute();
        $stmt_update_stock->close();

        // Insert order details into the orderdetails table
        $customer_name = $_POST["customer_name"];
        $status = "Unpaid";
        $order_datetime = date("Y-m-d H:i:s"); // Current date and time
        $sql_insert_order = "INSERT INTO orderdetails (customer_name, order_id, item_name, quantity, price, total, status, order_datetime) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_order = $db_connection->prepare($sql_insert_order);
        $item_name = array_column($menuItems, 'item_name', 'item_id')[$item_id];
        $stmt_insert_order->bind_param("sisiiiss", $customer_name, $new_order_id, $item_name, $quantity, $price, $total, $status, $order_datetime);
        $stmt_insert_order->execute();
        $stmt_insert_order->close();
    }

    // Commit the transaction
    $db_connection->commit();

    // Close the database connection
    $db_connection->close();

    // Redirect back to the student meal page after successful submission
    header("Location: student_meal.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Meal Menu</title>
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
    padding: 20px;
    text-align: center;
  }

  .title {
    font-size: 32px;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
  }

  .menu-category {
    width: 100%;
    max-width: 800px;
    margin-bottom: 30px;
  }

  .menu-category h2 {
    font-size: 24px;
    margin-bottom: 15px;
  }

  .menu-items {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
  }

  .menu-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: #fff;
    padding: 20px;
    margin: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 150px; /* Fixed width for items */
  }

  .menu-item span {
    margin-bottom: 10px;
    font-size: 18px;
  }

  .menu-item img {
    width: 100px; /* Adjust size as needed */
    height: 100px; /* Adjust size as needed */
    border-radius: 50%; /* For circular images */
    margin-bottom: 10px;
  }

  .quantity-controls {
    display: flex;
    align-items: center;
  }

  .quantity-controls button {
    padding: 5px 10px;
    font-size: 16px;
    cursor: pointer;
  }

  .quantity-controls input {
    width: 40px;
    text-align: center;
    margin: 0 5px;
  }

  .out-of-stock {
    color: red;
    font-weight: bold;
  }

  .checkout-button {
    padding: 15px 30px;
    font-size: 18px;
    color: #fff;
    background-color: #28a745;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
  }

  .checkout-button:hover {
    background-color: #218838;
  }
</style>
<script>
  function validateForm() {
    var name = document.getElementById("customer-name").value.trim();
    if (name === "") {
      alert("Please enter your name before checking out.");
      return false;
    }
    return true;
  }

  function incrementValue(item_id) {
    var input = document.getElementById(item_id);
    var value = parseInt(input.value, 10);
    value = isNaN(value) ? 0 : value;
    var stock = parseInt(input.getAttribute('data-stock'), 10);
    if (value < stock) {
      value++;
      input.value = value;
    } else {
      alert('Not enough stock available');
    }
  }

  function decrementValue(item_id) {
    var input = document.getElementById(item_id);
    var value = parseInt(input.value, 10);
    value = isNaN(value) ? 0 : value;
    value = value > 0 ? value - 1 : 0;
    input.value = value;
  }
  
</script>
</head>
<body>

<div class="container">
  <div class="title">WELCOME TO YANG-YANG EATERY</div>

  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm();">
    <!-- Added input field for customer name -->
    <label for="customer-name">Customer Name:</label>
    <input type="text" id="customer-name" name="customer_name" required>

    <div class="menu-category">
      <h2>Menu</h2>
      <div class="menu-items">
        <?php
        foreach ($menuItems as $item) {
            echo '<div class="menu-item">';
            echo '<img src="' . $item["image"] . '" alt="' . $item["item_name"] . '">';
            echo '<span>' . $item["item_name"] . '</span>';
            echo '<span>₱' . $item["price"] . '</span>';
            echo '<span>Stock: ' . $item["stock"] . '</span>';
            if ($item["stock"] > 0) {
                echo '<div class="quantity-controls">';
                echo '<button type="button" onclick="decrementValue(\'' . $item["item_id"] . '\')">-</button>';
                echo '<input type="number" name="quantity[' . $item["item_id"] . ']" id="' . $item["item_id"] . '" value="0" readonly data-stock="' . $item["stock"] . '">';
                echo '<button type="button" onclick="incrementValue(\'' . $item["item_id"] . '\')">+</button>';
                echo '</div>';
            } else {
                echo '<span class="out-of-stock">Out of Stock</span>';
            }
            echo '</div>';
        }
        ?>
      </div>
    </div>

    <button type="submit" class="checkout-button">Checkout</button>
  </form>
</div>

</body>
</html>
