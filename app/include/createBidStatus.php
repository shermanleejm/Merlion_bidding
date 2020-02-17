<?php

function createBidStatus() {

    include_once "autoload.php";
    $cm = new connectionManager();
    $conn = $cm->getConnection();
    $sql = "TRUNCATE boss.bid_status";
    $conn->query($sql);
    $sql = "SELECT * FROM course_enrolled"; 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    foreach ( $stmt->fetchAll() as $q ) {
        $sql = "INSERT INTO bid_status VALUES (:userid, :course, :section, :amount, :round, 'in')";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":course", $q["course"], PDO::PARAM_STR);
        $stmt->bindParam(":section", $q["section"], PDO::PARAM_STR);
        $stmt->bindParam(":round", $q["round"], PDO::PARAM_STR);
        $stmt->bindParam(":userid", $q["userid"], PDO::PARAM_STR);
        $stmt->bindParam(":amount", $q["bidamount"], PDO::PARAM_STR);
        $stmt->execute();
        $stmt=null;
    }
    $sql = "SELECT * FROM course_unsuccessful"; 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    foreach ( $stmt->fetchAll() as $q ) {
        $sql = "INSERT INTO bid_status VALUES (:userid, :course, :section, :amount, :round, 'out')";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":course", $q["course"], PDO::PARAM_STR);
        $stmt->bindParam(":section", $q["section"], PDO::PARAM_STR);
        $stmt->bindParam(":round", $q["round"], PDO::PARAM_STR);
        $stmt->bindParam(":userid", $q["userid"], PDO::PARAM_STR);
        $stmt->bindParam(":amount", $q["bidamount"], PDO::PARAM_STR);
        $stmt->execute();
        $stmt=null;
    }
    $conn=null;
}

?>