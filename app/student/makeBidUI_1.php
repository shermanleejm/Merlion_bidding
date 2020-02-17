<html>
<head>
<div align="right">
<a href="../include/logout.php">Logout</a>
</div>
<a href="studentDashboardUI.php">Home Page</a>
<br><br>
    <a href="biddingUI.php">Return to Bidding Page</a>
    </br>
</head>
</html>
<?php


    ###########################################################################################
    #  MAKEBID UI_1
    #
    #  Where the user select the section and confirm bid
    #
    #
    ###########################################################################################

    include_once('../include/css.html');
    include_once('../include/common.php');
    require_once("../include/protect.php");
    $bidsDao=new BidsDAO();
    $user = $_SESSION['username'];
    $allSections=$bidsDao->getAllSections();

    //take post information
    $selectedCourses=[];
    if (isset($_POST['selectedCourses'])){
        $selectedCourses=$_POST['selectedCourses'];
        $SelectedBidCount = count($selectedCourses);
        $currBidsArr = $bidsDao->getUserBids($user);
        $CurrentBidCount = count($currBidsArr);
    }
        if (sizeof($selectedCourses)<1){
            $err[]="Please select a course!";
            $_SESSION["errors"]=$err;
            header("Location: biddingUI.php");
            exit;
        }

        //find already enrolled course
        $enrolledCourses=$bidsDao->getEnrolledCourses($user);
        $enrolledBidCount = count($enrolledCourses);

        if (($CurrentBidCount+$SelectedBidCount+$enrolledBidCount)>5){
            $err[]="The maximum number of modules you can have/bid for cannot exceed 5!";
            $_SESSION["errors"]=$err;
            header("Location: biddingUI.php");
            exit;
        }
        //var_dump($allSections);

?>

<html>
<!-- <div align="right">
<a href="../include/logout.php">Logout</a> -->
</div>
    <head><title>Confirm your sections</title></head>

    <body><center>
        <h1>Choose Your Sections</h1>
        <table>
            <tr>
                <th>Course</th>
                <th>Key in Bid Amount</th>
                <th>Section</th>
                <th>Day</th>
                <th>Start</th>
                <th>End</th>
                <th>Instructor</th>
                <th>Venue</th>
                <th>Vacancies Remaining</th>
                <th>Minimum Bid</th>
                <th>Select 1 Section/Course</th>
            </tr>
            <form action='processBid.php' method='POST'>
                <?php
                        //display courses
                        $selection = 1; //Storing the section & code

                        foreach ($selectedCourses as $item){
                            
                            echo("<tr><td rowspan=".count($allSections[$item]).">".$item."</td>");

                            echo ("<td class='w3-panel w3-cyan' rowspan=".count($allSections[$item]).">
                                    e$ <input type='text' size=5 name='text:$selection'>
                                    </td>");
                            foreach ($allSections[$item] as $section=>$section_info){ //go through sections
                                echo("<td>$section</td>");
                                foreach ($section_info as $details){ //go through details
                                    echo("<td>$details</td>");
                                }
                                echo("<td>".$bidsDao->getMinBid($item,$section));
                                //check if any vacancy left
                                if ($section_info[5] == 0)
                                    echo ("<td>Class full</td>");
                                else
                                    echo ("<td class='w3-panel w3-cyan'><input type='radio' name='$selection' value='$item:$section'>$section</td>");
                                echo("</tr>");
                            }
                            $selection++;
                        }
                        echo("</tr>");
                
                ?>
                </table>
                <br/>
                <input type='submit' value='Submit'>
            </form>
            <br/><br/>
            <?php        
            // $bidsDao = new BidsDAO();
            $userid = $_SESSION['username'];

            $courses_completed = $bidsDao->getCompletedCourses($userid);
            $edollars = $bidsDao->getUserEDollars($userid);

            # courses successfully enrolled will be associative array of [ ‘course’ => section];
            ## HARDCODED DATA 
            // $courses_enrolled = ['ECON001' => 'S1', 'IS102' => 'S3','IS105' =>'S2'];
            $courses_enrolled = $bidsDao->getEnrolledCourses($userid);

            ## HARDCODED DATA
            $enrolled_courses_detail = [];
            foreach($courses_enrolled as $key=>$value){
                $enrolled_courses_detail["$key"]=$bidsDao->getSectionTimebyid($key,$value[0]);
                $enrolled_courses_detail["$key"][]=$value[0];
            }
            ## $enrolled courses details = ['COURSE'=> [day,starttime,endtime,section]]
            ## hardcoding currentbids [ [AMT,CODE,SECTION],[AMT,CODE,SECTION]]
            // $currentbids = [[15,'IS100','S1'],[20,'IS104','S1']];
            $currentbids = $bidsDao->getUserBids($userid);

            $currentbidsdict=[];
            foreach($currentbids as $bids){
                $currentbidsdict[$bids[1]]=$bidsDao->getSectionTimebyid($bids[1],$bids[2]);
                $currentbidsdict[$bids[1]][]=$bids[2];
            }
            ##currentbidsdict = ['COURSE'=> [day,starttime,endtime,section]]
        
?>
            <table>
            <tr>
                <th colspan=8>Class timetable</th>
            <tr>
            <?php
            $time = ['08:30-11:45'=>'08:30','12:00-15:15'=>'12:00','15:30-18:45'=>'15:30'];
            $day = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday', 7=>'Sunday'];
            echo"<tr><td></td>";
            foreach($day as $dayoftheweek=>$date){
                echo"<td>$date</td>";
            }
            echo"</tr>";
            
            foreach($time as $full=>$short){
                echo"<tr><td>$full</td>";
                for($i=1;$i<8;$i++){
                    $filled = FALSE;
                    $count = 0;
                    foreach($enrolled_courses_detail as $coursecode=>$coursedetails){
                        if($i == $coursedetails[0] && $short == substr($coursedetails[1],0,5)){
                            echo "<td>".$coursecode."<br>".$coursedetails[3]."</td>";
                            $filled = TRUE;
                            $count = 1;
                        }
                    }
                    foreach($currentbidsdict as $currentcourse=>$currentcoursedetails){
                        if($i == $currentcoursedetails[0] && $short == substr($currentcoursedetails[1],0,5) && $count == 0){
                            echo "<td class='w3-panel w3-cyan'>Bidded<br>".$currentcourse."<br>".$currentcoursedetails[3]."</td>";
                            $filled = TRUE;
                        }
                    }
                            
                    if($filled == FALSE){
                        echo"<td></td>";
                    }
                }
            echo"</tr>";
            }
            
            ?>
        </table>
    </body></center>
</html>