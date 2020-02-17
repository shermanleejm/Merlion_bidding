<html>
    <body>
        <a href="admin.php">Go back to Admin Dashboard</a>
        <br>
        <br>
        <br>
<?php

    ###########################################################################################
    #  DISPLAY ROUND RESULTS
    #
    #  Look in ClearingLogic.php for the functions. -Sue (4/10/2019)
    #
    #   Note: anything related to round2 is not done yet incl deleting of bids -Sue (4/10/2019)
    #
    ###########################################################################################

    require_once("../include/protect.php");
    include_once("../include/common.php");
    include_once('../include/css.html');

    $bidsDao = new BidsDAO();
    /*$round=null;
    if ($roundStatus=constant('ROUND_R1_CLOSE'))
        $round="1";
    elseif ($roundStatus=constant('ROUND_R2_CLOSE'))
        $round="2";
    else
        $round="ALL";*/

    $round="ALL";

    //display successful courses
    $successful = $bidsDao->getEnrolledCoursesByRound($round);

    echo("<h1>Successful Bids</h1>");
    echo('<table>
            <th>User ID</th>
            <th>Course</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Round</th>');
    if (count($successful)==0)
        echo("<tr><td colspan='5'>No bids</td></tr>");
    else{
        foreach ($successful as $i){
            echo("<tr>");
            foreach ($i as $n){
                echo("<td>$n</td>");
            }
            echo("</tr>");
        }
    }
    echo('</table>');

    //display unsuccessful courses

    $unsuccessful = $bidsDao->getUnsuccessfulCoursesByRound($round);

    echo("<h1><br>Unsuccessful Bids</h1>");
    echo('<table>
            <th>User ID</th>
            <th>Course</th>
            <th>Section</th>
            <th>Bid Amount</th>
            <th>Round</th>');
    if (count($unsuccessful)==0)
        echo("<tr><td colspan='5'>No bids</td></tr>");
    else{
        foreach ($unsuccessful as $a){
            echo("<tr>");
            foreach ($a as $b){
                echo("<td>$b</td>");
            }
            echo("</tr>");
        }
    }
    echo('</table>');
?>

    </body>
</html>