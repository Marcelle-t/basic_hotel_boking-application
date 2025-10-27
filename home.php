<?php
session_start();

echo "<HTML>\n
<header>
<a href='home.php' style='float: left'> Hotel.com</a>";

if(!isset($_SESSION['status'])) {
    echo "<a href='login.html' style='float: right'>Login</a>";
}
else {
    echo "<a href='logout.php' style='float: right'>Logout</a>
    <a href='profile.php' style='float: right'>Profile</a>";
}
echo "</header>";


// Search Area
echo '<br>
<div style="justify-content: center">
    <h1>Welcome to Hotel.com</h1>
    <h2>Plan your next getaway with us</h2>
    <form name="search" action="search_hotel.php" method="get">
        <div style="justify-content: space-around"><br>
            <input type="text" name="city" placeholder="Search by City">
            <input type="text" name="state" placeholder="Search by States">
            <input type="text" name="country" placeholder="Search by Country">
            <input type="submit" value="search">
        </div>
    </form>
</div>';

?>