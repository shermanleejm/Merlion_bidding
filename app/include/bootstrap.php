<?php
require_once "connectionManager.php";


function doBootstrap() {
    $linkmanager = new connectionManager();  
    $conn = $linkmanager->getConnection();
    //session_start();
    function delete_files($dirname) {
        if (is_dir($dirname))
          $dir_handle = opendir($dirname);
    if (!$dir_handle)
         return false;
    while($file = readdir($dir_handle)) {
          if ($file != "." && $file != "..") {
               if (!is_dir($dirname."/".$file))
                    unlink($dirname."/".$file);
               else
                    delete_files($dirname.'/'.$file);
          }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
}
    
    if (!file_exists('../resources/temp')) {
        mkdir('../resources/temp', 0777, true);
    }
    else if (file_exists('../resources/temp')) {
        // var_dump(file_exists('../resources/temp'));
        delete_files('../resources/temp');
        mkdir('../resources/temp', 0777, true);
    }

    $errors = array();
    $zip_file = $_FILES["bootstrap-file"]["tmp_name"];
    $temp_dir = sys_get_temp_dir();
    
    // $temp_dir = '../resources/temp';
    
    $zip = new ZipArchive;
    if ($zip->open($zip_file)) {
        $zip->extractTo($temp_dir);
        $zip->close();
    }

    $acceptablefilenames = ["student", "course", "section", "prerequisite", "course_completed", "bid"];
    $filesdone = [];
    $foldername = explode(".", $_FILES["bootstrap-file"]["name"]);
    $folder_location = $temp_dir . $foldername[0];
    $check_foldername = $temp_dir . "/" . $foldername[0];
    if (!file_exists($check_foldername)) {
        $temp_dir = sys_get_temp_dir();
    }
    else {
        $temp_dir = sys_get_temp_dir() . "/" . $foldername[0];
    }
    
    $count = 0;

    foreach ($acceptablefilenames as $name) {
        // if (empty(${"$name" . "_path"} = "$temp_dir/$name" . ".csv")) {
        //     $error = $name . ".csv not found";
        //     $_SESSION["fileerrors"][]= $error;
        //     $error = "";
        // }

        ${"$name" . "_path"} = "$temp_dir/$name" . ".csv";

        if ( file_exists(${"$name" . "_path"}) === FALSE ) {
            $error = $name . ".csv not found";
            $_SESSION["fileerrors"][]= $error;
            // fclose(${"$name"});
            // unlink(${"$name" . "_path"});
        }
        else{
            ${"$name" . "_file"} = fopen(${"$name" . "_path"}, "r");
            $filesdone[]=$name;
            $count ++;
        }      
    }

    if ($count != 6) {
        return FALSE;
        exit;
    }
   
    $sql = "create database if not exists boss;
    use boss;

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
TRUNCATE TABLE course_dropped;

    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    // mysqli_execute($stmt);

    // ERRROR CHECKING
    $_SESSION["bootstraperrors"] = array();
    $bidlines = 0;
    $courselines = 0; 
    $course_completedlines = 0;
    $prerequisitelines = 0;
    $sectionlines = 0;
    $studentlines = 0;


    // STUDENT
    $name = "student";
    $filename = $name . ".csv";
    ${"$name" . "_path"} = "$temp_dir/$name" . ".csv";
    $name = fopen(${"$name" . "_path"}, "r");
    // print_r (fgetcsv(${"$name" . "_file"}));
    $line = fgetcsv($name);
    $linecount = 2;
    $properlines = 0;
    $templistofuserid = [];
    $existinguserid = array();
    while ( ($tline = fgetcsv($name)) == TRUE ) {
        $line = [];
        $emptyfields = FALSE;
        for ($i=0;$i<count($tline);$i++) {
            $ch = $tline[$i];
            if (empty($ch)) {
                $emptyfields = TRUE;
                if (empty($line[4])) {
                    $lineerrormessage[]="blank e-dollar";
                }
                if (empty($line[3])) {
                    $lineerrormessage[]="blank school";
                }
                if (empty($line[1])) {
                    $lineerrormessage[]="blank password";
                }
                if (empty($line[0])) {
                    $lineerrormessage[]="blank userid";
                }
                if (empty($line[2])) {
                    $lineerrormessage[]="blank name";
                }
                if (empty($line[5])) {
                    $lineerrormessage[]="blank start";
                }
                if (empty($line[6])) {
                    $lineerrormessage[]="blank end";
                }
            }
            $line[] = trim($ch); 
        }
        $lineerrormessage = array();

        if ($emptyfields == FALSE) { 
            if (strpos(".", $line[4]) !== FALSE) {
                $decimalplaces = explode(".", $line[4])[1];
                if (strlen($decimalplaces) > 2) {
                    $lineerrormessage[]="invalid e-dollar";
                }
            }
            if ( !in_array($line[0], $templistofuserid) ) {
                $templistofuserid[]=$line[0];
            }
            else {
                $lineerrormessage[]="duplicate userid";
            }
            if ( strlen($line[0]) > 128 ) {
                $lineerrormessage[]="invalid userid";
            }
            if ( strlen($line[1]) > 128 ) {
                $lineerrormessage[]="invalid password";
            }
            if ( strlen($line[2]) > 100 ) {
                $lineerrormessage[]="invalid name";
            }
            
            if ( ($line[4] < 0) || !is_numeric($line[4])) {
                $lineerrormessage[]="invalid e-dollar";
            }
            else {
                if ( strpos($line[4], ".") !== FALSE ) {
                    $edollar = explode(".", $line[4]);
                    if (strlen($edollar[1]) > 2) {
                        $lineerrormessage[]="invalid e-dollar";
                    }
                }
            }
        }
        if (!empty($lineerrormessage)) {
            sort($lineerrormessage);
            $_SESSION["bootstraperrors"][]= array(
                "file"=>$filename , 
                "line"=>$linecount ,
                "message"=>$lineerrormessage
            );
        }
        else {
            $properlines++;
            // $hash = password_hash($line[1], PASSWORD_DEFAULT);
            $hash = $line[1];
            $existinguserid[]=$line[0];
            $stmt = $conn->prepare("use boss;
            INSERT INTO student 
            VALUES (:userid, :password, :name, :school, :edollar);
            ");
            $stmt->bindParam(":userid", $line[0], PDO::PARAM_STR);
            $stmt->bindParam(":password", $hash, PDO::PARAM_STR);
            $stmt->bindParam(":name", $line[2], PDO::PARAM_STR);
            $stmt->bindParam(":school", $line[3], PDO::PARAM_STR);
            $stmt->bindParam(":edollar", $line[4], PDO::PARAM_INT);
            $stmt->execute();
            
        }
        $studentlines = $properlines;
        $linecount++;
    }
    
    // COURSE
    $name = "course";
    $filename = $name . ".csv";
    ${"$name" . "_path"} = "$temp_dir/$name" . ".csv";
    $name = fopen(${"$name" . "_path"}, "r");
    // print_r (fgetcsv(${"$name" . "_file"}));
    $line = fgetcsv($name);
    $linecount = 2;
    $properlines = 0;
    $courses = [];
    $schoolandcourses = [];
    while ( ($tline = fgetcsv($name)) == TRUE ) {
        $line = [];
        foreach ($tline as $ch) {
            $line[] = trim($ch);
        }
        $lineerrormessage = array();

        if (empty($line[5])) {
            $lineerrormessage[]= "blank exam start";
        }
        else if (strpos($line[5], ":")) {
            $starttime = explode(":", $line[5]);
            if ( ($starttime[0] > 23) || ($starttime[1] > 59) ) {
                $lineerrormessage[]= "invalid exam start";
            }
        }
        else {
            $lineerrormessage[]= "invalid exam start";
        }

        if (empty($line[5])) {
            $lineerrormessage[]= "blank exam end";
        }
        else if (strpos($line[6], ":")) {
            $endtime = explode(":", $line[6]);
            if ( ($endtime[0] > 23) || ($endtime[1] > 59) ) {
                $lineerrormessage[]= "invalid exam end";
            }
        }
        else {
            $lineerrormessage[]= "invalid exam end";
        }

        if (isset($starttime) && isset($endtime)) {
            $starttimestr = $starttime[0] . $starttime[1];
            $endtimestr = $endtime[0] . $endtime[1];
            if ($endtimestr < $starttimestr) {
                $lineerrormessage[]= "invalid exam end";
            }
        }
        
        if (empty($line[4])) {
            $lineerrormessage[]= "blank exam date";
        }
        else if ( (strlen($line[4]) != 8) ){
            $lineerrormessage[]= "invalid exam date";
        }
        else if ( substr($line[4], 4, 2) == "02" ) {
            if ( substr($line[4], 0, 4) % 4 == 0 && substr($line[4], 6, 2) > 29 ) {
                $lineerrormessage[]= "invalid exam date";
            }
            else if ( substr($line[4], 0, 4) % 4 != 0 && substr($line[4], 6, 2) > 28 ) {
                $lineerrormessage[]= "invalid exam date";
            }
        }
        else if ( substr($line[4], 4, 2) > 12 ) {
            $lineerrormessage[]= "invalid exam date";
        }

        if (empty($line[2])) {
            $lineerrormessage[]= "blank title";
        }
        else if ( strlen($line[2]) > 100 ) {
            $lineerrormessage[]= "invalid title";
        }
        
        if (empty($line[3])) {
            $lineerrormessage[]= "blank description ";
        }
        elseif ( strlen($line[3] > 1000) ) {
            $lineerrormessage[]= "invalid description";
        }
        

        if (empty($line[0])) {
            $lineerrormessage[]= "blank course";
        }
        if (empty($line[1])) {
            $lineerrormessage[]= "blank school";
        }

        if (!empty($lineerrormessage)) {
            sort($lineerrormessage);
            $_SESSION["bootstraperrors"][]= array(
                "file"=>$filename , 
                "line"=>$linecount ,
                "message"=>$lineerrormessage
            );
        }
        else {
            if (!in_array($line[0], $courses)) {
                $courses[]=$line[0]; 
            }
            if ( !array_key_exists($line[1], $schoolandcourses) ) {
                $schoolandcourses[$line[1]] = array( $line[0] );
            }
            else {
                $schoolandcourses[$line[1]][]= $line[0];
            }
            $properlines++;
            $stmt = $conn->prepare("use boss;
            INSERT INTO course 
            VALUES (:course, :section, :title, :descr, :day, :start, :end);
            ");
            $stmt->bindParam(":course", $line[0], PDO::PARAM_STR);
            $stmt->bindParam(":section", $line[1], PDO::PARAM_STR);
            $stmt->bindParam(":title", $line[2], PDO::PARAM_STR);
            $stmt->bindParam(":descr", $line[3], PDO::PARAM_STR);
            $stmt->bindParam(":day", $line[4], PDO::PARAM_STR);
            $stmt->bindParam(":start", $line[5], PDO::PARAM_STR);
            $stmt->bindParam(":end", $line[6], PDO::PARAM_STR);
            $stmt->execute();
        }
        $courselines = $properlines;
        $linecount++;
    }

    // SECTION
    $name = "section";
    $filename = $name . ".csv";
    ${"$name" . "_path"} = "$temp_dir/$name" . ".csv";
    $name = fopen(${"$name" . "_path"}, "r");
    // print_r (fgetcsv(${"$name" . "_file"}));
    $line = fgetcsv($name);
    $linecount = 2;
    $properlines = 0;
    $coursesection = [];
    while ( ($tline = fgetcsv($name)) == TRUE ) {
        $line = [];
        foreach ($tline as $ch) {
            $line[] = trim($ch);
        }
        $lineerrormessage = array();
        if (empty($line[3])) {
            $lineerrormessage[]= "blank start";
        }
        else if (strpos($line[3], ":") !== FALSE ) {
            $secstarttime = explode(":", $line[3]);
        }
        else if (strpos($line[3], ":") === FALSE) {
            $lineerrormessage[]= "invalid start";
        }
        if (empty($line[4])) {
            $lineerrormessage[]= "blank end";
        } 
        else if (strpos($line[4], ":") !== FALSE ) {
            $secendtime = explode(":", $line[4]);
        }
        else {
            $lineerrormessage[]= "invalid end";
        }
        
        if (empty($line[0])) {
            $lineerrormessage[]= "blank course";
        }
        else if ( !in_array($line[0], $courses) ) {
            $lineerrormessage[]= "invalid course";
        }
        else {
            if (empty($line[1])) {
                $lineerrormessage[]= "blank section";
            }
            else if ( (strtolower($line[1][0]) != "s") || !is_numeric( ( explode( "s", strtolower( $line[1] ) )[1])) || (explode( "s", strtolower( $line[1] ) )[1] > 99) || (explode( "s", strtolower( $line[1] ) )[1] < 1) ) {
                $lineerrormessage[]= "invalid section";
            }
        }
        if (empty($line[2])) {
            $lineerrormessage[]= "blank day";
        }
        else if ( $line[2] > 7 || $line[2] < 1 || !is_numeric($line[2]) || strpos($line[2], ".") ) {
            $lineerrormessage[]= "invalid day";
        }
        if (isset($secstarttime) && isset($secendtime)) {
            $sectiontimeerror = FALSE;
            if ( $secstarttime[0] > 23 || $secstarttime[0] < 0 || $secstarttime[1] < 0 || $secstarttime[1] > 60 || !is_numeric($secstarttime[0]) || !is_numeric($secstarttime[1]) ) {
                if(!in_array("invalid start", $lineerrormessage)){$lineerrormessage[]= "invalid start";}
                $sectiontimeerror = TRUE;
            }
            if ( $secendtime[0] > 23 || $secendtime[0] < 0 || $secendtime[1] < 0 || $secendtime[1] > 60 || !is_numeric($secendtime[0]) || !is_numeric($secendtime[1])) {
                if(!in_array("invalid end", $lineerrormessage)){$lineerrormessage[]= "invalid end";}
                $sectiontimeerror = TRUE;
            }
            if ((strpos($line[4], ":") !== FALSE) && (strpos($line[3], ":") !== FALSE) && ($secstarttime[0] > $secendtime[0]) && $sectiontimeerror == FALSE) {
                $lineerrormessage[]= "invalid end";
            }
        }
        else if (!isset($secstarttime) && isset($secendtime)) {
            $lineerrormessage[]= "blank start";
        }
        else if (isset($secstarttime) && !isset($secendtime)) {
            $lineerrormessage[]= "blank end";
        }
        if (empty($line[5])) {
            $lineerrormessage[]= "blank instructor";
        }
        else if (strlen($line[5]) > 100) {
            $lineerrormessage[]= "invalid instructor";
        }
        if (empty($line[6])) {
            $lineerrormessage[]= "blank venue";
        }
        else if ( strlen($line[6]) > 100 ) {
            $lineerrormessage[]= "invalid venue";
        }
        if (empty($line[7]) && $line[7] != "0" ) {
            $lineerrormessage[]= "blank size";
        }
        else if ( $line[7] <= 0 || strpos($line[7], ".") || !is_numeric($line[7]) ) {
            $lineerrormessage[]= "invalid size";
        }

        if (!empty($lineerrormessage)) {
            sort($lineerrormessage);
            $_SESSION["bootstraperrors"][]= array(
                "file"=>$filename , 
                "line"=>$linecount ,
                "message"=>$lineerrormessage
            );
        }
        else {
            $properlines++;
            if ( !array_key_exists($line[0], $coursesection) ) {
                $coursesection[$line[0]] = [];
                array_push($coursesection[$line[0]], $line[1]);
            }
            else {
                array_push($coursesection[$line[0]], $line[1]);
            }
            $stmt = $conn->prepare("use boss;
            INSERT INTO section 
            VALUES (:course, :section, :day, :start, :end, :instructor, :venue, :size, 10);
            ");
            $stmt->bindParam(":course", $line[0], PDO::PARAM_STR);
            $stmt->bindParam(":section", $line[1], PDO::PARAM_STR);
            $stmt->bindParam(":day", $line[2], PDO::PARAM_INT);
            $stmt->bindParam(":start", $line[3], PDO::PARAM_STR);
            $stmt->bindParam(":end", $line[4], PDO::PARAM_STR);
            $stmt->bindParam(":instructor", $line[5], PDO::PARAM_STR);
            $stmt->bindParam(":venue", $line[6], PDO::PARAM_STR);
            $stmt->bindParam(":size", $line[7], PDO::PARAM_INT);
            $stmt->execute();
            
        }
        $sectionlines = $properlines;
        $linecount++;
    }

    // PREREQUISITE
    $name = "prerequisite";
    $filename = $name . ".csv";
    ${"$name" . "_path"} = "$temp_dir/$name" . ".csv";
    $name = fopen(${"$name" . "_path"}, "r");
    // print_r (fgetcsv(${"$name" . "_file"}));
    $line = fgetcsv($name);
    $linecount = 2;
    $properlines = 0;
    $prereqdict = [];
    while ( ($tline = fgetcsv($name)) == TRUE ) {
        $line = [];
        foreach ($tline as $ch) {
            $line[] = trim($ch);
        }
        $lineerrormessage = array();

        if (empty($line[0])) {
            $lineerrormessage[]="blank course";
        }
        else if ( !in_array($line[0], $courses) ) {
            $lineerrormessage[]="invalid course";
        }
        if (empty($line[1])) {
            $lineerrormessage[]="blank prerequisite";
        }
        if ( !in_array($line[1], $courses) ) {
            $lineerrormessage[]="invalid prerequisite";
        }

        if (!empty($lineerrormessage)) {
            sort($lineerrormessage);
            $_SESSION["bootstraperrors"][]= array(
                "file"=>$filename , 
                "line"=>$linecount ,
                "message"=>$lineerrormessage
            );
        }
        else {
            $properlines++;
            if ( !key_exists($line[0], $prereqdict) ) {
                $prereqdict[$line[0]] = [ $line[1] ];
            }
            else {
                $prereqdict[$line[0]][]= [ $line[1] ];
            }
            $stmt = $conn->prepare("use boss;
            INSERT INTO prerequisite 
            VALUES (:course, :prerequisite);
            ");
            $stmt->bindParam(":course", $line[0], PDO::PARAM_STR);
            $stmt->bindParam(":prerequisite", $line[1], PDO::PARAM_STR);
            $stmt->execute();
        }
        $prerequisitelines = $properlines;
        $linecount++;
    }

    // course_completed
    $name = "course_completed";
    $filename = $name . ".csv";
    ${"$name" . "_path"} = "$temp_dir/$name" . ".csv";
    $name = fopen(${"$name" . "_path"}, "r");
    // print_r (fgetcsv(${"$name" . "_file"}));
    $line = fgetcsv($name);
    $linecount = 2;
    $properlines = 0;
    $completedcourses = [];
    $prereqs = [];

    $sql = "SELECT * FROM boss.prerequisite";
    $cm = new connectionManager();
    $conn = $cm->getConnection();
    foreach ($conn->query($sql) as $q) {
        $course = $q["course"];
        $prereq = $q["prerequisite"];
        // echo "$course --> $prereq <br>";
        if (!array_key_exists($q["course"], $prereqs)) {
            $prereqs[$q["course"]] = [];
            array_push($prereqs[$q["course"]], $q["prerequisite"]);
        } 
        else {
            array_push($prereqs[$q["course"]], $q["prerequisite"]);
        }
    }

    // print_r ($prereqs);
    while ( ($tline = fgetcsv($name)) == TRUE ) {
        $line = [];
        foreach ($tline as $ch) {
            $line[] = trim($ch);
        }
        $lineerrormessage = array();
        if (empty($line[0])) {
            $lineerrormessage[]="blank userid";
        } 
        else if ( !in_array($line[0], $existinguserid) ) {
            $lineerrormessage[]="invalid userid";
        }
        if (empty($line[1])) {
            $lineerrormessage[]="blank course";
        } 
        else if ( !in_array($line[1], $courses) ) {
            $lineerrormessage[]="invalid course";
        }
    
        if (empty($lineerrormessage)) {
            if ( array_key_exists($line[1], $prereqs) ) {
                if ( array_key_exists($line[0], $completedcourses) ) {
                    foreach ( $prereqs[$line[1]] as $ch ) {
                        if ( in_array($ch, $completedcourses[$line[0]]) === FALSE ) {
                            $lineerrormessage[]="invalid course completed";
                        }
                    }
                }
                else {
                    $lineerrormessage[]="invalid course completed";
                }
            }
        }


        if (!empty($lineerrormessage)) {
            sort($lineerrormessage);
            $_SESSION["bootstraperrors"][]= array(
                "file"=>$filename , 
                "line"=>$linecount ,
                "message"=>$lineerrormessage
            );
        }
        else {
            $properlines++;
            if ( !array_key_exists($line[0], $completedcourses) ) {
                $completedcourses[$line[0]] = [];
                array_push($completedcourses[$line[0]], $line[1]);
            }
            else {
                array_push($completedcourses[$line[0]], $line[1]);
            }
            $stmt = $conn->prepare("use boss;
            INSERT INTO course_completed 
            VALUES (:userid, :code);
            ");
            $stmt->bindParam(":userid", $line[0], PDO::PARAM_STR);
            $stmt->bindParam(":code", $line[1], PDO::PARAM_STR);
            $stmt->execute();
        }
        $course_completedlines = $properlines;
        $linecount++;
    }

    // bid
    $name = "bid";
    $filename = $name . ".csv";
    ${"$name" . "_path"} = "$temp_dir/$name" . ".csv";
    $name = fopen(${"$name" . "_path"}, "r");
    // print_r (fgetcsv(${"$name" . "_file"}));
    $line = fgetcsv($name);
    $linecount = 2;
    $properlines = 0;
    $user_bid = array();

    $cm = new connectionManager();
    $conn = $cm->getConnection();
    $sql = "SELECT * FROM boss.roundstatus";
    foreach ($conn->query($sql) as $row) {
        $currentround = ($row["round"]);
    }

    $bids = array();
    while ( ($tline = fgetcsv($name)) == TRUE ) {
        $line = [];
        foreach ($tline as $ch) {
            $line[] = trim($ch);
        }
        $lineerrormessage = [];
        if (empty($line[0])) {
            $lineerrormessage[]="blank userid";
        } 
        if (empty($line[1])) {
            $lineerrormessage[]="blank amount";
        } 
        if (empty($line[2])) {
            $lineerrormessage[]="blank code";
        } 
        if (empty($line[3])) {
            $lineerrormessage[]="blank section";
        } 
        // echo $line[0];
        // echo "<pre>";
        // print_r ($existinguserid);
        // echo "</pre>";
        
        if (empty($lineerrormessage)) { 
            if ( !in_array($line[0], $existinguserid) ) {
                $lineerrormessage[]= "invalid userid";
            }
            if ( ($line[1] < 10) || !is_numeric($line[1])) {
                $lineerrormessage[]="invalid amount";
            }
            else {
                if ( strpos($line[1], ".") !== FALSE ) {
                    $edollar = explode(".", $line[1]);
                    if (strlen($edollar[1]) > 2) {
                        $lineerrormessage[]="invalid amount";
                    }
                }
            }
            if ( !in_array($line[2], $courses) ) {
                $lineerrormessage[]= "invalid course";
            }
            // echo "<pre>";
            // print_r ($coursesection);
            // echo "</pre";
            else {  
                if ( array_key_exists($line[2], $coursesection) ) {
                    if ( !in_array($line[3], $coursesection[$line[2]]) ) {
                        $lineerrormessage[]= "invalid section";
                    }
                }
                else {
                    $lineerrormessage[]= "invalid section";
                }
            }
        }

        if (empty($lineerrormessage)) {
            # bidding for non sch mods in round 1
            $cm =new connectionManager();
            $conn = $cm->getConnection();
            $sql = "SELECT school FROM boss.student WHERE userid=:userid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":userid", $line[0]);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $r) {
                $user_sch = $r["school"];
            }
            $sql = "SELECT school FROM boss.course WHERE course=:course";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":course", $line[2]);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $r) {
                $correct_sch = $r["school"];
            }
            if (isset($correct_sch)) { 
                if ($user_sch != $correct_sch) {
                    $lineerrormessage[]="not own school course";
                }
            }
            # check for course completed
            if ( array_key_exists($line[0], $completedcourses) ) {
                if ( in_array($line[2], $completedcourses[$line[0]]) ) {
                    $lineerrormessage[]="course completed";
                }
            }

            # generate list of students
            $student_and_bids = [];
            $cm = new connectionManager();
            $conn = $cm->getConnection();
            $sql = "SELECT * FROM boss.bid";
            foreach ($conn->query($sql) as $q) {
                $cs = [ $q["code"], $q["section"] ];

                $sql = "SELECT * FROM boss.section WHERE course=:course AND section=:section";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":course", $q["code"]);
                $stmt->bindParam(":section", $q["section"]);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $j) {
                    array_push($cs, $j["dayoftheweek"]);
                    array_push($cs, $j["starttime"]);
                    array_push($cs, $j["endtime"]);
                }

                $sql = "SELECT * FROM boss.course WHERE course=:course";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":course", $q["code"]);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $k) {
                    array_push($cs, $k["examdate"]);
                    array_push($cs, $k["examstart"]);
                    array_push($cs, $k["examend"]);
                }

                if ( !array_key_exists($q["userid"], $student_and_bids) ) {
                    $student_and_bids[$q["userid"]] = [];

                    array_push($student_and_bids[$q["userid"]], $cs);

                }
                else {
                    array_push($student_and_bids[$q["userid"]], $cs);
                }

            }

            # check for 5 sections
            // var_dump(array_key_exists($line[0], $student_and_bids));
            if ( array_key_exists($line[0], $student_and_bids) ) {
                if ( count($student_and_bids[$line[0]]) >= 5 ) {
                    $lineerrormessage[]="section limit reached";
                }
            }

            # generate bids with timings
            $student_and_bids = [];
            $cm = new connectionManager();
            $conn = $cm->getConnection();
            $sql = "SELECT * FROM boss.bid";
            foreach ($conn->query($sql) as $q) {
                $cs = [ $q["code"], $q["section"] ];

                $sql = "SELECT * FROM boss.section WHERE course=:course AND section=:section";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":course", $q["code"]);
                $stmt->bindParam(":section", $q["section"]);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $j) {
                    array_push($cs, $j["dayoftheweek"]);
                    array_push($cs, $j["starttime"]);
                    array_push($cs, $j["endtime"]);
                }

                $sql = "SELECT * FROM boss.course WHERE course=:course";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":course", $q["code"]);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $k) {
                    array_push($cs, $k["examdate"]);
                    array_push($cs, $k["examstart"]);
                    array_push($cs, $k["examend"]);
                }

                if ( !array_key_exists($q["userid"], $student_and_bids) ) {
                    $student_and_bids[$q["userid"]] = [];

                    array_push($student_and_bids[$q["userid"]], $cs);

                }
                else {
                    array_push($student_and_bids[$q["userid"]], $cs);
                }
                
            }
            // var_dump ($student_and_bids);
            if ( array_key_exists($line[0], $student_and_bids) ) {
                foreach ( $student_and_bids[$line[0]] as $stu ) {
                    // if($linecount==7){var_dump($stu);echo"<br>";}
                    
                        $check = [ $line[2], $line[3] ];
                        
                        $sql = "SELECT * FROM boss.section WHERE course=:course AND section=:section";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":course", $line[2]);
                        $stmt->bindParam(":section", $line[3]);
                        $stmt->execute();
                        foreach ($stmt->fetchAll() as $a) {
                            array_push($check, $a["dayoftheweek"]);
                            array_push($check, $a["starttime"]);
                            array_push($check, $a["endtime"]);
                        }

                        $sql = "SELECT * FROM boss.course WHERE course=:course";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":course", $line[2]);
                        $stmt->execute();
                        foreach ($stmt->fetchAll() as $b) {
                            array_push($check, $b["examdate"]);
                            array_push($check, $b["examstart"]);
                            array_push($check, $b["examend"]);
                        }

                    // if($linecount==7){var_dump($check);echo"<br><br>";}
                    // echo "<pre>";
                    // var_dump($stu);
                    // echo "<br><br>";
                    // var_dump($check);
                    // echo "</pre>";
                        if ( !empty($stu[2]) && !empty($check[2]) )  {  
                            $same_course_same_section = ($stu[0] == $check[0] && $stu[1] == $check[1]);
                            $diff_course_diff_section = ($stu[0] != $check[0] && $stu[1] != $check[1]);
                            $same_course_diff_section = ($stu[0] == $check[0] && $stu[1] != $check[1]);
                            $diff_course_same_section = ($stu[0] != $check[0] && $stu[1] == $check[1]);
                            $actual_class_start_time = explode(":", $check[3])[0] . explode(":", $check[3])[1];
                            $class_s_time = explode(":", $stu[3])[0] . explode(":", $stu[3])[1];
                            $actual_class_end_time = explode(":", $check[4])[0] . explode(":", $check[4])[1];
                            $class_e_time = explode(":", $stu[4])[0] . explode(":", $stu[4])[1];
                            if ( ($stu[2] == $check[2]) && $diff_course_diff_section && $diff_course_same_section ) {
                                // print_r ("check");
                                
                                // print_r ($class_s_time);
                                // print_r ($actual_class_start_time);
                                if ( (($class_s_time >= $actual_class_start_time) && ($class_s_time <= $actual_class_end_time)) || $class_s_time==$actual_class_start_time ) {
                                    $lineerrormessage[]="class timetable clash";
                                }
                            }
                            if ( ($stu[2] == $check[2]) && $actual_class_start_time == $class_s_time && $same_course_same_section == FALSE && $same_course_diff_section == FALSE) {
                                $lineerrormessage[]="class timetable clash";
                            }

                            
                        }
                        if ( !empty($stu[5]) && !empty($check[5]) )  {  
                            $same_course_same_section = ($stu[0] == $check[0] && $stu[1] == $check[1]);
                            $diff_course_diff_section = ($stu[0] != $check[0] && $stu[1] != $check[1]);
                            $same_course_diff_section = ($stu[0] == $check[0] && $stu[1] != $check[1]);
                            $diff_course_same_section = ($stu[0] != $check[0] && $stu[1] == $check[1]);
                            $actual_exam_start_time = explode(":", $check[6])[0] . explode(":", $check[6])[1];
                            $exam_s_time = explode(":", $stu[6])[0] . explode(":", $stu[6])[1];
                            $actual_exam_end_time = explode(":", $check[7])[0] . explode(":", $check[7])[1];
                            $exam_e_time = explode(":", $stu[7])[0] . explode(":", $stu[7])[1];
                            $overlap = ( $exam_s_time >= $actual_exam_start_time && $exam_s_time < $actual_exam_end_time ) || ( $actual_exam_start_time >= $exam_s_time && $actual_exam_start_time < $exam_e_time );
                            // var_dump($stu[7]);
                            // var_dump($check[6]);
                            // echo "<br><br>";
                            if ( ($stu[5] == $check[5]) && $diff_course_diff_section && $diff_course_same_section ) {
                                if ( ($exam_s_time >= $actual_exam_start_time) && ($exam_s_time <= $actual_exam_end_time) ) {
                                    $lineerrormessage[]="exam timetable clash";
                                }
                            }
                            
                            if ( ($stu[5] == $check[5]) && $overlap && $same_course_diff_section == FALSE && $same_course_same_section == FALSE ) {
                                $lineerrormessage[]="exam timetable clash";
                            } 

                            // else if ( ($stu[5] == $check[5]) && $stu[7] == $check[6] && $same_course_diff_section == FALSE && $same_course_same_section == FALSE ) {
                            //     $lineerrormessage[]="exam timetable clash";
                            // }
                            
                        }

                }
            }
            # check for incomplete prerequisites
            # pull prereqs
            $sql = "SELECT * FROM boss.prerequisite WHERE course=:course";
            $cm = new connectionManager();
            $conn = $cm->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":course", $line[2]);
            $stmt->execute();
            $query_prereq = array();
            foreach($stmt->fetchAll() as $preq) {
                array_push($query_prereq, $preq["prerequisite"]);
            }

            $sql = "SELECT * FROM boss.course_completed WHERE userid=:userid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":userid", $line[0]);
            $stmt->execute();
            $query_cc = array();
            foreach ($stmt->fetchAll() as $cc) {
                array_push($query_cc, $cc["code"]);
            }
            if ( !empty($query_prereq) ) {
                foreach ($query_prereq as $pq) {
                    if ( !in_array($pq, $query_cc) ) {
                        $lineerrormessage[]="incomplete prerequisites";
                        break;
                    }
                }
            }
        }
        
        

        if (!empty($lineerrormessage)) {
            // sort($lineerrormessage);
            $_SESSION["bootstraperrors"][]= array(
                "file"=>$filename , 
                "line"=>$linecount ,
                "message"=>$lineerrormessage
            );
        }
        else {
            
            # generate past bids for this student
            $past_bids = [];
            $sql = "SELECT * FROM boss.bid WHERE userid=:userid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":userid", $line[0]);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $beed) {
                $past_bids[]=$beed["code"];
            }

            # bid for course before
            if ( in_array($line[2], $past_bids) ) {
                # get current edollar
                $sql = "SELECT * FROM boss.student WHERE userid=:userid";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":userid", $line[0]);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $st) {
                    $current_edollar = $st["edollar"];
                }
                # get past bid amount
                $sql = "SELECT * FROM boss.bid WHERE userid=:userid AND code=:course";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":userid", $line[0]);
                $stmt->bindParam(":course", $line[2]);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $bi) {
                    $prev_bid = $bi["amount"];
                }
                #invisible total = current edollar + past bid amount
                $invis_total = $current_edollar + $prev_bid;
                # check if invisible total >= new bid amount

                # if not enough throw error
                if ($invis_total < $line[1]) {
                    $lineerrormessage[]="not enough e-dollar";
                    // sort($lineerrormessage);
                    $_SESSION["bootstraperrors"][]= array(
                        "file"=>$filename , 
                        "line"=>$linecount ,
                        "message"=>$lineerrormessage
                    );
                    break;
                }
                # if enough update student edollar(invisible total - new bid amount) and bid table 
                else {
                    $updated_amount = $invis_total - $line[1];
                    $sql = "UPDATE boss.student SET edollar=:edollar WHERE userid=:userid";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":edollar", $updated_amount);
                    $stmt->bindParam(":userid", $line[0]);
                    $stmt->execute();

                    $sql = "UPDATE boss.bid SET amount=:amount, section=:section WHERE userid=:userid AND code=:course";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":userid", $line[0]);
                    $stmt->bindParam(":amount", $line[1]);
                    $stmt->bindParam(":course", $line[2]);
                    $stmt->bindParam(":section", $line[3]);
                    $stmt->execute();

                    $properlines++;
                }
            }
            # never bid for course before
            else {
                # check if enough edollar
                $sql = "SELECT * FROM boss.student WHERE userid=:userid";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":userid", $line[0]);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $st) {
                    $current_edollar = $st["edollar"];
                }
                #if not enough throw error
                if ( $current_edollar < $line[1] ) {
                    $lineerrormessage[]="not enough e-dollar";
                    // sort($lineerrormessage);
                    $_SESSION["bootstraperrors"][]= array(
                        "file"=>$filename , 
                        "line"=>$linecount ,
                        "message"=>$lineerrormessage
                    );
                }
                #if enough update student edollar and bid table
                else {
                    $updated_amount = $current_edollar - $line[1];
                    $sql = "UPDATE boss.student SET edollar=:edollar WHERE userid=:userid";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":edollar", $updated_amount);
                    $stmt->bindParam(":userid", $line[0]);
                    $stmt->execute();

                    $sql = "INSERT INTO boss.bid VALUES (:userid, :amount, :code, :section)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":userid", $line[0]);
                    $stmt->bindParam(":amount", $line[1]);
                    $stmt->bindParam(":code", $line[2]);
                    $stmt->bindParam(":section", $line[3]);
                    $stmt->execute();

                    $properlines++;
                }
            }
        }

        $bidlines = $properlines;
        $linecount++;
    }

    $num_record_loaded = [
        "bid.csv"=>$bidlines,
        "course.csv"=>$courselines,
        "course_completed.csv"=>$course_completedlines,
        "prerequisite.csv"=>$prerequisitelines,
        "section.csv"=>$sectionlines,
        "student.csv"=>$studentlines,
    ];
    $_SESSION["num_record_loaded"] = $num_record_loaded;
    return TRUE;  
      
}
?>