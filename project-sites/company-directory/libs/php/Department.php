<?php

    include_once("Validation.php");
    include_once("DB.php");

    class Department {

        // department input values
        private $name;
        private $locationID;
        private $departmentID;

        // validation data
        private $validation;

        function __construct( $name = "", $locationID = "", $departmentID = null ) {

            $this -> validation = new Validation();

            $this -> setName( $name );
            $this -> setLocation( $locationID );

            // added to property only when updating department
            if ( $departmentID !== null ) $this -> departmentID = $departmentID;

        }

        /**
         * 
         * setName, setLocation
         * 
         *              All methods executed on constructor function
         * 
         */

        private function setName( $value ) {

            $this -> name = $value;

        }

        private function setLocation( $value ) {

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
         *              such as to prevernt copies of department name.
         * 
         */

        public function validateInputs() {

            $this -> validation -> name("name") -> value( $this -> name) -> min_length(2) -> required();
            $this -> validation -> name("locationID") -> value( $this -> locationID) -> required();

        }

        public function nameUniqueInLocation() {

            $result = $this -> countUniqueNameInLocation();

            if ( $result > 0 ) {

                $this -> validation -> errors['name'] = "This department name already exists in this location";

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
         *              Both creates and deletes all connections
         *              and executes the data, no need to do seperatly
         * 
         */

        public function insert() {

            $conn = DB::connect();

            $stmt = $conn -> prepare("INSERT INTO department (name, locationID) VALUES(?, ?)");
            $stmt -> bind_param("si", $this -> name, $this -> locationID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public function update() {

            $conn = DB::connect();

            $stmt = $conn -> prepare("UPDATE department SET name = ?, locationID = ? WHERE id = ?");
            $stmt -> bind_param("sii", $this -> name, $this -> locationID, $this -> departmentID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public static function delete( $departmentID = null ) {

            if ( $departmentID === null ) throw new Exception( "Must include department id as agrument" );


            $result = Department::countDependencies( $departmentID );
            if ( $result > 0 ) throw new Exception( "personnel are dependent on this department", 400 );

            $conn = DB::connect();

            $stmt = $conn -> prepare("DELETE FROM department WHERE id = ?");
            $stmt -> bind_param("i", $departmentID);
            $stmt -> execute();

            $conn -> close();
            $stmt -> close();

        }

        public static function search( $input = null ) {

            if ( !$input ) throw new Exception("Input is empty", 400);

            $departmentName = "%" . $input . "%";

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT id, name FROM department WHERE name LIKE ? LIMIT 5");
            $stmt -> bind_param("s", $departmentName);
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
         * 
         */

        public function countUniqueNameInLocation() {

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT COUNT(id) FROM department WHERE name = ? AND locationID = ?");
            $stmt -> bind_param("si", $this -> name, $this -> locationID);
            $stmt -> execute();

            // Return data before closing connection
            $stmt -> bind_result($result);
            $stmt -> fetch();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        public static function getDepartmentByID( $departmentID ) {

            $conn = DB::connect();

            // Begin prepare statement
            $stmt = $conn -> prepare("SELECT id, name, locationID FROM department WHERE id = ?");
            $stmt -> bind_param("i", $departmentID);
            $stmt -> execute();

            // Return data before closing connection
            $result = $stmt -> get_result();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        public static function countDependencies( $departmentID = null ) {

            if ( $departmentID === null ) throw new Exception("Must include department id as argument");

            $conn = DB::connect();

            $stmt = $conn -> prepare("SELECT count(id) as dependecies FROM personnel WHERE departmentID = ?");
            $stmt -> bind_param("i", $departmentID);
            $stmt -> execute();

            // Return data before closing connection
            $stmt -> bind_result($result);
            $stmt -> fetch();

            // Close both connection
            $stmt -> close();
            $conn -> close();

            return $result;

        }

        public static function getAll() {

            $conn = DB::connect();

            $result = $conn -> query("SELECT department.id, department.name, location.name as locationName FROM department LEFT JOIN location ON department.locationID=location.id");

            $conn -> close();

            return $result;

        }


    }

?>