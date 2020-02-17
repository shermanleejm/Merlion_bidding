<?php
include_once "../include/autoload.php";
include_once "../include/token.php";
include_once "../include/BidsDAO.php";
include_once "../include/constants.php";

//manual link = http://localhost/project-g7t2/app/json/startround.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTIxIDA3OjE4OjI4In0.P92261SUnHKqmlB42MCAB7Mqf_kyAqJ294mDNOkvgcw

if (isset($_GET["token"])){
    $token = $_GET["token"];
    if (strtolower(verify_token($_GET["token"])) == "admin"){

        $bidsDAO=new BidsDAO();
        $output=[];
        $currRound=$bidsDAO->getRound();

        if ($currRound != false){
            switch ($currRound) {
                case (constant('ROUND_0_CLOSE')):
                    if ($bidsDAO->setRound(constant('ROUND_R1_OPEN'))){
                        $output["status"]="success";
                        $output["round"]=1;
                    }
                    else{
                        $output["status"]="error";
                        $output["message"]=["failed to open round 1"];                        
                    }
                break;
                case (constant('ROUND_R1_OPEN')):
                        $output["status"]="success";
                        $output["round"]=1;
                break;
                case (constant('ROUND_R1_CLOSE')):
                    if ($bidsDAO->setRound(constant('ROUND_R2_OPEN'))){
                        $output["status"]="success";
                        $output["round"]=2;
                    }
                    else{
                        $output["status"]="error";
                        $output["message"]=["failed to open round 2"];                        
                    }
                break;
                case (constant('ROUND_R2_OPEN')):
                    $output["status"]="success";
                    $output["round"]=2;
                break;
                case (constant('ROUND_R2_CLOSE')):
                    $output["status"]="error";
                    $output["message"]=["round 2 ended"];
                break;
            }
        }
        else{
            //somehow getRound failed
            $output["status"]="error";
            $output["message"]=["failed to retrieve round"];
        }

        header('Content-Type: application/json');
        echo(json_encode($output, JSON_PRETTY_PRINT));
    }
}

?>
    