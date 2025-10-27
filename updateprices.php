<?php
echo "<HTML>\n";

// Include database configuration
include("dbconfig.php");

$con = mysqli_connect($server, $login, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_price'])) {
    // Get the Room ID, Hotel ID, and new price from the form
    $roomID = $_POST['room_id'];
    $hotelID = $_POST['hotel_id'];
    $hotelName = $_POST['hotel_name'];
    $newPrice = $_POST['new_price'];

    // Validate inputs
    if (is_numeric($newPrice) && $newPrice > 0) {
        // Update the room price in the database
        $updateSql = "UPDATE H_Room SET Price = $newPrice WHERE RID = $roomID AND HID = $hotelID";
        if (mysqli_query($con, $updateSql)) {
            // After successful update, use JavaScript to refresh the viewrooms.php page
            echo "<script>
                    document.location.href = 'viewrooms.php?HID=$hotelID&Name=" . urlencode($hotelName) . "';
                  </script>";
        } else {
            echo "<p style='color: red;'>Error updating price: " . mysqli_error($con) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Invalid price. Please enter a positive numeric value.</p>";
    }
} else {
    echo "<p style='color: red;'>Invalid request. Please submit the form correctly.</p>";
}

mysqli_close($con);
?>
