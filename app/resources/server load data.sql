-- #TO LOAD DATA MANUALLY#
-- #Pls update path to your own file path.
-- #This is to load data to tables for testing ONLY.

LOAD DATA LOCAL INFILE  
'./opt/bitnami/apache2/htdocs/app/resources/sample data/course.csv'
INTO TABLE course  
FIELDS TERMINATED BY ',' 
-- ENCLOSED BY '"'
LINES TERMINATED BY '\n';
-- IGNORE 1 ROWS
-- (course,school,title,descr,examdate,examstart,examend);

-- #note: there is error in row 2 'Advanced Calculus', delete the 2 spaces in the course description.

LOAD DATA LOCAL INFILE  
'./opt/bitnami/apache2/htdocs/app/resources/sample data/prerequisite.csv'
INTO TABLE prerequisite  
FIELDS TERMINATED BY ',' 
-- ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(course,prerequisite);

LOAD DATA LOCAL INFILE  
'./opt/bitnami/apache2/htdocs/app/resources/sample data/bid.csv'
INTO TABLE bid  
FIELDS TERMINATED BY ',' 
-- ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(userid,amount,code,section);

LOAD DATA LOCAL INFILE  
'./opt/bitnami/apache2/htdocs/app/resources/sample data/course_completed.csv'
INTO TABLE course_completed  
FIELDS TERMINATED BY ',' 
-- ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(userid,code);

LOAD DATA LOCAL INFILE  
'./opt/bitnami/apache2/htdocs/app/resources/sample data/section.csv'
INTO TABLE section  
FIELDS TERMINATED BY ',' 
-- ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(course,section,dayoftheweek,starttime,endtime,instructor,venue,size);

LOAD DATA LOCAL INFILE  
'./opt/bitnami/apache2/htdocs/app/resources/sample data/student.csv'
INTO TABLE student 
FIELDS TERMINATED BY ',' 
-- ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(userid, studentpassword, studentname, school, edollar);