################################TO INSERT DATA INTO THE DATABASE##################################
##############INSERT MEMBERHIP TYPES#################
INSERT INTO MEMBERSHIP_TYPES (MEMBER_TYPE_CODE, MEMBER_TYPE_NAME)
VALUES ('C', 'cadet'),
        ('S','senior member');
#############INSERT VALUES FOR TYPES OF OFFENSES########
INSERT INTO DISCIPLINE_OFFENSES (OFFENSE_CODE, OFFENSE_NAME)
VALUES ('DIS','disrespectful behavior'),
        ('UNP','Unproffesional behavior'),
        ('LGS','Not meeting the grooming standards'),
        ('SPE','Speaking out of turn');
##############INSERT EVENT TYPES########################
INSERT INTO EVENT_TYPES (EVENT_TYPE_CODE, EVENT_TYPE_NAME)
VALUES ('M','Meeting'),
        ('SE','Squadron Events'),
        ('HG','Honor Guard'),
        ('CG','Color Guard'),
        ('CP','CyberPatriot'),
        ('SARX','Search and Rescue Exercise');
##############INSERT SUB EVENT TYPE######################
INSERT INTO SUBEVENT_TYPE (SUBEVENT_TYPE, SUBEVENT_NAME)
VALUES ('SAF','Safety'),
        ('ES','Emergency Services'),
        ('AE','Aerospace Education'),
        ('CD','Character Development');
#################INSERT VALUES FOR CPFT TYPE#############
INSERT INTO CPFT_TEST_TYPES (TEST_CODE, TEST_NAME,IS_RUNNING)
VALUES ('SR','Sit and Reach',FALSE),
        ('CU','Curl-ups',FALSE),
        ('PU','Push-ups',FALSE),
        ('RS','Shuttle Run',TRUE),
        ('MR','Mile Run',TRUE);
#################INSERT INTRUSION TYPES##################
INSERT INTO INTRUSION_TYPE (INTRUSION_CODE,INTRUSION_NAME)
VALUES ('DC','Page direct call'),
        ('SI','SQL injections'),
        ('ER','Error with DB'),
        ('SH','Session Hijacking'),
        ('KS','Killed a Session'),
        ('EX','php exception'),
        ('UF','File Upload'),
        ('FM','File upload exceeded max. size'),
        ('FT','File Upload of improper type'),
        ('FA','File upload attack'),
        ('FR','File upload error');
################INSERT PHASES ############################
INSERT INTO PHASES (PHASE_NUM, PHASE_NAME, MEMBER_TYPE)
VALUES (1,'The Learning Phase','C'),
        (2,'The Leadership Phase','C'),
        (3, 'The command Phase','C'),
        (4, 'The Executive Phase','C'),
        (5, 'Spaatz','C');
#################INSERT cadet grades###############
INSERT INTO GRADE (GRADE_ABREV,MEMBER_TYPE,GRADE_NAME)
VALUES ('C/AB' ,'C', 'Airman Basic'),
('C/Amn','C','Airman'),
('C/A1C' ,'C', 'Airman 1st Class'),
('C/SrA' ,'C', 'Senior Airman'),
('C/SSgt' ,'C', 'Staff Sergeant'),
('C/TSgt' ,'C', 'Technical Sergeant'),
('C/MSgt' ,'C', 'Master Sergeant'),
('C/SMSgt' ,'C', 'Senior Master Sergeant'),
('C/CMSgt' ,'C', 'Chief Master Sergeant'),
('C/2d Lt' ,'C', '2nd Lieutenant'),
('C/1st Lt' ,'C', '1st Lieutenant'),
('C/Capt' ,'C', 'Captain'),
('C/Maj' ,'C', 'Major'),
('C/Lt Col' ,'C', 'Lieutenant Colonel'),
('C/Col' ,'C', 'Colonel');
###################INSERT CADET ACHIEVEMENTS###################
INSERT INTO ACHIEVEMENT (ACHIEV_CODE,MEMBER_TYPE,ACHIEV_NAME,GRADE,PHASE,NEXT_ACHIEV,ACHIEV_NUM)
VALUES('SPA' ,'C', 'Gen Carl A. Spaatz','C/Col',5,NULL,21),
('EAK','C','Gen. Ira C Eaker','C/Lt Col',4,'SPA',20),
('16' ,'C', 'C/Commander','C/Maj',4,'EAK',20),
('15' ,'C', 'Logistics Officer','C/Maj',4,'16',19),
('14' ,'C', 'Operations Officer','C/Maj',4,'15',18),
('13' ,'C', 'Aerospace Officer','C/Capt',4,'14',17),
('12' ,'C', 'Leadership Officer','C/Capt',4,'13',16),
('EAR' ,'C', 'Emelia Earhart','C/Capt',3,'12',14),
('11' ,'C', 'Public Affairs','C/1st Lt',3,'EAR',13),
('10' ,'C', 'Admin. Officer','C/1st Lt',3,'11',12),
('9' ,'C', 'Flight Commander','C/2d Lt',3,'10',11),
('BMI' ,'C', 'Gen. Billy Mitchell','C/2d Lt',2,'9',10),
('8' ,'C', 'Neil Armstrong','C/CMSgt',2,'BMI',9),
('7' ,'C', 'Robert Goddard','C/CMSgt',2,'8',8),
('6' ,'C', 'Gen Jimmy Doolittle','C/SMSgt',2,'7',7),
('5' ,'C', 'Charles Lindberg','C/MSgt',2,'6',6),
('4' ,'C', 'Eddie Rickenbacker','C/TSgt',2,'5',5),
('WB' ,'C', 'Wright Brothers','C/SSgt',1,'4',4),
('3' ,'C', 'Mary Feik','C/SrA',1,'WB',3),
('2' ,'C', 'Gen Hap Arnold','C/A1C',1,'3',2),
('1','C','John F. Curry','C/Amn',1,'2',1),
('0','C','Airman Basic','C/AB',1,'1',0); 
###########################INSERT TEXT SET#########################
INSERT INTO TEXT_SETS (TEXT_SET_CODE, TEXT_SET_NAME)
VALUES ('L21C','Leadership for the 21st Century'),
('L2L','Learn to Lead'),
('ALL','All text sets');
##########################insert test types########################
INSERT INTO REQUIREMENT_TYPE (TYPE_CODE,TYPE_NAME, MEMBER_TYPE)
VALUES('LT','Leadership Test'),
    ('AE','Aerospace Test','C'),
    ('DT','Drill Test','C'),
    ('PT','Cadet Physical Fitness Test','C'),
    ('PB','Promotion Board','C'),
    ('SD','Staff Duty Analysis','C'),
    ('ME','Mentor a cadet','C'),
    ('AC','Squadron Activity'),
    ('CD','Character Development','C'),
    ('SA','Safety',NULL),
    ('CO','Cadet Oath','C'),
    ('GS','Grooming Standards','C');
#########################insert promotion requirements##########################
INSERT INTO PROMOTION_REQUIREMENT (ACHIEV_CODE, REQUIREMENT_TYPE, PER_ACHIEVEMENT, PER_PHASE, TEXT_SET, NAME, PASSING_PERCENT, IS_ONLINE)
VALUES('WB','LT',TRUE,FALSE,'L21C','Wright Brothers','80',false),
    ('WB','CD',TRUE,FALSE,'ALL',null,null,false),
    ('WB','CO',TRUE,FALSE,'ALL',null,null,false),
    ('WB','GS',true,false,'ALL',null,null,false);
#######################insert contact relationships############################
INSERT INTO CONTACT_RELATIONS(RELATION_CODE,RELATION_NAME)
VALUES('FA','Father'),
    ('MO','Mother'),
    ('BR','Brother'),
    ('GM','GrandMother'),
    ('GF','GrandFather');
INSERT INTO REGION(REGION_CODE,REGION_NAME)
VALUES('RMR','Rocky Mountain Region');
INSERT INTO WING(WING,WING_NAME)
VALUES('ID','Idaho Wing');
###################################INSERT ACTION TYPES########################
INSERT INTO TASK_TYPE (TYPE_CODE, TYPE_NAME)
VALUES('ME','Membership Action'),
    ('TP','Testing and Promotions'),
    ('EV','Event Management'),
    ('DA','Discipline action'),
    ('RE','Squadron Reports'),
    ('AD','Administration');
###############################insert staff positions for permissions#########
INSERT INTO STAFF_POS_PERM(STAFF_CODE, STAFF_NAME)
VALUES('AD','Administrator'),
        ('AL','All');
##############################insert tasks#################################
INSERT INTO TASKS(TASK_CODE, TASK_NAME, TYPE_CODE, URL, NEW_TAB, UNGRANTABLE)
VALUES('MEA','Approve Added Members','ME','member/approve.php',FALSE,FALSE),
        ('HOM','Home','ME','home.php',FALSE,FALSE),
        ('MSE','Member Search','ME','member/search.php',FALSE,FALSE),
        ('DDE','Find Discipline Event','DA','discipline/details.php',FALSE,FALSE),
        ('EVR','Find Event Report','EV','attendance/event.php',FALSE,FALSE),
        ('NEV','Create a new Event','EV','attendance/new.php',FALSE,FALSE),
        ('EVI','Insert attendance for an Event','EV','attendance/add.php',FALSE,FALSE),
        ('TSE','View Testing Sign-up','TP','testing/testSignUp',FALSE,FALSE),
        ('PSE','Promotion Sign-up','TP','testing/promotionSign.php',FALSE,TRUE),
        ('PTT','Manage CPFT Testing','TP','testing/PTtest.php',FALSE,FALSE),
        ('PRR','Edit Promotion Record','TP','testing/promoRecord.php',FALSE,TRUE),
        ('EMR','Emergency Contact Information','RE','reporting/emergency_contact.php',true,FALSE),
        ('PAS','Change your Password','AD','adminis/pswdChange.php',false,FALSE),
        ('NME','Create Staff Member','AD','adminis/newMember.php',false,true),
        ('SPM','Change Staff Permissions','AD','adminis/staffPerm.php',false,true),
        ('ADL','View site Logs','AD','adminis/auditLog.php',false,true),
        ('LLO','Login Logs and Locks','AD','adminis/loginLog.php',false,true),
        ('CLO','Clear System Logs','AD','adminis/clearLog.php',false,true);
############################INSERT STAFF_PERMISSIONS######################
INSERT INTO STAFF_PERMISSIONS(STAFF_CODE,TASK_CODE)
VALUES('CTO','MEA')
        ('CTO','MSE'),
        ('AL','HOM')
        ('CTO','DDE'),
        ('CTO','EVR'),
        ('CTO','NEV'),
        ('CTO','EVI'),
        ('CTO','TSE'),
        ('CTO','PSE'),
        ('CTO','PTT'),
        ('CTO','PRR'),
        ('CTO','EMR'),
        ('AL','PAS'),
        ('CTO','NME'),
        ('CTO','SPM'),
        ('CTO','ADL'),
        ('CTO','LLO'),
        ('CTO','CLO');
###########################INSERT INTO PERMANENT_VISIT##############
INSERT INTO NEXT_VISIT(LAST_URL,NEXT_URL)
VALUES('member/approve.php','member/finalApprove.php'),
        ('member/search.php','member/report.php'),
        ('discipline/details.php','member/report.php'),
        ('attendance/event.php','member/report.php'),
        ('testing/testSignUp.php','member/report.php'),
        ('testing/promotionSign.php','member/report.php'),
        ('testing/PTtest.php','testing/ptCSV.php'),
        ('adminis/staffPerm.php','member/report.php'),
        ('adminis/loginLog.php','member/report.php'),
        ('adminis/auditLog.php','member/report.php'),
        ('adminis/clearLog.php','adminis/deleteIt.php'),
        ('adminis/clearLog.php','member/research.php');
#############################INSERT STAFF POSITIONS##################
INSERT INTO STAFF_POSITIONS(STAFF_CODE, STAFF_NAME)
VALUES('CC','Squadron Commander'),
        ('DCC','Deputy Commander of Cadets'),
        ('CCC','Cadet Commander'),
        ('CDC','Cadet Deputy Commander'),
        ('CXO','Cadet Executive Officer'),
        ('FLC','Flight Commander'),
        ('FLS','Flight Sergeant'),
        ('AL','All staff positions','A');
INSERT INTO CHAIN_OF_COMMAND(POS_CODE, STAFF_CODE, FLIGHT, ELEMENT, NEXT_IN_CHAIN)
VALUES('CC','CC',null,null,null),
        ('DCC','DCC',null,null,'CC'),
        ('CCC','CCC',null,null,'CC'),
        ('CDC','CDC',null,null,'CCC');