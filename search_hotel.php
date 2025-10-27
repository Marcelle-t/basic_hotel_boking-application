<?php
session_start();
echo "<HTML>\n";
include("dbconfig.php");

$con = mysqli_connect($server, $login, $password, $dbname);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if 'city', 'state', and 'country' are set in the GET request

    // Retrieve inputs with default values
    $city = $_GET['City'] ?? '';
    $state = $_GET['State'] ?? '';
    $country = $_GET['Country'] ?? '';
    $city = isset($_GET['city']) ? $_GET['city'] : '';
$state = isset($_GET['state']) ? $_GET['state'] : '';
$country = isset($_GET['country']) ? $_GET['country'] : '';

// Debugging: Display what is received in the GET request
#echo "Debug: City = '$city', State = '$state', Country = '$country'<br>";


    // Debugging user input
    #echo "Debug: City = '$city', State = '$state', Country = '$country'<br>";

    // Construct SQL query using prepared statements
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($city)) {
        $conditions[] = "City LIKE ?";
        $params[] = "%$city%";
        $types .= 's';
    }
    if (!empty($state)) {
        $conditions[] = "State LIKE ?";
        $params[] = "%$state%";
        $types .= 's';
    }
    if (!empty($country)) {
        $conditions[] = "Country LIKE ?";
        $params[] = "%$country%";
        $types .= 's';
    }

    $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $sql = "SELECT * FROM 2024F_tamegnom.Hotel_Amen $whereClause";

    // Debugging SQL query
    #echo "Debug: SQL Query = $sql<br>";

    $stmt = $con->prepare($sql);
    if ($stmt && count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Output table
    echo '<table border="1" cellspacing="2" cellpadding="2"> 
    <tr> 
        <td> <font face="Arial">Hotel</font> </td> 
        <td> <font face="Arial">City</font> </td>
        <td> <font face="Arial">State</font> </td>
        <td> <font face="Arial">Country</font> </td> 
        <td> <font face="Arial">Amenities</font> </td> 
        <td> <font face="Arial">View</font> </td> 
    </tr>';

    // Check if the query was successful and if any rows were returned
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hid = $row['HID'];
            $hotel = htmlspecialchars($row["Hotel"]);
            $city = htmlspecialchars($row["City"]);
            $state = htmlspecialchars($row["State"]);
            $country = htmlspecialchars($row["Country"]);
            $amenities = htmlspecialchars($row["Amenities"]);

            // Display row
            echo "<tr> 
            <td>$hotel</td> 
            <td>$city</td> 
            <td>$state</td>
            <td>$country</td>
            <td>$amenities</td> 
            <td><a href='c_viewrooms.php?HID=$hid&Name=" . urlencode($hotel) . "'>View</a></td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No records found.</td></tr>";
    }

    echo '</table>';
} else {
    echo "Invalid request method.";
}

// Close the connection
mysqli_close($con);
?>
