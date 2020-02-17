<html>
<?php
require_once 'include/autoload.php';
session_start();

// print_r ("200.00" - "60.00");

$connmgr = new connectionManager();
$conn = $connmgr->getConnection();

$dao = new studentDAO();
$bidsDao = new BidsDAO();


$sql = 'drop table if exists administrator;
create table if not exists administrator (
    user varchar(5) not null,
    pass varchar(255) not null
);
insert into administrator values ("admin", 
"$2y$10$nkVr36uzHah5/673m8gvqeZLEVXgM6GjfNfMfkirGS.NyFrcee8rK");';

$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt = NULL;


$errors = '';

if (isset($_SESSION["errors"])) {
    $errors = $_SESSION["errors"];
    unset($_SESSION['errors']);
}


elseif(isset($_POST['username']) && isset($_POST['password'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT * from administrator;";
    //$connmgr = new connectionManager();
    //$conn = $connmgr->getConnection();
    foreach ( $conn->query($sql) as $q ) {
        $hash = $q["pass"];
        $adminuser = $q["user"];
    }
    $q = NULL;

    try {
        $bidsDao->getRound();
    } catch (Exception $e)
    {
        $sql2 = 'CREATE TABLE IF NOT EXISTS roundstatus(round varchar(16) not null PRIMARY KEY);
        delete from roundstatus;insert into roundstatus values (0)';

        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        $stmt2=null;
    }
    
    if((strtolower($username) == $adminuser) && (password_verify($password, $hash))){
        $_SESSION['username'] = $adminuser; 
        header("Location: admin/admin.php");
        exit;
    }

    try{    
        $student = $dao->retrieve($username); //retrieve student object
    }catch(Exception $e){
        Header("Location: login.php");
    }
    
    if($student == null || ($password != $student->getPassword()) ){  //compare password in students table = password keyed in
        $errors = 'Password or username keyed wrongly';
    }else{
        $_SESSION['username'] = $username; 
        header("Location: student/studentDashboardUI.php");
        exit;
    }
}



?>
<h1 align="center">Merlion University BIOS</h1>
<div align="center">
<form method="POST" >
    Username:<input type="text" name="username">
    Password:<input type="password" name="password">
    <input type="submit" name="submit">
</form>
</div>

<?= $errors ?>

</html>