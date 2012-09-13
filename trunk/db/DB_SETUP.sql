/* * Copyright 2012 Micah Gale
 *
 * This file is a part of Squadron Manager
 *
 *Squadron Manager is free software licensed under the GNU General Public License version 3.
 * You may redistribute and/or modify it under the terms of the GNU General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * Squadron Manager comes without a warranty; without even the implied warranty of merchantability
 * or fitness for a particular purpose. See the GNU General Public License version 3 for more
 * details.
 *
 * You should have received the GNU General Public License version 3 with this in GPL.txt
 * if not it is available at <http://www.gnu.org/licenses/gpl.txt>.
 *
 * 
 */
CREATE DATABASE IF NOT EXISTS SQUADRON_INFO;
USE SQUADRON_INFO;
##################SETSUP THE TABLE FOR NEW_MEMBER SIGN IN###############
CREATE TABLE IF NOT EXISTS NEW_MEMBER ( 
NAME_LAST VARCHAR(50) NOT NULL,
NAME_FIRST VARCHAR(50) NOT NULL,
DATE_CAME DATE NULL,
EMERGENCY_CONTACT_NAME VARCHAR(50) NOT NULL,
EMERGENCY_CONTACT_NUMBER VARCHAR(12) NOT NULL,
GREETED BOOLEAN DEFAULT FALSE NOT NULL,
PRIMARY KEY (NAME_LAST, NAME_FIRST),
INDEX(NAME_LAST),
INDEX(GREETED))
ENGINE=InnoDB;

##############TABLE FOR THE TYPES OF THE MEMBERS
CREATE TABLE IF NOT EXISTS MEMBERSHIP_TYPES (
MEMBER_TYPE_CODE CHAR(1) NOT NULL PRIMARY KEY UNIQUE,
MEMBER_TYPE_NAME VARCHAR(20) NOT NULL)
ENGINE = INNODB;
#####################table FOR REGIONS####################
CREATE TABLE IF NOT EXISTS REGION (
REGION_CODE CHAR(3) NOT NULL PRIMARY KEY UNIQUE,
REGION_NAME VARCHAR(40) NOT NULL)
ENGINE=INNODB;
######################TABLE FOR WINGS######################
CREATE TABLE IF NOT EXISTS WING (
WING CHAR(2) NOT NULL PRIMARY KEY UNIQUE,
WING_NAME VARCHAR(40) NOT NULL)
ENGINE=INNODB;
#################TABLE FOR CAP UNITS #######################
CREATE TABLE IF NOT EXISTS CAP_UNIT(
CHARTER_NUM CHAR(10) NOT NULL PRIMARY KEY UNIQUE,
REGION CHAR(3) NOT NULL,
WING CHAR(2) NOT NULL,
SQUAD_NAME VARCHAR(35) NOT NULL,
CONSTRAINT FK_UNIT_REGION FOREIGN KEY (REGION)
    REFERENCES REGION(REGION_CODE),
CONSTRAINT FK_UNIT_WING FOREIGN KEY (WING)
    REFERENCES WING(WING))
ENGINE = INNODB;

##################TABLE FOR THE TYPE OF TEXTBOOK SETS#########################
CREATE TABLE IF NOT EXISTS TEXT_SETS (
TEXT_SET_CODE VARCHAR(5) NOT NULL PRIMARY KEY UNIQUE,
TEXT_SET_NAME VARCHAR(45) NOT NULL)
ENGINE = INNODB;
####################TABLE FOR HOLDING THE PHASES
CREATE TABLE IF NOT EXISTS PHASES (
PHASE_NUM SMALLINT,
MEMBER_TYPE CHAR(1),
PHASE_NAME VARCHAR(32),
PRIMARY KEY(PHASE_NUM, MEMBER_TYPE),
CONSTRAINT FK_PHASE_MEMBER_TYPE FOREIGN KEY(MEMBER_TYPE)
	REFERENCES MEMBERSHIP_TYPES (MEMBER_TYPE_CODE),
INDEX(PHASE_NUM))
ENGINE = INNODB;

#######################TABLE FOR THE GRADES ###############################
CREATE TABLE IF NOT EXISTS GRADE (
GRADE_ABREV VARCHAR(10) PRIMARY KEY UNIQUE NOT NULL,
MEMBER_TYPE CHAR(1) NOT NULL,
GRADE_NAME VARCHAR(32) NOT NULL,
CONSTRAINT FK_GRADE_MEMBER_TYPE FOREIGN KEY (MEMBER_TYPE)
	REFERENCES MEMBERSHIP_TYPES (MEMBER_TYPE_CODE))
ENGINE = INNODB;

##############################TABLE FOR RIBBONS####################
CREATE TABLE IF NOT EXISTS RIBBON (
RIBBON_CODE VARCHAR(5) NOT NULL UNIQUE PRIMARY KEY,
RIBBON_NAME VARCHAR(30) NOT NULL,
MEMBER_TYPE CHAR(1) NULL,
CONSTRAINT FK_RIBBON_MEMBER FOREIGN KEY (MEMBER_TYPE)
    REFERENCES MEMBERSHIP_TYPES (MEMBER_TYPE_CODE))
ENGINE=INNODB;

######################TABLE FOR ALL THE ACHIEVEMENTS #######################
CREATE TABLE IF NOT EXISTS ACHIEVEMENT (
ACHIEV_CODE VARCHAR(5) NOT NULL NULL,
MEMBER_TYPE CHAR(1) NOT NULL,
ACHIEV_NAME VARCHAR(40) NOT NULL,
GRADE VARCHAR(10) NOT NULL,
PHASE SMALLINT NULL,
NEXT_ACHIEV VARCHAR(5) NULL,
RIBBON VARCHAR(5) NULL,
ACHIEV_NUM SMALLINT NOT NULL,
PRIMARY KEY(ACHIEV_CODE, MEMBER_TYPE),
CONSTRAINT FK_ACHIEVEMENT_MEMBER_TYPE FOREIGN KEY(MEMBER_TYPE)
	REFERENCES MEMBERSHIP_TYPES (MEMBER_TYPE_CODE),
CONSTRAINT FK_ACHIEVEMENT_GRADE FOREIGN KEY (GRADE)
	REFERENCES GRADE (GRADE_ABREV),
CONSTRAINT FK_NEXT_ACHIEVEMENT FOREIGN KEY(NEXT_ACHIEV)
	REFERENCES ACHIEVEMENT (ACHIEV_CODE),
CONSTRAINT FK_ACHIEVEMENT_PHASE FOREIGN KEY (PHASE)
    REFERENCES PHASES(PHASE_NUM),
CONSTRAINT FK_AHCIEVEMENT_RIBBON FOREIGN KEY(RIBBON)
    REFERENCES RIBBON(RIBBON_CODE))
ENGINE = INNODB;

###########################TABLE FOR HOLDING MEMBERS INFORMATION ##########
CREATE TABLE IF NOT EXISTS MEMBER (
CAPID INTEGER NOT NULL PRIMARY KEY UNIQUE,
NAME_LAST VARCHAR(32) NOT NULL,
NAME_FIRST VARCHAR(32) NOT NULL,
GENDER CHAR(1) NOT NULL CHECK (GENDER= 'M'OR'F'),
DATE_OF_BIRTH DATE NOT NULL,
ACHIEVEMENT VARCHAR(5) NOT NULL DEFAULT 0,
MEMBER_TYPE CHAR(1) NOT NULL,
TEXTBOOK_SET VARCHAR(5) NULL,
HOME_UNIT CHAR(10) NOT NULL DEFAULT 'RMR-ID-073',
DATE_JOINED DATE NOT NULL,
DATE_TERMINATED DATE NULL,
APPROVED BOOLEAN NOT NULL DEFAULT FALSE,
PROFILE_PICTURE VARCHAR(50) NULL,
CONSTRAINT FK_MEMBER_ACHIEVEMENT FOREIGN KEY (ACHIEVEMENT)
	REFERENCES ACHIEVEMENT (ACHIEV_CODE),
CONSTRAINT FK_MEMBER_MEMBER_TYPE FOREIGN KEY (MEMBER_TYPE)
	REFERENCES MEMBERSHIP_TYPES (MEMBER_TYPE_CODE),
CONSTRAINT FK_MEMBER_TEXT_SET FOREIGN KEY (TEXTBOOK_SET)
	REFERENCES TEXT_SETS (TEXT_SET_CODE),
CONSTRAINT FK_MEMBER_HOME_UNIT FOREIGN KEY (HOME_UNIT)
	REFERENCES CAP_UNIT (CHARTER_NUM),
INDEX(NAME_LAST),
INDEX(NAME_FIRST),
INDEX(MEMBER_TYPE),
INDEX(ACHIEVEMENT))
ENGINE = INNODB; 

#############TABLE FOR THE TYPE OF RELATION THE EMERGENCY CONTACT HAS ################
CREATE TABLE IF NOT EXISTS CONTACT_RELATIONS (
RELATION_CODE CHAR(2) NOT NULL PRIMARY KEY UNIQUE,
RELATION_NAME VARCHAR(20) NOT NULL)
ENGINE = INNODB;

###########TABLE FOR THE MEMBERS EMERGENCY CONTACT INFORMATION ################
CREATE TABLE IF NOT EXISTS EMERGENCY_CONTACT (
CAPID INTEGER NOT NULL,
RELATION CHAR(2) NOT NULL,
CONTACT_NAME VARCHAR(32) NOT NULL,
CONTACT_NUMBER VARCHAR(12) NOT NULL,
PRIMARY KEY( CAPID, RELATION),
CONSTRAINT FK_EMERGENCY_CONTACT_CAPID FOREIGN KEY (CAPID)
	REFERENCES MEMBER (CAPID),
CONSTRAINT FK_EMERGENCY_RELATION FOREIGN KEY (RELATION)
	REFERENCES CONTACT_RELATIONS (RELATION_CODE),
INDEX(CAPID))
ENGINE = INNODB; 

#################TABLE FOR TRACKING THE PROMOTIONS DATES###########
CREATE TABLE IF NOT EXISTS PROMOTION_RECORD  (
CAPID INTEGER NOT NULL, 
ACHIEVEMENT VARCHAR(5) NOT NULL,
DATE_PROMOTED DATE NOT NULL, 
PRIMARY KEY(CAPID, ACHIEVEMENT), 
CONSTRAINT FK_PROMOTIONS_CAPID FOREIGN KEY (CAPID) 
	REFERENCES MEMBER (CAPID), 
CONSTRAINT FK_PROMOTIONS_ACHIEVEMENT FOREIGN KEY (ACHIEVEMENT) 
	REFERENCES ACHIEVEMENT (ACHIEV_CODE),
INDEX(CAPID))
ENGINE = INNODB;

##############################TABLE FOR RIBBON REQUESTS###############
CREATE TABLE IF NOT EXISTS RIBBON_REQUEST (
CAPID INTEGER NOT NULL,
RIBBON VARCHAR(5) NOT NULL,
DATE_REQUESTED DATE NOT NULL,
APPROVED BOOLEAN NOT NULL DEFAULT FALSE,
APROVED_BY INTEGER NULL,
PRIMARY KEY(CAPID,RIBBON),
CONSTRAINT FK_RIBBON_REQUEST_CAPID FOREIGN KEY(CAPID)
    REFERENCES MEMBER (CAPID),
CONSTRAINT FK_RIBBON_REQUEST_RIBBON FOREIGN KEY (RIBBON)
    REFERENCES RIBBON(RIBBON_CODE),
CONSTRAINT FK_RIBBON_REQUEST_APPROVE_MEMBER FOREIGN KEY (APROVED_BY)
    REFERENCES MEMBER(CAPID))
ENGINE=INNODB;
#############################CPFT REQUIREMENT SECTION ##############################################

################TABLE FOR THE TYPE OF CPFT REQUIREMENTS#################
CREATE TABLE IF NOT EXISTS CPFT_TEST_TYPES (
TEST_CODE CHAR(2) NOT NULL PRIMARY KEY UNIQUE,
TEST_NAME VARCHAR(20) NOT NULL)
ENGINE = INNODB;

#########################THE ACTUAL CPFT REQUIREMENTS##################
CREATE TABLE IF NOT EXISTS CPFT_REQUIREMENTS (
GENDER CHAR(1) NOT NULL CHECK (GENDER = 'M'OR'F'),
AGE SMALLINT NOT NULL,
PHASE SMALLINT NOT NULL,
TEST_TYPE CHAR(2) NOT NULL,
REQUIREMENT FLOAT NOT NULL,
START_ACHIEV VARCHAR(5) NULL,
END_ACHIEV VARCHAR(5) NULL,
PRIMARY KEY (GENDER, AGE, PHASE),
CONSTRAINT FK_CPFT_PHASE FOREIGN KEY (PHASE)
	REFERENCES PHASES (PHASE_NUM),
CONSTRAINT FK_CPFT_TEST_TYPE FOREIGN KEY (TEST_TYPE) 
	REFERENCES CPFT_TEST_TYPES (TEST_CODE),
CONSTRAINT FK_CPFT_START_ACHIEV FOREIGN KEY (START_ACHIEV)
        REFERENCES ACHIEVEMENT (ACHIEV_CODE),
CONSTRAINT FK_CPFT_END_ACHIEV FOREIGN KEY (END_ACHIEV)
        REFERENCES ACHIEVEMENT (ACHIEV_CODE),
INDEX(GENDER),
INDEX(AGE),
INDEX(PHASE),
INDEX(TEST_TYPE))
ENGINE = INNODB; 

##############################AUDIT LOGGING SECTION######################################################
##################TABLE FOR INTRUSION TYPE#########################
CREATE TABLE IF NOT EXISTS INTRUSION_TYPE (
INTRUSION_CODE CHAR(2) NOT NULL PRIMARY KEY,
INTRUSION_NAME VARCHAR(50) NOT NULL)
ENGINE = INNODB;
####################TABLE FOR LOGGING AUDIT EVENTS##################
CREATE TABLE IF NOT EXISTS AUDIT_LOG (
TIME_OF_INTRUSION TIMESTAMP NOT NULL PRIMARY KEY UNIQUE DEFAULT CURRENT_TIMESTAMP,
INTRUSION_TYPE CHAR(2) NOT NULL,
PAGE VARCHAR(50) NULL,
IP_ADDRESS VARCHAR(15) NOT NULL,
CONSTRAINT FK_AUDIT_TYPE FOREIGN KEY (INTRUSION_TYPE)
    REFERENCES INTRUSION_TYPE (INTRUSION_CODE))
ENGINE = INNODB;
####################TABLE FOR HOLDING VARIABLE DUMP###################
CREATE TABLE IF NOT EXISTS AUDIT_DUMP (
TIME_OF_INTRUSION TIMESTAMP NOT NULL,
FIELD_NAME VARCHAR(32) NOT NULL,
FIELD_VALUE VARCHAR(200) NOT NULL,
PRIMARY KEY (TIME_OF_INTRUSION, FIELD_NAME),
CONSTRAINT FK_AUDIT_DUMP_TIME FOREIGN KEY (TIME_OF_INTRUSION)
	REFERENCES AUDIT_LOG (TIME_OF_INTRUSION),
INDEX(TIME_OF_INTRUSION),
INDEX(FIELD_NAME))
ENGINE = INNODB; 

###########################staff positoins section ###########################################################
#######################TABLE FOR TYPES OF POSITIONS########################
CREATE TABLE IF NOT EXISTS STAFF_POSITIONS (
STAFF_CODE VARCHAR(3) NOT NULL PRIMARY KEY UNIQUE,
STAFF_NAME VARCHAR(50) NOT NULL,
MEMBER_TYPE CHAR(1) NOT NULL,
CONSTRAINT FK_STAFF_MEMBER_TYPE FOREIGN KEY (MEMBER_TYPE)
    REFERENCES MEMBERSHIP_TYPES(MEMBER_TYPE_CODE))
ENGINE = INNODB;
########################TABLE FOR HOLDING FLIGHTS#########################
CREATE TABLE IF NOT EXISTS FLIGHTS (
FLIGHT CHAR(1) NOT NULL PRIMARY KEY UNIQUE,
FLIGHT_NAME VARCHAR(20) NULL)
ENGINE = INNODB;
########################TABLE SHOWING CHAIN OF COMMAND AND EXACT POSITIONS###############
CREATE TABLE IF NOT EXISTS CHAIN_OF_COMMAND(
POS_CODE VARCHAR(6) NOT NULL UNIQUE PRIMARY KEY,
STAFF_CODE VARCHAR(3) NOT NULL,
FLIGHT CHAR(1) NULL,
ELEMENT SMALLINT NULL,
NEXT_IN_CHAIN VARCHAR(6) NULL,
CONSTRAINT FK_CHAIN_STAFF FOREIGN KEY (STAFF_CODE)
    REFERENCES STAFF_POSITIONS (STAFF_CODE),
CONSTRAINT FK_CHAIN_FLIGHT FOREIGN KEY (FLIGHT)
    REFERENCES FLIGHTS(FLIGHT),
CONSTRAINT FK_CHAIN_NEXT_UP FOREIGN KEY (NEXT_IN_CHAIN)
    REFERENCES CHAIN_OF_COMMAND(POS_CODE))
ENGINE=INNODB;
##########################TABLE FOR HOLDING STAFF POSITIONS HELD###########
CREATE TABLE IF NOT EXISTS STAFF_POSITIONS_HELD (
STAFF_POSITION VARCHAR(6) NOT NULL,
CAPID INTEGER NOT NULL,
PRIMARY KEY(STAFF_POSITION,CAPID),
CONSTRAINT FK_STAFF_HOLD_CHAIN FOREIGN KEY (STAFF_POSITION)
    REFERENCES CHAIN_OF_COMMAND(POS_CODE),
CONSTRAINT FK_STAFF_HOLD_CAPID FOREIGN KEY (CAPID)
    REFERENCES MEMBER(CAPID))
ENGINE = INNODB; 
#################################attendance section ###########################################################
################table for the event types######################
CREATE TABLE IF NOT EXISTS EVENT_TYPES(
EVENT_TYPE_CODE VARCHAR(5)NOT NULL PRIMARY KEY UNIQUE,
EVENT_TYPE_NAME VARCHAR(40)NOT NULL)
ENGINE = INNODB;
##################TABLE FOR LOCATIONS#########################
CREATE TABLE IF NOT EXISTS EVENT_LOCATION(
LOCAT_CODE VARCHAR(5) NOT NULL UNIQUE PRIMARY KEY,
LOCAT_NAME VARCHAR(50) NOT NULL
DEFAULT_LOCAT BOOLEAN NOT NULL DEFAULT FALSE)
ENGINE=INNODB;
###################TABLE FOR THE ACTUAL EVENTS#################
CREATE TABLE IF NOT EXISTS EVENT (
EVENT_CODE VARCHAR(32) NOT NULL PRIMARY KEY UNIQUE,
EVENT_DATE DATE NOT NULL,
EVENT_TYPE VARCHAR(2) NOT NULL,
EVENT_NAME VARCHAR(32) NULL,
IS_CURRENT BOOLEAN NOT NULL DEFAULT FALSE,
LOCATION VARCHAR(5) NULL,
END_DATE DATE NULL,
CONSTRAINT FK_EVENT_TYPE FOREIGN KEY (EVENT_TYPE)
	REFERENCES EVENT_TYPES (EVENT_TYPE_CODE),
CONSTRAINT FK_EVENT_LOCAT FOREIGN KEY (LOCATION)
    REFERENCES EVENT_LOCATION(LOCAT_CODE),
INDEX(EVENT_DATE),
INDEX(EVENT_TYPE),
INDEX(EVENT_NAME),
INDEX(LOCATION))
ENGINE = INNODB;
#######################TABLE FOR ATTENDENCE###################
CREATE TABLE IF NOT EXISTS ATTENDANCE(
CAPID INTEGER NOT NULL,
EVENT_CODE VARCHAR(32) NOT NULL,
PRIMARY KEY (CAPID, EVENT_CODE),
CONSTRAINT FK_ATTENDENCE_CAPID FOREIGN KEY (CAPID)
	REFERENCES MEMBER (CAPID),
CONSTRAINT FK_ATTENDENCE_EVENT FOREIGN KEY (EVENT_CODE)
	REFERENCES EVENT (EVENT_CODE),
INDEX(CAPID),
INDEX(EVENT_CODE))
ENGINE = INNODB;

######################TABLE FOR HOLDING THE TYPES OF SUBEVENTS (I.E. SAFETY)
CREATE TABLE IF NOT EXISTS SUBEVENT_TYPE (
SUBEVENT_TYPE CHAR(3) NOT NULL PRIMARY KEY UNIQUE,
SUBEVENT_NAME VARCHAR(40) NOT NULL)
ENGINE = INNODB;
##############TABLE FOR HOLDING SUBEVENTS###################################
CREATE TABLE IF NOT EXISTS SUBEVENT (
PARENT_EVENT_CODE VARCHAR(32) NOT NULL,
SUBEVENT_CODE CHAR(3) NOT NULL,
START_TIME TIME NULL,
END_TIME TIME NULL,
DESCRIPTION VARCHAR(125) NULL,
PRIMARY KEY (PARENT_EVENT_CODE, SUBEVENT_CODE),
CONSTRAINT FK_SUBEVENT_PARENT_EVENT FOREIGN KEY (PARENT_EVENT_CODE)
	REFERENCES EVENT (EVENT_CODE),
CONSTRAINT FK_SUBEVENT_TYPE FOREIGN KEY (SUBEVENT_CODE)
	REFERENCES SUBEVENT_TYPE (SUBEVENT_TYPE),
INDEX(PARENT_EVENT_CODE),
INDEX(SUBEVENT_CODE))
ENGINE = INNODB;
##########################SECTION FOR LOGGING DISCIPLINE ACTIONS TAKEN #######################################
##########################TABEL FOR THE TYPES OF DISCIPLINE ACTIONS
CREATE TABLE IF NOT EXISTS DISCIPLINE_TYPE (
DISCIPLINE_CODE CHAR(3) NOT NULL PRIMARY KEY UNIQUE,
DISCIPLINE_NAME VARCHAR(32) NOT NULL)
ENGINE = INNODB;
#################TABLE FOR THE OFFENSES###########################
CREATE TABLE IF NOT EXISTS DISCIPLINE_OFFENSES (
OFFENSE_CODE CHAR(3) NOT NULL PRIMARY KEY UNIQUE,
OFFENSE_NAME VARCHAR(64) NOT NULL)
ENGINE = INNODB;
###############TABLE FOR LOGGING DISCIPLINARY MEASURES TAKEN
CREATE TABLE IF NOT EXISTS DISCIPLINE_LOG (
CAPID INTEGER NOT NULL,
TYPE_OF_ACTION CHAR(3) NOT NULL,
EVENT_CODE VARCHAR(32) NOT NULL,
OFFENSE CHAR(3) NOT NULL,
SEVERITY SMALLINT NOT NULL,
GIVEN_BY INTEGER NOT NULL,
DETAILS VARCHAR(1024) NULL,
PRIMARY KEY (CAPID, TYPE_OF_ACTION,EVENT_CODE, OFFENSE,GIVEN_BY),
CONSTRAINT FK_DISCIPLINE_CAPID FOREIGN KEY (CAPID)
	REFERENCES MEMBER (CAPID),
CONSTRAINT FK_DISCIPLINE_DISCIPLINE_ACTION FOREIGN KEY (TYPE_OF_ACTION)
	REFERENCES DISCIPLINE_TYPE (DISCIPLINE_CODE),
CONSTRAINT FK_DISCIPLINE_EVENT_CODE FOREIGN KEY (EVENT_CODE)
	REFERENCES EVENT (EVENT_CODE),
CONSTRAINT FK_DISCIPLINE_OFFENSE FOREIGN KEY (OFFENSE)
	REFERENCES DISCIPLINE_OFFENSES (OFFENSE_CODE),
CONSTRAINT FK_DISCIPLINE_GIVEN_BY FOREIGN KEY (GIVEN_BY)
	REFERENCES MEMBER (CAPID),
INDEX(CAPID),
INDEX(EVENT_CODE),
INDEX(OFFENSE),
INDEX(TYPE_OF_ACTION))
ENGINE = INNODB;
####################TABLE FOR HOLDING PROMOTION BOARDS#############
CREATE TABLE IF NOT EXISTS PROMOTION_BOARD (
CAPID INTEGER NOT NULL,
PHASE SMALLINT NOT NULL,
BOARD_DATE DATE NOT NULL,
APPROVED BOOLEAN DEFAULT TRUE NOT NULL,
NEXT_SCHEDULED DATE NULL,
PRIMARY KEY (CAPID, BOARD_DATE),
CONSTRAINT FK_PROMO_BOARD_CAPID FOREIGN KEY (CAPID)
	REFERENCES MEMBER (CAPID),
CONSTRAINT FK_PROMO_BOARD_PHASE FOREIGN KEY (PHASE)
	REFERENCES PHASES (PHASE_NUM),
INDEX(CAPID))
ENGINE = INNODB;
#############################################SECTION FOR TESTING ######################################
###################TABLE FOR REQUIREMENT TYPES###################
CREATE TABLE IF NOT EXISTS REQUIREMENT_TYPE (
TYPE_CODE CHAR(2) NOT NULL PRIMARY KEY UNIQUE,
TYPE_NAME VARCHAR(50) NOT NULL,
MEMBER_TYPE CHAR(1) NULL
CONSTRAINT FK_REQUIREMENT_TYPE_MEMBER FOREIGN KEY(MEMBER_TYPE)
    REFERENCES MEMBERSHIP_TYPES(MEMBER_TYPE_CODE))
ENGINE = INNODB;
##################TABLE FOR REQUIREMENTS########################
CREATE TABLE IF NOT EXISTS PROMOTION_REQUIREMENT (
ACHIEV_CODE VARCHAR(5) NOT NULL,
REQUIREMENT_TYPE CHAR(2) NOT NULL,
PER_ACHIEVEMENT BOOLEAN NOT NULL DEFAULT TRUE,
PER_PHASE BOOLEAN NOT NULL DEFAULT FALSE,
TEXT_SET VARCHAR(5) NULL,
NAME VARCHAR(50) NULL,
PASSING_PERCENT FLOAT NULL,
IS_ONLINE BOOLEAN NOT NULL DEFAULT FALSE,
PRIMARY KEY(ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET),
CONSTRAINT FK_PROM_REQUIRE_ACHIEVEMENT FOREIGN KEY (ACHIEV_CODE)
    REFERENCES ACHIEVEMENT (ACHIEV_CODE),
CONSTRAINT FK_PROMO_REQUIRE_TYPE FOREIGN KEY (REQUIREMENT_TYPE)
    REFERENCES REQUIREMENT_TYPE (TYPE_CODE),
CONSTRAINT FK_PROMO_REQUIRE_TEXT_SET FOREIGN KEY (TEXT_SET)
    REFERENCES TEXT_SETS(TEXT_SET_CODE),
INDEX(ACHIEV_CODE),
INDEX(REQUIREMENT_TYPE))
ENGINE=INNODB;
####################TABLE FOR SIGNING UP FOR TESTING############
CREATE TABLE IF NOT EXISTS  TESTING_SIGN_UP (
CAPID INTEGER NOT NULL,
ACHIEV_CODE VARCHAR(5) NOT NULL,
REQUIRE_TYPE CHAR(2) NOT NULL,
REQUESTED_DATE DATE NOT NULL,
PRIMARY KEY (CAPID, ACHIEV_CODE,REQUIRE_TYPE),
CONSTRAINT FK_TEST_SIGN_UP_CAPID FOREIGN KEY (CAPID)
    REFERENCES MEMBER (CAPID),
CONSTRAINT FK_TEST_SIGN_UP_ACHIEV FOREIGN KEY (ACHIEV_CODE)
    REFERENCES ACHIEVEMENT (ACHIEV_CODE),
CONSTRAINT FK_TEST_SIGN_UP_TYPE FOREIGN KEY (REQUIRE_TYPE)
    REFERENCES REQUIREMENT_TYPE (TYPE_CODE),
INDEX(REQUIRE_TYPE))
ENGINE=INNODB;
##################TABLE FOR PROMOTION SIGN UP###################
CREATE TABLE IF NOT EXISTS PROMOTION_SIGN_UP (
CAPID INTEGER NOT NULL,
ACHIEV_CODE VARCHAR(5) NOT NULL,
DATE_REQUESTED DATE NOT NULL,
APPROVED BOOLEAN NOT NULL DEFAULT FALSE,
PRIMARY KEY(CAPID, ACHIEV_CODE),
CONSTRAINT FK_PROMO_SIGN_CAPID FOREIGN KEY (CAPID)
    REFERENCES MEMBER (CAPID),
CONSTRAINT FK_PROMO_SIGN_ACHIEV FOREIGN KEY (ACHIEV_CODE)
    REFERENCES ACHIEVEMENT (ACHIEV_CODE),
INDEX(APPROVED),
INDEX(ACHIEV_CODE))
ENGINE=INNODB;
#################TABLE FOR REQUIREMENTS PASSED################
CREATE TABLE IF NOT EXISTS REQUIREMENTS_PASSED (
CAPID INTEGER NOT NULL,
ACHIEV_CODE VARCHAR(5) NOT NULL,
REQUIREMENT_TYPE CHAR(2) NOT NULL,
TEXT_SET VARCHAR(5) NOT NULL,
PASSED_DATE DATE NOT NULL,
ON_ESERVICES BOOLEAN NOT NULL DEFAULT FALSE,
PRIMARY KEY(CAPID,ACHIEV_CODE,REQUIREMENT_TYPE),
CONSTRAINT FK_REQUIRE_PASSED_CAPID FOREIGN KEY (CAPID)
    REFERENCES MEMBER (CAPID),
CONSTRAINT FK_REQUIRE_PASSED_ACHIEV FOREIGN KEY (ACHIEV_CODE)
    REFERENCES ACHIEVEMENT (ACHIEV_CODE),
CONSTRAINT FK_REQUIRE_PASSED_TYPE FOREIGN KEY (REQUIREMENT_TYPE)
    REFERENCES REQUIREMENT_TYPE (TYPE_CODE),
INDEX(CAPID),
INDEX(ACHIEV_CODE),
INDEX(REQUIREMENT_TYPE))
ENGINE=INNODB;
################################################tables for telling who can do what#########
##############################TABLE FOR MAJOR ACTION GROUPING##########
CREATE TABLE IF NOT EXISTS TASK_TYPE (
TYPE_CODE CHAR(2) NOT NULL UNIQUE PRIMARY KEY,
TYPE_NAME VARCHAR(32) NOT NULL UNIQUE)
ENGINE=INNODB;
#######################TABLE FOR HOLDING SPECIFIC TASKS#################
CREATE TABLE IF NOT EXISTS TASKS (
TASK_CODE CHAR(3) NOT NULL UNIQUE PRIMARY KEY,
TASK_NAME VARCHAR(32) NOT NULL,
URL VARCHAR(128) NOT NULL UNIQUE,
TYPE_CODE CHAR(2) NOT NULL,
CONSTRAINT FK_TAST_TYPE FOREIGN KEY (TYPE_CODE)
    REFERENCES TASK_TYPE (TYPE_CODE),
INDEX(URL))
ENGINE=INNODB;
##############################TABLE FOR PERMISSIONS FOR STAFF POSITIONS###############
CREATE TABLE IF NOT EXISTS STAFF_PERMISSIONS (
STAFF_CODE CHAR(3) NOT NULL,
TASK_CODE CHAR(3) NOT NULL,
PRIMARY KEY(STAFF_CODE,TASK_CODE), 
CONSTRAINT FK_STAFF_PERM_STAFF_POS FOREIGN KEY (STAFF_CODE) 
    REFERENCES STAFF_POSITIONS(STAFF_CODE),
CONSTRAINT FK_STAFF_PERM_TASK FOREIGN KEY (TASK_CODE) 
    REFERENCES TASKS(TASK_CODE)) 
ENGINE=INNODB; 
###################################TABLE FOR TASK SPECIFIC PERMISSIONS###########
CREATE TABLE IF NOT EXISTS SPECIAL_PERMISSION (
CAPID INTEGER NOT NULL,
TASK_CODE CHAR(3) NOT NULL,
PRIMARY KEY(CAPID, TASK_CODE),
CONSTRAINT FK_SPECIAL_PERMIS_CAPID FOREIGN KEY (CAPID)
    REFERENCES MEMBER(CAPID),
CONSTRAINT FK_SPECIAL_PERMIS_TASK FOREIGN KEY (TASK_CODE)
    REFERENCES TASKS(TASK_CODE))
ENGINE=INNODB;
####################################table for predicted next path#################
CREATE TABLE IF NOT EXISTS NEXT_VISIT (
LAST_URL VARCHAR(128) NOT NULL,
NEXT_URL VARCHAR(128) NOT NULL,
PRIMARY KEY(LAST_URL, NEXT_URL),
CONSTRAINT FK_NEXT_VIST_OLD FOREIGN KEY (LAST_URL)
    REFERENCES TASKS(URL))
ENGINE=INNODB;

###################################table for logging logins and attemps############
CREATE TABLE IF NOT EXISTS LOGIN_LOG (
TIME_LOGIN TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
CAPID INTEGER NOT NULL,
IP_ADDRESS VARCHAR(15) NOT NULL,
SUCEEDED BOOLEAN NOT NULL,
FACTORED BOOLEAN NOT NULL DEFAULT TRUE,
PRIMARY KEY(TIME_LOGIN, CAPID, IP_ADDRESS))
ENGINE=INNODB;

##############################table for holding account locks#####################
CREATE TABLE IF NOT EXISTS ACCOUNT_LOCKS(
CAPID INTEGER NOT NULL UNIQUE PRIMARY KEY,
VALID_UNTIL TIMESTAMP NOT NULL)
ENGINE=INNODB;
