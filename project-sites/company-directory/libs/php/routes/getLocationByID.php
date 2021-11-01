<?php

	header('Content-Type: application/json; charset=UTF-8');

	include_once("../Location.php");
	include_once("../Response.php");

	$data = [];

	try {

		$result = Location::getLocationByID( $_GET["id"] );

		while ($row = mysqli_fetch_assoc($result)) {

			array_push($data, $row);
	
		}

		$numOfDependecies = Location::countDependencies( $_GET["id"] );

		if ( $numOfDependecies > 0 ) {
			$data['hasDependecies'] = true;
		} else {
			$data['hasDependecies'] = false;
		}

	} catch (Exception $err) {

		$error = new Response(500, "unsuccesful query", "cannot get department");
		$error -> send();

	}
   
	$success = new Response();
	$success -> setData( $data );
	$success -> send();

?>