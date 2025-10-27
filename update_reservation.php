<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: profile.php');
    exit;
}

// Retrieve the logged-in user's email and status
$email = $_SESSION['email'];
$status = $_SESSION['status'];

include("dbconfig.php");
$con = mysqli_connect($server, $login, $password, $dbname);

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hid = isset($_POST['HID']) ? intval($_POST['HID']) : null;
    $rid = isset($_POST['RID']) ? intval($_POST['RID']) : null;
    $name = isset($_POST['Name']) ? mysqli_real_escape_string($con, $_POST['Name']) : null;
} else {
    $hid = isset($_GET['HID']) ? intval($_GET['HID']) : null;
    $rid = isset($_GET['RID']) ? intval($_GET['RID']) : null;
    $name = isset($_GET['Name']) ? mysqli_real_escape_string($con, $_GET['Name']) : null;
}

// Debug: Check values after GET/POST
#echo "HID: $hid, RID: $rid, Name: $name<br>";


// Retrieve the reservation details using GET parameters
$new_rid = isset($_GET['RID']) ? intval($_GET['RID']) : null;
$new_hid = isset($_GET['HID']) ? intval($_GET['HID']) : null;
$email_param = isset($_GET['Email']) ? mysqli_real_escape_string($con, $_GET['Email']) : null;

// Validate if all required parameters are provided
if (!$new_rid || !$new_hid || !$email_param) {
    die("Missing required parameters.");
}

// Fetch the current reservation details
$sql = "SELECT * FROM 2024F_haiderma.Reservation WHERE RID = $new_rid AND HID = $new_hid AND Email = '$email_param'";
$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Reservation not found.");
}

$row = mysqli_fetch_assoc($result);

// Retrieve current reservation details
$current_check_in = $row['CheckIn'];
$current_check_out = $row['CheckOut'];
$current_paytype = $row['PayType'];
$current_cost = $row['TCost'];

// Handle form submission to update the reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated data from the form
    $new_check_in = mysqli_real_escape_string($con, $_POST['check_in']);
    $new_check_out = mysqli_real_escape_string($con, $_POST['check_out']);
    $new_payment_type = $_POST['payment_type'] === 'paypal' ? 'PayPal' : 'Credit/Debit Card';

    // Validate the new dates
    $current_date = date('Y-m-d');
    if ($new_check_in < $current_date || $new_check_out < $current_date) {
        die("Check-in and check-out dates must be in the future.");
    }
    if ($new_check_in >= $new_check_out) {
        die("Check-out date must be after check-in date.");
    }

    // Calculate new duration and cost
    $check_in_date = new DateTime($new_check_in);
    $check_out_date = new DateTime($new_check_out);
    $duration = $check_in_date->diff($check_out_date)->days;

    $sql_check = "SELECT Price FROM 2024F_haiderma.H_Room WHERE RID = $new_rid AND HID = $new_hid";
    $result_check = mysqli_query($con, $sql_check);
    if ($result_check && mysqli_num_rows($result_check) > 0) {
        $room = mysqli_fetch_assoc($result_check);
        $price_per_night = $room['Price'];
        $new_total_cost = $duration * $price_per_night;

        // Update the reservation in the database
        $sql_update = "UPDATE 2024F_haiderma.Reservation 
                       SET CheckIn = '$new_check_in', CheckOut = '$new_check_out', PayType = '$new_payment_type', TCost = $new_total_cost 
                       WHERE RID = $new_rid AND HID = $new_hid AND Email = '$email_param'";

        if (mysqli_query($con, $sql_update)) {
            // Redirect to refresh variables and display success message
            header("Location: update_reservation.php?RID=$new_rid&HID=$new_hid&Email=" . urlencode($email_param) . "&Name=" . urlencode($name) . "&success=1");
            exit;
        } else {
            echo "Error updating reservation: " . mysqli_error($con);
        }
    } else {
        echo "Error fetching room price.";
    }
}

// Display success message if redirected with success
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<p style='color: green;'>Reservation updated successfully!</p>";
}

// Close the connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Reservation</title>
</head>
<body>
    <h2>Update Reservation</h2>
    <form action="update_reservation.php?RID=<?php echo $rid; ?>&HID=<?php echo $hid; ?>&Email=<?php echo urlencode($email_param); ?>" method="POST">
        <label for="check_in">Check-In Date:</label>
        <input type="date" name="check_in" id="check_in" value="<?php echo htmlspecialchars($current_check_in); ?>" required><br><br>

        <label for="check_out">Check-Out Date:</label>
        <input type="date" name="check_out" id="check_out" value="<?php echo htmlspecialchars($current_check_out); ?>" required><br><br>

        <label for="payment_type">Payment Type:</label>
        <input type="radio" name="payment_type" value="credit_card" <?php echo $current_paytype == 'Credit/Debit Card' ? 'checked' : ''; ?>>Credit/Debit Card
        <input type="radio" name="payment_type" value="paypal" <?php echo $current_paytype == 'PayPal' ? 'checked' : ''; ?>>PayPal<br><br>

        <button type="submit">Update Reservation</button>
    </form>

    <br>
    <a href="search_hotel.php?RID=<?php echo $row['RID']; ?>&HID=<?php echo $row['HID']; ?>&Email=<?php echo urlencode($row['Email']); ?>&Name=<?php echo urlencode($name); ?>">Update Hotel</a>
<br>
<a href="reservation.php?RID=<?php echo $row['RID']; ?>&HID=<?php echo $row['HID']; ?>&Email=<?php echo urlencode($row['Email']); ?>&Name=<?php echo urlencode($name); ?>">Back to Reservations</a>

</body>
</html>
