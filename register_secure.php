<?php
include("dbconfig.php");

echo "<HTML>\n";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['pass']) && isset($_POST['first']) 
        && isset($_POST['last']) && isset($_POST['phone'])) {

        // Collect and sanitize inputs
        $email = $_POST['email'];
        $pass = $_POST['pass'];
        $first = $_POST['first'];
        $last = $_POST['last'];
        $phone = $_POST['phone'];
        $type = $_POST['type'];

        // Hash password for security
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

        // Establish database connection
        $con = new mysqli($server, $login, $password, $dbname);

        // Check for connection errors
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }

        

        // Check if email already exists using a prepared statement
        $checkQuery = "SELECT COUNT(*) AS count FROM " . $type . " WHERE Email = ?";
        $stmt = $con->prepare($checkQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo '<p style="color:red;">User with that email already exists!</p><br><a href="login.html">Back to Login</a>';
        } else {
            // Insert the new user using a prepared statement
            $insertQuery = "INSERT INTO " . $type . " (Email, Password, FirstName, LastName, Phone) VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($insertQuery);
            $stmt->bind_param("sssss", $email, $hashed_pass, $first, $last, $phone);
            if ($stmt->execute()) {
                echo '<p style="color:green;">Registration successful!</p><br><a href="login.html">Back to Login</a>';
            } else {
                echo '<p style="color:red;">Error: ' . htmlspecialchars($stmt->error) . '</p><br><a href="login.html">Back to Login</a>';
            }
            $stmt->close();
        }

        // Close database connection
        $con->close();
    } else {
        echo '<p style="color:red;">All fields are required!</p><br><a href="login.html">Back to Login</a>';
    }
} else {
    echo '<p style="color:red;">Invalid request method!</p><br><a href="login.html">Back to Login</a>';
}
?>
