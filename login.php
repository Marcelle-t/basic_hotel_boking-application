<?php
session_start();

if(isset($_SESSION['status'])){
    header('Location: profile.php');
    exit;
}

echo "<HTML>\n";
include("dbconfig.php");

$con = mysqli_connect($server, $login, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if 'login' and 'pass' are set in the POST request
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $pass = $_POST['password'];

        // Prepare the SQL query to check the credentials
        $sql = "SELECT Email, Password FROM 2024F_haiderma.Customers WHERE email='$login'";

        $result = mysqli_query($con, $sql);

        // Check if the query was successful and if any rows were returned
        if ($result) {
            $row = mysqli_fetch_array($result);
            if (mysqli_num_rows($result) > 0 && password_verify($pass, $row['Password'])) { 
                    // Set the cookie for the user ID
                    $_SESSION['status'] = "Active";
                    $_SESSION['email'] = $row['Email'];
                    $_SESSION['isActive'] = $row['Active'];
                    
                    // setcookie('email', $row['Email'], time() + 1000);
                    // echo "Welcome $login";
                    header("Location: profile.php");
            } else {
                echo "<br>Invalid login or password.";
            }
        } else {
            echo "<br>Login failed!\n";
        }

    } else {
        // If 'login' or 'pass' are not set in POST request, show the form
        echo "Please enter your login credentials.";
    }
} else {
    echo "Something happens here?";
}
 // Close the connection
 mysqli_close($con);
?>