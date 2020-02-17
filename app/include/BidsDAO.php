<?php 

###########################################################################################
#  BIDS DAO
#
#
###########################################################################################

class bidsDAO{

        // public function __construct(){
        //         require_once("connectionManager.php");
        // }

        public function isValidUserID($userid){
                $status=false;
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from student where userid=:userid';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                if ($row = $stmt->fetch())
                        $status = true;
                $stmt=null;
                $pdo=null;
                return $status;
        }

        public function isValidSection($course, $section){
                $status=false;
                $row;
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from section where course=:course and section=:section';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                if ($row = $stmt->fetch())
                        $status = true;
                $stmt=null;
                $pdo=null;
                return $status;
        }       
        
        /* FUNCTION: getUserBids */
        //returns 2D normal array where:
        // [0] - amount (num)
        // [1] - code (string)
        // [2] - section (string)
        public function getUserBids($userid){
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from bid where userid=:userid';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[]=array($row['amount'], trim($row['code']), trim($row['section']));
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;
        }

        /* FUNCTION: getUserEDollars */
        //returns num value
        public function getUserEDollars($userid){
                $edollar=0;
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select edollar from student where userid=:userid';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                if ($row = $stmt->fetch())
                        $edollar=$row['edollar'];
                $stmt=null;
                $pdo=null;
                return $edollar;
        }

        public function setUserEDollars($userid, $amount){
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='update student set edollar=:edollar where userid=:userid;';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->bindParam(':edollar', $amount, PDO::PARAM_STR);
                $success=$stmt->execute();
                $stmt=null;
                $pdo=null;
                return $success;   
        }

        public function getBid($userid,$code){
                $output=null;
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from bid where userid=:userid and code=:code';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->bindParam(':code', $code, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                if ($row = $stmt->fetch())
                        $output=array($row['amount'], trim($row['code']), trim($row['section']));
                $stmt=null;
                $pdo=null;
                return $output;
        }
         /* FUNCTION: deleteBid */
        // takes userid and array of bids_to_delete where bids_to_delete[0] is coursecode  
        // returns false if failure and true if success
        public function deleteBid($userid, $courseArr){ 
                //where $courseArr is an array of [courseid, e$ bidded]
                        $connMgr = new connectionManager();
                        $pdo=$connMgr->getConnection();
                        $status=false;
        
                        foreach ($courseArr as $item){
        
                                //to delete the course
                                $sql='delete from bid where userid=:userid and code=:code';
                                $stmt=$pdo->prepare($sql);
                                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                                $stmt->bindParam(':code', $item[0], PDO::PARAM_STR);
                                $status=$stmt->execute();
                                $stmt=null; 
                                
                                //to update the e$ - first fetch the existing e$
                                $newEdollar=$this->getUserEDollars($userid);
        
                                // then tabulate
                                $newEdollar+=$item[1];
        
                                //and write back
                                $sql='update student set edollar=:newEdollar where userid=:userid;';
                                $stmt=$pdo->prepare($sql);
                                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                                $stmt->bindParam(':newEdollar', $newEdollar, PDO::PARAM_STR);
                                $status=$stmt->execute();
                        }
                $stmt=null; 
                $pdo=null;     
                return $status;      
        }

        public function getAllCourseCodes(){
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select course from course';
                $stmt=$pdo->prepare($sql);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[]=trim($row['course']);
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;                
        }

        /* FUNCTION: getAllCourses */
        //returns associative array where:
        // key - coursecode (string)
        // value -      [0] - school
        //              [1] - title
        //              [2] - description
        //              [3] - examdate
        //              [4] - examstart
        //              [5] - examend
        public function getAllCourses(){
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from course';
                $stmt=$pdo->prepare($sql);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[trim($row['course'])]=array(trim($row['school']), trim($row['title']), trim($row['descr']), 
                                        $row['examdate'], $row['examstart'], $row['examend']);
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;
        }

        public function getCourseExambyId($course){
                $result ="";
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from course where course=:course';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $result= [$row["examdate"], $row["examstart"], $row["examend"]];
                }
                $stmt=null;
                $pdo=null;
                return $result;
        }

        public function getSectionTimebyid($course,$section){
                $result=[];
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from section where course=:course and section=:section';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $result= [$row["dayoftheweek"], $row["starttime"], $row["endtime"]];
                    }
                $stmt=null;
                $pdo=null;
                return $result;
        }

        /* FUNCTION: getCoursePrerequisite */
        //returns normal array of all prerequisites associated with this course
        public function getCoursePrerequisite($coursecode){
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select prerequisite from prerequisite where course=:code';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':code', $coursecode, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[]=trim($row['prerequisite']);
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;
        }

        /* FUNCTION: getCompletedCourses */
        //returns normal array of all coursecodes previously completed by user
        public function getCompletedCourses($userid){
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select code from course_completed where userid=:userid';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->execute();
                
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[]=trim($row['code']);
                }
                
                $stmt=null;
                $pdo=null;
                return $resultArr;                
        }

        /* FUNCTION: getBiddableCourses */
        //returns associative array where:
        // key - coursecode (string)
        // value -      [0] - school
        //              [1] - title
        //              [2] - description
        //              [3] - examdate
        //              [4] - examstart
        //              [5] - examend
        // does not include courses that user previously completed but include courses that 
        //still needs prerequisites
        public function getBiddableCourses($userid){

                //get list of all courses
                $allCourses=$this->getAllCourses();

                //get list of completed courses
                $completedCourses=$this->getCompletedCourses($userid);

                $availableCourses=[];

                //filter to list of courses user can pick
                //first take out all completed courses

                foreach ($allCourses as $key=>$value){
                        if (!in_array($key, $completedCourses, true)){
                                $availableCourses[$key]=$value;
                        }
                }

                //note this does not filter out the courses which alr have bids. i wrote it this way as its 
                //more intuitive for user to be prompted which bid to keep upon clashing bids.
                //may revise if theres time constraint

                //return list of biddable courses in associative array
                //$availableCourses=[];
                return $availableCourses;
        }

        public function getAllSectionsOnly(){
                //returns assoc array [coursecode=>[section, section, ...]]
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from section';
                $stmt=$pdo->prepare($sql);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $currCourse=trim($row['course']);
                        $newSection=trim($row['section']);
                        if (!array_key_exists($currCourse,$resultArr)){
                                $resultArr[$currCourse]=[$newSection];
                        }
                        else{
                                $temp=$resultArr[$currCourse];
                                $temp[]=$newSection;
                                $resultArr[$currCourse]=$temp;
                        }
                        $currCourse=null;
                        $newSection=null;
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;
        }

        public function getAllSections(){
                //the array returned is an associative array nested in another associative array
                // Outer associative array = key->course code, value->associative array containing section info
                // Inner associative array = key->session code, value->details associated with section
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from section';
                $stmt=$pdo->prepare($sql);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $currCourse=trim($row['course']);
                        $sectionName=trim($row['section']);
                        $sectionDetails=array($row['dayoftheweek'], $row['starttime'], 
                        $row['endtime'], trim($row['instructor']), trim($row['venue']), $row['size']);
                        $newSection[$sectionName]=$sectionDetails;
                        if (!array_key_exists($currCourse,$resultArr)){
                                $resultArr[$currCourse]=$newSection;
                        }
                        else{
                                $resultArr[$currCourse][$sectionName]=$sectionDetails;
                        }
                        $currCourse=null;
                        $newSection=null;
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;
        }

        public function getBidsByCodeSection($coursecode, $section){
        //takes in 2 strings coursecode and section
        //returns associative array with userid as key and amount as value
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select userid, amount from bid where code=:coursecode and section=:section order by amount desc, userid asc';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $userid=trim($row['userid']);
                        $amount=$row['amount'];
                        $resultArr[$userid]=$amount;
                        $userid=null;
                        $amount=null;
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;                
        }

        public function getbidsamount($userid, $coursecode, $section){
                //takes in 2 strings coursecode and section
                        $amount=null;
        
                        $connMgr = new connectionManager();
                        $pdo=$connMgr->getConnection();
                        $sql='select amount from bid where code=:coursecode and section=:section and userid=:userid';
                        $stmt=$pdo->prepare($sql);
                        $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
                        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        while($row = $stmt->fetch()) {
                                $amount=$row['amount'];
                        }
                        $stmt=null;
                        $pdo=null;
                        return $amount;                
                }

                //$coursebiddedarray = $code=>[$section,$amount]
        public function addAllBids($userid, $coursesBiddedArray) {

                $sql = 'INSERT INTO bid (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';
                
                $connMgr = new connectionManager();       

                foreach ($coursesBiddedArray as $key=>$value){
                        $conn = $connMgr->getConnection();
                        $stmt = $conn->prepare($sql); 

                        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                        $stmt->bindParam(':amount', $value[1], PDO::PARAM_STR);
                        $stmt->bindParam(':code', $key, PDO::PARAM_STR);
                        $stmt->bindParam(':section', $value[0], PDO::PARAM_STR);
                        
                        $status = False;
                        if ($stmt->execute()) {
                                $status = True;
                        }
                        $stmt=null;
                        $conn=null;
                }
                return $status;
        }

        public function getVacancy($coursecode, $section){
                $vacancy=false;
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select size from section where course=:coursecode and section=:section';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                if ($row = $stmt->fetch())
                        $vacancy=$row['size'];
                $stmt=null;
                $pdo=null;
                return $vacancy;
        }

        public function setVacancy($newVacancy, $course, $section){
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='update section set size=:size where course=:course and section=:section';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':size', $newVacancy, PDO::PARAM_STR);
                $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $success=$stmt->execute();
                $stmt=null;
                $pdo=null;
                return $success;   
        }
        
        public function setRound($roundnumber){
        //deletes all values from roundnumber table and inserts new setround value
        //returns bool for success/failure
                $sql = 'delete from roundstatus;insert into roundstatus values (:roundnumber)';
                
                $connMgr = new connectionManager();       

                $conn = $connMgr->getConnection();
                $stmt = $conn->prepare($sql); 

                $stmt->bindParam(':roundnumber', $roundnumber, PDO::PARAM_STR);
                
                $status = False;
                if ($stmt->execute()) {
                        $status = True;
                }
                $stmt=null;
                $conn=null;
                return $status;
        }

        public function getRound(){
                //gets current round and returns it. false upon error
                $round=false;
                $sql = 'select * from roundstatus';
                
                $connMgr = new connectionManager();       

                $conn = $connMgr->getConnection();
                $stmt = $conn->prepare($sql); 
                $stmt->execute();
                if ($row = $stmt->fetch())
                        $round=intval($row['round']);
                $stmt=null;
                $conn=null;
                return $round;

        }

        public function getUniqueCoursesInBids(){
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select distinct code, section from bid;';
                $stmt=$pdo->prepare($sql);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[]=[(trim($row['code'])), (trim($row['section']))];
                }
                $stmt=null;
                $pdo=null;
                return $resultArr;     
        }

        public function getUserSchool($userid){
                //get user's school
                $school=false;
                $sql = 'select school from student where userid=:userid';
                
                $connMgr = new connectionManager();       

                $conn = $connMgr->getConnection();
                $stmt = $conn->prepare($sql); 
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->execute();
                if ($row = $stmt->fetch())
                        $school=trim($row['school']);
                $stmt=null;
                $conn=null;
                return $school;

        }

        public function setEnrolledCourse($userid, $coursecode, $section, $bidamount, $round){
                $connMgr = new ConnectionManager();
                $pdo=$connMgr->getConnection();
                $sql='insert into course_enrolled values (:userid, :coursecode, :section, :bidamount, :round)';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->bindParam(':bidamount', $bidamount, PDO::PARAM_STR);
                $stmt->bindParam(':round', $round, PDO::PARAM_STR);
                $success=$stmt->execute();
                $stmt=null;
                $pdo=null;
                return $success;                  
        }

        public function getEnrolledCoursesByRound($round){
        //where $round is "1","2", "ALL"
        //return normal array [userid, coursecode, section, bidamount]
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                if ($round=="ALL"){
                        $sql='select * from course_enrolled';
                        $stmt=$pdo->prepare($sql);
                        $stmt->execute();                        
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        while($row = $stmt->fetch()) {
                                $resultArr[]=[trim($row['userid']), trim($row['course']), trim($row['section']), trim($row['bidamount']), trim($row['round'])];
                        }
                }
                else{
                        $sql='select * from course_enrolled where round=:round';
                        $stmt=$pdo->prepare($sql);
                        $stmt->bindParam(':round', $round, PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        while($row = $stmt->fetch()) {
                                $resultArr[]=[trim($row['userid']), trim($row['course']), trim($row['section']), trim($row['bidamount']), trim($row['round'])];
                        }
                }
                
                $stmt=null;
                $pdo=null;
                return $resultArr;                     
        }

        public function getEnrolledCoursesByCourseSection($course,$section){
                //return assoc array [userid=>bidamount]
                        $resultArr=[];
        
                        $connMgr = new connectionManager();
                        $pdo=$connMgr->getConnection();
                        $sql='select userid, bidamount from course_enrolled where course=:course and section=:section order by bidamount desc, userid asc';
                        $stmt=$pdo->prepare($sql);
                        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        while($row = $stmt->fetch()) {
                                $resultArr[trim($row['userid'])]=trim($row['bidamount']);
                        }
                        
                        $stmt=null;
                        $pdo=null;
                        return $resultArr;                     
                }

                public function getEnrolledCoursesByCourseSectionRound($course,$section,$round){
                        //return assoc array [userid=>bidamount]
                                $resultArr=[];
                
                                $connMgr = new connectionManager();
                                $pdo=$connMgr->getConnection();
                                if ($round == 1 || $round == 2){
                                        $sql='select userid, bidamount from course_enrolled where course=:course and section=:section and round=:round order by bidamount desc, userid asc';
                                        $stmt=$pdo->prepare($sql);
                                        $stmt->bindParam(':round', $round, PDO::PARAM_STR);
                                }else{
                                        $sql='select userid, bidamount from course_enrolled where course=:course and section=:section order by bidamount desc, userid asc';
                                        $stmt=$pdo->prepare($sql);                                       
                                }

                                $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                                $stmt->bindParam(':section', $section, PDO::PARAM_STR);

                                $stmt->execute();
                                
                                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                while($row = $stmt->fetch()) {
                                        $resultArr[trim($row['userid'])]=trim($row['bidamount']);
                                }
                                
                                $stmt=null;
                                $pdo=null;
                                return $resultArr;                     
                        }
        


        public function getEnrolledCourses($userid){
        //return associative array 
        //course => [ section , bidamt ]
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select course,section,bidamount,round from course_enrolled where userid=:userid';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->execute();
                
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[trim($row['course'])]=[trim($row['section']), trim($row['bidamount']), trim($row['round'])];
                }
                
                $stmt=null;
                $pdo=null;
                return $resultArr;                    
        }

        public function setUnsuccessfulCourse($userid, $coursecode, $section, $bidamount, $round){
                $connMgr = new ConnectionManager();
                $pdo=$connMgr->getConnection();
                $sql='insert into course_unsuccessful values (:userid, :coursecode, :section, :bidamount, :round)';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->bindParam(':bidamount', $bidamount, PDO::PARAM_STR);
                $stmt->bindParam(':round', $round, PDO::PARAM_STR);
                $success=$stmt->execute();
                $stmt=null;
                $pdo=null;
                return $success;                  
        }

        public function getUnsuccessfulCourses($userid){
        //return associative array ([course,round]=>[section,bidamount])
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select * from course_unsuccessful where userid=:userid';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->execute();
                
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        // $key=[trim($row['course']), trim($row['round'])];
                        // $value=[trim($row['section']), trim($row['bidamount'])];
                        // $resultArr[$key]=$value;
                        $resultArr[]=[trim($row['course']), trim($row['section']), trim($row['bidamount']), trim($row['round'])];
                }
                
                $stmt=null;
                $pdo=null;
                return $resultArr;                    
        }

        public function getUnsuccessfulCoursesByRound($round){
                //where $round is "1","2", "ALL"
                //return normal array [userid, coursecode, section, bidamount]
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                if ($round=="ALL"){
                        $sql='select * from course_unsuccessful';
                        $stmt=$pdo->prepare($sql);
                        $stmt->execute();                        
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        while($row = $stmt->fetch()) {
                                $resultArr[]=[trim($row['userid']), trim($row['course']), trim($row['section']), trim($row['bidamount']), trim($row['round'])];
                        }
                }
                else{
                        $sql='select * from course_unsuccessful where round=:round';
                        $stmt=$pdo->prepare($sql);
                        $stmt->bindParam(':round', $round, PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        while($row = $stmt->fetch()) {
                                $resultArr[]=[trim($row['userid']), trim($row['course']), trim($row['section']), trim($row['bidamount']), trim($row['round'])];
                        }
                }
                
                $stmt=null;
                $pdo=null;
                return $resultArr;                     
        }

        public function getCombinedSuccessfulUnsuccessful($course, $section, $round){
                //where $round is "1","2", "ALL"
                //return associative array [userid]=[bidamount,status]
                $resultArr=[];

                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();

                if ($round==1 || $round==2){
                        $sql="select userid, bidamount, 'successful' as status from course_enrolled 
                        where course=:course and section=:section and round=:round
                        union all
                        (select userid, bidamount, 'unsuccessful' as status from course_unsuccessful 
                        where course=:course and section=:section and round=:round)
                        order by bidamount desc, userid asc";                        
                        $stmt=$pdo->prepare($sql);
                        $stmt->bindParam(':round', $round, PDO::PARAM_STR);
                }else{
                        $sql="select userid, bidamount, 'successful' as status from course_enrolled 
                        where course=:course and section=:section and round=:round
                        union all
                        (select userid, bidamount, 'unsuccessful' as status from course_unsuccessful 
                        where course=:course and section=:section)
                        order by bidamount desc, userid asc";
                        $stmt=$pdo->prepare($sql);
                }

                $stmt->bindParam(':course', $course, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->execute();
                
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                while($row = $stmt->fetch()) {
                        $resultArr[$row['userid']]=[$row['bidamount'], $row['status']];
                }
                
                $stmt=null;
                $pdo=null;
                return $resultArr;                     
        }

        public function deleteAllBids(){
                $sql = 'delete from bid';
                
                $connMgr = new connectionManager();       

                $conn = $connMgr->getConnection();
                $stmt = $conn->prepare($sql);                
                $status = False;
                if ($stmt->execute()) {
                        $status = True;
                }
                $stmt=null;
                $conn=null;
                return $status;
        }

        public function removeEnrolledCourse($userid, $coursecode){
                $sql = 'delete from course_enrolled where userid=:userid and course=:code';
                
                $connMgr = new connectionManager();       

                $conn = $connMgr->getConnection();
                $stmt = $conn->prepare($sql);   
                $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
                $stmt->bindParam(':code', $coursecode, PDO::PARAM_STR);             
                $status = False;
                if ($stmt->execute()) {
                        $status = True;
                }
                $stmt=null;
                $conn=null;
                return $status;               
        }

        public function resetAllMinBid(){
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='update section set minbid_r2=10';
                $stmt=$pdo->prepare($sql);
                $success=$stmt->execute();
                $stmt=null;
                $pdo=null;
                return $success;                   
        }

        public function getMinBid($coursecode, $section){
                $result=null;
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='select minbid_r2 from section where course=:course and section=:section';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':course', $coursecode, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                if ($row = $stmt->fetch())
                        $result=$row['minbid_r2'];
                $stmt=null;
                $pdo=null;
                return $result;
        }

        public function setMinBid($coursecode, $section, $amount){
                $connMgr = new connectionManager();
                $pdo=$connMgr->getConnection();
                $sql='update section set minbid_r2=:amount where course=:coursecode and section=:section;';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
                $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
                $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                $success=$stmt->execute();
                $stmt=null;
                $pdo=null;
                return $success;   
        }

        public function getBidsOrderedByAmountUserID($coursecode, $section){
                //takes in 2 strings coursecode and section
                //returns associative array with userid as key and amount as value
                        $resultArr=[];
        
                        $connMgr = new connectionManager();
                        $pdo=$connMgr->getConnection();
                        $sql="SELECT * FROM bid WHERE code=:coursecode AND section=:section ORDER BY amount DESC, userid ASC";
                        $stmt=$pdo->prepare($sql);
                        $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
                        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        while($row = $stmt->fetch()) {
                                $userid=trim($row['userid']);
                                $amount=$row['amount'];
                                $resultArr[$userid]=$amount;
                                $userid=null;
                                $amount=null;
                        }
                        $stmt=null;
                        $pdo=null;
                        return $resultArr;                   
        }
}
?>