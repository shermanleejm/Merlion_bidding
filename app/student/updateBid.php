<html>
<head>
<div align="right">
<a href="../include/logout.php">Logout</a>
</div>
<a href="studentDashboardUI.php">Home Page</a><br><br>
    <a href="biddingUI.php">Return to Bidding Page</a>
    <br/><br/>
</head>
<body>
    
</body>
</html>
<?php
include_once('../include/css.html');
require_once('../include/common.php');
require_once("../include/protect.php");
$user = $_SESSION['username'];
$bidsDao = new BidsDAO();

if(!isset($_SESSION['listofcourses']) && !isset($_SESSION['updateprocess'])){
    Header("Location: studentDashboardUI.php");
    exit();
}else{
    if(isset($_SESSION['listofcourses'])){
        $listofcourses = $_SESSION['listofcourses'];
        
        // if(isset($_GET['id'])){
        //     $courseselected = $_GET['id'];
        // }
        // foreach($listofcourses as $course){
            if(isset($_GET['id'])){
                $courseselected = $_GET['id'];
                $coursearray = explode(":",$courseselected);
                
                $bid = $bidsDao->getBid($user,$coursearray[0]);
                $_SESSION['originalbid'] = $bid;
                $sectiondetails = $bidsDao->getAllSections();
                $biddetails = $sectiondetails[$coursearray[0]];
                
                $collength= count($biddetails);
                $sectionselected = $bid[2];
                $courseselected=$bid[1];
                $_SESSION['sectionselected'] = $sectionselected;
            }elseif(isset($_SESSION['course2'])){
                $courseselected = $_SESSION['course2'];
                unset($_SESSION['course2']);
                $coursearray = explode(":",$courseselected);
                
                $bid = $bidsDao->getBid($user,$coursearray[0]);
                $_SESSION['originalbid'] = $bid;
                $sectiondetails = $bidsDao->getAllSections();
                $biddetails = $sectiondetails[$coursearray[0]];
                
                $collength= count($biddetails);
                $sectionselected = $bid[2];
                $courseselected=$bid[1];
                $_SESSION['sectionselected'] = $sectionselected;
            }
                
        // }
    }
    if(isset($_SESSION['updateprocess'])){
        unset($_SESSION['updateprocess']);
    }

}
echo"<table>
    <tr>
        <th>Course</th>
        <th>Re-enter bid amount</th>
        <th>Section</th>
        <th>Day</th>
        <th>Start</th>
        <th>End</th>
        <th>Instructor</th>
        <th>Venue</th>
        <th>Size</th>
    </tr>";

    if(!isset($biddetails)){
        echo"<tr><td colspan='10'>No bids selected</td></tr>";
        exit();
    }
    echo"<form action='processUpdateBid.php' method='POST'>
         <tr><td rowspan=$collength>$courseselected</td>          
         <td class='w3-panel w3-cyan' rowspan=$collength>e$:<input type='text'value={$bid[0]} size=5 name='edollar'></td>";
         foreach($biddetails as $section=>$sectiondetails){
            if($section == $sectionselected){
            echo"<td>$section</td>";
             foreach($sectiondetails as $deets){
                 echo"<td>$deets</td>";
                }
            }
            echo "</tr>";
         }
         echo"</table>"; 
         echo"<br/>
         <input type='hidden' name='course' value=$courseselected>
         <input type='submit' name='updated' value='Update Bid'>
     </form>";           

?>