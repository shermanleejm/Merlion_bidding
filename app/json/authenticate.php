<?PHP
include_once "../include/autoload.php";
include_once "../include/token.php";
// include_once "../include/retrieveAdmin.php";



$status = "error";
$message = [];
$bidsDao = new BidsDAO();
$connmgr = new connectionManager();
$conn = $connmgr->getConnection();

$sql = 'create database if not exists boss;
use boss;

drop table if exists administrator;
create table if not exists administrator (
    user varchar(5) not null,
    pass varchar(255) not null
);
insert into administrator values ("admin", "$2y$10$nkVr36uzHah5/673m8gvqeZLEVXgM6GjfNfMfkirGS.NyFrcee8rK");

create table if not exists student (
    userid varchar(64) not null primary key,
    studentpassword varchar(64) not null,
    studentname varchar(64) not null,
    school varchar(100) not null,
    edollar decimal(6,2) not null
);
    TRUNCATE table student;

create table if not exists course (
    course varchar(64) not null,
    school varchar(8) not null,
    title varchar(64) not null,
    descr varchar(1000) not null,
    examdate date not null,
    examstart time not null,
    examend time not null,
    primary key (course, school, examdate, examstart, examend)
);
    TRUNCATE table course;

create table if not exists section (
    course varchar(64) not null,
    section varchar(64) not null,
    dayoftheweek int not null,
    starttime time not null,
    endtime time not null,
    instructor varchar(64) not null,
    venue varchar(64) not null,
    size int not null,
    minbid_r2 decimal(6,2) not null,
    primary key(course, section)
);
    TRUNCATE table section;

create table if not exists prerequisite (
    course varchar(64) not null,
    prerequisite varchar(64) not null,
    primary key (course, prerequisite)
);
    TRUNCATE table prerequisite;

create table if not exists course_completed (
    userid varchar(64) not null,
    code varchar(64) not null,
    primary key (userid, code)
);
    TRUNCATE table course_completed;

create table if not exists bid (
    userid varchar(64) not null,
    amount decimal(6,2) not null,
    code varchar(64) not null,
    section varchar(8) not null,
    primary key (userid, code)
);
    TRUNCATE table bid;

create table if not exists course_enrolled (
    userid varchar(64) not null,
    course varchar(64) not null,
    section varchar(64) not null,
    bidamount varchar(64) not null,
    round int not null
);
TRUNCATE TABLE course_enrolled;

create table if not exists course_unsuccessful (
    userid varchar(64) not null,
    course varchar(64) not null,
    section varchar(64) not null,
    bidamount varchar(64) not null,
    round int not null
);
TRUNCATE TABLE course_unsuccessful;

create table if not exists course_dropped (
    userid varchar(64) not null,
    course varchar(64) not null,
    section varchar(64) not null,
    bidamount varchar(64) not null,
    round int not null
);
TRUNCATE TABLE course_dropped;

create table if not exists bid_status (
    userid varchar(64) not null,
    course varchar(64) not null,
    section varchar(64) not null,
    amount varchar(64) not null,
    round int not null,
    status varchar(1000)
);
TRUNCATE TABLE course_dropped;';


// $sql = "SELECT * FROM boss.administrator;";
//     $connmgr = new connectionManager();
//     $conn = $connmgr->getConnection();
//     $q = $conn->query($sql);
//     foreach ($q as $line) {
//         $hash = $line["pass"] ;
//         $adminuser = $line["user"];
//     }

$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt = NULL;


if ( !isset($_POST["username"]) ) {
    $message[]="missing username";
}
else if ($_POST["username"]==""){
    $message[]="blank username";

}
if ( !isset($_POST["password"]) ) {
    $message[]="missing password";

}
elseif($_POST["password"]==""){
    $message[]="blank password";

}
if ( isset($_POST["username"]) && isset($_POST["password"]) && $_POST["password"]!="" && $_POST["username"]!="") {
    $user = strtolower($_POST["username"]);
    $pass = strtolower($_POST["password"]);

    $sql = "SELECT * from administrator;";
    //$connmgr = new connectionManager();
    //$conn = $connmgr->getConnection();
    foreach ( $conn->query($sql) as $q ) {
        $hash = $q["pass"];
        $adminuser = $q["user"];
    }
    $q = NULL;

    try {
        $bidsDao->getRound();
    } catch (Exception $e)
    {
        $sql2 = 'CREATE TABLE IF NOT EXISTS roundstatus(round varchar(16) not null PRIMARY KEY);
        delete from roundstatus;insert into roundstatus values (0)';

        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        $stmt2=null;
    }
    // $sql = "SELECT userid FROM student";
    // $listofstudentid = ["admin"];
    // $cm = new connectionManager();
    // $conn = $cm->getConnection();
    // foreach ($conn->query($sql) as $q) {
    //     $listofstudentid[]=$q["userid"];
    // }
    // if ( !in_array($user, $listofstudentid) ) {
    //     $message[]="invalid username";
    //     $message[]="invalid password";
    // }
    // else { 
    if ( strtolower($user) == "admin" ) {
        if ( password_verify($pass, $hash) ) {
            $status = "success";
            $token = generate_token($user);
            $output = ["status"=>$status, "token"=>$token];
            header("Content-Type: application/json");
            $result = json_encode($output, JSON_PRETTY_PRINT) ;
            echo $result;
            exit;
        }
        else {
            $message[]="invalid password";
        }
    }else{
        $message[]="invalid username";
    }
        // else {
        //     $sql = "SELECT studentpassword from student WHERE userid=:userid";
        //     $cm = new connectionManager();
        //     $conn = $cm->getConnection();
        //     $stmt = $conn->prepare($sql);
        //     $stmt->bindParam(":userid", $user, PDO::PARAM_STR);
        //     $stmt->execute();
        //     $studentpassword = $stmt->fetch()["studentpassword"];
        //     if ($pass == $studentpassword) {
        //         $status = "success";
        //         $token = generate_token($user);
        //         $output = ["status"=>$status, "token"=>$token];
        //         header("Content-Type: application/json");
        //         $result = json_encode($output, JSON_PRETTY_PRINT) ;
        //         echo $result;
        //         exit;
        //     }
        //     else {
        //         $message[]="invalid password";
        //     }
        // }
    // }
}


$output = ["status"=>$status, "message"=>$message];
header("Content-Type: application/json");
$result = json_encode($output, JSON_PRETTY_PRINT) ;
echo $result;

?>