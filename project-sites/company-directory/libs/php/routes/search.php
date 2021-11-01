<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header('Content-Type: application/json; charset=UTF-8');

    include_once("../Person.php");
    include_once("../Department.php");
    include_once("../Location.php");
    include_once("../Response.php");

    /**
     * 
     * search personnel
     * 
     * Must seperate the input string in order to search
     * both first and last name columns. The separation is on each 
     * space in the string, only the first two words are used in database search
     * 
     */

    $personData = [];
    $departmentData = [];
    $locationData = [];

    try {

        $personnelResult = Person::search( $_POST['search'] );
		while ($row = mysqli_fetch_assoc($personnelResult)) {
			array_push($personData, $row);
        }
        
        $deparmtentResult = Department::search( $_POST['search'] );
        while ($row = mysqli_fetch_assoc($deparmtentResult)) {
			array_push($departmentData, $row);	
        }

        $deparmtentResult = Location::search( $_POST['search'] );
        while ($row = mysqli_fetch_assoc($deparmtentResult)) {
			array_push($locationData, $row);	
        }

    } catch (Exception $err) {

        if ( $err -> getCode() !== 400) {

            header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');

            $prepareError = new Response( Response::HTTP_INTERNAL_SERVER_ERROR, "prepare statement error", $err -> getMessage() );
            $prepareError -> send();   
    
            exit;
        }

    }

    $allData = [
        "personnel" => $personData,
        "department" => $departmentData,
        "location" => $locationData,
    ];

	// Create final success response message
	$success = new Response();
	$success -> setData( $allData );
	$success -> send();

?>