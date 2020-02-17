<?PHP
# check: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTExIDA2OjQ5OjM2In0.4pQQmOZlfPcQBhYLVZYn2zXMRqg91P9bkQfZeVQyDoE
/*
#For missing course, section, bid, error on page

REQUEST:
    http://localhost/BOSS/json/delete-bid.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTExIDA2OjQ5OjM2In0.4pQQmOZlfPcQBhYLVZYn2zXMRqg91P9bkQfZeVQyDoE&r={"userid":"ada.goh.2012","course":"IS100","section":"S1"}

*/
include_once "../include/autoload.php";
include_once "../include/token.php";

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
    }else{
        array_push($message, "missing course");
        //array_push($message, "missing section");
        array_push($message, "missing userid");
        $status = "error";
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

        //verify "round ended"
        $roundStatus=$bidsDao->getRound();
        if ($roundStatus == 2 or $roundStatus == 4){
                array_push($message, "round ended");
                $status = 'error';
        }

            //verify "no such bid" 
            if(isset($userid) && isset($section) && isset($course) && $validity == TRUE && ($roundStatus==1 or $roundStatus==3)){
                $deletedBids = array($course, $section);
                $allBidsCodeSection = $bidsDao->getBidsByCodeSection($deletedBids[0], $deletedBids[1]);
                if (!array_key_exists($userid, $allBidsCodeSection)){ //no value
                    array_push($message, "no such bid");
                    $status= 'error';
                }

                if (sizeof($message) < 1){
                    $temp=$bidsDao->getbidsamount($userid,$deletedBids[0],$deletedBids[1]);
                    $arrForDeleteBid[] = array($deletedBids[0], $temp);
                    if ($success=$bidsDao->deleteBid($userid,$arrForDeleteBid)){
                        $status = "success";
                        $output = ["status"=>$status];
                        header("Content-Type: application/json");
                        $result = json_encode($output, JSON_PRETTY_PRINT) ;
                        // $result1 = str_replace('[', '{', $result);
                        // $result2 = str_replace(']', '}', $result1);
                        echo $result;
                        exit();
                    }  
                }
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