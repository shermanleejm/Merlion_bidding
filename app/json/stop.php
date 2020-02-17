<?php
include_once "../include/autoload.php";
include_once "../include/token.php";
include_once "../include/BidsDAO.php";
include_once "../include/constants.php";
include_once "../include/ClearingLogic.php";
//manual link = http://localhost/project-g7t2/app/json/stopround.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTIxIDA3OjE4OjI4In0.P92261SUnHKqmlB42MCAB7Mqf_kyAqJ294mDNOkvgcw


if (isset($_GET["token"])){
    $token = $_GET["token"];
    if (strtolower(verify_token($_GET["token"])) == "admin"){

        $bidsDAO=new BidsDAO();
        $output=[];
        $currRound=$bidsDAO->getRound();

        if ($currRound != false){
            switch ($currRound) {
                case (constant('ROUND_0_CLOSE')):
                    $output["status"]="error";
                    $output["message"]=["round already ended"];
                break;
                case (constant('ROUND_R1_OPEN')):
                    if ($bidsDAO->setRound(constant('ROUND_R1_CLOSE'))){
                        closeRound($bidsDAO, 1);
                        $output["status"]="success";
                    }else{
                        $output["status"]="error";
                        $output["message"]=["can't close round 1"];                        
                    }
                break;
                case (constant('ROUND_R1_CLOSE')):
                    $output["status"]="error";
                    $output["message"]=["round already ended"];
                break;
                case (constant('ROUND_R2_OPEN')):
                    if ($bidsDAO->setRound(constant('ROUND_R2_CLOSE'))){
                        closeRound($bidsDAO, 2);
                        $output["status"]="success";
                    }else{
                        $output["status"]="error";
                        $output["message"]=["can't close round 2"];
                    }

                break;
                case (constant('ROUND_R2_CLOSE')):
                    $output["status"]="error";
                    $output["message"]=["round already ended"];
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
    