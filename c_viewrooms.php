
<?php
session_start();
echo "<HTML>\n";
include("dbconfig.php");

$con = mysqli_connect($server, $login, $password, $dbname);

// Sample email
$email = "test3@gmail.com";
//echo $_GET['HID'];
// Check if HID and Name are set in GET parameters
if (isset($_GET['HID']) && isset($_GET['Name'])) {
    $HID = mysqli_real_escape_string($con, $_GET['HID']);
    $Name = htmlspecialchars($_GET['Name']);
    //echo $email;
    // Fetch room details for the hotel
    $sql = "SELECT hr.RID, hr.Price, hr.Availability, hr.Quantity, hr.Description, 
                   r.Type, r.Accessibility, r.Capacity 
            FROM H_Room hr
            JOIN Hotel_Owned ho ON hr.HID = ho.HID
            JOIN Rooms r ON hr.RID = r.RID
            WHERE  hr.HID = '$HID'";
    
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        echo '<h2>Rooms for ' . htmlspecialchars($Name) . '</h2>';
        echo '<table border="1" cellpadding="10">';
        echo '<tr>
                <th>Room ID</th>
                <th>Type</th>
                <th>Accessibility</th>
                <th>Capacity</th>
                <th>Description</th>
                <th>Availability</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Book Room</th>
             
              </tr>';
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $row['RID'] . '</td>';
            echo '<td>' . $row['Type'] . '</td>';
            echo '<td>' . $row['Accessibility'] . '</td>';
            echo '<td>' . $row['Capacity'] . '</td>';
            echo '<td>' . $row['Description'] . '</td>';
            echo '<td>' . $row['Availability'] . '</td>';
            echo '<td>' . $row['Quantity'] . '</td>';
            echo '<td>' . $row['Price'] . '</td>';
            $RID = $row['RID'] ;
            echo '<td>
                    <form action="updateprices.php" method="POST">
                        <input type="hidden" name="rid" value="' . $row['RID'] . '">
                        <input type="hidden" name="hid" value="' . $HID . '">
                        <input type="hidden" name="hotel_name" value="' . htmlspecialchars($Name) . '">
                        <a href="reservation.php?RID=' .$RID.'&HID=' . $HID . '&Name=' . urlencode($Name) . '">Book</a>
                    </form>
                  </td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo "No rooms found for this hotel.";
    }
} else {
    echo "No Hotel ID or Name provided.";
}
// Close the connection
mysqli_close($con);
?>
