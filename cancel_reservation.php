<?php
session_start();

include("dbconfig.php");
$con = mysqli_connect($server, $login, $password, $dbname);

if (!$con) { 
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  #  if (!isset($_SESSION['email'])) {
   #     die("Unauthorized access. Please log in.");
   # }

    $email = $_SESSION['email'];
    $hid = intval($_GET["HID"] ?? 0);
    $rid = intval($_GET["RID"] ?? 0);
    $name = mysqli_real_escape_string($con, $_GET['Name'] ?? '');

    // Debug input values
    #echo "HID=$hid, RID=$rid, Name='$name'<br>";

    // Check if reservation exists
    $stmt = $con->prepare("SELECT * FROM 2024F_haiderma.Reservation WHERE HID = ? AND RID = ?");
    $stmt->bind_param("ii", $hid, $rid);

    if ($stmt->execute()) {
        $check_reservation_result = $stmt->get_result();
        echo "Query executed successfully.<br>";

        if ($check_reservation_result->num_rows == 1) {
            // Proceed with deletion
            $stmt_delete = $con->prepare("DELETE FROM 2024F_haiderma.Reservation WHERE HID = ? AND RID = ?");
            $stmt_delete->bind_param("ii", $hid, $rid);

            if ($stmt_delete->execute()) {
                echo "Successfully deleted the Reservation for Room $rid at Hotel '$name'.";
                echo'<br><a href="search_hotel.php">Hotels</a>';
            } else {
                echo "Delete order failed: " . $stmt_delete->error;
            }
        } else {
            echo "The reservation does not exist, the order cannot be canceled.";
        }
    } else {
        echo "Error executing query: " . $stmt->error;
    }
} else {
    echo "Invalid request method.";
}

mysqli_close($con);
?>
