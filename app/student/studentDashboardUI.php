<?php 

        ##########################################################################################
        #  STUDENT DASHBOARD UI: Student users see this upon login. 
        #
        #
        ###########################################################################################

        include_once('../include/css.html');
        require_once('../include/common.php');
        require_once("../include/protect.php");
        
        $bidsDao = new BidsDAO();
        $userid = $_SESSION['username'];
        $sql = "SELECT * FROM boss.student WHERE userid=:userid";
        $cm = new connectionManager();
        $conn = $cm->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $userid);
        $stmt->execute();
        foreach ($stmt->fetchAll() as $s ) {
            $student_name = $s["studentname"];
        }

        $courses_completed = $bidsDao->getCompletedCourses($userid);
        $edollars = $bidsDao->getUserEDollars($userid);

        # courses successfully enrolled will be associative array of [ ‘course’ => section];
        
        // $courses_enrolled = ['ECON001' => 'S1', 'IS102' => 'S3','IS105' =>'S2'];
        $courses_enrolled = $bidsDao->getEnrolledCourses($userid);
    
       
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
        //var_dump($currentbidsdict);
        ##currentbidsdict = ['COURSE'=> [day,starttime,endtime,section]]
        
?>

<!DOCTYPE html>
<html>
<div align="right">
<a href="../include/logout.php">Logout</a>
</div>
<div align='center'>
    <head>
        <title>School timetable</title>
    </head>
    <body>    
        <h1> Hello <?=$student_name?>, welcome to BIOS home page! </h1>
        <?php
               if ($roundStatus == constant('ROUND_0_CLOSE')){
                echo("<h5>No round is active.</h5>");
             }
             else if ($roundStatus == constant('ROUND_R1_OPEN')){
                echo("<h5>Round 1 is active.</h5>");
             }
             else if ($roundStatus == constant('ROUND_R1_CLOSE')){
                echo("<h5>Round 1 has ended.</h5>");
             }
             else if ($roundStatus == constant('ROUND_R2_OPEN')){
                echo("<h5>Round 2 is active.</h5>");
             }
             else if ($roundStatus == constant('ROUND_R2_CLOSE')){
                echo("<h5>Round 2 has ended.</h5>");
             }
        ?>
        <h5> e$: <?=$edollars?> </h5>

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
                            echo "<td class='w3-panel w3-cyan'>Pending Bid<br>".$currentcourse."<br>".$currentcoursedetails[3]."</td>";
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
        <br>
        <br>
        <table>
            <tr>
            <th>Course(s) completed</th>
            
            
                <?php foreach($courses_completed as $course){
                    echo "<tr><td>$course</td></tr>";
                }
                ?>
            
        </table>

        <hr>
        
        <form action='studentDashboardUI.php' method='$_POST'>
            <?php 
            if ($roundStatus == ROUND_R1_OPEN || $roundStatus == ROUND_R2_OPEN){
                //echo("<input type='submit' formaction='dropSectionUI.php' value='Drop a Section'> | 
                echo("<input type='submit' formaction='biddingUI.php' value='Bidding Main Page'> | ");
                echo("<input type='submit' formaction='studentBidResultUI.php' value='View All Rounds Bidding Results'>");
            }
            else if ($roundStatus == ROUND_R1_CLOSE || $roundStatus == ROUND_R2_CLOSE)
                echo("<input type='submit' formaction='studentBidResultUI.php' value='View Bidding Result'>");
            ?>
        </form>
    </body>
</div>
</html>