<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meal Options</title>
<style>
  /* Centering the form */
  .container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    height: 100vh;
  }

  /* Styling the title */
  .title {
    font-size: 32px;
    font-weight: bold;
    color: #333;
    margin-bottom: 30px;
  }

  /* Basic CSS styling for buttons */
  .meal-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 25px 90px;
    margin: 10px;
    font-size: 25px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.3s;
    background-color: #4CAF50; /* Default to green */
  }

  .meal-button img {
    width: 250px;
    height: 250px;
    margin-bottom: 10px;
  }
  
  .meal-button.student {
    background-color: #2196F3; /* Blue */
  }
  
  .meal-button:hover {
    background-color: #555;
  }

</style>
</head>
<body>

<div class="container">
  <div class="title">WELCOME TO YANG-YANG EATERY</div>

  <?php
  // Check if a meal option is selected
  if (isset($_POST['meal'])) {
      $selectedMeal = $_POST['meal'];
      if ($selectedMeal == "Regular Meal") {
          header("Location: regular_meal.php"); // Redirect to regular_meal.php
      } elseif ($selectedMeal == "Student Meal") {
          header("Location: student_meal.php"); // Redirect to student_meal.php
      }
      exit();
  }
  ?>

  <form method="post">
    <button class="meal-button regular" type="submit" name="meal" value="Regular Meal">
      <img src="regular.png" alt="Regular Meal">
      Regular Meal
    </button>
    <button class="meal-button student" type="submit" name="meal" value="Student Meal">
      <img src="student.png" alt="Student Meal">
      Student Meal
    </button>
  </form>
</div>

</body>
</html>
