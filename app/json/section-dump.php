<?php
# http://localhost/app/json/section-dump.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTEyIDAxOjQ1OjQxIn0.rBCzVdS6lRPnHYNuj2i8fZpCnTv2DM4my9SiVtqAohg&r={"course":"IS100","section":"S1"}
# http://localhost/app/json/section-dump.php?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTEyIDAxOjQ1OjQxIn0.rBCzVdS6lRPnHYNuj2i8fZpCnTv2DM4my9SiVtqAohg&r={"course":"IS100","section":"S10"}
require_once ("../include/token.php");
require_once ("../include/autoload.php");

$status = "success";
$message = [];
$studentArr=[];
$bidsDao = new BidsDAO();
$isSuccessful=false;

if(isset($_GET["token"])){
    $token = $_GET["token"];
    if($token==''){
        array_push($message, "blank token");
        $status = 'error';
    }else if (strtolower(verify_token($_GET["token"])) == "admin"){
              
        if(isset($_GET['r'])){
            
            $r = json_decode($_GET['r'], TRUE);
            //for course//
            if(!isset($r['course'])){
                array_push($message, "missing course");
                $status = 'error';
            }else if($r['course'] == ''){
                array_push($message, "blank course");
                $status = 'error';
            }else{
                if(!isset($r['section'])){
                    array_push($message, "missing section");
                    $status = 'error';
                }else if($r['section'] == ''){
                    array_push($message, "blank section");
                    $status = 'error';
                }
                else{
                    $course=trim($r['course']);
                    $section=trim($r['section']);

                    $allCourse=$bidsDao->getAllCourseCodes();
                    $allSections=$bidsDao->getAllSectionsOnly();

                    if (!in_array($course,$allCourse)){
                        array_push($message, "invalid course");
                    } else {
                        if (!in_array($section,$allSections[$course])){
                            array_push($message, "invalid section");
                        }
                        else {
                            $students=getEnrolledCoursesByCourseSection($course,$section);

                            $studentArr=[];
        
                            foreach ($students as $key=>$value){
                                $oneStudent["userid"]=$key;
                                $oneStudent["amount"]=(float)$value;
                                $studentArr[]=$oneStudent;
                            }
                            $isSuccessful=true;
                        }
                    }
                }
            }
        }
    }else{
        array_push($message, "invalid token");
        $status = 'error';
    }

}else{
    array_push($message, "missing token");
    $status = 'error';
}

if ($isSuccessful){
    $output = ["status"=>$status, "students"=>$studentArr];
}else{
    $output = ["status"=>$status,"message"=>$message];
}

header("Content-Type: application/json");
echo json_encode($output, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);


function getEnrolledCoursesByCourseSection($course,$section){
    //return assoc array [userid=>bidamount]
            $resultArr=[];

            $connMgr = new connectionManager();
            $pdo=$connMgr->getConnection();
            $sql='select userid, bidamount from course_enrolled where course=:course and section=:section order by userid asc';
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
?>