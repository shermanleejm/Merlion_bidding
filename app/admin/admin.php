<!DOCTYPE html>
<html>
<div align="right">
<a href="../include/logout.php">Logout</a>
</div>
<?php
require_once "../include/common.php";
require_once "../include/protect.php";
require_once "../include/bootstrap.php";

// if ( strpos($_SERVER['HTTP_USER_AGENT'], "Macintosh") !== FALSE) {
// 	$password = "root";
// }
// else {
// 	$password = "";
// }
// $conn = new PDO("mysql:host=localhost", "root", $password);

?>
<h1 align=center>Hello Administrator</h1>

<body>
      
    <form align=center action="processfile.php" method="POST" enctype="multipart/form-data">
       <input type="file" name="bootstrap-file" />
       <input type="submit" name="submit"/>
    </form>
   <br>
   <br>

 </body>

 <?php

	if (isset($_SESSION["message"])) {
		echo $_SESSION["message"];
		unset($_SESSION["message"]);
	}
	if (isset($_SESSION["fileerrors"])){
		echo "<ul>";
		foreach ( $_SESSION["fileerrors"] as $error ) {
			echo "<li>" . $error . "</li>";
		}
		echo "</ul>";
		unset($_SESSION["fileerrors"]);
   }
   if (isset($_SESSION["num_record_loaded"])){
      echo "<table border = '1'>";
      echo "<tr><th>File</th><th># of Fields</th></tr>";
      foreach ($_SESSION["num_record_loaded"] as $csv_file=>$lines){
         echo "<tr><td>$csv_file</td><td>$lines</td></tr>";
      }
      echo "</table>";
      unset($_SESSION["num_record_loaded"]);
   }
	if (isset($_SESSION["bootstraperrors"])) {
		foreach ($_SESSION["bootstraperrors"] as $error) {
			$file = $error["file"];
			$line = $error["line"];
			$message = $error["message"];
			echo "
			<ul>
				<li>$file - line $line has this message:";
			echo "
			<ul>";
			foreach ($message as $thing) {
            echo "<li>$thing</li>";
			}
			echo "</ul>";
			echo "</ul>";
		}
		unset($_SESSION["bootstraperrors"]);
   }


   ################# ROUND STUFF (NOT SQUARE STUFF) ########################

   //I put in this part to check if the bootstrap tables are present, and use it to generate bool to show 
   //round changing feature

   $canSetRounds=true;
   try{
      $bidsDao->getAllCourses();
   }catch (Exception $e){
      $canSetRounds=false;
   }

   if ($canSetRounds){
      $bMsg="";
      $bName="";
   
      if ($roundStatus == constant('ROUND_0_CLOSE')){
         //open round 1
         echo("<h2>No round is active.</h2>");
         $bMsg="Start_Round_1";
         $bName="startround1";
      }
      else if ($roundStatus == constant('ROUND_R1_OPEN')){
         //close round 1
         echo("<h2>Round 1 is active.</h2>");
         $bMsg="Close_Round_1";
         $bName="closeround1";
      }
      else if ($roundStatus == constant('ROUND_R1_CLOSE')){
         //open round 2
         echo("<h2>Round 1 has stopped and cleared successfully.</h2>");
         $bMsg="Start_Round_2";
         $bName="startround2";
         }
         else if ($roundStatus == constant('ROUND_R2_OPEN')){
            //close round 2
            echo("<h2>Round 2 is active.</h2>");
            $bMsg="Close_Round_2";
            $bName="closeround2";
         }
         else if ($roundStatus == constant('ROUND_R2_CLOSE')){
            //do something
            echo("<h2>Round 2 has stopped and cleared successfully.</h2>");
            $bMsg="Start_Round_1";
            $bName="startround1";
         }
      
         echo("<form method='POST' action='setRound.php'>
               <input type='submit' value=$bMsg name=$bName>
         </form>");
        
      echo("<br/>
            <a href='displayroundresults.php'>View Clearing Results</a>");
   }else{
      echo("Please bootstrap the round first.");
   }
   
   
	################# ROUND STUFF (NOT SQUARE STUFF) END ########################
?>

</body>
        