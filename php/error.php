<?php
    class Error
    {
        public $isError;
        public $message;
        public $details;

        public function __construct($isError, $message, $details)
        {
            $this->isError = $isError;
            $this->message = $message;
            $this->details = $details;
        }

        public function output()
        {
            echo "<div class='error'>";
            echo "<p class='center'>" . $this->message . "</p>";
            echo "<output>" . $this->details . "</output>";
            echo "</div>";
        }
    }