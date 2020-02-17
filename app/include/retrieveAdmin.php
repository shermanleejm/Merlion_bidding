<?php

require_once "autoload.php";
function retrieveAdmin() {
    $sql = "SELECT * FROM boss.administrator;";
    $connmgr = new connectionManager();
    $conn = $connmgr->getConnection();
    $q = $conn->query($sql);
    foreach ($q as $line) {
        $hash = $line["pass"] ;
        $adminuser = $line["user"];
    }
    return [$adminuser, $hash];
}
    

?>