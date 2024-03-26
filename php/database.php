<?php
    class Database {
        private static $instance = null;
        public $conn;

        private $host = 'localhost';
        private $user = 'Admin';
        private $pass = 'P4ssw0rd!!';
        private $database = 'COP4710';
        
        private function __construct()
        {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->database);

            if ($this->conn->connect_error)
            {
                $retValue = '{"error":"' . $this->conn->connect_error . '"}';
		        header('Content-type: application/json');
                echo $retValue;
                exit();
            }
        }

        public static function getInstance()
        {
            if (self::$instance == null)
            {
                self::$instance = new Database();
            }
        
            return self::$instance;
        }
    }
    
    $database = Database::getInstance();
    $conn = $database->conn;

    function closeConnection()
    {
        global $conn;
        $conn->close();
        die(0);
    }

    function console_log($data) {
        $output = $data;
        if (is_array($output))
            $output = implode(',', $output);
    
        echo "<script>console.log('PHP: " . $output . "' );</script>";
    }