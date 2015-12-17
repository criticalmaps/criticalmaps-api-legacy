<?php

require_once("../credentials.php");

//save file
$imageId = uniqid();
$filename = "./images/" . $imageId . ".jpg";
$filename_thumb = "./images/" . $imageId . "_thumb.jpg";

if (!move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $filename)) {
	die("fail: moving file");
}


//create thumbnail
$final_width_of_image = 100;

$image = imagecreatefromjpeg($filename);

$oldWidth = imagesx($image);
$oldHeight = imagesy($image);

$newWidth = $final_width_of_image;
$newHeight = floor($oldHeight * ($final_width_of_image / $oldWidth));

$newImage = imagecreatetruecolor($newWidth, $newHeight);

imagecopyresized($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

imagejpeg($newImage, $filename_thumb);


//save to database
$jsonObject = json_decode($_POST["data"]);

$longitude = filter_var($jsonObject -> longitude, FILTER_SANITIZE_STRING);
$latitude = filter_var($jsonObject -> latitude, FILTER_SANITIZE_STRING);

$con=mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
	die("fail: mysql");
}

if (isset($longitude) && isset($latitude) && isset($imageId)) {
	$locationObject = $jsonObject -> location;
	$query = "INSERT INTO `Images` (`id`, `timestamp`, `longitude`, `latitude`, `imageId` ) VALUES (NULL, CURRENT_TIMESTAMP,'" . $longitude . "', '" . $latitude . "', '" . $imageId . "' );";
	mysqli_query($con, $query);
}

mysqli_close($con);

echo "success";
