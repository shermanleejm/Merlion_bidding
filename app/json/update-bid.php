<?php

include_once "../include/autoload.php";
include_once "../include/token.php";
include_once "../student/processRound2BidFunctions.php";

$status = "success";
$message = [];
$bidsDao = new BidsDAO();

if(isset($_GET["token"])){
    $token = $_GET["token"];
    // isset(verify_token($token));
    if($token==''){
        array_push($message, "blank token");
        $status = 'error';
    }
    else if (strtolower(verify_token($_GET["token"])) == "admin"){
        ////////////1////////////////
        if(isset($_GET['r'])){
 ////////////////////////2222////////////////////////////           
            $r = json_decode($_GET['r'], TRUE);

            //ROUND STATUS LEGIT
            $roundstatus = $bidsDao->getRound();
            if($roundstatus % 2 ==0 ){
                $message = [];
                array_push($message,"round ended");
                $status = "error";
                $output = ["status"=>$status,"message"=>$message];
                header("Content-Type: application/json");
                echo json_encode($output, JSON_PRETTY_PRINT);
                exit();
            }

            //for amount//
            if(!isset($r['amount'])){
                array_push($message, "missing amount");
                $status = 'error';
            }else if($r['amount'] == ''){
                array_push($message, "blank amount");
                $status = 'error';
            }else{

                $edollartest = $r['amount'];
                if(preg_match('/\.\d{3,}/', $edollartest) || $edollartest<10 || !is_numeric($edollartest)){
                    array_push($message, "invalid amount");
                    $status = 'error';
                }else{
                    $edollar = $r['amount'];
                    }
                
                // $pos = strpos($edollartest,'.');
                // if($pos === FALSE){
                //     if($edollartest < 10 || !is_numeric($edollartest)){
                //         array_push($message, "invalid amount");
                //         $status = 'error';
                //     }else{
                //         $edollar = $r['amount'];
                //     }
                // }else{
                //     $edollarlist = $edollartest.explode('.',$edollartest);
                //     if($edollarlist[1] > 2 || $edollartest<10 || !is_numeric($edollartest)){
                //         array_push($message, "invalid amount");
                //         $status = 'error';
                //     }else{
                //         $edollar = $r['amount'];
                //         }
                }
            
            ///end of amount validation//
            
            //for course and section tgt//
            if(!isset($r['course'])){
                array_push($message, "missing course");
                $status = 'error';
            }else if($r['course'] == ''){
                array_push($message, "blank course");
                $status = 'error';
            }
            
            if (!isset($r['section'])){
                array_push($message, "missing section");
                $status = 'error';
            }else if($r['section'] == ''){
                array_push($message, "blank section");
                $status = 'error';
            }

            if(!in_array("blank course", $message)){ 
            $result = [];
            $coursec = $r['course'];
            $connMgr = new connectionManager();
            $conn=$connMgr->getConnection();
            $sql0 = " SELECT * FROM course";
            $stmt = $conn->prepare($sql0);
            $stmt->execute();
            while($row = $stmt->fetch()) {
                $result[]=$row['course'];
            }
    
            if(!in_array($coursec,$result)){
                array_push($message,"invalid courseid");
                $status = "error";
                // end of course validation//
            }else{
                //check for section//
                //start of section validation//
                $course = $r['course'];
                if(!isset($r['section'])){
                    if(!in_array("missing section",$message)){
                    array_push($message, "missing section");}
                    $status = 'error';
                }else if($r['section'] == ''){
                    if(!in_array("blank section",$message)){
                    array_push($message, "blank section");}
                    $status = 'error';
                }else{ 
                $sections = [];
                $sectionc = $r['section'];
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql1 = " SELECT * FROM section WHERE course=:course";
                $stmt = $pdo->prepare($sql1);
                $stmt->bindParam(':course',$course,PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    $sections[]=$row['section'];
                }
    
                if(!in_array($sectionc,$sections)){
                    array_push($message,"invalid section");
                    $status = "error";
                }else{
                    $section = $r['section'];
                    //end of section validation//
                }
                }
            }
            
//
            }
                //for userid//
                if(!isset($r['userid'])){
                    array_push($message, "missing userid");
                    $status = 'error';
                }else if($r['userid'] == ''){
                    array_push($message, "blank userid");
                    $status = 'error';
                }else{ 
                $useridd = $r['userid'];
                $resultss = [];
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql2 = " SELECT * FROM student " ;
                $stmt = $pdo->prepare($sql2);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                    $resultss[]=$row['userid'];
                }
    
                if(!in_array($useridd,$resultss)){
                    array_push($message,"invalid userid");
                    $status = "error";
                }else{
                    $userid = $r['userid'];
                    }
                }

                if(isset($userid) && isset($edollar) && isset($course) && isset($section)){
                    $validity = 'hi';
                    $courses_completed = $bidsDao->getCompletedCourses($userid);
                    if(in_array($course,$courses_completed)){
                        array_push($message,"course completed");
                        $status = "error";
                    }

                    $courses_enrolled = $bidsDao->getEnrolledCourses($userid);
                    if(array_key_exists($course,$courses_enrolled)){
                        array_push($message,"course enrolled");
                        if( $section == $courses_enrolled[$course][0]){
                            if(!in_array("class timetable clash",$message)){
                                array_push($message,"class timetable clash");
                            }
                            if(!in_array("exam timetable clash",$message)){
                                array_push($message,"exam timetable clash");
                            }
                        }
                        $status = "error";
                    }

                    $course_prerequisite = $bidsDao->getCoursePrerequisite($course);                   
                
                    if(!empty($course_prerequisite)){ 
                        foreach ($course_prerequisite as $n){
                            if (!in_array($n, $courses_completed)){
                                array_push($message,"incomplete prerequisites");
                                $status = "error";
                            }
                        }
                    }

                        if($roundstatus ==1){
                            $school = $bidsDao->getUserSchool($userid);
                            $connMgr = new connectionManager();
                            $pdo=$connMgr->getConnection();
                            $sql='select school from course where course=:course';
                            $stmt=$pdo->prepare($sql);
                            $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                            $stmt->execute();
                            $stmt->setFetchMode(PDO::FETCH_ASSOC);
                            while($row = $stmt->fetch()) {
                                    $schoolcourse=trim($row['school']);
                            }

                            if($school != $schoolcourse){
                                array_push($message,"not own school course");
                                $status = "error";
                            }
                            //check edollar//
                            $currentedollar = $bidsDao->getUserEDollars($userid);

                            $codeArr = [];
                            $connMgr = new connectionManager();
                            $pdo=$connMgr->getConnection();
                            $sql='select * from bid where userid=:userid';
                            $stmt=$pdo->prepare($sql);
                            $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                            $stmt->execute();
                            $stmt->setFetchMode(PDO::FETCH_ASSOC);
                            while($row = $stmt->fetch()) {
                                    $codeArr[trim($row['code'])]=array(trim($row['amount']), trim($row['section']));
                            }
                            
                            if(array_key_exists($course,$codeArr)){
                                $bidamt = $codeArr[$course][0];
                                $hiddenedollar = $bidamt + $currentedollar;
                                if($edollar > $hiddenedollar){
                                    array_push($message,"insufficient edollar");
                                    $status = "error";
                                }
                                //IF SAME SECTION SAME COURSE
                                if($section==$codeArr[$course][1] && $status != 'error'){
                                    //  DO DELETE BID AND UPDATE EDOLLAR//
                                    $bidnewarr = array($course=>[$section,$edollar]);
                                    $deletearr = array(array($course,$bidamt));

                                    $bidsDao->deleteBid($userid,$deletearr);
                                    $bidsDao->addAllBids($userid, $bidnewarr);

                                    $newedollar = $hiddenedollar - $edollar;
                                    $bidsDao->setUserEDollars($userid, $newedollar);

                                
                                }elseif($section!=$codeArr[$course][1] && !array_key_exists($course,$courses_enrolled)){
                                    $currentsectiontime = $bidsDao->getSectionTimebyid($course,$section);
                                    $currentexamtime = $bidsDao->getCourseExambyId($course);
                                    $arrayofsectiontimes = [];
                                    $arrayofexamtimes = [];
                                    foreach($codeArr as $coursecode=>$details){
                                        if($coursecode != $course){
                                        $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($coursecode,$details[1]);
                                        $arrayofexamtimes[]=$bidsDao->getCourseExambyId($coursecode);}
                                    }
                                    $enrolledcourses = $bidsDao->getEnrolledCourses($userid);
                                    foreach($enrolledcourses as $key=>$value){
                                        $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($key,$value[0]);
                                        $arrayofexamtimes[]=$bidsDao->getCourseExambyId($key);
                                    }
                                   

                                    if(!empty($arrayofsectiontimes) && in_array($currentsectiontime,$arrayofsectiontimes)){
                                        
                                        array_push($message,"class timetable clash");
                                        $status = "error";
                                    }

                                    else{
                                        foreach($arrayofsectiontimes as $sectiontimes){
                                            if ($currentsectiontime[0] == $sectiontimes[0]){
                                                $lowerboundlist = explode(':',$currentsectiontime[1]);
                                                $lowerbound = implode('',$lowerboundlist);
                                                $lowerbound = intval($lowerbound);

                                                $upperboundlist = explode(':',$currentsectiontime[2]);
                                                $upperbound = implode('',$upperboundlist);
                                                $upperbound = intval($upperbound);

                                                $comparinglowerboundlist = explode(':',$sectiontimes[1]);
                                                $comparinglowerbound = implode('',$comparinglowerboundlist);
                                                $comparinglowerbound = intval($comparinglowerbound);

                                                $comparingupperboundlist = explode(':',$sectiontimes[2]);
                                                $comparingupperbound = implode('',$comparingupperboundlist);
                                                $comparingupperbound = intval($comparingupperbound);

                                                if($comparinglowerbound > $lowerbound && $comparinglowerbound <$upperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }elseif($comparingupperbound > $lowerbound && $comparingupperbound <$upperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }elseif($lowerbound > $comparinglowerbound && $lowerbound < $comparingupperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }elseif($upperbound > $comparinglowerbound && $upperbound < $comparingupperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }
                                            }
                                        }
                                    }

                                    // if(!empty($arrayofexamtimes) && in_array($currentexamtime,$arrayofexamtimes)){
                                    //     if($coyrse)
                                    //     array_push($message,"exam timetable clash");
                                    //     $status = "error";
                                    // }


                                    // UPDATE bid with diff code IF NO ERROR 
                                    if($status != 'error'){
                                        // $newsection = $codeArr[$course][1];
                                        $bidnewarr = array($course=>[$section,$edollar]);
                                        $deletearr = array(array($course,$bidamt));

                                        $bidsDao->deleteBid($userid,$deletearr);
                                        $bidsDao->addAllBids($userid, $bidnewarr);

                                        $newedollar = $hiddenedollar - $edollar;
                                        $bidsDao->setUserEDollars($userid, $newedollar);
                                    }
                                    


                                }

                            }elseif($edollar>$currentedollar){
                                array_push($message,"insufficient edollar");
                                $status = "error";
                            }

                            if(!array_key_exists($course,$codeArr) && !array_key_exists($course,$courses_enrolled)){
                                

                                $currentsectiontime = $bidsDao->getSectionTimebyid($course,$section);
                                $currentexamtime = $bidsDao->getCourseExambyId($course);
                                $arrayofsectiontimes = [];
                                $arrayofexamtimes = [];
                                
                                foreach($codeArr as $coursecode=>$details){
                                    if($coursecode != $course){
                                    $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($coursecode,$details[1]);
                                    $arrayofexamtimes[]=$bidsDao->getCourseExambyId($coursecode);}
                                }
                                $enrolledcourses = $bidsDao->getEnrolledCourses($userid);
                                foreach($enrolledcourses as $key=>$value){
                                    $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($key,$value[0]);
                                    $arrayofexamtimes[]=$bidsDao->getCourseExambyId($key);
                                }
                               

                                if(!empty($arrayofsectiontimes) && in_array($currentsectiontime,$arrayofsectiontimes)){
                                    array_push($message,"class timetable clash");
                                    $status = "error";
                                }else{
                                    foreach($arrayofsectiontimes as $sectiontimes){
                                        if ($currentsectiontime[0] == $sectiontimes[0]){
                                            $lowerboundlist = explode(':',$currentsectiontime[1]);
                                            $lowerbound = implode('',$lowerboundlist);
                                            $lowerbound = intval($lowerbound);

                                            $upperboundlist = explode(':',$currentsectiontime[2]);
                                            $upperbound = implode('',$upperboundlist);
                                            $upperbound = intval($upperbound);

                                            $comparinglowerboundlist = explode(':',$sectiontimes[1]);
                                            $comparinglowerbound = implode('',$comparinglowerboundlist);
                                            $comparinglowerbound = intval($comparinglowerbound);

                                            $comparingupperboundlist = explode(':',$sectiontimes[2]);
                                            $comparingupperbound = implode('',$comparingupperboundlist);
                                            $comparingupperbound = intval($comparingupperbound);

                                            if($comparinglowerbound > $lowerbound && $comparinglowerbound <$upperbound){
                                                if(!in_array("class timetable clash",$message)){
                                                array_push($message,"class timetable clash");}
                                                $status = "error";
                                            }elseif($comparingupperbound > $lowerbound && $comparingupperbound <$upperbound){
                                                if(!in_array("class timetable clash",$message)){
                                                array_push($message,"class timetable clash");}
                                                $status = "error";
                                            }elseif($lowerbound > $comparinglowerbound && $lowerbound < $comparingupperbound){
                                                if(!in_array("class timetable clash",$message)){
                                                array_push($message,"class timetable clash");}
                                                $status = "error";
                                            }elseif($upperbound > $comparinglowerbound && $upperbound < $comparingupperbound){
                                                if(!in_array("class timetable clash",$message)){
                                                array_push($message,"class timetable clash");}
                                                $status = "error";
                                            }
                                        }
                                    }
                                }
                                

                                if(!empty($arrayofexamtimes) && in_array($currentexamtime,$arrayofexamtimes)){
                                    array_push($message,"exam timetable clash");
                                    $status = "error";
                                }else{

                                    foreach($arrayofexamtimes as $examtimes){
                                        if ($currentexamtime[0] == $examtimes[0]){
                                            $lowerboundlist = explode(':',$currentexamtime[1]);
                                            $lowerbound = implode('',$lowerboundlist);
                                            $lowerbound = intval($lowerbound);

                                            $upperboundlist = explode(':',$currentexamtime[2]);
                                            $upperbound = implode('',$upperboundlist);
                                            $upperbound = intval($upperbound);

                                            $comparinglowerboundlist = explode(':',$examtimes[1]);
                                            $comparinglowerbound = implode('',$comparinglowerboundlist);
                                            $comparinglowerbound = intval($comparinglowerbound);

                                            $comparingupperboundlist = explode(':',$examtimes[2]);
                                            $comparingupperbound = implode('',$comparingupperboundlist);
                                            $comparingupperbound = intval($comparingupperbound);

                                            if($comparinglowerbound > $lowerbound && $comparinglowerbound <$upperbound){
                                                if(!in_array("exam timetable clash",$message)){
                                                array_push($message,"exam timetable clash");}
                                                $status = "error";
                                            }elseif($comparingupperbound > $lowerbound && $comparingupperbound <$upperbound){
                                                if(!in_array("exam timetable clash",$message)){
                                                array_push($message,"exam timetable clash");}
                                                $status = "error";
                                            }elseif($lowerbound > $comparinglowerbound && $lowerbound < $comparingupperbound){
                                                if(!in_array("exam timetable clash",$message)){
                                                array_push($message,"exam timetable clash");}
                                                $status = "error";
                                            }elseif($upperbound > $comparinglowerbound && $upperbound < $comparingupperbound){
                                                if(!in_array("exam timetable clash",$message)){
                                                array_push($message,"exam timetable clash");}
                                                $status = "error";
                                            }
                                        }
                                    }


                                }

                                
                            }
                           

                           

                            //check vacancy//
                            if( $bidsDao->getVacancy($course, $section) == 0){
                                array_push($message,"no vacancy");
                                $status = "error";
                            }
                            if(!array_key_exists($course,$codeArr)){
                                $limit = 5 - count($bidsDao->getEnrolledCourses($userid));
                                if((count($codeArr))>=$limit){
                                    array_push($message,"section limit reached");
                                    $status = "error";
                                }

                                if($status == 'success'){
                                    // DO THE ADD BID AND update edollar//
                                    $bidnewarr = array($course=>[$section,$edollar]);
                                    $bidsDao->addAllBids($userid, $bidnewarr);
                                    $newedollar = $currentedollar - $edollar;
                                    $bidsDao->setUserEDollars($userid, $newedollar);
                                }
                            }

                           
                            



                            //round 1///
                        }else{

                            //round2//
                            $currentedollar = $bidsDao->getUserEDollars($userid);

                            $codeArr = [];
                            $connMgr = new connectionManager();
                            $pdo=$connMgr->getConnection();
                            $sql='select * from bid where userid=:userid';
                            $stmt=$pdo->prepare($sql);
                            $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                            $stmt->execute();
                            $stmt->setFetchMode(PDO::FETCH_ASSOC);
                            while($row = $stmt->fetch()) {
                                    $codeArr[trim($row['code'])]=array(trim($row['amount']), trim($row['section']));
                            }
                            
                            //CALL MIN BID//
                            $minbid = $bidsDao->getMinBid($course, $section);
                            if($edollar < $minbid){
                                array_push($message,"bid too low");
                                $status = "error";
                            }

                            if(array_key_exists($course,$codeArr)){
                                $bidamt = $codeArr[$course][0];
                                $hiddenedollar = $bidamt + $currentedollar;
                                if($edollar > $hiddenedollar){
                                    array_push($message,"insufficient edollar");
                                    $status = "error";
                                }

                                if($section==$codeArr[$course][1] && $status != 'error'){
                                    //  DO DELETE BID AND UPDATE bid & EDOLLAR//
                                    //PROCESS MIN BID AGAIN//
                                    $bidnewarr = array($course=>[$section,$edollar]);
                                    $deletearr = array(array($course,$bidamt));

                                    $bidsDao->deleteBid($userid,$deletearr);
                                    $bidsDao->setUserEDollars($userid, $hiddenedollar);        
                                    //throw to process r2 bid
                                    //only place bid and update min bid
                                    $courseOrderForm[$course]=[$section,$edollar];
                                    doR2Update($bidsDao, $courseOrderForm, $userid); //<-places bid and sets min value as well                                  
                                }else{
                                    $currentsectiontime = $bidsDao->getSectionTimebyid($course,$section);
                                    $currentexamtime = $bidsDao->getCourseExambyId($course);
                                    $arrayofsectiontimes = [];
                                    $arrayofexamtimes = [];
                                    foreach($codeArr as $coursecode=>$details){
                                        if($coursecode != $course){
                                        $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($coursecode,$details[1]);
                                        $arrayofexamtimes[]=$bidsDao->getCourseExambyId($coursecode);}
                                    }
                                    $enrolledcourses = $bidsDao->getEnrolledCourses($userid);
                                    foreach($enrolledcourses as $key=>$value){
                                        $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($key,$value[0]);
                                        $arrayofexamtimes[]=$bidsDao->getCourseExambyId($key);
                                    }

                                    if(!empty($arrayofsectiontimes) && in_array($currentsectiontime,$arrayofsectiontimes) && !array_key_exists($course,$courses_enrolled)){
                                        array_push($message,"class timetable clash");
                                        $status = "error";
                                    }else{
                                        foreach($arrayofsectiontimes as $sectiontimes){
                                            if ($currentsectiontime[0] == $sectiontimes[0]){
                                                $lowerboundlist = explode(':',$currentsectiontime[1]);
                                                $lowerbound = implode('',$lowerboundlist);
                                                $lowerbound = intval($lowerbound);

                                                $upperboundlist = explode(':',$currentsectiontime[2]);
                                                $upperbound = implode('',$upperboundlist);
                                                $upperbound = intval($upperbound);

                                                $comparinglowerboundlist = explode(':',$sectiontimes[1]);
                                                $comparinglowerbound = implode('',$comparinglowerboundlist);
                                                $comparinglowerbound = intval($comparinglowerbound);

                                                $comparingupperboundlist = explode(':',$sectiontimes[2]);
                                                $comparingupperbound = implode('',$comparingupperboundlist);
                                                $comparingupperbound = intval($comparingupperbound);

                                                if($comparinglowerbound > $lowerbound && $comparinglowerbound <$upperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }elseif($comparingupperbound > $lowerbound && $comparingupperbound <$upperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }elseif($lowerbound > $comparinglowerbound && $lowerbound < $comparingupperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }elseif($upperbound > $comparinglowerbound && $upperbound < $comparingupperbound){
                                                    if(!in_array("class timetable clash",$message)){
                                                    array_push($message,"class timetable clash");}
                                                    $status = "error";
                                                }
                                            }
                                        }
                                    }

                                    // if(in_array($currentexamtime,$arrayofexamtimes)){
                                    //     array_push($message,"exam timetable clash");
                                    //     $status = "error";
                                    // }
                                    
                                    //UPDATE BID IF NO ERROR// RECALCULATE MIN BID//
                                    //HERE//
                                    $courseOrderForm[$course]=[$section,$edollar];
                                    doR2Update($bidsDao, $courseOrderForm, $userid); //<-places bid and sets min value as well 


                                    $bidnewarr = array($course=>[$section,$edollar]);
                                    $deletearr = array(array($course,$bidamt));

                                    $bidsDao->deleteBid($userid,$deletearr);
                                    $bidsDao->setUserEDollars($userid, $hiddenedollar);  
                                }

                            }elseif($edollar>$currentedollar){
                                array_push($message,"insufficient edollar");
                                $status = "error";
                            }
                            if(!array_key_exists($course,$codeArr) && !array_key_exists($course,$courses_enrolled)){
                            $currentsectiontime = $bidsDao->getSectionTimebyid($course,$section);
                            $currentexamtime = $bidsDao->getCourseExambyId($course);
                            $arrayofsectiontimes = [];
                            $arrayofexamtimes = [];
                            
                            foreach($codeArr as $coursecode=>$details){
                                if($coursecode != $course){
                                $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($coursecode,$details[1]);
                                $arrayofexamtimes[]=$bidsDao->getCourseExambyId($coursecode);}
                            }
                            $enrolledcourses = $bidsDao->getEnrolledCourses($userid);
                            foreach($enrolledcourses as $key=>$value){
                                $arrayofsectiontimes[]=$bidsDao->getSectionTimebyid($key,$value[0]);
                                $arrayofexamtimes[]=$bidsDao->getCourseExambyId($key);
                            }
                           

                            if(!empty($arrayofsectiontimes) && in_array($currentsectiontime,$arrayofsectiontimes)){
                                array_push($message,"class timetable clash");
                                $status = "error";
                            }else{
                                foreach($arrayofsectiontimes as $sectiontimes){
                                    if ($currentsectiontime[0] == $sectiontimes[0]){
                                        $lowerboundlist = explode(':',$currentsectiontime[1]);
                                        $lowerbound = implode('',$lowerboundlist);
                                        $lowerbound = intval($lowerbound);

                                        $upperboundlist = explode(':',$currentsectiontime[2]);
                                        $upperbound = implode('',$upperboundlist);
                                        $upperbound = intval($upperbound);

                                        $comparinglowerboundlist = explode(':',$sectiontimes[1]);
                                        $comparinglowerbound = implode('',$comparinglowerboundlist);
                                        $comparinglowerbound = intval($comparinglowerbound);

                                        $comparingupperboundlist = explode(':',$sectiontimes[2]);
                                        $comparingupperbound = implode('',$comparingupperboundlist);
                                        $comparingupperbound = intval($comparingupperbound);

                                        if($comparinglowerbound > $lowerbound && $comparinglowerbound <$upperbound){
                                            if(!in_array("class timetable clash",$message)){
                                            array_push($message,"class timetable clash");}
                                            $status = "error";
                                        }elseif($comparingupperbound > $lowerbound && $comparingupperbound <$upperbound){
                                            if(!in_array("class timetable clash",$message)){
                                            array_push($message,"class timetable clash");}
                                            $status = "error";
                                        }elseif($lowerbound > $comparinglowerbound && $lowerbound < $comparingupperbound){
                                            if(!in_array("class timetable clash",$message)){
                                            array_push($message,"class timetable clash");}
                                            $status = "error";
                                        }elseif($upperbound > $comparinglowerbound && $upperbound < $comparingupperbound){
                                            if(!in_array("class timetable clash",$message)){
                                            array_push($message,"class timetable clash");}
                                            $status = "error";
                                        }
                                    }
                                }
                            }

                            if(!empty($arrayofexamtimes) && in_array($currentexamtime,$arrayofexamtimes)){
                                array_push($message,"exam timetable clash");
                                $status = "error";
                            }else{
                                foreach($arrayofexamtimes as $examtimes){
                                    if ($currentexamtime[0] == $examtimes[0]){
                                        $lowerboundlist = explode(':',$currentexamtime[1]);
                                        $lowerbound = implode('',$lowerboundlist);
                                        $lowerbound = intval($lowerbound);

                                        $upperboundlist = explode(':',$currentexamtime[2]);
                                        $upperbound = implode('',$upperboundlist);
                                        $upperbound = intval($upperbound);

                                        $comparinglowerboundlist = explode(':',$examtimes[1]);
                                        $comparinglowerbound = implode('',$comparinglowerboundlist);
                                        $comparinglowerbound = intval($comparinglowerbound);

                                        $comparingupperboundlist = explode(':',$examtimes[2]);
                                        $comparingupperbound = implode('',$comparingupperboundlist);
                                        $comparingupperbound = intval($comparingupperbound);

                                        if($comparinglowerbound > $lowerbound && $comparinglowerbound <$upperbound){
                                            if(!in_array("exam timetable clash",$message)){
                                            array_push($message,"exam timetable clash");}
                                            $status = "error";
                                        }elseif($comparingupperbound > $lowerbound && $comparingupperbound <$upperbound){
                                            if(!in_array("exam timetable clash",$message)){
                                            array_push($message,"exam timetable clash");}
                                            $status = "error";
                                        }elseif($lowerbound > $comparinglowerbound && $lowerbound < $comparingupperbound){
                                            if(!in_array("exam timetable clash",$message)){
                                            array_push($message,"exam timetable clash");}
                                            $status = "error";
                                        }elseif($upperbound > $comparinglowerbound && $upperbound < $comparingupperbound){
                                            if(!in_array("exam timetable clash",$message)){
                                            array_push($message,"exam timetable clash");}
                                            $status = "error";
                                        }
                                    }
                                }


                            }
                        }
                            //check vacancy//
                            if( $bidsDao->getVacancy($course, $section) == 0){
                                array_push($message,"no vacancy");
                                $status = "error";
                            }
                            if(!array_key_exists($course,$codeArr)){
                                $limit = 5 - count($bidsDao->getEnrolledCourses($userid));
                                if((count($codeArr))>=$limit){
                                    array_push($message,"section limit reached");
                                    $status = "error";
                                }

                                if($status == 'success'){
                                    //UPDATE BID HERE//retabluate min bid
                                    $courseOrderForm[$course]=[$section,$edollar];
                                    doR2Update($bidsDao, $courseOrderForm, $userid); //<-places bid and sets min value as well 
                                    
                                    $bidnewarr = array($course=>[$section,$edollar]);
                                    //$deletearr = array(array($course,$bidamt));

                                    //$bidsDao->deleteBid($userid,$deletearr);
                                $bidsDao->setUserEDollars($userid, /*$hiddenedollar*/$currentedollar-$edollar); 
                                }


                            }

                            

                        }

                    

                }

                
 ////////////////////////2222////////////////////////////  
            }else{
                array_push($message,"missing amount");
                array_push($message,"missing course");
                array_push($message,"missing userid");
                $status = "error";
            }
/////////////////1//////////////////////
}else{
    array_push($message, "invalid token");
    $status = 'error';
}
}else{
    array_push($message, "missing token");
    $status = 'error';
}


if($status == 'error'){
    if(isset($validity)){
        sort($message);
    }
    $output = ["status"=>$status,"message"=>$message];
}else{
    $output = ["status"=>$status];
}

header("Content-Type: application/json");
echo json_encode($output, JSON_PRETTY_PRINT);

//START FUNCTIONS//

function doR2Update($bidsDao, $courseOrderForm, $user)
{
    //courseorderform - [coursecode=>[section, amount]]
    foreach ($courseOrderForm as $key=>$value){
        $temp=doUpdateBid($bidsDao,$user, $courseOrderForm, $key, $value[0], $value[1]);
        $canWrite=$temp[0];
        $newMinBid=$temp[1];

        //write bid and clearing price
        if ($canWrite){
            $courseBidded[$key]=[$value[0],$value[1]];
            $bidsDao->addAllBids($user,$courseBidded);

            $bidsDao->setMinBid($key, $value[0], $newMinBid);
            $courseBidded=null;
        }
    }
}