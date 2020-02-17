<?php

// DO NOT MODIFY THIS FILE
// UNLESS YOU ARE USING MAMP (then you may need to specify a Port)

class connectionManager {

    public function getConnection() {
        
        $servername = 'localhost';
        $username = 'root';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
            $password = 'xOmKAo4AdZWh';
        } 
        else if ( strtoupper(substr(PHP_OS, 0, 3)) === 'DAR' ) {
            $password = "root";
        }
        else {
            $password = "";
        }

        $dbname = 'boss';
        $port = 3306;
        
        // Create connection
        /*$conn = new PDO("mysql:host=$servername;dbname=$dbname;port=$port", $username, $password);     
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // if fail, exception will be thrown*/

        // Create connection
        $conn = new PDO("mysql:host=$servername;port=$port", $username, $password);     
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // if fail, exception will be thrown

        $sql="use $dbname";
        $sql2="CREATE DATABASE IF NOT EXISTS $dbname;use $dbname";

        $stmt = $conn->prepare($sql);

        try {
            $stmt->execute();
        } 
        catch (Exception $e){
            $stmt2=$conn->prepare($sql2);
            $stmt2->execute();
            $stmt2=null;
        }
        $stmt=null;

        // Return connection object
        return $conn;
    }

}