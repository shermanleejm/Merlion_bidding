<?php

class bid {

    private $userid;
    private $amount;
    private $code;
    private $section;


    public function __construct($userid, $amount, $code, $section){
        $this->userid = $userid;
        $this->amount = $amount;
        $this->code = $code;
        $this->section = $section;
    }

    public function getUserID() {
        return $this->userid;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getCode() {
        return $this->code;
    }

    public function getSection() {
        return $this->section;
    }

}
?>