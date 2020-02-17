<?php

include_once('../include/css.html');
require_once('../include/common.php');
require_once("../include/protect.php");
$user = $_SESSION['username'];
$bidsDao = new BidsDAO();
$_SESSION['updateprocess'] = 'updated';

if(!isset($_POST['updated'])){
    Header("Location: studentDashboardUI.php");
    exit();
}else{
    $edollar=$_POST['edollar'];
    $course=$_POST['course'];
    
    $section = $_SESSION['sectionselected'];
    $bid = $_SESSION['originalbid'];
    unset($_SESSION['originalbid']);

    $bidamt = $bid[0];
    $originalsection=$bid[2];
    $bidnewarr = array($course=>[$section,$edollar]);
    $biddeletedarr = array($course=>[$originalsection,$bidamt]);
    $deletearr = array(array($course,$edollar));
    
    $currentedollar = $bidsDao->getUserEDollars($user);
    $hiddenedollar = $bidamt + $currentedollar;
    $newedollar = $hiddenedollar - $edollar;
    
    
    $alrBidded=$bidsDao->getUserBids($user);
    $alrEnrolled=$bidsDao->getEnrolledCourses($user);
    $_SESSION['details']=[$alrBidded,$alrEnrolled];
    //$EDOLLAR VALIDATION//

    if($edollar<10 || !is_numeric($edollar)){
        $err[]="Please input edollar correctly";
        $_SESSION['errors']=$err;
        $_SESSION['course2']=$course;
        Header("Location: updateBid.php");
        exit;  
    }else if($edollar > $hiddenedollar){
        $err[]="Insufficient Balance";
        $_SESSION['errors']=$err;
        $_SESSION['course2']=$course;
        Header("Location: updateBid.php");
        exit;  
    }

    if ($roundStatus==constant('ROUND_R1_OPEN')){
        $bidsDao->deleteBid($user,$deletearr);
        $bidsDao->addAllBids($user, $bidnewarr);
        $bidsDao->setUserEDollars($user, $newedollar);
        $_SESSION['status']=array('Bid updated successfully!');
        Header("Location: biddingUI.php");
        exit();
    } elseif ($roundStatus==constant('ROUND_R2_OPEN')){

        //check min bid
        if ($edollar >= $bidsDao->getMinBid($course,$section)){
            $bidsDao->deleteBid($user,$deletearr);
            $bidsDao->setUserEDollars($user, $hiddenedollar);        
            //throw to process r2 bid
            $_SESSION['coursesBidded']=$bidnewarr;
            $_SESSION['source']="Passing through";
            $_SESSION['status']=array('Bid updated successfully!');
            Header("Location: processRound2Bid.php");
            exit(); 
        }else{
            $err[]="Bid amount is lower than minimum bid.";
            $_SESSION["errors"]=$err;
            Header("Location: biddingUI.php");
            exit();
        }   
    }
}

        
?>