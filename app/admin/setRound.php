<?php

    ###########################################################################################
    #  SET ROUNDS
    #
    #  Look in ClearingLogic.php for the functions. -Sue (4/10/2019)
    #
    #   Note: anything related to round2 is not done yet incl deleting of bids -Sue (4/10/2019)
    #
    ###########################################################################################

require_once("../include/protect.php");
include_once("../include/common.php");
include_once('../include/ClearingLogic.php');

$bidsDao = new BidsDAO();

if (isset($_POST['startround1'])){
    $bidsDao->setRound(constant('ROUND_R1_OPEN'));
    $bidsDao->resetAllMinBid();
    Header('Location: admin.php');
    exit();
}
else if (isset($_POST['closeround1'])){

    $bidsDao->setRound(constant('ROUND_R1_CLOSE'));
    closeRound($bidsDao, 1);
    Header('Location: admin.php');
    exit();
}
else if (isset($_POST['startround2'])){
    $bidsDao->setRound(constant('ROUND_R2_OPEN'));
    Header('Location: admin.php');
    exit();
}
else if (isset($_POST['closeround2'])){
    $bidsDao->setRound(constant('ROUND_R2_CLOSE'));
    closeRound($bidsDao, 2);
    Header('Location: admin.php');
    exit();
}

/*Header('Location: admin.php');
exit();*/

?>