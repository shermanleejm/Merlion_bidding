<?php
include_once "../include/autoload.php";
include_once "../include/token.php";
include_once "../include/createBidStatus.php";

# test http://localhost/app/json/bid-dump.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTExIDA2OjMzOjE1In0.kgRm44FzJYeITti2XvjzAV4nhnTnf8579ukB5wto1Ag&r={"course":"IS100","section":"S1"}

$status = "success";
$bids = [];
$message = [];
$sql = "SELECT round FROM roundstatus";
$cm = new connectionManager();
$conn = $cm->getConnection();
$q = $conn->query($sql);
$roundstatus = $q->fetch()["round"];
$bidsDao = new BidsDAO();

if (!isset($_GET["token"])) {
    $status = "error";
    $message[]="missing token";
    $output = ["status"=>$status, "message"=>$message];
    header("Content-Type: application/json");
    $result = json_encode($output, JSON_PRETTY_PRINT) ;
    echo $result;
    exit;
}elseif ( $_GET['token'] == ''){
    $message[]= "blank token";
    $status = "error";
    $output = ["status"=>$status, "message"=>$message];
    header("Content-Type: application/json");
    $result = json_encode($output, JSON_PRETTY_PRINT) ;
    echo $result;
    exit;
}
else {
    if ( strtolower(verify_token($_GET["token"])) != "admin" ) {
        $status = "error";
        $message[]="invalid token";
        $output = ["status"=>$status, "message"=>$message];
        header("Content-Type: application/json");
        $result = json_encode($output, JSON_PRETTY_PRINT) ;
        echo $result;
        exit;
    }
    else {
        if (!isset($_GET["r"])) { 
            $status = "error";
            $message[]= "missing course";
            // $message[]= "missing section";
            $output = ["status"=>$status, "message"=>$message];
            header("Content-Type: application/json");
            $result = json_encode($output, JSON_PRETTY_PRINT) ;
            echo $result;
            exit;
        }
        else {
            $r = json_decode($_GET["r"], TRUE);
            if ( !isset($r["course"]) ) {
                $status = "error";
                $message[]="missing course";
                $output = ["status"=>$status, "message"=>$message];
                header("Content-Type: application/json");
                $result = json_encode($output, JSON_PRETTY_PRINT) ;
                echo $result;
                exit();
            }elseif($r["course"] == ''){
                $status = "error";
                $message[]="blank course";
                $output = ["status"=>$status, "message"=>$message];
                header("Content-Type: application/json");
                $result = json_encode($output, JSON_PRETTY_PRINT) ;
                echo $result;
                exit();
            }

            $course = $r['course'];
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
                $status = "error";
                $message[]="invalid course";
                $output = ["status"=>$status, "message"=>$message];
                header("Content-Type: application/json");
                $result = json_encode($output, JSON_PRETTY_PRINT) ;
                echo $result;
                exit();
            }
    
           
            if ( !isset($r["section"]) ) {
                $status = "error";
                $message[]="missing section";
                $output = ["status"=>$status, "message"=>$message];
                header("Content-Type: application/json");
                $result = json_encode($output, JSON_PRETTY_PRINT) ;
                echo $result;
                exit();
            }elseif( $r["section"] == ''){
                $status = "error";
                $message[]="blank section";
                $output = ["status"=>$status, "message"=>$message];
                header("Content-Type: application/json");
                $result = json_encode($output, JSON_PRETTY_PRINT) ;
                echo $result;
                exit();
            }

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
                $status = "error";
                $message[]="invalid section";
                $output = ["status"=>$status, "message"=>$message];
                header("Content-Type: application/json");
                $result = json_encode($output, JSON_PRETTY_PRINT) ;
                echo $result;
                exit();
            }
            else {
                $status = "success";
                $courseq = $r["course"];
                $sectionq = $r["section"];
                if ( $roundstatus == 0 || $roundstatus == 1 || $roundstatus == 3 ) {
                    $sql = "SELECT * FROM bid WHERE code=:course AND section=:section ORDER BY amount DESC, userid ASC";
                    $cm = new connectionManager();
                    $conn = $cm->getConnection();
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":course", $courseq, PDO::PARAM_STR);
                    $stmt->bindParam(":section", $sectionq, PDO::PARAM_STR);
                    $stmt->execute();
                    $row = 1;
                    foreach ($stmt->fetchAll() as $q) {
                        $temp = [];
                        $temp["row"] = $row;
                        $temp["userid"] = $q["userid"];
                        $temp["amount"] = (float)$q["amount"];
                        $temp["result"] = "-";
                        $bids[]=$temp;
                        $row ++;
                    }
                }
                else if ( $roundstatus == 2 || $roundstatus == 4 ) {
                    $tempbids = [];
                    createBidStatus();
                    $sql = "SELECT * FROM bid_status WHERE course=:course AND section=:section ORDER BY amount DESC, userid ASC";
                    $cm = new connectionManager();
                    $conn = $cm->getConnection();
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":course", $courseq, PDO::PARAM_STR);
                    $stmt->bindParam(":section", $sectionq, PDO::PARAM_STR);
                    $stmt->execute();
                    $row = 1;
                    foreach ($stmt->fetchAll() as $q) {
                        $temp = [];
                        $temp["row"] = $row;
                        $temp["userid"] = $q["userid"];
                        $temp["amount"] = (float)$q["amount"];
                        $temp["result"] = $q["status"];
                        $bids[]=$temp;
                        $row ++;
                    }
                }
                $output = ["status"=>$status, "bids"=>$bids];
                header("Content-Type: application/json");
                $result=json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                echo $result;
                exit();
            }
        }

    }
}


?>