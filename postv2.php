<?php

require_once("credentials.php");

$jsonObject = json_decode( file_get_contents( 'php://input' ) );

header('Access-Control-Allow-Origin: *'); 

// var_dump($jsonObject);

//CONNECT TO DB 
$con=mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    die ("Failed to connect to MySQL:");
}

//SAVE LOCATION
if (isset($jsonObject->device ) && isset($jsonObject->location))
{
	$locationObject = $jsonObject->location;
   $query = "INSERT INTO `Locations` (`id`, `device`, `timestamp`, `longitude`, `latitude`) VALUES (NULL, '" . $jsonObject->device ."', CURRENT_TIMESTAMP, '" . $locationObject->longitude . "', '". $locationObject->latitude . "' );";
   mysqli_query($con, $query);
}

//SAVE MESSAGES
if (isset($jsonObject->messages)){
	$receivedChatMessages = $jsonObject->messages;
	$counter = 0;
	
	//sort by timestamp 
	function cmp($a, $b)
	{
	    return $a->timestamp > $b->timestamp;
	}
	usort($receivedChatMessages, "cmp");
	
	foreach ($receivedChatMessages as $message) {    	
		$ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
		
		$latitudeString = "NULL";
		$longitudeString = "NULL";
		
		if (isset($jsonObject->device ) && isset($jsonObject->location))
		{
			$locationObject = $jsonObject->location;
			
			$latitudeString = $locationObject->latitude;
			$longitudeString = $locationObject->longitude;
		}
		
		$query = "INSERT INTO `ChatMessages` (`message`, `timestamp`, `ip`, `indentifier`, `longitude`, `latitude` ) VALUES ('" . $message->text . "', CURRENT_TIMESTAMP + " . $counter . ", '" . $ip ."', '" . $message->identifier ."'," . $latitudeString . ", " . $longitudeString . " ) ON DUPLICATE KEY UPDATE indentifier = indentifier;";
		mysqli_query($con, $query);
		$counter++;
	}
}


//PREPARE LOCATION OUTPUT
$resultLocations = mysqli_query($con, "SELECT * FROM Locations WHERE timestamp > (NOW() - INTERVAL 5 MINUTE)");

$locationsArray = [];

while ($row = mysqli_fetch_array($resultLocations)) {
    if (array_key_exists($row['device'], $locationsArray)) {
        if (strtotime($locationsArray[$row['device']]['timestamp']) < strtotime($row['timestamp'])) {
            $locationsArray[$row['device']] = array(
                'longitude' => $row['longitude'],
                'latitude' => $row['latitude'],
                'timestamp' => strtotime($row['timestamp']));
        }
    } else {
        $locationsArray[$row['device']] = array(
            'longitude' => $row['longitude'],
            'latitude' => $row['latitude'],
            'timestamp' => strtotime($row['timestamp']));
    }
}

//remove own location
if (isset($jsonObject->device) && array_key_exists($jsonObject->device, $locationsArray)) {
    unset($locationsArray[$jsonObject->device]);
}


//PREPARE MESSAGE OUTPUT
$resultChat = mysqli_query($con, "SELECT * FROM ChatMessages WHERE timestamp > ( NOW() - INTERVAL 30 MINUTE )");

$chatMessagesArray = [];

while ($row = mysqli_fetch_array($resultChat)) {
	$chatMessagesArray[$row['indentifier']] = array(
                'message' => $row['message'],
                'timestamp' => strtotime($row['timestamp']));
				
	if (isset($jsonObject->device ) && isset($jsonObject->location)){
		//TODO filter messages to far away	
		// $locationObject = $jsonObject->location;
		// $locationObject->longitude . "', '". $locationObject->latitude;
	}
}


//OUTPUT

echo "{";
echo "\"locations\" : ";

if ( empty($locationsArray) ){
	echo "{}";
}else{ 
	echo json_encode($locationsArray);
}

echo ", \"chatMessages\" : ";

if ( empty($chatMessagesArray) ){
	echo "{}";
}else {
	echo json_encode($chatMessagesArray);
}

echo "}";


//CLEANUP 
$query = "DELETE FROM `db539887603`.`Locations` WHERE timestamp < (NOW() - INTERVAL 5 MINUTE);";
mysqli_query($con, $query);

//CLOSE CONNECTION
mysqli_close($con);

?>