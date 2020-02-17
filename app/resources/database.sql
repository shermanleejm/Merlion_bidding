drop database if exists boss;
create database if not exists boss;
use boss;

drop table if exists administrator;
create table if not exists administrator (
    user varchar(5) not null,
    pass varchar(255) not null
);
insert into administrator values ("admin", "$2y$10$QaqioLT7pBtIeZoZmcwtVeJs2Fer8eEVG1cZJOiALp1P2dYy2H6NK");

drop table if exists bid;
create table if not exists bid (
    userid varchar(64) not null,
    amount int not null,
    code varchar(64) not null,
    section varchar(8) not null,
    primary key (userid, code)
);

drop table if exists course_completed;
create table if not exists course_completed (
    userid varchar(64) not null,
    code varchar(64) not null,
    primary key (userid, code)
);

drop table if exists course;
create table if not exists course (
    course varchar(64) not null,
    school varchar(8) not null,
    title varchar(64) not null,
    descr varchar(1000) not null,
    examdate date not null,
    examstart time not null,
    examend time not null,
    primary key (course, school, examdate, examstart, examend)
);

drop table if exists prerequisite;
create table if not exists prerequisite (
    course varchar(64) not null,
    prerequisite varchar(64) not null,
    primary key (course, prerequisite)
);

drop table if exists section;
create table if not exists section (
    course varchar(64) not null,
    section varchar(64) not null,
    dayoftheweek int not null,
    starttime time not null,
    endtime time not null,
    instructor varchar(64) not null,
    venue varchar(64) not null,
    size int not null,
    primary key(course, section)
);

drop table if exists student;
create table if not exists student (
    userid varchar(64) not null primary key,
    studentpassword varchar(64) not null,
    studentname varchar(64) not null,
    school varchar(8) not null,
    edollar int not null
);

drop table if exists course_enrolled;
create table if not exists course_enrolled (
    userid varchar(64) not null,
    course varchar(64) not null,
    section varchar(64) not null
);



