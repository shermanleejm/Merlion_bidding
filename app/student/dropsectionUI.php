<!DOCTYPE html>
<html>
<head>
<div align="right">
<a href="../include/logout.php">Logout</a>
</div>
    <a href="biddingUI.php">Back to Bidding</a>
    </br>

</head>
<body>
    
</body>
</html>
<?php


        echo"<br>";
        include_once('../include/css.html');
        require_once('../include/common.php');
        require_once("../include/protect.php");

        $bidsDao = new BidsDAO();

        $userid = $_SESSION['username'];
        $enrolledcourses = $bidsDao->getEnrolledCourses($userid);

        // if(isset($_SESSION['status'])){
        //     // echo $_SESSION['status'];
        //     // unset($_SESSION['status']);
        //     var_dump($_SESSION['status']);
        // }


        echo"<form action='processEnrolled.php' method='POST'>";
        echo"
        <table>
        <tr><th>Course</th><th>Section</th><th>Bidded Amount</th><th>Drop Section</th></tr>
        ";

        if($enrolledcourses == []){
            echo"<tr><td colspan='4'>No courses enrolled!</td></tr></table>";
        }else{
        foreach($enrolledcourses as $course=>$value){
            $section = $value[0];
            $bidamt = $value[1];
            echo"<tr><td>$course</td><td>$section</td><td>$bidamt</td>
            <td><input type='checkbox', name='deletedSections[]' value=$course></td></tr>";}
            
        echo"</table>";
        echo"<br><input type='submit', name='Submit Drop bids'></form>";
    }

        

?>