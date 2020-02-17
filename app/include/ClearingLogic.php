<?php 

    include_once "constants.php";

    ###########################################################################################
    #  FUNCTION COLLECTION FOR CLEARING LOGIC
    #
    #   Here is all the useful clearing logic functions.  - Sue (2/10/2019)
    #   Has been rewritten in preparation for R2 - Sue (15/10/2019)
    #
    ###########################################################################################

    function sortAllBids($bidsDao, $coursecode, $section){
        //ranks all bids for that particular ccode and section in descending order 
        //returns an array with the sorted bids
        //returns   array if successful, 
        //          empty array if no result, 
        //          and false if unsuccessful

        $working=$bidsDao->getBidsByCodeSection($coursecode, $section);

        if (array_multisort($working, SORT_DESC, SORT_NUMERIC))
            return $working;
        else
            return false;
    }

    function sortAllBidsFromArray($working){
        //ranks all bids for that array in descending order 
        //returns an array with the sorted bids
        //returns   array if successful, 
        //          empty array if no result, 
        //          and false if unsuccessful

        if (array_multisort($working, SORT_DESC, SORT_NUMERIC))
            return $working;
        else
            return false;
    }

    function getVacancy($bidsDAO, $coursecode, $section){
    //this function gets the vacancy for the section and course
    //it just calls the $bidsDao function.
    //it returns integer value for the vacancy and false if unsuccessful.
        return $bidsDao->getVacancy($coursecode, $section);
    }

    function checkLessBidsThanVacancy($userBidsArr, $vacancy){
        $totalSize=sizeof($userBidsArr);
        if ($totalSize < $vacancy)
            return $totalSize;
        return false;
    }

    function checkSufficientMoreBidsThanVacancy($userBidsArr, $vacancy, $round){

        if ($userBidsArr!=false){
            $successfulArr=[];
            $unsuccessfulArr=[];

            if (sizeof($userBidsArr) == $vacancy){

                if ($round == 1){
                    //need to check if the last is clash with anyone else
                    $firstIndex = 0;
                    $outIndex = ((int)$vacancy)-1; //last guy
                    $prevIndex = ($outIndex-1); //2nd last guy
                    $lastGuysBid=getGuyAtIndex($userBidsArr, $outIndex);
                    $prevBid=getGuyAtIndex($userBidsArr, $prevIndex);

                    $temp=sortForBidClashes($userBidsArr, $lastGuysBid, $prevBid, $firstIndex, $outIndex, $prevIndex, $vacancy);

                    $successfulArr=$temp[0];
                    $unsuccessfulArr=$temp[1];
                }else if ($round == 2){
                    $successfulArr=$userBidsArr;
                    $unsuccessfulArr=[];   
                }
            }
            else{
                $firstIndex = 0;
                $outIndex = (int)$vacancy; //guy who just missed it
                $outGuysBid=getGuyAtIndex($userBidsArr, $outIndex);
                $lastGuysBid=getGuyAtIndex($userBidsArr, $outIndex-1);

                if ($lastGuysBid==$outGuysBid){
                    $outIndex = (int)$vacancy; //guy who just missed it
                    $prevIndex = ($outIndex-1);
                    $outGuysBid=getGuyAtIndex($userBidsArr, $outIndex);
                    $prevBid=getGuyAtIndex($userBidsArr, $prevIndex);
                    $temp=sortForBidClashes($userBidsArr, $outGuysBid, $prevBid, $firstIndex, $outIndex, $prevIndex, $vacancy);      
                    
                    $successfulArr=$temp[0];
                    $unsuccessfulArr=$temp[1];
                }
                else{
                    $lastIndex = ((int)$vacancy); //last guy
                    $prevIndex = ($lastIndex-1);
                    $lastGuysBid=getGuyAtIndex($userBidsArr, $lastIndex);
                    $prevBid=getGuyAtIndex($userBidsArr, $prevIndex);

                    $temp=sortForBidClashes($userBidsArr, $lastGuysBid, $prevBid, $firstIndex, $lastIndex, $prevIndex, $vacancy);

                    $successfulArr=$temp[0];
                    $unsuccessfulArr=$temp[1];
                }
            }
            return [$successfulArr, $unsuccessfulArr];
        }
    }

    function sortForBidClashes($userBidsArr, $outGuysBid, $prevBid_, $firstIndex, $outIndex_, $prevIndex_, $vacancy)
    {
        $unsuccessfulArr=[];
        $successfulArrArr=[];
        //$lastGuysBid=null;
        $prevIndex=$prevIndex_;
        $outIndex=$outIndex_;
        $prevBid=$prevBid_;

        //case of no bid clash
        if ($outGuysBid[1] != $prevBid[1]){
            $successfulArr=array_slice($userBidsArr,$firstIndex,$vacancy,true);
            $unsuccessfulArr=array_slice($userBidsArr,$vacancy, null, true);
        }else{
            //$lastGuysBid=$prevBid;

            //case of bid clash
            while(($outGuysBid[1] == $prevBid[1]) && ($prevIndex != -1)){
                $prevIndex-=1;
                $outIndex-=1;
                $prevBid=getGuyAtIndex($userBidsArr, $prevIndex);
            }

            //everyone bid clash, all same bid (highly unlikely)
            if ($prevIndex==-1){
                
                $unsuccessfulArr=$userBidsArr;
                $successfulArr=[];
            }
            //case of some bid clash but not all
            else{
                $successfulArr=array_slice($userBidsArr,$firstIndex,$outIndex,true);
                $unsuccessfulArr=array_slice($userBidsArr,$outIndex, null, true);                
            }
        }
        return [$successfulArr,$unsuccessfulArr];
    }

    function sortCourseBidsWithVacancy($bidsDao, $userBidsArr, $vacancy, $coursecode, $section, $round){
        //ranks bids for one course using the Arr from sortallbids and the value from getVacancy 
        //in desc order only until class vacancy is reached
        //produces successful and unsuccessful array and writes both via writeEnrollmentVacancy

        //case less bids than vacancy
        $existLessBidsThanVacancy=checkLessBidsThanVacancy($userBidsArr,$vacancy);
        if ($existLessBidsThanVacancy != false){  
            $unsuccessfulArr=[];
            if ($existLessBidsThanVacancy > 0){
                writeEnrollmentandVacancy($bidsDao, $userBidsArr, $unsuccessfulArr, $coursecode, $section, $round);
            }
        }
        else{
            //case more or equal bids than vacancy
            $temp=checkSufficientMoreBidsThanVacancy($userBidsArr, $vacancy, $round);
            $successfulArr=$temp[0];
            $unsuccessfulArr=$temp[1];
            //$roundStatus=null;
            writeEnrollmentandVacancy($bidsDao,$successfulArr,$unsuccessfulArr,$coursecode,$section,$round);
        }
    }

    function writeEnrollmentandVacancy($bidsDao, $successfulArr, $unsuccessfulArr, $coursecode, $section, $round){
        $vacancy=$bidsDao->getVacancy($coursecode, $section);
        if ($successfulArr == null)
            $totalSize=0;
        else
            $totalSize=sizeof($successfulArr);

        //update vacancy
        $newVacancy=$vacancy-$totalSize;
        $bidsDao->setVacancy($newVacancy,$coursecode,$section);

        //write enrollment for all of them   
        if ($successfulArr != null){
            foreach ($successfulArr as $key=>$value){
                $bidsamount = $bidsDao->getbidsamount($key,$coursecode,$section);
                $bidsDao->setEnrolledCourse($key,$coursecode,$section,$bidsamount, $round);
            } 
        }

        foreach ($unsuccessfulArr as $k=>$v){
            //refund money
            $oldMoney=$bidsDao->getUserEDollars($k);
            $newMoney=$oldMoney+$v;
            $bidsDao->setUserEDollars($k, $newMoney);
            //write to unsuccessful table
            $bidsDao->setUnsuccessfulCourse($k, $coursecode, $section, $v, $round);
        }
    }

    function getGuyAtIndex($array, $index){
        $working=getIndexValueFromAssociative($array, $index);
        return convertAssociativeArrayToIndexed($working);
    }

    function getIndexValueFromAssociative($array, $index){
        return array_slice($array, $index, 1, true);
    }

    function convertAssociativeArrayToIndexed($array){
        $arr = [];
        foreach ($array as $key=>$value){
            $arr=[$key,$value];
        }
        return $arr;
    }

    function closeRound($bidsDao, $round){
        $uniqueCourses=$bidsDao->getUniqueCoursesInBids();
    
        foreach ($uniqueCourses as $course){
            
            //get all the bids
            $userAmountsArr=sortAllBids($bidsDao,$course[0],$course[1]);
    
            //fetch the vacancy list
            $vacancy=$bidsDao->getVacancy($course[0], $course[1]);
    
            //sorts and then writes the successful bids back to db
            sortCourseBidsWithVacancy($bidsDao, $userAmountsArr, $vacancy, $course[0], $course[1], $round);
        }
    
        //wipe d whole bids table
        $bidsDao->deleteAllBids();
    }
?>