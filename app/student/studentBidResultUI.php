<?php

    ###########################################################################################
    #  DISPLAY STUDENT BID RESULT
    #
    #  Currently only works for Round 1.
    #  Thank you very much. - Sue(4/10/2019)
    #   
    #
    ###########################################################################################

include_once('../include/common.php');
include_once('../include/css.html');
require_once("../include/protect.php");
$userid = $_SESSION['username'];
$enrolledbids = $bidsDao->getEnrolledCourses($userid);
$unsuccessBids = $bidsDao->getUnsuccessfulCourses($userid);
$pendingBids = $bidsDao->getUserBids($userid);
// var_dump($unsuccessBids);
$successfulbids=[];
$unsuccessfulbids=[];

foreach ($enrolledbids as $key=>$value){
    $successfulbids[]=[$key,$value[0],$value[1],$value[2]];
}

foreach ($unsuccessBids as $unsuccessful){
        $unsuccessfulbids[]=[$unsuccessful[0],$unsuccessful[1],$unsuccessful[2]];
}


?>

<html>
<div align="right">
<a href="../include/logout.php">Logout</a>
</div>
    <body>
    <?php
    if ($roundStatus == ROUND_R1_OPEN || $roundStatus == ROUND_R2_OPEN)
        echo("<a href='biddingUI.php'>Back to Bidding</a>");

    else if ($roundStatus == ROUND_R1_CLOSE || $roundStatus == ROUND_R2_CLOSE)
        echo("<a href='studentDashboardUI.php'>Home Page</a>");
    ?>
    <br>

    <h2>Bidding Status</h2>
    <table>
        <tr>
            <th>Course code</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Status</th>
        </tr>
    <?php
        #Bid- Success  
        foreach ($successfulbids as $item){
            echo("<tr>");
                echo ("<td>".$item[0]."</td>
                        <td>".$item[1]."</td>
                        <td>".$item[2]."</td>
                        <td>"."Success"."</td>"
                    );
            echo("</tr>");
        }
        #Bid- Fail
        foreach ($unsuccessfulbids as $i){
            echo("<tr>");
                echo ("<td>".$i[0]."</td>
                        <td>".$i[1]."</td>
                        <td>".$i[2]."</td>
                        <td>"."Fail"."</td>");
            echo("</tr>");
        }
        #Bid- Pending
        foreach ($pendingBids as $pending){
            echo("<tr>");
                echo ("<td>".$pending[1]."</td>
                        <td>".$pending[2]."</td>
                        <td>".$pending[0]."</td>
                        <td>"."Pending"."</td>");
            echo("</tr>");
        }
        if (sizeof($successfulbids)==0 && sizeof($unsuccessfulbids)==0 && sizeof($pendingBids)==0){
            echo("<tr><td colspan = 4>No bids successful, failed or pending.</td></tr>");
        }
    ?>
    </table>
    <br>

    <!-- Individual bid statuses commented out-->    

    <!-- <h2>Successful bids</h2>
    <table>
        <tr>
            <th>Course code</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Round</th>
        </tr>
        <?php
            if (sizeof($successfulbids)==0){
                echo("<tr><td colspan = 4>None of your bids were successful.</td></tr>");
            }
            else{
                foreach ($successfulbids as $item){
                    echo("<tr>");
                        echo ("<td>".$item[0]."</td>
                                <td>".$item[1]."</td>
                                <td>".$item[2]."</td>
                                <td>".$item[3]."</td>"
                            );
                    echo("</tr>");
                }
            }
        ?>
        </table>
<br>

<h2>Unsuccessful bids</h2>
<table>
    <tr>
        <th>Course code</th>
        <th>Section</th>
        <th>Bid Amount</th>
        <th>Round</th>
    </tr>
        <?php
            if (sizeof($unsuccessfulbids)==0){
                echo("<tr><td colspan = 4>None of your bids were unsuccessful.</td></tr>");
            }else{
                foreach ($unsuccessfulbids as $i){
                    echo("<tr>");
                        echo ("<td>".$i[0]."</td>
                                <td>".$i[1]."</td>
                                <td>".$i[2]."</td>
                                <td>".$i[3]."</td>");
                    echo("</tr>");
                }
            }
        ?>
    </table>
<br>

<h2>Pending bids</h2>
    <table>
    <tr>
        <th>Course code</th>
        <th>Section</th>
        <th>Bid Amount</th>
        <th>Status</th>
    </tr>

        <?php
            if (sizeof($pendingBids)==0){
                echo("<tr><td colspan = 3>No pending bids.</td></tr>");
            }else{
                foreach ($pendingBids as $pending){
                    echo("<tr>");
                        echo ("<td>".$pending[1]."</td>
                                <td>".$pending[2]."</td>
                                <td>".$pending[0]."</td>
                                <td>"."Pending"."</td>");
                    echo("</tr>");
                }
            }
        ?>
    </table> -->

    </body>
</html>