<?php
include_once "../include/autoload.php";
include_once "../include/token.php";
include_once "../include/createBidStatus.php";
# manual checking: http://localhost/app/json/dump.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTExIDA2OjMzOjE1In0.kgRm44FzJYeITti2XvjzAV4nhnTnf8579ukB5wto1Ag

$tables = ["course", "section", "student", "prerequisite", "bid", "course_completed", "course_enrolled" ];
$status = "success";
$message = [];
$sql = "SELECT round FROM roundstatus";
$cm = new connectionManager();
$conn = $cm->getConnection();
$q = $conn->query($sql);
$roundstatus = $q->fetch()["round"];

if ( isset($_GET["token"]) ) {
    $token = $_GET["token"];
    if ( $_GET['token'] == ''){
        array_push($message, "missing token");
        $status = "error";
    }
    elseif ( strtolower(verify_token($_GET["token"])) == "admin" ) {

        //course
        $course = [];
        $sql = "select * from boss.course order by course;";
        $cm = new connectionManager();
        $conn = $cm->getConnection();
        foreach ( $conn->query($sql) as $q ) {
            $temp = [];
            $date = str_replace("-","",$q["examdate"]);
            $start = ltrim(substr(str_replace(":","",$q["examstart"]), 0, -2), "0") ;
            $end = ltrim(substr(str_replace(":","",$q["examend"]), 0, -2), "0") ;
            $temp["course"] = $q["course"];
            $temp["school"] = $q["school"];
            $temp["title"] = $q["title"];
            $temp["description"] = $q["descr"];
            $temp["exam date"] = $date;
            $temp["exam start"] = $start;
            $temp["exam end"] = $end;
            array_push($course, $temp);
        }

        //section
        $section = [];
        $sql = "SELECT * FROM boss.section ORDER BY course, section;";
        foreach ($conn->query($sql) as $q) {
            $temp = [];
            $temp["course"] = $q["course"];
            $temp["section"] = $q["section"];
            $temp["day"] = convertDay($q["dayoftheweek"]);
            $temp["start"] = convertTime($q["starttime"]);
            $temp["end"] = convertTime($q["endtime"]);
            $temp["instructor"] = $q["instructor"];
            $temp["venue"] = $q["venue"];
            $temp["size"] = (int)$q["size"];
            array_push($section, $temp);
        }

        //student
        $student = [];
        $sql = "SELECT * FROM boss.student ORDER BY userid";
        foreach ( $conn->query($sql) as $q ) {
            $temp = [];
            $temp["userid"] = $q["userid"];
            $temp["password"] = $q["studentpassword"];
            $temp["name"] = $q["studentname"];
            $temp["school"] = $q["school"];
            $temp["edollar"] = (float)$q["edollar"];
            array_push($student, $temp);
        }

        //prerequisite
        $prerequisite = [];
        $sql = "SELECT * FROM boss.prerequisite ORDER BY course, prerequisite";
        foreach ( $conn->query($sql) as $q ) {
            $temp = [];
            $temp["course"] = $q["course"];
            $temp["prerequisite"] = $q["prerequisite"];
            array_push($prerequisite, $temp);
        }

        //bid
        $bid = [];
        if ( $roundstatus == 0 || $roundstatus == 1 || $roundstatus == 3 ) {
            $sql = "SELECT * FROM bid ORDER BY code ASC, section ASC, amount DESC, userid ASC ";
            $conn = $cm->getConnection();
            foreach ($conn->query($sql) as $q) {
                $temp = [];
                $temp["userid"] = $q["userid"];
                $temp["amount"] = +number_format($q["amount"], 1, ".", " ");
                $temp["course"] = $q["code"];
                $temp["section"] = $q["section"];
                $bid[]=$temp;
            }
        }
        else if ( $roundstatus == 2) {
            createBidStatus();
            $sql = "SELECT * FROM bid_status WHERE round=1 ORDER BY course ASC, section ASC, amount DESC, userid ASC ";
            $conn = $cm->getConnection();
            foreach ($conn->query($sql) as $q) {
                $temp = [];
                $temp["userid"] = $q["userid"];
                $temp["amount"] = number_format($q["amount"], 1, ".", " ");
                $temp["course"] = $q["course"];
                $temp["section"] = $q["section"];
                $bid[]=$temp;
            }
        }
        else if ( $roundstatus == 4) {
            createBidStatus();
            $sql = "SELECT * FROM bid_status WHERE round=3 ORDER BY course ASC, section ASC, amount DESC, userid ASC ";
            $conn = $cm->getConnection();
            foreach ($conn->query($sql) as $q) {
                $temp = [];
                $temp["userid"] = $q["userid"];
                $temp["amount"] = $q["amount"];
                $temp["course"] = $q["course"];
                $temp["section"] = $q["section"];
                $bid[]=$temp;
            }
        }

        //course_completed
        $course_completed = [];
        $sql = "SELECT * FROM boss.course_completed ORDER BY code, userid";
        foreach ( $conn->query($sql) as $q ) {
            $temp = [];
            $temp["userid"] = $q["userid"];
            $temp["course"] = $q["code"];
            array_push($course_completed, $temp);
        }

        //section_student
        $section_student = [];
        $sql = "SELECT * FROM boss.course_enrolled ORDER BY course, userid";
        foreach ( $conn->query($sql) as $q ) {
            $temp = [];
            $temp["userid"] = $q["userid"];
            $temp["course"] = $q["course"];
            $temp["section"] = $q["section"];
            $temp["amount"] = (float)$q["bidamount"];
            array_push($section_student, $temp);
        }
    }
    else {
        array_push($message, "invalid token");
        $status = "error";
    }
}
else {
    array_push($message, "missing token");
    $status = "error";
}

$output = ["status"=>$status, "course"=>$course, "section"=>$section, "student"=>$student, "prerequisite"=>$prerequisite, "bid"=>$bid, "completed-course"=>$course_completed, "section-student"=>$section_student];

//var_dump($output);
header('Content-Type: application/json');
echo(json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION));
//echo json_last_error_msg(); // Print out the error if any
//die(); // halt the script

// $result1 = str_replace('[', '{', $result);
// $result2 = str_replace(']', '}', $result1);

function convertTime($timeStr){
    $exploded=explode(":", $timeStr);
    $exploded[0]=ltrim($exploded[0], "0");
    return $exploded[0].$exploded[1];
}

function convertDay($day){
    switch ($day) {
        case 1:
            return "Monday";
            break;
        case 2:
            return "Tuesday";
            break;
        case 3:
            return "Wednesday";
            break;
        case 4:
            return "Thursday";
            break;    
        case 5:
            return "Friday";
            break;
    }
}
?>