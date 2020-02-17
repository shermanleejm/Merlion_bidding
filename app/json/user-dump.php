<?php
include_once "../include/autoload.php";
include_once "../include/token.php";
//manual link = http://localhost/app/json/user-dump.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFteS5uZy4yMDA5IiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTIxIDE1OjA2OjUxIn0.fmfjCOr1OF6JZiYakrW1y7xG5YmHsseHx9NxhKwEftIr={"userid":"amy.ng.2009"}
// $tables = ["userid", "studentpassword", "studentname", "school", "edollar"];
$status = "error";
$msg = [];

if ( !isset($_GET["token"]) ) {
    $msg[]= "missing token";
}elseif ( $_GET['token'] == ''){
    $msg[]= "blank token";
}
else {
    $token = $_GET["token"];
    if ( strtolower(verify_token($token)) !== "admin" ) {
        $msg[]= "invalid token";
    }
    else {
        if ( !isset($_GET["r"]) ) {
            $msg[]= "missing userid";
        }
        else {
            $r = json_decode($_GET["r"], TRUE);
            if ( empty($r) ) {
                $msg[]= "missing userid";
            }elseif($r['userid'] == ''){
                $msg[]= "blank userid";
            }
            else { 
                $userid = $r["userid"];
                $alluserid = [];
                $sql = "SELECT userid FROM boss.student";
                $cm = new connectionManager();
                $conn = $cm->getConnection();
                foreach ( $conn->query($sql) as $q ) {
                    $alluserid[]=$q["userid"];
                }
                if ( !in_array($userid, $alluserid) ) {
                    $msg[]= "invalid userid";
                }
                else {
                    $status = "success";
                    $sql = "SELECT * FROM boss.student WHERE userid=:userid";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
                    $stmt->execute();
                    foreach ( $stmt->fetchAll() as $q ) {
                        $userid = $q["userid"];
                        $studentpassword = $q["studentpassword"];
                        $studentname = $q["studentname"];
                        $school = $q["school"];
                        $edollar = (float)$q["edollar"];
                        $edollar = floatval(number_format($edollar, 2, '.', ''));

                        
                    }
                    $output = [ 
                        "status"=>$status, 
                        "userid"=>$userid,
                        "password"=>$studentpassword,
                        "name"=>$studentname,
                        "school"=>$school,
                        "edollar"=>$edollar
                    ];
                    header("Content-Type: application/json");
                    $result = json_encode($output,JSON_PRETTY_PRINT|JSON_PRESERVE_ZERO_FRACTION) ;
                    echo $result;
                    exit;
                }
            }
        }
    }
}
$output = [
    "status"=>$status,
    "message"=>$msg
];
header("Content-Type: application/json");
$result = json_encode($output, JSON_PRETTY_PRINT) ;
echo $result;
exit;
?>
    