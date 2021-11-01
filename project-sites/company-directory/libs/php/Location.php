<?php

    include_once("Validation.php");
    include_once("DB.php");

    class Location {

        // User input values
        private $name;
        private $locationID;

        // validation data
        private $validation;

        function __construct( $name = "", $locationID = null ) {

            $this -> validation = new Validation();

            $this -> setName( $name );
            if ( $locationID ) $this -> setLocationID( $locationID );

        }

        private function setName( $value ) {

            $this -> name = $value;

        }

        private function setLocationID( $value ) {

            $this -> locationID = $value;

        }

        /**
         * 
         * Check validation
         * 
         *              Check for validation on inputs, and return 
         *              results if necesary. 
         * 
         *              Also includes ability to check for duplicates,
         *              such as to prevernt copies of location name
         * 
         */

        public function validateInputs() {

            $this -> validation -> name("name") -> value($this -> name) -> required();

        }

        public function validateNameIsUnique() {

            $result = $this -> getName();

            if ( mysqli_num_rows( $result ) > 0 ) {

                $this -> validation -> errors['name'] = "This location already exists";

            }

        }

        public function hasValidationErrors() {

            return $this -> validation -> is_success() ? false : true;

        }

        public function getValidationErrors() {

            return $this -> validation -> get_errors();

        }

        /**
         * 
         * Create a prepared statement
         * 
         */

        public function insert() {

            $conn = DB::connect();

            $stmt = $conn -> prepare("INSERT INTO location (name) VALUES(?)");
            $stmt -> bind_param("s", $this -> name);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public function update() {

            $conn = DB::connect();

            $stmt = $conn -> prepare("UPDATE location SET name = ? WHERE id = ?");
            $stmt -> bind_param("si", $this -> name, $this -> locationID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public static function delete( $locationID = null ) {

            if ( $locationID === null ) throw new Exception( "Must include department id as agrument" );


            // Throw error if departments are dependent on location
            $result = Location::countDependencies( $locationID );

            if ( $result > 0 ) throw new Exception( "departments are dependent on this location", 400 );
            $conn = DB::connect();

            $stmt = $conn -> prepare("DELETE FROM location WHERE id = ?");
            $stmt -> bind_param("i", $locationID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public static function search( $input = null ) {

            if ( !$input ) throw new Exception("Input is empty", 400);

            $locationName = "%" . $input . "%";

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT id, name FROM location WHERE name LIKE ? LIMIT 5");
            $stmt -> bind_param("s", $locationName);
            $stmt -> execute();

            // Return data before closing connection
            $result = $stmt -> get_result();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        /**
         * 
         * Get data
         * 
         */

        public function getName() {

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT name FROM location WHERE name = ?");
            $stmt -> bind_param("s", $this -> name);
            $stmt -> execute();

            // Return data before closing connection
            $result = $stmt -> get_result();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        public static function getAll() {

            $conn = DB::connect();

            $result = $conn->query("SELECT l.id, l.name, COUNT(p.id) AS numOfPersonnel FROM location l LEFT JOIN department d ON (l.id=d.locationID) LEFT JOIN personnel p ON (d.id=p.departmentID) GROUP BY l.name");

            if (!$result) throw new Exception("issue with the query, cannot get all information");

            $conn -> close();

            return $result;

        }

        public static function getLocationByID( $locationID ) {

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT id, name FROM location WHERE id = ?");
            $stmt -> bind_param("i", $locationID);
            $stmt -> execute();

            // Return data before closing connection
            $result = $stmt -> get_result();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        public static function countDependencies( $locationID ) {

            $conn = DB::connect();

            $stmt = $conn -> prepare("SELECT count(id) as dependecies FROM department WHERE locationID = ?");
            $stmt -> bind_param("i", $locationID);
            $stmt -> execute();

            // Return data before closing connection
            $stmt -> bind_result($result);
            $stmt -> fetch();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;
        }

    }

?>