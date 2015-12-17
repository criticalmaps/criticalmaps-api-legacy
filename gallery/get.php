<?php

require_once("../credentials.php");

header('Access-Control-Allow-Origin: *'); 

$con=mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    die ("Failed to connect to MySQL:");
}

$imagesFromDb = mysqli_query($con, "SELECT * FROM Images WHERE timestamp > (NOW() - INTERVAL 12 MONTH)");

$imagesArray = [];

while ($row = mysqli_fetch_array($imagesFromDb)) {
	  $imagesArray[$row['id']] = array(
                'longitude' => $row['longitude'],
                'latitude' => $row['latitude'],
                'timestamp' => strtotime($row['timestamp']),
				'imageId' => $row['imageId'] );
}

echo json_encode($imagesArray);

mysqli_close($con);
