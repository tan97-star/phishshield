<?php
class databasePhishShield {
    
    private $servername = ""; 
    private $username = "";              
    private $password = ""; 
    private $dbname = "";    
    
    public $conn;

    public function __construct() {
        // Bina sambungan secara automatik
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}

// Kekalkan pemboleh ubah $conn untuk index.php
$db_obj = new databasePhishShield();
$conn = $db_obj->conn; 
?>