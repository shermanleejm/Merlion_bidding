<?php

class course {

    private $course;
    private $school;
    private $title;
    private $descr;
    private $examdate;
    private $examstart;
    private $examend;


    public function __construct($course, $school, $title, $descr, $examddate, $examstart, $examend){
        $this->course = $course;
        $this->school = $school;
        $this->title = $title;
        $this->descr = $descr;
        $this->examdate = $examdate;
        $this->examstart = $examstart;
        $this->examend = $examend;

    }
}
?>