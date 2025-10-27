<?php
session_start();

echo "<HTML>\n";
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

echo "<HTML>\n
          <header>
                <a href='home.php' style='float: left'>Hotel.com</a>
                <a href='owner_profile.php' style='float: right; margin-right: 10px;'>Profile</a>
                <a href='logout.php' style='float: right; margin-right: 20px;'>Logout</a>
          </header><br><br>";

$con = mysqli_connect($server, $login, $password, $dbname);

echo '<form action="" method="post">';

echo '<label for="type">Type: </label>';
echo '<select name="type" id="type" required>';
echo '<option value="Single">Single</option>';
echo '<option value="Double">Double</option>';
echo '<option value="Queen">Queen</option>';
echo '<option value="King">King</option>';
echo '</select><br><br>';

echo '<label for="accessibility">Accessibility: </label>';
echo '<select name="accessibility" id="accessibility" required>';
echo '<option value="0">No Accessibility Features</option>';
echo '<option value="1">Accessible</option>';
echo '</select><br><br>';

echo '<label for="description">Description: </label>';
echo '<textarea name="description" id="description" rows="4" cols="50" maxlength="255" placeholder="Enter room description" required></textarea><br><br>';

echo '<label for="availability">Availability: </label>';
echo '<input type="number" name="availability" id="availability" required><br><br>';

echo '<label for="quantity">Quantity: </label>';
echo '<input type="number" name="quantity" id="quantity" required><br><br>';

echo '<label for="price">Price: </label>';
echo '<input type="number" name="price" id="price" step="0.01" required><br><br>';

echo '<button type="submit" name="submit">Add Room</button>';
echo '</form>';

if (isset($_GET['HID']) && isset($_GET['Name'])) {
    $HID = $_GET['HID'];
    $Name = $_GET['Name'];
    echo '<a href="viewrooms.php?HID=' . $HID . '&Name=' . $Name . '">View Existing Rooms</a>';
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the room details from the form
        $type = $_POST['type'];
        $accessibility = $_POST['accessibility'];
        $description = $_POST['description'];
        $availability = $_POST['availability'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];

        // Validate inputs
        if ($price > 0 && $quantity > 0) {
            if ($quantity >= $availability) {
                // Check if a room with the same type and accessibility already exists in the H_Room table
                $checkSql = "SELECT * FROM H_Room WHERE RID IN (SELECT RID FROM Rooms WHERE Type = '$type' AND Accessibility = $accessibility) AND HID = $HID";
                $checkResult = mysqli_query($con, $checkSql);

                if (mysqli_num_rows($checkResult) > 0) {
                    // Room with the same type and accessibility already exists
                    echo "<p style='color: red;'>This room type with the selected accessibility already exists in the database for this hotel.</p>";
                    echo "<script>
                        // Prevent back navigation if invalid input
                        window.history.pushState(null, '', window.location.href);
                        window.onpopstate = function() {
                            window.location.href = 'viewrooms.php?HID=$HID&Name=$Name'; 
                        };
                    </script>";
                } else {
                    // Retrieve Room ID (RID) from Rooms table for the given type and accessibility
                    $ridQuery = "SELECT RID FROM Rooms WHERE Type = '$type' AND Accessibility = $accessibility";
                    $ridResult = mysqli_query($con, $ridQuery);

                    if ($ridResult && mysqli_num_rows($ridResult) > 0) {
                        $row = mysqli_fetch_assoc($ridResult);
                        $roomID = $row['RID'];

                        // Insert into H_Room table
                        $escapedDescription = mysqli_real_escape_string($con, $description);

                        $insertQuery = "INSERT INTO H_Room (HID, RID, Price, Availability, Quantity, Description) 
                                        VALUES ($HID, $roomID, $price, $availability, $quantity, '$escapedDescription')";
                        if (mysqli_query($con, $insertQuery)) {
                            // Room added successfully, redirect to viewrooms.php
                            echo "<p style='color: green;'>Room added successfully!</p>";
                            echo "<script>
                                setTimeout(function() {
                                    window.location.href = 'viewrooms.php?HID=$HID&Name=$Name';
                                }, 1000);
                            </script>";
                        } else {
                            echo "<p style='color: red;'>Error adding room: " . mysqli_error($con) . "</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>No matching Room ID found for the specified type and accessibility.</p>";
                    }
                }
            } else {
                echo "<p style='color: red;'>Invalid input. Quantity must be greater than availability.</p>";
                echo "<script>
                    // Prevent back navigation if invalid input
                    window.history.pushState(null, '', window.location.href);
                    window.onpopstate = function() {
                        window.location.href = 'viewrooms.php?HID=$HID&Name=$Name'; 
                    };
                </script>";
            }
        } else {
            echo "<p style='color: red;'>Invalid input. Quantity and Price must be greater than zero.</p>";
            echo "<script>
                // Prevent back navigation if invalid input
                window.history.pushState(null, '', window.location.href);
                window.onpopstate = function() {
                    window.location.href = 'viewrooms.php?HID=$HID&Name=$Name'; 
                };
            </script>";
        }
    }
} else {
    echo "<p style='color: red;'>No Hotel ID or Name provided.</p>";
}

mysqli_close($con);
?>
