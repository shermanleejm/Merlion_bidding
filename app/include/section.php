<?php

class section {

    private $course;
    private $section;
    private $dayoftheweek;
    private $starttime;
    private $endtime;
    private $instructor;
    private $venue;
    private $size;


    public function __construct($course, $section, $dayoftheweek, $starttime, $endtime, $instructor, $venue, $size) {
        $this->course = $course;
        $this->section = $section;
        $this->dayoftheweek = $dayoftheweek;
        $this->starttime = $starttime;
        $this->endtime = $endtime;
        $this->instructor = $instructor;
        $this->venue = $venue;
        $this->size = $size;
    }

    public function getCourse() {
        return $this->course;
    }

    public function getSection() {
        return $this->section;
    }

    public function getDayOfTheWeek() {
        return $this->dayoftheweek;
    }

    public function getStartTime() {
        return $this->starttime;
    }

    public function getEndTime() {
        return $this->endtime;
    }

    public function getInstructor() {
        return $this->instructor;
    }

    public function getVenue() {
        return $this->venue;
    }

    public function getSize() {
        return $this->size;
    }
}
?>