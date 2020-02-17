<?php 
require_once("../include/protect.php");
include_once('../include/common.php');

$bidsDao=new BidsDAO();

$user = $_SESSION['username'];

$deletedBids;

if (isset($_POST['deletedBids'])){

    foreach ($_POST['deletedBids'] as $item){
        $deletedBids[]=explode(":",$item);
    }

    $success=$bidsDao->deleteBid($user,$deletedBids);

    if ($success){
        $status[]='Bid(s) successfully deleted!';
        $_SESSION['status']=$status;
        Header("Location: biddingUI.php");
        // Header('Location: deleteBidUI.php?status=1');
        exit();
    }
    else{
        $err[]="Bid(s) deletion failed!";
        $_SESSION['errors']=$err;
        Header("Location: biddingUI.php");
        // Header('Location: deleteBidUI.php?status=0');
        exit();
    }
}else{
    $err[]="Please select a course to be deleted!";
    $_SESSION["errors"]=$err;
    Header('Location: biddingUI.php');
    exit();
}
?>