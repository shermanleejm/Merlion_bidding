<?php

require_once 'autoload.php';

class studentDAO {

    public  function retrieve($userid) {
        $sql = 'select userid, studentpassword, studentname, school, edollar from student where userid=:userid';
        
        $connMgr = new connectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new student($row['userid'], $row['studentpassword'], $row['studentname'], $row['school'], $row['edollar']);
        }
    }

        public function add($student) {
            $sql = "INSERT IGNORE INTO student (userid, studentpassword, studentname, school, edollar) VALUES (:userid, :studentpassword, :studentname, :school, :edollar)";
    
            $connMgr = new connectionManager();      
            $conn = $connMgr->getConnection();
            $stmt = $conn->prepare($sql);
            
            $student->password = password_hash($student->password,PASSWORD_DEFAULT);
    
            $stmt->bindParam(':userid', $student->userid, PDO::PARAM_STR);
            $stmt->bindParam(':studentpassword', $student->studentpassword, PDO::PARAM_STR);
            $stmt->bindParam(':studentname', $student->studentname, PDO::PARAM_STR);
            $stmt->bindParam(':school', $student->school, PDO::PARAM_STR);
            $stmt->bindParam(':edollar', $student->edollar, PDO::PARAM_STR);
    
    
            $status = False;
            if ($stmt->execute()) {
                $status = True;
            }
    
            return $status;
        }
    }












?>