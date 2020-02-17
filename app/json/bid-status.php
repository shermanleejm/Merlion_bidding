<?php
include_once "../include/autoload.php";
include_once "../include/token.php";
include_once "../include/bidsDAO.php";
include_once "../include/constants.php";
include_once "../include/clearingLogic.php";

//manual link = http://localhost/project-g7t2/app/json/bid-status.php?r={"course": "IS100", "section": "S1"}&token=[tokenValue]

/*
This new web service will allow an administrator to retrieve a comprehensive bid information given a section and 
a course, i.e., vacancy, the minimum bid price, and all the bids with (userid, bid amount, e-dollor balance, status). 
The status is one of {pending, success, fail}. 
*/

if (isset($_GET["token"])){
    $status=null;
    $message=[];
    $output=[];
    $token = $_GET["token"];
    if (strtolower(verify_token($_GET["token"])) == "admin"){

        $bidsDAO=new bidsDAO();
        $currRound=$bidsDAO->getRound();
        $currCourse=null;
        $currSection=null;
        
        if(isset($_GET['r'])){
            
            $r = json_decode($_GET['r'], TRUE);
            if(!isset($r['course'])){
                array_push($message, "invalid course");
                $status = 'error';
            }else if($r['course'] == ''){
                array_push($message, "invalid course");
                $status = 'error';
            }else{ 
                //value provided for course

                //check if it exists
                $course=$r['course'];
                $allCourseCodes=$bidsDAO->getAllCourseCodes();

                if (in_array($course, $allCourseCodes)){
                    if(!isset($r['section'])){
                        array_push($message, "invalid section");
                        $status = 'error';
                    }
                    else if($r['section'] == ''){
                        array_push($message, "invalid section");
                        $status = 'error';   
                    }
                    else{
                        //value provided for section                       
                        $section=$r['section'];

                        if ($currRound != false){
                            switch ($currRound) {
                                case (constant('ROUND_0_CLOSE')):
                                    //this not exist in theory but in reality it does ;)
                                break;
                                case (constant('ROUND_R1_OPEN')):
                                    /*Vacancy: the total available seats as all the bids are still pending.
                                    Minimum bid price: when #bid is less than the #vacancy, report the lowest bid amount. Otherwise, set the price as the clearing price. When there is no bid made, the minimum bid price will be 10.0 dollars.
                                    Bids: report (userid, bid amount, e-dollar balance, status) for all the bids made so far during round 1. Status should be "pending".
                                    Balance: follow the round 1 logic.*/

                                    if ($bidsDAO->isValidSection($course, $section)){
                                        $vacancy=$bidsDAO->getVacancy($course, $section);
                                        $bidsForCourseSection=$bidsDAO->getBidsByCodeSection($course, $section);
                                        $minBid=null;

                                        if (empty($bidsForCourseSection)){
                                            $minBid=$bidsDAO->getMinBid($course, $section);
                                        }elseif (count($bidsForCourseSection)<$vacancy){
                                            $minBid=min($bidsForCourseSection);
                                        }else{
                                            //set the price as the clearing price
                                            $userBidsArr=sortAllBids($bidsDAO,$course,$section);

                                            //case more or equal bids than vacancy
                                            $temp=checkSufficientMoreBidsThanVacancy($userBidsArr, $vacancy, 1);
                                            if (empty($temp[0]))
                                                $minBid=10.0;
                                            else
                                                $minBid=number_format(min($temp[0]), 1); 
                                        }

                                        //students
                                        $studentArr=[];
                                        $bidsList=$bidsDAO->getBidsOrderedByAmountUserID($course, $section);
                                        foreach ($bidsList as $userid=>$amountbid){
                                            $tempArr=[];
                                            $tempArr['userid']=$userid;
                                            $tempArr['amount']=number_format($amountbid, 1);
                                            $tempArr['balance']=number_format($bidsDAO->getUserEDollars($userid), 1);
                                            $tempArr['status']="pending";
                                            $studentArr[]=$tempArr;
                                        }

                                        //encode
                                        $output['status']='success';
                                        $output['vacancy']=$vacancy;
                                        $output['min-bid-amount']=$minBid;
                                        $output['students']=$studentArr;
                                        header('Content-Type: application/json');
                                        $result = json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                                        echo($result);
                                        exit();
                                    }else{
                                        array_push($message, "invalid section");
                                        $status = 'error';                                         
                                    }
                                break;
                                case (constant('ROUND_R1_CLOSE')):
                                    /*
                                        Vacancy: (the total available seats) - (number of successful bid during round 1).
                                        Minimum bid price: report the lowest successful bid. If there was no bid made (or no 
                                        successful bid) during round 1, the value will be 10.0 dollars.
                                        Bids: report (userid, bid amount, e-dollor balance, status) for all the bids. Status 
                                        should be either "success" or "fail" according to the round 1 clearing logic.
                                        Balance: follow the clearing round 1 logic.
                                    */

                                    $bidsList=$bidsDAO->getCombinedSuccessfulUnsuccessful($course, $section, 1);
                                    if (empty($bidsList)){
                                        array_push($message, "invalid section");
                                        $status = 'error';                                           
                                    }
                                    else{//have bids present
                                        $successfulArr=$bidsDAO->getEnrolledCoursesByCourseSectionRound($course,$section,1);
                                        //vacancy
                                        $vacancy=intval($bidsDAO->getVacancy($course, $section));

                                        //minbid
                                        if(empty($successfulArr)){
                                            $minbid = 10.0;
                                        }else{
                                        $minBid=number_format(min($successfulArr),1);}

                                        //students
                                        $studentArr=[];
                                        foreach ($bidsList as $userid=>$details){
                                            $tempArr=[];
                                            $tempArr['userid']=$userid;
                                            $tempArr['amount']=number_format($details[0],1);
                                            $tempArr['balance']=number_format($bidsDAO->getUserEDollars($userid),1);
                                            $tempArr['status']=$details[1];
                                            $studentArr[]=$tempArr;
                                        }
                                        //encode
                                        $output['status']='success';
                                        $output['vacancy']=$vacancy;
                                        $output['min-bid-amount']=$minBid;
                                        $output['students']=$studentArr;
                                        header('Content-Type: application/json');
                                        $result = json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                                        echo($result);
                                        exit();
                                    }

                                break;
                                case (constant('ROUND_R2_OPEN')):
                                    /*
                                        Vacancy: follow the round 2 logic. (put the total available vacancies as the round is not 
                                        over)
                                        Minimum bid price: follow the round 2 logic.
                                        Bids: report (userid, bid amount, e-dollor balance, status) for all the bids made during 
                                        round 2. Status should be either "success" or "fail" reflecting the real-time bidding status.
                                        Balance: follow the round 2 logic.
                                    */

                                    $vacancy=$bidsDAO->getVacancy($course, $section);
                                    $minBid=$bidsDAO->getMinBid($course,$section);
                                    $bidsArr=$bidsDAO->getBidsByCodeSection($course, $section);

                                    //get the course status
                                    $bidStatus="fail";
                                    $sortedBidsTable=sortAllBids($bidsDAO, $course, $section);
                                    if ($sortedBidsTable != false){
                                        if (count($sortedBidsTable) <= $vacancy){
                                            $successArray=$sortedBidsTable;
                                        }
                                        else{
                                            $temp=checkSufficientMoreBidsThanVacancy($sortedBidsTable, $vacancy, 2);
                                            $successArray=$temp[0];
                                        }
                                    }

                                    foreach ($bidsArr as $userid=>$amount){
                                        if (array_key_exists($userid,$successArray)){
                                            $bidsArr[$userid]=[$amount, "success"];
                                        }else{
                                            $bidsArr[$userid]=[$amount, "fail"];
                                        }
                                    }

                                    //prep studentArr
                                    $studentArr=[];
                                    foreach ($bidsArr as $userid=>$details){
                                        $tempArr=[];
                                        $tempArr['userid']=$userid;
                                        $tempArr['amount']=number_format($details[0],1);
                                        $tempArr['balance']=number_format($bidsDAO->getUserEDollars($userid),1);
                                        $tempArr['status']=$details[1];
                                        $studentArr[]=$tempArr;
                                    }
                                    //encode
                                    $output['status']='success';
                                    $output['vacancy']=$vacancy;
                                    $output['min-bid-amount']=number_format($minBid,1);
                                    $output['students']=$studentArr;
                                    header('Content-Type: application/json');
                                    $result = json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                                    echo($result);
                                    exit();
                                break;
                                case (constant('ROUND_R2_CLOSE')):
                                    /*    
                                        Vacancy: (the total available seats) - (number of successfully enrolled students in round 1 
                                        and 2).
                                        Minimum bid price: the minimum successful bid amount during round 2. If there was no bid 
                                        made (or no successful bid) during round 2, the value will be 10.0 dollars.
                                        Bids: report (userid, bid amount, e-dollor balance, status) for all the successful bids made 
                                        in round 1 and 2. Do not include failed bids.
                                        Balance: the e-dollor left after deducting all successful bid amounts in round 1 and 2.
                                    */

                                    $allSuccessful=$bidsDAO->getEnrolledCoursesByCourseSection($course,$section);
                                    $allSuccessfulR2=$bidsDAO->getEnrolledCoursesByCourseSectionRound($course,$section,2);
                                    $vacancy=intval($bidsDAO->getVacancy($course, $section));

                                    if (empty($allSuccessfulR2))
                                        $minBid=10.0;
                                    else
                                        $minBid=number_format(min($allSuccessfulR2),1); //check this

                                    //prep studentArr
                                    $studentArr=[];
                                    foreach ($allSuccessful as $userid=>$details){
                                        $tempArr=[];
                                        $tempArr['userid']=$userid;
                                        $tempArr['amount']=number_format($details,1);
                                        $tempArr['balance']=number_format($bidsDAO->getUserEDollars($userid),1);
                                        $tempArr['status']="success";
                                        $studentArr[]=$tempArr;
                                    }
                                    //encode
                                    $output['status']='success';
                                    $output['vacancy']=$vacancy;
                                    $output['min-bid-amount']=$minBid;
                                    $output['students']=$studentArr;
                                    header('Content-Type: application/json');
                                    $result = json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                                    echo($result);
                                    exit();                                    
                                break;
                            }
                        }
                        else{
                            //somehow getRound failed
                            array_push($message, "failed to retrieve round");
                        }                  
                    }                     
                }
                else{
                    array_push($message, "invalid course");
                    $status = 'error'; 
                }            
            }
        }//end isset get
        else{
            array_push($message, "invalid course");
            $status = 'error';            
        }
    }//end verify token
    else{
        array_push($message, "invalid token");
        $status = 'error';     
    }
    $output['status']=$status;
    $output['message']=$message;
    header('Content-Type: application/json');
    $result = json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
    echo($result);
    exit();
}

?>