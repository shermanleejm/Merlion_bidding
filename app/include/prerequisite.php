<?php

class prerequisite {

    private $course;
    private $prerequisite;
   

    public function __construct($course, $prerequisite){
        $this->course = $course;
        $this->prerequisite= $prerequisite;
    

    }
}
?>