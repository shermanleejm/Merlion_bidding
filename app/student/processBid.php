<?php

    ###########################################################################################
    #  PROCESS BID
    #
    #  Bid mechanics here
    #
    #   THERE ARE FUNCTIONS AT THE BOTTOM PLEASE SEE THEM 
    #
    ###########################################################################################

    include_once('../include/common.php');
    require_once("../include/protect.php");
    $user = $_SESSION['username'];
    $alrBidded=$bidsDao->getUserBids($user);
    $alrEnrolled=$bidsDao->getEnrolledCourses($user);

    //redirect user if no session selected
    if (isset($_POST['back'])){
        $err[]="Please select a session!";
        $_SESSION["errors"]=$err;
        Header("Location: biddingUI.php");
        exit;
    }
    echo ("You have this amount: e$" . $bidsDao->getUserEDollars($user));

    //validation of course numbers alr done on makebidui_1
    //now assign to array
    $coursesBidded=[];
    $handled=0;
    for ($i=1;$i<6;$i++){
        if(isset($_POST["$i"])){
            $handled++;
            if (!empty($_POST["text:$i"])){
                if (is_numeric($_POST["text:$i"])){
                    $holding = explode(":", $_POST["$i"]);
                    $holdingEdollar=$_POST["text:$i"];
                    $courseValue=$holding[1];
                    $minBid=$bidsDao->getMinBid($holding[0], $holding[1]);
                    //var_dump($_POST["text:$i"]);
                    if (($minBid != null) && ($_POST["text:$i"] >= $minBid)){
                        $coursesBidded[$holding[0]]=[$courseValue,$holdingEdollar];
                    }
                    else{
                        $err[]="Smallest bid amount allowed is $minBid!";
                        $_SESSION['errors']=$err;
                        Header("Location: biddingUI.php");
                        exit;                          
                    }
                }
                else{
                    $err[]="Bid amount can only be a number!";
                    $_SESSION['errors']=$err;
                    Header("Location: biddingUI.php");
                    exit;                    
                }
            }
            else{
                $err[]="Bid amount not specified!";
                $_SESSION['errors']=$err;
                Header("Location: biddingUI.php");
                exit;
            }
        }
        if ($handled<1) //no section radio buttons selected
        {
            $err[]="No sections selected!";
            $_SESSION['errors']=$err;
            Header("Location: biddingUI.php");
            exit;            
        }
    }

    //does bid already exist?
    $alreadyCourses=$bidsDao->getBid($user,$holding[0]);
    if ($alreadyCourses != null)
    {
        $err[]="You already have an existing bid, please drop it before you place a bid for another section";
        $_SESSION['errors']=$err;
        Header("Location: biddingUI.php");
        exit;           
    }

    if (!validateClassTiming($bidsDao,$alrBidded,$coursesBidded,$alrEnrolled)){
        //throw error
        $err[]="Your class timing is clashing with previous/current bids!";
        $_SESSION['errors']=$err;
        Header("Location: biddingUI.php");
        exit;   
    }else{
        if (!validateExamTiming($bidsDao,$alrBidded,$coursesBidded,$alrEnrolled)){
            //throw error
            $err[]="Your exam timing is clashing!";
            $_SESSION['errors']=$err;
            Header("Location: biddingUI.php");
            exit;  
        }
        else{
            $userEdollar=validateBidAmount($bidsDao, $user, $coursesBidded);
            if ($userEdollar==-10){
                //throw error
                $err[]="You don't have enough e$!";
                $_SESSION['errors']=$err;
                Header("Location: biddingUI.php");
                exit;  
            }
            else{
                if ($roundStatus == constant('ROUND_R1_OPEN')){
                    placeBid($bidsDao, $user, $userEdollar, $coursesBidded);
                    
                    
                }else if ($roundStatus == constant('ROUND_R2_OPEN')){
                    //bring the orders to round2 processing
                    $_SESSION['coursesBidded']=$coursesBidded;
                    Header("Location: processRound2Bid.php");
                    exit();
                }
            }
        }
    }

    ####################### START FUNCTION DECLARATIONS ######################################

    function placeBid($bidsDao, $user, $edollar, $coursesBidded){

        //write the new course to database
        if ($bidsDao->addAllBids($user,$coursesBidded)){
            //write the new edollar to database
            if ($bidsDao->setUserEDollars($user, $edollar)){
                //return the user to biddingUI.php
                $_SESSION['errors']=null;
                $status[]='Bid(s) successfully placed!';
                $_SESSION['status']=$status;
                Header("Location: biddingUI.php");
                exit;    
            }
            else{
                //throw error
                $err[]="An error has occurred. Please contact the administrator.";
                $_SESSION['errors']=$err;
                Header("Location: biddingUI.php");
                exit;                 
            }
        }
        else{
            //throw error
            $err[]="Your bid was not placed. Please contact the administrator.";
            $_SESSION['errors']=$err;
            Header("Location: biddingUI.php");
            exit;  
        }

    }

    function validateClassTiming($bidsDao,$alrBidded,$coursesBidded,$alrEnrolled){
        $workingArr[]=[00,00,00]; //need initiate this value for below logic to work

        //check against selected courses
        foreach ($coursesBidded as $key=>$value){
            $thisSection=$bidsDao->getSectionTimebyid($key,$value[0]);
            foreach ($workingArr as $n){
                $test=array_diff($n,$thisSection);
                if (empty($test)){
                    return false;
                }
                else{
                    $workingArr[]=$thisSection;
                }
            }
        }

        //now check against previous bidded courses
        foreach ($alrBidded as $prevBid){
            $thisSection=$bidsDao->getSectionTimebyid($prevBid[1],$prevBid[2]);
            foreach ($workingArr as $n){
                //var_dump($n);
                $test=array_diff($n,$thisSection);
                //var_dump($test);
                if (empty($test)){
                    return false;
                }
                else{
                    $workingArr[]=$thisSection;
                }
            }
        }

        //now check against previously enrolled courses
        foreach ($alrEnrolled as $k=>$v){
            $enrolledSection = $v[0];
            $thisEnrolledSection=$bidsDao->getSectionTimebyid($k,$enrolledSection);
            foreach ($workingArr as $n){
                //var_dump($n);
                $test=array_diff($n,$thisEnrolledSection);
                //var_dump($test);
                if (empty($test)){
                    return false;
                }
                else{
                    $workingArr[]=$thisEnrolledSection;
                }
            }
        }

        return true;
    }

    function validateExamTiming($bidsDao,$alrBidded,$coursesBidded,$alrEnrolled){

        $workingArr=array('00','00','00');

        foreach ($coursesBidded as $key=>$value){
            $thisExam=$bidsDao->getCourseExambyId($key);
            foreach ($workingArr as $n){
                if ($n[0]=='00'){
                    $workingArr[0]=$thisExam;
                }
                else{
                    //first check exam date
                    if (strtotime($n[0])==strtotime($thisExam[0])){
                        //clashing date

                        //then check if start time 2 later or equal than start time 1
                        if (strtotime($thisExam[1]) >= strtotime($n[1])){
                            //if so, check if start time 2 earlier or equal than end time 1
                            if (strtotime($thisExam[1]) <= strtotime($n[2])){
                                
                                //that's a clash.
                                return false;
                            }
                        }
                    }
                    else{
                        $workingArr[]=$thisExam;
                    }
                }
            }
        }

        //check against past bids
        foreach ($alrBidded as $prevBid){
            $pastExam=$bidsDao->getCourseExambyId($prevBid[1]);
            foreach ($workingArr as $i){

                //first check exam date
                if (strtotime($i[0])==strtotime($pastExam[0])){
                    //clashing date

                    //then check if start time 2 later or equal than start time 1
                    if (strtotime($pastExam[1]) >= strtotime($i[1])){
                        //if so, check if start time 2 earlier or equal than end time 1
                        if (strtotime($pastExam[1]) <= strtotime($i[2])){
                            
                            //that's a clash.
                            return false;
                        }
                    }
                }
                else{
                    $workingArr[]=$pastExam;
                }
            }
        }

            //check against enrolled bids
            
            foreach ($alrEnrolled as $k=>$v){
                $enrolledSection = $v[0];
                $pastEnrolledExam=$bidsDao->getCourseExambyId($k);
                foreach ($workingArr as $i){
    
                    //first check exam date
                    if (strtotime($i[0])==strtotime($pastEnrolledExam[0])){
                        //clashing date
    
                        //then check if start time 2 later or equal than start time 1
                        if (strtotime($pastEnrolledExam[1]) >= strtotime($i[1])){
                            //if so, check if start time 2 earlier or equal than end time 1
                            if (strtotime($pastEnrolledExam[1]) <= strtotime($i[2])){
                                
                                //that's a clash.
                                return false;
                            }
                        }
                    }
                    else{
                        $workingArr[]=$pastEnrolledExam;
                    }
                }
            }

        //var_dump($workingArr);
        return true;
    }


    //returns null if not work, returns user edollar amount if work
    function validateBidAmount($bidsDao, $user, $coursesBidded){
        $userEdollar=$bidsDao->getUserEDollars($user);
        
        //calculate edollars spent
        $bill=0;
        foreach ($coursesBidded as $key=>$value){
            $bill+=$value[1];
        }

        //and validate it
        if ($bill>$userEdollar){
            return -10;
        }
        else{
            return ($userEdollar-$bill);
        }
    }

####################### END FUNCTION DECLARATIONS ######################################

?>