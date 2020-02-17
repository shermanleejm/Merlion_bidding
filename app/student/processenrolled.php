<?php

    include_once('../include/common.php');
    require_once("../include/protect.php");

    $user = $_SESSION['username'];

    $bidsDao=new BidsDAO();

    if(isset($_POST['deletedSections'])){

        $deletedsections = $_POST['deletedSections'];
        $allenrolled = $bidsDao->getEnrolledCourses($user);

        foreach($deletedsections as $coursecode){
            $bidamt = $allenrolled[$coursecode][1];
            $section = $allenrolled[$coursecode][0];
            $bidsDao->removeEnrolledCourse($user,$coursecode);
            $currentedollar = $bidsDao->getUserEDollars($user);
            $newedollar = $bidamt + $currentedollar;
            $bidsDao->setUserEDollars($user, $newedollar);
            $newvacancy = $bidsDao->getVacancy($coursecode, $section) + 1;
            $bidsDao->setVacancy($newvacancy, $coursecode, $section);
        }
        

        $err[] = 'Section successfully dropped, e$ has been refunded';
        $_SESSION['status'] = $err;
        Header('Location: dropSectionUI.php');
        
        exit();
    }else{
        $err[]="Please select a section to be deleted!";
        $_SESSION["errors"]=$err;
        Header('Location: dropsectionUI.php');
        exit();
        
    }

?>