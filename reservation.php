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

// Retrieve HID, RID, and Name values from GET or POST
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

// Validate HID and RID
if (!$hid || !$rid) {
    die("Missing HID or RID.");
}

// Handle room reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_in'], $_POST['check_out'], $_POST['payment_type'])) {
        $check_in = mysqli_real_escape_string($con, $_POST['check_in']);
        $check_out = mysqli_real_escape_string($con, $_POST['check_out']);
        $payment_type = $_POST['payment_type'] === 'paypal' ? 'PayPal' : 'Credit/Debit Card';

        // Validate dates
        $current_date = date('Y-m-d');
        if ($check_in < $current_date || $check_out < $current_date) {
            die("Check-in and check-out dates must be in the future.");
        }
        if ($check_in >= $check_out) {
            die("Check-out date must be after check-in date.");
        }

        // Calculate duration
        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        $duration = $check_in_date->diff($check_out_date)->days;

        // Check room availability
        $sql_check = "SELECT Availability, Price FROM 2024F_haiderma.H_Room WHERE RID = $rid AND HID = $hid";
        $result = mysqli_query($con, $sql_check);

        if (!$result || mysqli_num_rows($result) <= 0) {
            die("Sorry, no rooms are available for the selected hotel.");
        } else {
            $room = mysqli_fetch_assoc($result);
            $availability = $room['Availability'];
            $price_per_night = $room['Price'];

            if ($availability > 0) {
                // Calculate total cost
                $total_cost = $duration * $price_per_night;

                // Insert reservation into the database
                $sql_insert = "INSERT INTO 2024F_haiderma.Reservation 
                               (Email, HID, RID, CheckIn, CheckOut, PayType, TCost, status) 
                               VALUES ('$email', $hid, $rid, '$check_in', '$check_out', '$payment_type', $total_cost, '$status')";

                if (mysqli_query($con, $sql_insert)) {
                    echo "Room reserved successfully!";
                } else {
                    die("Error inserting reservation: " . mysqli_error($con));
                }
            } else {
                echo "No availability for the selected room.";
            }
        }
    } else {
        echo "Missing check-in, check-out, or payment type.";
    }
}

// Display reservation summary for the logged-in user
$sql_summary = "SELECT * FROM 2024F_haiderma.Reservation WHERE Email = '$email'";
$result_summary = mysqli_query($con, $sql_summary);

if ($result_summary && mysqli_num_rows($result_summary) > 0) {
    echo '<h2>Reservation Summary for ' . htmlspecialchars($name) . '</h2>';
    echo '<table border="1" cellpadding="10">';
    echo '<tr>
            <th>Email</th>
            <th>Hotel ID</th>
            <th>Room ID</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Payment Type</th>
            <th>Total Cost</th>
            <th>Actions</th>
          </tr>';
    while ($row = mysqli_fetch_assoc($result_summary)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['Email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['HID']) . '</td>';
        echo '<td>' . htmlspecialchars($row['RID']) . '</td>';
        echo '<td>' . htmlspecialchars($row['CheckIn']) . '</td>';
        echo '<td>' . htmlspecialchars($row['CheckOut']) . '</td>';
        echo '<td>' . htmlspecialchars($row['PayType']) . '</td>';
        echo '<td>' . htmlspecialchars($row['TCost']) . '</td>';
        echo '<td>
                <a href="update_reservation.php?RID=' . $row['RID'] . '&HID=' . $row['HID'] . '&Email=' . urlencode($row['Email']) .'&Name=' . urlencode($name). '">Update</a> |
                <a href="cancel_reservation.php?RID=' . $row['RID'] . '&HID=' . $row['HID'] . '&Email=' . urlencode($row['Email']) . '&Name=' . urlencode($name). '">Cancel</a>
              </td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo "<p>No reservations found for you.</p>";
}

// Close the connection
mysqli_close($con);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Reservation</title>
</head>
<body>
    <h2>Room Reservation</h2>
    <form action="reservation.php" method="POST">
        <input type="hidden" name="HID" value="<?php echo htmlspecialchars($hid); ?>">
        <input type="hidden" name="RID" value="<?php echo htmlspecialchars($rid); ?>">
        <input type="hidden" name="Name" value="<?php echo htmlspecialchars($name); ?>">

        <label for="hotel">Hotel Name:</label>
        <input type="text" name="hotel" id="hotel" value="<?php echo htmlspecialchars($name); ?>" readonly><br><br>

        <label for="check_in">Check-In Date:</label>
        <input type="date" name="check_in" id="check_in" required><br><br>

        <label for="check_out">Check-Out Date:</label>
        <input type="date" name="check_out" id="check_out" required><br><br>

        <label for="payment_type">Payment Type:</label>
        <input type="radio" name="payment_type" value="credit_card" required>Credit/Debit Card
        <input type="radio" name="payment_type" value="paypal" required>PayPal<br><br>

        <button type="submit">Reserve</button>
    </form>
</body>
</html>
