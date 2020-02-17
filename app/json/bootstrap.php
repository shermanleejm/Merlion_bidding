<?php
include_once "../include/autoload.php";
include_once "../include/bootstrap.php";
include_once "../include/token.php";
// include_once "../include/common.php";

$emsg = [];
$status = "error";

$bidsDAO = new BidsDAO();
if (isset($_POST["token"])) {
    $token = $_POST["token"];
    if($token == ''){
        $emsg[] = "blank token";
        $output = [
            "status"=>$status,
            "message"=>$emsg
        ];
        header("Content-Type: application/json");
        echo json_encode($output, JSON_PRETTY_PRINT);
        exit;
    }
    if (strtolower(verify_token($token)) == 'admin') {
        if (isset($_FILES["bootstrap-file"])) {
            $file_name = $_FILES['bootstrap-file']['name'];
            $file_size = $_FILES['bootstrap-file']['size'];
            $file_tmp = $_FILES['bootstrap-file']['tmp_name'];
            $file_type = $_FILES['bootstrap-file']['type'];
            $file_ext = strtolower(explode('.', $file_name)[1]);
            if ($file_ext != "zip") {
                $emsg[] = "invalid file type";
            }
            else {
                $resultofbootstrap = doBootstrap();
                $bootstraperrors = array();
                
                foreach ($_SESSION["bootstraperrors"] as $e) {
                    if ($e["file"] == "bid.csv") {
                        array_push($bootstraperrors, $e);
                    }
                }
                foreach ($_SESSION["bootstraperrors"] as $e) {
                    if ($e["file"] == "course.csv") {
                        array_push($bootstraperrors, $e);
                    }
                }
                foreach ($_SESSION["bootstraperrors"] as $e) {
                    if ($e["file"] == "course_completed.csv") {
                        array_push($bootstraperrors, $e);
                    }
                }
                foreach ($_SESSION["bootstraperrors"] as $e) {
                    if ($e["file"] == "prerequisite.csv") {
                        array_push($bootstraperrors, $e);
                    }
                }
                foreach ($_SESSION["bootstraperrors"] as $e) {
                    if ($e["file"] == "section.csv") {
                        array_push($bootstraperrors, $e);
                    }
                }
                foreach ($_SESSION["bootstraperrors"] as $e) {
                    if ($e["file"] == "student.csv") {
                        array_push($bootstraperrors, $e);
                    }
                }

                if (empty($bootstraperrors)) {
                    $status = "success";
                    $num_record_loaded = $_SESSION["num_record_loaded"];

                }
                else {
                    $status = "error";
                    $num_record_loaded = $_SESSION["num_record_loaded"];
                }


            }
        }
        else {
            $emsg[] = "missing file";
        }
    }
    else {
        $emsg[] = "invalid token";
    }
}
else {
    $emsg[] = "missing token";
}

if ( !empty($emsg) ) {
    $output = [
        "status"=>$status,
        "message"=>$emsg
    ];
    header("Content-Type: application/json");
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}
if(isset($num_record_loaded)){
    $result = [];
    foreach($num_record_loaded as $key=>$value){
        $dict = array();
        $dict[$key] = $value;
        array_push($result,$dict);
    }

}
if ($status == "success") {
    $output = [
        "status"=>$status,
        "num-record-loaded"=>$result,
    ];
}

else {
    $output = [
        "status"=>$status,
        "num-record-loaded"=>$result,
        "error"=>$bootstraperrors
    ];
}

#Start round 1
$bidsDAO->setRound(1);

header("Content-Type: application/json");
echo json_encode($output, JSON_PRETTY_PRINT);
?>