

<?php

    ###########################################################################################
    #  BIDDING UI
    #
    #   
    #
    #
    ###########################################################################################

    include_once('../include/css.html');
    include_once('../include/common.php');
    require_once("../include/protect.php");
    include_once('../include/clearingLogic.php');

    $bidsDao=new BidsDAO();
    $user = $_SESSION['username'];
    #courses bidded for
    $currBidsArr = $bidsDao->getUserBids($user);
    // var_dump($currBidsArr);
    #list of courses available
    $availableCourses=$bidsDao->getBiddableCourses($user);
    #school that user is in
    $userSchool = $bidsDao->getUserSchool($user);
    // var_dump($_SESSION['errors']);
    
    // $_SESSION['CurrentBids'] = $currBidsArr;
?>

<html>
<div align="right">
<a href="../include/logout.php">Logout</a>
</div>
    <head>
        <title>Place Bid</title>
    </head>

    <body>

        <a href="studentDashboardUI.php">Home Page</a><br>

        <center>
    
        <form action='BiddingUI.php' method='$_POST'>
            <?php 
            if ($roundStatus == ROUND_R1_OPEN || $roundStatus == ROUND_R2_OPEN){
                echo("<input type='submit' formaction='dropSectionUI.php' value='Drop an Enrolled Section'> | ");
                echo("<input type='submit' formaction='studentBidResultUI.php' value='View All Rounds Bidding Results'>  ");
            }
            else if ($roundStatus == ROUND_R1_CLOSE || $roundStatus == ROUND_R2_CLOSE)
                echo("<input type='submit' formaction='studentBidResultUI.php' value='View All Rounds Bidding Results'>  ");
            ?>
        </form>
        <br><br>
        <?php 
        $thisRound="";
        if ($roundStatus==ROUND_0_CLOSE)
            $thisRound="All rounds are closed";
        elseif ($roundStatus==ROUND_R1_OPEN)
            $thisRound="Round 1 is currently open";
        elseif ($roundStatus==ROUND_R1_CLOSE)
            $thisRound="Round 1 is currently closed";
        elseif ($roundStatus==ROUND_R2_OPEN)
            $thisRound="Round 2 is currently open";
        elseif ($roundStatus==ROUND_0_CLOSE)
            $thisRound="Round 2 is currently closed";
        
        ?>

        <h3><?=$thisRound?>. You have <?php echo($bidsDao->getUserEDollars($user)) ?> e$.</h3>

        <form action='deleteBid.php' method="POST">
            <table>
                <tr>
                    <th>Amount</th>
                    <th>Coursecode</th>
                    <th>Section</th>
                    <th>Min Bid</th>
                    <th>Vacancy</th>
            <?php
                //bid status part
                if ($roundStatus == constant('ROUND_R2_OPEN')){
                    echo("<th>Current Bid Status</th>");
                }
                //bid status end

                if (sizeof($currBidsArr)<1){
                    $colspan=5;
                    if ($roundStatus == constant('ROUND_R2_OPEN'))
                        $colspan=6;
                    echo("<tr><td colspan=$colspan>No Courses Bidded Yet!</td>");
                }
                else{
                    echo ("<th> <input type='submit' value='Delete Bid'></th><th>Update Bid Amount</th>
                    </tr>");
                    $_SESSION['listofcourses'] = [];
                    foreach ($currBidsArr as $item){
                        echo("<tr>");
                        foreach ($item as $col){
                            echo("<td>$col</td>");
                        }
                        $value=$item[1].":".$item[0];

                        array_push($_SESSION['listofcourses'],$value);

                        //display min bid
                        $minBid=$bidsDao->getMinBid($item[1], $item[2]);
                        echo("<td>$minBid</td>");
                        $aVacancy=$bidsDao->getVacancy($item[1], $item[2]);
                        echo("<td>$aVacancy</td>");

                        if ($roundStatus == constant('ROUND_R2_OPEN')){
                            //display the course status
                            $bidStatus="FAILED";
                            $thisBidsTable=sortAllBids($bidsDao, $item[1], $item[2]);
                            $thisVacancy=$bidsDao->getVacancy($item[1], $item[2]);
                            if ($thisBidsTable != false){
                                if (count($thisBidsTable) <= $thisVacancy){
                                    $bidStatus="SUCCESS";
                                }
                                else{
                                    $temp=checkSufficientMoreBidsThanVacancy($thisBidsTable, $thisVacancy, 2);
                                    $successArray=$temp[0];
                                    if (array_key_exists($user,$successArray)){
                                        $bidStatus="SUCCESS";
                                    }else{
                                        $bidStatus="FAILED";}
                                }
                            }
                            echo("<td>$bidStatus</td>");
                        }
                        //end display the course status
                        echo("<td><input type='checkbox', name='deletedBids[]' value=$value></td>");
                        echo "<td><input type='submit'formaction='updateBid.php?id=$value' value='Update'></td></tr>
                        ";
                    }
                }
                //Let the use know how many sections he has bidded for 
                $SectionCount = count($currBidsArr);
                $MaxSectionCount = 5;
                echo "No. of Sections bidded: ".$SectionCount."/".$MaxSectionCount;
            ?>
            </table>
            <input type=hidden name=user value=$user>
        </form>
        
        <!-- Filtering by course -->
        <!-- <form action='BiddingUI.php' method = 'POST'>
        <?php
        $course = ''; 
        if( isset($_POST['course']) ) {
            $course = $_POST['course'];
        }
        var_dump($course);
        echo "Filter by Course: ";
        echo "<select name='course'>";

        $selected=""; #initialise
         
        if ($course != ""){
            $selected="selected";        
        }
        
        foreach($availableCourses as $course=>$value){
            if ($roundStatus == ROUND_R1_OPEN){
                if ($value[0] == $userSchool){
                    // displayTheCourse($bidsDao, $key, $value, $completed,$enrolled,$currBidsArr);
                    echo "<option value ='$course' <?=$selected ?>$course</option>";
                }
            }
        }
        echo "</select>";
        ?>
        <input type='submit' value='Filter'/>
        </form> -->

        <form action="makeBidUI_1.php" method='POST'>
        <br>
        <br>
        <h3>Available courses for bidding</h3><input type='submit' value='View sections for selected course(s)'><br/><br/>
            <br><table>
                    <tr>
                        <th>Course</th>
                        <th>School</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Exam Date</th>
                        <th>Exam Start</th>
                        <th>Exam End</th>
                        <th>View Section Details</th>
                    </tr>
                <?php
                    $completed=$bidsDao->getCompletedCourses($user);
                    $enrolled=$bidsDao->getEnrolledCourses($user);

                    if (count($availableCourses)>0){
                        foreach ($availableCourses as $key=>$value){
                            if ($roundStatus == ROUND_R1_OPEN){
                                if ($value[0] == $userSchool){
                                    displayTheCourse($bidsDao, $key, $value, $completed,$enrolled,$currBidsArr);
                                }
                            }
                            else if ($roundStatus == ROUND_R2_OPEN) {
                                //not done yet, just display all courses
                                displayTheCourse($bidsDao, $key, $value, $completed,$enrolled,$currBidsArr);
                            }
                        }
                    }
                    else{
                        echo("<tr><td colspan=8>No courses available!</td>");                    
                    }
                ?>
            </table>
            <br>
            
        </form>
    </body></center>
</html>

<?php

    function displayTheCourse($bidsDao,$ccode,$courseInfoArr,$completed,$enrolled,$currBidsArr){
        echo("<tr>");
        echo("<td>$ccode</td>");

        $needsPrereq = false;
        $alrEnrolled=false;
        $alrBidded=false;
        $prerqmessage = "Still needs prerequisite "; //additional message to display at title
        $enrolledmessage = "Already enrolled, please drop section to re-bid another one!";
        $biddedmessage= "<br>Already bidded, please delete your existing bid if you want to change section.";

        //get course needs prerequisite
        $prerequisite=$bidsDao->getCoursePrerequisite($ccode);

        //check if prerequisites are in completed courses
        foreach ($prerequisite as $pre){
            if (!in_array($pre, $completed)){
                $needsPrereq=true;
                $prerqmessage.=$pre." ";
            }
        }

        //check if course has already been enrolled
        if (array_key_exists($ccode, $enrolled)){
            $alrEnrolled=true;
        }
        foreach($currBidsArr as $currentbid){
            if($currentbid[1]==$ccode){
                $alrBidded=true;
            }
        }
            
        //if so display the course
        for ($x=0; $x<count($courseInfoArr); $x++){
                echo("<td>".$courseInfoArr[$x]."</td>");
        }
        echo("<td class='w3-panel w3-cyan'>");
        if ($alrEnrolled){
            echo($enrolledmessage);
        }
        else if ($needsPrereq){
            echo($prerqmessage);
        }/*else if ($alrBidded){
            echo($biddedmessage);
        }*/
        else{
            echo("<input type='checkbox' name=selectedCourses[] value=$ccode>");
            if ($alrBidded){
                echo($biddedmessage);
            }
        }
        echo("</td>");
        echo("</tr>");
    }
?>