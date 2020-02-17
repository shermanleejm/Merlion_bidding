<?php
include_once "../include/autoload.php";
include_once "../include/token.php";

//manual link = http://localhost/project-g7t2/app/json/user-dump.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTEyIDA2OjA4OjA4In0.JkfkTMbrHYa2GvBNI2h_fkerq1fRUQYvP4b5idUjv2I&r={"userid":"ben.ng.2009"}

$status = "success";
$message = [];
$bidsDao = new BidsDAO();

$validity = FALSE;

if(isset($_GET["token"])){
    $token = $_GET["token"];
    if($token == ''){
        array_push($message, "blank token");
        $status = 'error';
    }
    else if (strtolower(verify_token($_GET["token"])) == "admin"){
        if(isset($_GET['r'])){
            
            $r = json_decode($_GET['r'], TRUE);
            //for course//
            if(!isset($r['course'])){
                array_push($message, "missing course");
                $status = 'error';
            }else if($r['course'] == ''){
                array_push($message, "blank course");
                $status = 'error';
            }else{ 
            $result = [];
            $course = $r['course'];
            $connMgr = new connectionManager();
            $conn=$connMgr->getConnection();
            $sql0 = " SELECT * FROM course";
            $stmt = $conn->prepare($sql0);
            $stmt->execute();
            while($row = $stmt->fetch()) {
                $result[]=$row['course'];
            }
    
            if(!in_array($course,$result)){
                array_push($message,"invalid courseid");
                $status = "error";
            }else{
                //check for section//
                $course = $r['course'];
                if(!isset($r['section'])){
                    array_push($message, "missing section");
                    $status = 'error';
                }else if($r['section'] == ''){
                    array_push($message, "blank section");
                    $status = 'error';
                }else{ 
                $sections = [];
                $section = $r['section'];
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql1 = " SELECT * FROM section WHERE course=:course";
                $stmt = $pdo->prepare($sql1);
                $stmt->bindParam(':course',$course,PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    $sections[]=$row['section'];
                }
    
                if(!in_array($section,$sections)){
                    array_push($message,"invalid section");
                    $status = "error";
                }else{
                    $section = $r['section'];
                    $validity = TRUE;
                }
                }
            }
            }

          
            //for userid//
            if(!isset($r['userid'])){
                array_push($message, "missing userid");
                $status = 'error';
            }else if($r['userid'] == ''){
                array_push($message, "blank userid");
                $status = 'error';
            }else{ 
            $userid = $r['userid'];
            $resultss = [];
            $connMgr = new connectionManager();
            $pdo=$connMgr->getConnection();
            $sql2 = " SELECT * FROM student " ;
            $stmt = $pdo->prepare($sql2);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            while($row = $stmt->fetch()) {
                $resultss[]=$row['userid'];
            }

            if(!in_array($userid,$resultss)){
                array_push($message,"invalid userid");
                $status = "error";
                $validity = FALSE;
            }else{
                $userid = $r['userid'];
                if($validity == TRUE){
                    $validity = TRUE;
                }
            }
            }
            //checkround
            $connMgr = new connectionManager();
            $conn=$connMgr->getConnection();
            $sql = "SELECT * FROM roundstatus";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            while($row = $stmt->fetch()){
                $roundstatus= $row['round'];
            }
            if($roundstatus % 2 == 0){
                array_push($message,"round not active");
                $status = "error";
                $output = ["status"=>$status,"message"=>$message];
                header("Content-Type: application/json");
                echo json_encode($output, JSON_PRETTY_PRINT);
                exit();
            }


            if(isset($userid) && isset($section) && isset($course) && $validity == TRUE){
                
                $resultArr = [];
                $connMgr = new connectionManager();
                $conn=$connMgr->getConnection();
                $sql = "SELECT * FROM course_enrolled WHERE userid = :userid and course = :course and section = :section";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':userid',$userid,PDO::PARAM_STR);
                $stmt->bindParam(':course',$course,PDO::PARAM_STR);
                $stmt->bindParam(':section',$section,PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    $resultArr=[trim($row['userid']), trim($row['course']), trim($row['section']), trim($row['bidamount']), trim($row['round'])];
                }

                if($resultArr == []){
                    //array_push($message,"no such enrollment record");
                    $status = "success";
                    $output["status"]=$status;
                    header("Content-Type: application/json");
                    echo json_encode($output, JSON_PRETTY_PRINT);
                    exit();
                }else{
                    $bidamt = $resultArr[3];
                    $bidsDao->removeEnrolledCourse($userid,$course);
                    $currentedollar = $bidsDao->getUserEDollars($userid);
                    $newedollar = $bidamt + $currentedollar;
                    $bidsDao->setUserEDollars($userid, $newedollar);
                    $newvacancy = $bidsDao->getVacancy($course, $section) + 1;
                    $bidsDao->setVacancy($newvacancy, $course, $section);
                    $output = ["status"=>$status];
                    header("Content-Type: application/json");
                    echo json_encode($output, JSON_PRETTY_PRINT);
                    exit();
                }
            }

        }else{
                array_push($message,"missing course");
                //array_push($message,"missing section");
                array_push($message,"missing userid");
                $status='error';
        }

}else{
    array_push($message, "invalid token");
    $status = 'error';
}
}else{
    array_push($message, "missing token");
    $status = 'error';
}


// header('Content-Type: application/json');
$output = ["status"=>$status,"message"=>$message];
header("Content-Type: application/json");
echo json_encode($output, JSON_PRETTY_PRINT);

?>
    