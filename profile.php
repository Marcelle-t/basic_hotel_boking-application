<?php
session_start();
include ("dbconfig.php");

// If not logged in, return to login.html
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "Active") {
    header("Location: login.html");
    exit();
}

// prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if User's account has not been deleted
// if ($_SESSION['isActive'] != 1){
//     echo "User's account has been deleted. <br>";
// }

echo "<HTML>\n
<header>
    <a href='home.php' style='float: left'> Hotel.com</a>
</header><br><br>";

$email = $_SESSION['email'];
$con = mysqli_connect($server, $login, $password, $dbname);
$sql = "SELECT FirstName, LastName, Active FROM Customers where Email = '$email'";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result);

$active = $row['Active'];

if (!$active){
    echo "The account registered under ". $email . " is currently inactive! Please reenable the account!";
}
else{
    echo "Welcome, " . $row['FirstName'] . " " . $row['LastName'] . "!";
}


$sql = "select RID from Reservation where Email='$email'";
$result = mysqli_query($con, $sql);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        echo $row;
    }
    else {
        echo "<br>No reservations";
    }
}

// Logout/Delete Account
if ($active){
    echo '<br><a href="toggle_acc.php?mode=0">Deactivate Account</a>';
}
else {
    echo '<br><a href="toggle_acc.php?mode=1">Activate Account</a>';
}

echo '<br><a href="logout.php">Logout</a>';

?>