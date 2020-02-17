<?php 
    ###########################################################################################
    #  DELETE BID
    #
    #
    ###########################################################################################

    include_once('../include/css.html');
    require_once("../include/protect.php");
?>

<html>
<!-- <div align="right"> -->
<!-- <a href="../include/logout.php">Logout</a> -->
</br>
</div>
<div align='left'>
<a href="biddingUI.php">Return to Bidding Page</a>
</div>
    <head>
        <title>Delete Bid</title>
    </head>
    <br/>
    <body>
        <?php
            if ($_GET['status']==1)
                echo("Your bid has been deleted.");
            else
                echo("Bid deletion failed.");

        ?>

    </body>
</html>