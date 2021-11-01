<?php

    class Validation {

        public $errors = array();

        /**
         * @var string $name
         */
        public function name($name) {

            $this->name = $name;

            return $this;
        }

        /**
         * @var mixed $value
         */
        public function value($value) {

            $this->value = $value;

            return $this;
        }

        public function is_email() {

            if (filter_var($this->value, FILTER_VALIDATE_EMAIL)) return $this;

            $this->errors[$this->name] = 'Not a valid email';

            return $this;
        }

        /**
         * @var int $value
         */
        public function min_length($num) {

            if (strlen($this->value) >= $num) return $this;

            $this->errors[$this->name] = "Must be longer than " . $num . " characters";

            return $this;
        }

        public function is_num() {
            
            if (is_int($this->value)) return $this;

            $this->errors[$this->name] = "Must be a number";

            return $this;
        }

        public function required() {

            if ($this->value !== "") return $this;

            $this->errors[$this->name] = 'This feild is required';

            return $this;
        }

        public function is_success() {

            if ( empty($this->errors) ) return true;
        }

        public function get_errors() {

            return $this->errors;
        }

    }

?>