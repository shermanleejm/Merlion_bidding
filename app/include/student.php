<?php

class student {

    private $userid;
    private $studentpassword;
    private $studentname;
    private $school;
    private $edollar;


    public function __construct($userid, $studentpassword, $studentname, $school, $edollar) {
        $this->userid = $userid;
        $this->studentpassword = $studentpassword;
        $this->studentname = $studentname;
        $this->school = $school;
        $this->edollar = $edollar;
    }

    public function getUserid(){
        return $this->$userid;
    }
    public function getPassword() {
        return $this->studentpassword;
    }

    // public function authenticate($pw) {
    //     if($pw == $this->studentpassword){
    //         return TRUE
    //     }
    //     return FALSE
    // }
    public function authenticate($enteredPwd) {
        return password_verify ($enteredPwd, $this->studentpassword);
    }

    public function getStudentname() {
        return $this->studentname;
    }

    public function getSchool() {
        return $this->school;
    }

    public function getEdollar() {
        return $this->edollar;
    }

}


?>