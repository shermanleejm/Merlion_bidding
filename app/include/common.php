<?php

echo('<link rel="stylesheet" href="../include/w3.css">');

include_once "constants.php";

spl_autoload_register(function($class) {
    require_once "$class.php"; 
  
});

   $bidsDao=new bidsDAO;

    session_start();

    if (isset($_SESSION["errors"])) {
        //echo "<div align=center><ul>";
        echo("<div class='w3-panel w3-red'><ul>");
        foreach ($_SESSION["errors"] as $error) {
           echo "<li>$error</li>";
        }
        echo "</ul></div>";
     }
     unset($_SESSION["errors"]);

     if (isset($_SESSION["status"])) {
      //   echo "<div align=center><ul>";
        echo("<div class='w3-panel w3-green'><ul>");
        foreach ($_SESSION["status"] as $status) {
           echo "<li>$status</li>";
        }
        echo "</ul></div>";      
     }
     unset($_SESSION["status"]);

   $roundStatus = $bidsDao->getRound();
?>