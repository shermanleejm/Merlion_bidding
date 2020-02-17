<?php
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // ...
// }
// $folder = date("Ymd");mkdir ($folder, 0755);
// require_once("../include/protect.php");
include_once("../include/bootstrap.php");
include_once("../include/common.php");
// if (isset($_SESSION["bootstraperrors"])) {
//     foreach ($_SESSION["bootstraperrors"] as $error) {
//         echo "$error<br>";
//     }
//     $_SESSION["bootstraperrors"] = [];

// }
// echo strpos($_SERVER['HTTP_USER_AGENT'], "Macintosh") !== FALSE;

if (file_exists($_FILES["bootstrap-file"]["tmp_name"])) {
    $errors = array();
    $file_name = $_FILES['bootstrap-file']['name'];
    $file_size = $_FILES['bootstrap-file']['size'];
    $file_tmp = $_FILES['bootstrap-file']['tmp_name'];
    $file_type = $_FILES['bootstrap-file']['type'];
    $file_ext = strtolower(explode('.', $file_name)[1]);

    if ($file_size <= 0) {
        $errors[]= "Please upload file";
        $_SESSION["bootstraperrors"] = $errors;
        header("Location: admin.php");
        exit;
    }

    if ($file_ext != "zip") {
        $errors[]= "Please upload a zip file only";
        $_SESSION["fileerrors"] = $errors;
        header("Location: admin.php");
        exit;
    }

    if (empty($errors)) {
        $_SESSION["filename"] = strtolower(explode('.', $file_name)[0]);
        $resultofbootstrap = doBootstrap();
        if ($resultofbootstrap !== FALSE) {
            // echo "<div align=center><h1>The round has started. Good luck!</h1></div>";
            $_SESSION["message"] = "<div align=center><h1>The round has started. Good luck!</h1></div>";
            // header("Location: admin.php");
            
            //to set the round as round 1 open
            $bidsDao=new BidsDAO();
            $bidsDao->setRound(constant('ROUND_R1_OPEN'));
            
        }
        else {
            header("Location: admin.php");
            exit;
        }
    }
    
} 
// else {
    header("Location: admin.php");
// }



?>