<?php
    include_once('../include/clearingLogic.php');

    function placeBid($bidsDao, $user, $edollar, $coursesBidded){

        //write the new course to database
        if ($bidsDao->addAllBids($user,$coursesBidded)){
            //write the new edollar to database
            if ($bidsDao->setUserEDollars($user, $edollar)){
                $_SESSION['errors']=null;
            }
            else{
                //throw error
                $err[]="Course didn't write successfully";
                $_SESSION['errors']=$err;     

            }
        }
        else{
            //throw error
            $err[]="Your bid was not placed. Please contact the administrator.";
            $_SESSION['errors']=$err;  
        }
    }

    function doUpdateBid($bidsDao, $user, $courseOrderForm, $thisCourseCode_, $thisSection_, $thisBidPrice_){
        $canWrite=false;
        $successfulArr=[];

        $thisCourseCode=$thisCourseCode_;
        $thisSection=$thisSection_;
        $thisBidPrice=$thisBidPrice_;
        $thisVacancy=$bidsDao->getVacancy($thisCourseCode,$thisSection);
        $thisMinBid=$bidsDao->getMinBid($thisCourseCode,$thisSection);
        $allBids=sortAllBids($bidsDao, $thisCourseCode, $thisSection);
        $newMinBid=null;
        $allBidsSize=count($allBids);

        if ($allBidsSize < $thisVacancy){
            if ($thisBidPrice >= $thisMinBid){
                $canWrite=true;
                if (($allBidsSize+1)==$thisVacancy){
                    $allBids[$user]=$thisBidPrice;
                    $working=sortAllBidsFromArray($allBids);
                    $newMinBid=end($working)+1;
                }else{
                    $newMinBid=$thisMinBid;
                }
            }
        }else if ($allBidsSize == $thisVacancy){ //case of new user being 1x more than vacancy
            if ($thisBidPrice >= $thisMinBid){
                $canWrite=true;
                $allBids[$user]=$thisBidPrice;
                $working=sortAllBidsFromArray($allBids);
                $temp=checkSufficientMoreBidsThanVacancy($working, $thisVacancy, 2);
                $workingSuccess=$temp[0];
                if (end($workingSuccess) >= $thisMinBid){
                    $newMinBid=end($workingSuccess)+1;
                }
                else{
                    $newMinBid=$thisMinBid;
                }
            }
        }else
        {
            if ($thisBidPrice >= $thisMinBid){
                $canWrite=true;
                $allBids[$user]=$thisBidPrice;
                $working=sortAllBidsFromArray($allBids);
                $temp=checkSufficientMoreBidsThanVacancy($working, $thisVacancy, 2);
                $workingSuccess=$temp[0];
                if (end($workingSuccess) > $thisMinBid){
                    $newMinBid=end($workingSuccess)+1;
                }
                else{
                    $newMinBid=$thisMinBid+1;
                }
            }
        }
        return [$canWrite,$newMinBid, $thisVacancy];
    }
?>