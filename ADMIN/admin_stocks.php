<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Menu</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    color: #333;
    margin: 0;
    padding: 0;
  }

  .navbar {
    overflow: hidden;
    background-color: #333;
    text-align: center;
  }

  .navbar a {
    display: inline-block;
    color: #f2f2f2;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
  }

  .navbar a:hover {
    background-color: #ddd;
    color: black;
  }

  .container {
    display: none;
    padding: 20px;
  }

  .menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    justify-items: center;
    margin: 0 auto;
    max-width: 1200px;
  }

  .menu-item, .admin-section {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.3s ease;
  }

  .menu-item:hover, .admin-section:hover {
    transform: translateY(-5px);
  }

  .menu-item img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    margin-bottom: 15px;
  }

  input[type="number"], input[type="text"], input[type="file"] {
    width: 80%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    margin-top: 10px;
    font-size: 16px;
  }

  button {
    margin-top: 15px;
    padding: 10px 25px;
    font-size: 16px;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  button:hover {
    background-color: #218838;
  }

  h3 {
    margin-bottom: 20px;
  }

  p {
    font-size: 18px;
    color: #555;
  }

  .active {
    display: block;
  }
</style>
</head>
<body>

<div class="navbar">
  <a href="#special-menu" onclick="showSection('special-menu')">Student Menu</a>
  <a href="#regular-menu" onclick="showSection('regular-menu')">Regular Meal Menu</a>
  <a href="#admin-special-menu" onclick="showSection('admin-special-menu')">Edit Student Menu</a>
  <a href="#admin-regular-menu" onclick="showSection('admin-regular-menu')">Edit Regular Meal Menu</a>
  <a href="admin_dashboard.php" onclick="showSection('admin-regular-menu')">Back To Dashboard</a>
</div>

<!-- Container for "menu" table items -->
<div id="special-menu" class="container active">
  <h2>Student Menu</h2>
  <div class="menu-grid">
    <?php
    // Database connection details
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

    // Fetch menu items from the database for display
    $sql = "SELECT item_id, item_name, image, price, stock FROM menu";
    $result = $db_connection->query($sql);

    // Display menu items with input fields for stock quantity
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<div class="menu-item">';
            echo '<img src="' . $row["image"] . '" alt="' . $row["item_name"] . '">';
            echo '<h3>' . $row["item_name"] . '</h3>';
            echo '<p>₱' . $row["price"] . '</p>';
            echo '<p>Stock: ' . $row["stock"] . '</p>';
            echo '<input type="number" id="stock_' . $row["item_id"] . '" placeholder="Stock Quantity">';
            echo '<button onclick="addStock(' . $row["item_id"] . ')">Add Stock</button>';
            echo '</div>';
        }
    } else {
        echo "No menu items available.";
    }

    // Close the database connection
    $db_connection->close();
    ?>
  </div>
</div>

<!-- Container for "menu1" table items -->
<div id="regular-menu" class="container">
  <h2>Regular Meal Menu</h2>
  <div class="menu-grid">
    <?php
    // Re-establish database connection
    $db_connection = new mysqli('localhost', $user, $pass, $db, $port);

    // Check if there are any connection errors
    if ($db_connection->connect_error) {
        die("Connection failed: " . $db_connection->connect_error);
    }

    // Fetch menu1 items from the database for display
    $sql = "SELECT item_id, item_name, image, price, stock FROM menu1";
    $result = $db_connection->query($sql);

    // Display menu1 items with input fields for stock quantity
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<div class="menu-item">';
            echo '<img src="' . $row["image"] . '" alt="' . $row["item_name"] . '">';
            echo '<h3>' . $row["item_name"] . '</h3>';
            echo '<p>₱' . $row["price"] . '</p>';
            echo '<p>Stock: ' . $row["stock"] . '</p>';
            echo '<input type="number" id="stock1_' . $row["item_id"] . '" placeholder="Stock Quantity">';
            echo '<button onclick="addStock1(' . $row["item_id"] . ')">Add Stock</button>';
            echo '</div>';
        }
    } else {
        echo "No menu items available.";
    }

    // Close the database connection
    $db_connection->close();
    ?>
  </div>
</div>

<!-- Admin sections for "menu" -->
<div id="admin-special-menu" class="container">
  <div class="admin-section">
    <h3>Add New Dish to Student Menu</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="text" name="item_name" placeholder="Dish Name" required><br>
      <input type="number" name="price" placeholder="Price" step="0.01" required><br>
      <input type="number" name="stock" placeholder="Stock" required><br>
      <input type="file" name="image" required><br>
      <button type="submit" name="add_dish">Add Dish</button>
    </form>
  </div>

  <div class="admin-section">
    <h3>Delete Dish from Student Menu</h3>
    <form method="post">
      <input type="number" name="item_id" placeholder="Dish ID" required><br>
      <button type="submit" name="delete_dish">Delete Dish</button>
    </form>
  </div>

  <div class="admin-section">
    <h3>Update Dish Price in Student Menu</h3>
    <form method="post">
      <input type="number" name="item_id" placeholder="Dish ID" required><br>
      <input type="number" name="new_price" placeholder="New Price" step="0.01" required><br>
      <button type="submit" name="update_price">Update Price</button>
    </form>
  </div>

  <div class="admin-section">
    <h3>Update Dish Stock in Student Menu</h3>
    <form method="post">
      <input type="number" name="item_id" placeholder="Dish ID" required><br>
      <input type="number" name="new_stock" placeholder="New Stock" required><br>
      <button type="submit" name="update_stock">Update Stock</button>
    </form>
  </div>
</div>

<!-- Admin sections for "menu1" -->
<div id="admin-regular-menu" class="container">
  <div class="admin-section">
    <h3>Add New Dish to Regular Meal Menu</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="text" name="item_name1" placeholder="Dish Name" required><br>
      <input type="number" name="price1" placeholder="Price" step="0.01" required><br>
      <input type="number" name="stock1" placeholder="Stock" required><br>
      <input type="file" name="image1" required><br>
      <button type="submit" name="add_dish1">Add Dish</button>
    </form>
  </div>

  <div class="admin-section">
    <h3>Delete Dish from Regular Meal Menu</h3>
    <form method="post">
      <input type="number" name="item_id1" placeholder="Dish ID" required><br>
      <button type="submit" name="delete_dish1">Delete Dish</button>
    </form>
  </div>

  <div class="admin-section">
    <h3>Update Dish Price in Regular Meal Menu</h3>
    <form method="post">
      <input type="number" name="item_id1" placeholder="Dish ID" required><br>
      <input type="number" name="new_price1" placeholder="New Price" step="0.01" required><br>
      <button type="submit" name="update_price1">Update Price</button>
    </form>
  </div>

  <div class="admin-section">
    <h3>Update Dish Stock in Regular Meal Menu</h3>
    <form method="post">
      <input type="number" name="item_id1" placeholder="Dish ID" required><br>
      <input type="number" name="new_stock1" placeholder="New Stock" required><br>
      <button type="submit" name="update_stock1">Update Stock</button>
    </form>
  </div>
</div>

<script>
function showSection(sectionId) {
  var sections = document.querySelectorAll('.container');
  sections.forEach(function(section) {
    section.classList.remove('active');
  });
  document.getElementById(sectionId).classList.add('active');
}

function addStock(itemId) {
  var stockInput = document.getElementById('stock_' + itemId);
  var stockQuantity = stockInput.value.trim();
  
  if (stockQuantity === "") {
    alert("Please enter a valid stock quantity.");
    return;
  }
  
  // Send an AJAX request to update the stock quantity
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "update_stock.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        alert(xhr.responseText);
        // Optionally, you can update the UI here after successful update
      } else {
        alert("Error: " + xhr.statusText);
      }
    }
  };
  xhr.send("item_id=" + itemId + "&stock_quantity=" + stockQuantity);
}

function addStock1(itemId) {
  var stockInput = document.getElementById('stock1_' + itemId);
  var stockQuantity = stockInput.value.trim();
  
  if (stockQuantity === "") {
    alert("Please enter a valid stock quantity.");
    return;
  }
  
  // Send an AJAX request to update the stock quantity
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "update_stock1.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        alert(xhr.responseText);
        // Optionally, you can update the UI here after successful update
      } else {
        alert("Error: " + xhr.statusText);
      }
    }
  };
  xhr.send("item_id=" + itemId + "&stock_quantity=" + stockQuantity);
}

// Show the special menu section by default
showSection('special-menu');
</script>

</body>
</html>

<?php
// Function to handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

  // Handle adding a new dish to "menu"
  if (isset($_POST['add_dish'])) {
    $item_name = $_POST['item_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $sql = "INSERT INTO menu (item_name, price, stock, image) VALUES ('$item_name', '$price', '$stock', '$target_file')";
      if ($db_connection->query($sql) === TRUE) {
        echo "New dish added successfully";
      } else {
        echo "Error: " . $sql . "<br>" . $db_connection->error;
      }
    } else {
      echo "Error uploading image.";
    }
  }

  // Handle deleting a dish from "menu"
  if (isset($_POST['delete_dish'])) {
    $item_id = $_POST['item_id'];
    $sql = "DELETE FROM menu WHERE item_id = '$item_id'";
    if ($db_connection->query($sql) === TRUE) {
      echo "Dish deleted successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $db_connection->error;
    }
  }

  // Handle updating the price of a dish in "menu"
  if (isset($_POST['update_price'])) {
    $item_id = $_POST['item_id'];
    $new_price = $_POST['new_price'];
    $sql = "UPDATE menu SET price = '$new_price' WHERE item_id = '$item_id'";
    if ($db_connection->query($sql) === TRUE) {
      echo "Price updated successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $db_connection->error;
    }
  }

  // Handle updating the stock of a dish in "menu"
  if (isset($_POST['update_stock'])) {
    $item_id = $_POST['item_id'];
    $new_stock = $_POST['new_stock'];
    $sql = "UPDATE menu SET stock = '$new_stock' WHERE item_id = '$item_id'";
    if ($db_connection->query($sql) === TRUE) {
      echo "Stock updated successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $db_connection->error;
    }
  }

  // Handle adding a new dish to "menu1"
  if (isset($_POST['add_dish1'])) {
    $item_name = $_POST['item_name1'];
    $price = $_POST['price1'];
    $stock = $_POST['stock1'];
    $image = $_FILES['image1']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image1']['tmp_name'], $target_file)) {
      $sql = "INSERT INTO menu1 (item_name, price, stock, image) VALUES ('$item_name', '$price', '$stock', '$target_file')";
      if ($db_connection->query($sql) === TRUE) {
        echo "New dish added successfully";
      } else {
        echo "Error: " . $sql . "<br>" . $db_connection->error;
      }
    } else {
      echo "Error uploading image.";
    }
  }

  // Handle deleting a dish from "menu1"
  if (isset($_POST['delete_dish1'])) {
    $item_id = $_POST['item_id1'];
    $sql = "DELETE FROM menu1 WHERE item_id = '$item_id'";
    if ($db_connection->query($sql) === TRUE) {
      echo "Dish deleted successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $db_connection->error;
    }
  }

  // Handle updating the price of a dish in "menu1"
  if (isset($_POST['update_price1'])) {
    $item_id = $_POST['item_id1'];
    $new_price = $_POST['new_price1'];
    $sql = "UPDATE menu1 SET price = '$new_price' WHERE item_id = '$item_id'";
    if ($db_connection->query($sql) === TRUE) {
      echo "Price updated successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $db_connection->error;
    }
  }

  // Handle updating the stock of a dish in "menu1"
  if (isset($_POST['update_stock1'])) {
    $item_id = $_POST['item_id1'];
    $new_stock = $_POST['new_stock1'];
    $sql = "UPDATE menu1 SET stock = '$new_stock' WHERE item_id = '$item_id'";
    if ($db_connection->query($sql) === TRUE) {
      echo "Stock updated successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $db_connection->error;
    }
  }

  // Close the database connection
  $db_connection->close();
}
?>
