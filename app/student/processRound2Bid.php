<?php

    ###########################################################################################
    #  PROCESS ROUND 2 BID
    #
    #  Further processing of round2 clearing logic
    #
    #       //round 2 clearing logic
    #       //display available seats (aka size)
    #       //display clearing price
    #
    ###########################################################################################

    include_once('../include/common.php');
    require_once("../include/protect.php");
    include_once('../include/css.html');
    include_once('../include/clearingLogic.php');
    include_once('processRound2BidFunctions.php');

    $user = $_SESSION['username'];

    $bidsDao=new BidsDAO();
    $outputTable=[];
    $edollar=$bidsDao->getUserEDollars($user);

    $courseOrderForm=null;
    if (isset($_SESSION['coursesBidded'])){
        $courseOrderForm=$_SESSION['coursesBidded'];
        unset($_SESSION['coursesBidded']);
    }else{
        //throw error
        $err[]="Something went wrong, please try to bid again.";
        $_SESSION['errors']=$err;
        Header("Location: biddingUI.php");
        exit;  
    }

    foreach ($courseOrderForm as $key=>$value){
        $temp=doUpdateBid($bidsDao,$user,$courseOrderForm, $key, $value[0], $value[1]);
        $canWrite=$temp[0];
        $newMinBid=$temp[1];
        $thisVacancy=$temp[2];

        //write bid and clearing price
        if ($canWrite){
            $courseBidded[$key]=[$value[0],$value[1]];
            placeBid($bidsDao, $user, $edollar-$value[1], $courseBidded);

            $bidsDao->setMinBid($key, $value[0], $newMinBid);
            $courseBidded=null;
            //$success="BID WAS PLACED";
            $err[]="Bid placed successfully.";
            $_SESSION['status']=$err;
        }
        else{
            $err[]="Bid was not placed.";
            $_SESSION['status']=$err;
        }

    //show update bid info
    if (isset($_SESSION['source'])){
        unset($_SESSION['source']);
    }

    Header("Location: biddingUI.php");
    exit();
/*
        //write to output table
        $outputTable[]=[$key, $value[0], $thisVacancy, $value[1],
                        $bidsDao->getMinBid($key,$value[0]),$success];
*/
    }
?>
<?php

/*
<html>

    <body>
    <a href="biddingUI.php">Go back to Bidding</a><br><br><br><br>
        
        <h1>Round 2 Real-Time Bidding Information</h1>

        <table>
            <tr>
                <th>Course Code</th>
                <th>Section</th>
                <th>Vacancy</th>
                <th>Amount Bidded</th>
                <th>Current Minimum Bid</th>
                <th>Bid Status</th>
            </tr>
            <?php
                foreach ($outputTable as $n){
                    echo("<tr>");
                    foreach ($n as $m){
                        echo("<td>$m</td>");
                    }
                    echo("</tr>");
                }
        ?>
        </table>

    </body>

</html>
*/
?>
