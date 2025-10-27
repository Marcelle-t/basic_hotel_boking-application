<?php
// Include the database configuration file
include 'dbconfig.php';

// Start session (optional, if needed for validation)
session_start();
$con = mysqli_connect($server, $login, $password, $dbname);

// Check if the email is provided
if (isset($_POST['login'])) {
    // Get the email from the query string
    $email = $_POST['login'];
    $password = $_POST['pass'];

    // Validate the email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Prepare the SQL DELETE query
       // $sql = "DELETE FROM Customers WHERE Email = ?";
        $sql = 'UPDATE 2024F_haiderma.Customers
            SET Email = Null, Password = Null, FirstName = Null, LastName = Null, Phone = Null
            WHERE Email = $email and Password = $password';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);

        // Execute the query
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "User with email $email has been deleted successfully.";
            } else {
                echo "No user found with email $email.";
            }
        } else {
            echo "Error deleting user: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Invalid email format.";
    }
} else {
    echo "No email provided. Unable to delete user.";
}

// Close the database connection
mysqli_close($con);
?>
