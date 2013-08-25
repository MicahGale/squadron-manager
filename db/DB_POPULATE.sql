# ###############################TO INSERT DATA INTO THE DATABASE##################################
# #############INSERT MEMBERHIP TYPES#################
INSERT INTO MEMBERSHIP_TYPES (MEMBER_TYPE_CODE, MEMBER_TYPE_NAME)
VALUES ('C', 'cadet'),
        ('S','senior member'),
    ('P','Cadet Sponsor Member'),
    ('L','Legislative and Congressional Member'),
    ('A','Aerospace Educator Member');
# ############INSERT VALUES FOR TYPES OF OFFENSES########
# INSERT INTO DISCIPLINE_OFFENSES (OFFENSE_CODE, OFFENSE_NAME)
# VALUES ('DIS','disrespectful behavior'),
#        ('UNP','Unproffesional behavior'),
#        ('LGS','Not meeting the grooming standards'),
#        ('SPE','Speaking out of turn');
# #############INSERT EVENT TYPES########################
INSERT INTO EVENT_TYPES (EVENT_TYPE_CODE, EVENT_TYPE_NAME)
VALUES ('M','Meeting'),
        ('SE','Squadron Events'),
        ('HG','Honor Guard'),
        ('CG','Color Guard'),
        ('CP','CyberPatriot'),
        ('SARX','Search and Rescue Exercise'),
        ('ENC','Basic Encampment');
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
        ('FR','File upload error'),
        ('DR','Delete Record');
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
VALUES('LT','Leadership Test','C'),
    ('AE','Aerospace Test','C'),
    ('DT','Drill Test','C'),
    ('PT','Cadet Physical Fitness Test','C'),
    ('PB','Promotion Board','C'),
    ('SD','Staff Duty Analysis','C'),
    ('SS','Staff Service','C'),
    ('ME','Mentor a cadet','C'),
    ('AC','Squadron Activity','C'),
    ('CD','Character Development','C'),
    ('SA','Safety',NULL),
    ('CO','Cadet Oath','C'),
    ('GS','Grooming Standards','C'),
    ('SE','Speech and Essay','C'),
    ('EC','Basic Encampment','C'),
    ('LA','Leadership Activity','C');
#########################insert promotion requirements##########################
INSERT INTO PROMOTION_REQUIREMENT (ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET, NAME, PASSING_PERCENT, IS_ONLINE)
VALUES ('1','LT','L2L','Chapter 1',0.80,true),
    ('1','CD','ALL','Foundations Module',null,false),
('1','AC','ALL',null,null,false),
('1','PT','ALL',null,null,false),
('1','DT','L2L',null, 0.7333,FALSE),
('1','CO','ALL',null,null,false),
('1','GS','ALL',null,null,false),
('1','SA','ALL',null,null,false),
('2','LT','L2L','Chapter 2',0.8,true),
('2','AE','ALL','Module 1',0.8,true),
('2','PT','ALL',null,null,false),
('2','DT','L2L',null,.7333,false),
('2','AC','ALL',null,null,false),
('2','CD','ALL',null,null,false),
('2','SA','ALL',null,null,false),
('2','CO','ALL',null,null,false),
('2','GS','ALL',null,null,false),
('3','LT','L2L','Chapter 3',0.8,true),
('3','AE','ALL','Module 2',0.8,true),
('3','DT','L2L',null,0.727272727,false),
('3','PT','ALL',null,null,false),
('3','AC','ALL',null,null,false),
('3','CD','ALL',NULL,NULL,FALSE),
('3','SA','ALL',null,NULL,FALSE),
('3','CO','ALL',NULL,NULL,FALSE),
('3','GS','ALL',NULL,NULL,FALSE),
   ('WB','LT','L2L','Ch. 1-3 Cumulative Review',0.8,false),
('WB','DT','L2L',null,0.7333,false),                # TODO check percentage
('WB','PT','ALL',null,null,false),
('WB','AC','ALL',null,null,false),
('WB','CD','ALL',NULL,NULL,FALSE),
('WB','SA','ALL',NULL,NULL,FALSE),
('WB','CO','ALL',NULL,NULL,FALSE),
('WB','GS','ALL',NULL,NULL,FALSE),
('4','LT','L2L','Chapter 4',0.8,true),
('4','AE','ALL','Module 3',0.8,TRUE),
('4','DT','L2L',NULL,0.8,FALSE),
('4','CD','ALL',NULL,NULL,FALSE),
('4','AC','ALL',NULL,NULL,FALSE),
('4','PT','ALL',NULL,NULL,FALSE),
('4','CD','ALL',NULL,NULL,FALSE),
('4','SA','ALL',NULL,NULL,FALSE),
('4','CO','ALL',NULL,NULL,FALSE),
('4','GS','ALL',NULL,NULL,FALSE),
('5','LT','L2L','Chapter 5',0.8,true),
('5','AE','ALL','Module 4',0.8,true),
('5','DT','L2L',NULL,0.7619,FALSE),
('5','CD','ALL',NULL,NULL,FALSE),
('5','AC','ALL',NULL,NULL,FALSE),
('5','PT','ALL',NULL,NULL,FALSE),
('5','SA','ALL',NULL,NULL,FALSE),
('5','CO','ALL',NULL,NULL,FALSE),
('5','GS','ALL',NULL,NULL,FALSE),
('6','LT','L2L','Chapter 6',0.8,TRUE),
('6','AE','ALL','Module 5',0.8,true),
('6','DT','L2L',NULL,0.8,FALSE),
('6','AC','ALL',NULL,NULL,FALSE),
('6','CD','ALL',NULL,NULL,FALSE),
('6','SA','ALL',NULL,NULL,FALSE),
('6','CO','ALL',NULL,NULL,FALSE),
('6','GS','ALL',NULL,NULL,FALSE),
('7','LT','L2L','Chapter 7',0.8,TRUE),
('7','AE','ALL','Module 6',0.8,true),
('7','DT','ALL',NULL,0.8,FALSE),
('7','PT','ALL',NULL,NULL,FALSE),
('7','CD','ALL',NULL,NULL,FALSE),
('7','SA','ALL',NULL,NULL,FALSE),
('7','AC','ALL',NULL,NULL,FALSE),
('7','CO','ALL',NULL,NULL,FALSE),
('7','GS','ALL',NULL,NULL,FALSE),
('8','SE','ALL','Speech and Essay',null,false),
('8','PT','ALL',NULL,NULL,FALSE),
('8','DT','ALL',NULL,0.7,FALSE),
('8','CD','ALL',NULL,NULL,FALSE),
('8','AC','ALL',NULL,NULL,FALSE),
('8','SA','ALL',NULL,NULL,FALSE),
('8','CO','ALL',NULL,NULL,FALSE),
('8','GS','ALL',NULL,NULL,FALSE),
('BMI','LT','L2L','Ch. 1-7 Cumulitive Review',0.8,false),
('BMI','AE','ALL','Review of all modules',0.8,false),
('BMI','DT','L2L',NULL,NULL,FALSE), # TODO check on drill test
('BMI','CD','ALL',NULL,NULL,FALSE),
('BMI','AC','ALL',NULL,NULL,FALSE),
('BMI','EC','ALL',NULL,NULL,FALSE),
('BMI','PT','ALL',NULL,NULL,FALSE),
('BMI','SA','ALL',NULL,NULL,FALSE),
('BMI','CO','ALL',NULL,NULL,FALSE),
('BMI','GS','ALL',NULL,NULL,FALSE),
('9','LT','L2L','Chapter 9',0.8,true),
('9','AE','ALL','Chapters 1,7,8',0.8,true),
('9','SD','ALL','Flight Commander',null,false),
('9','CD','ALL',NULL,NULL,FALSE),
('9','AC','ALL',NULL,NULL,FALSE),
('9','ME','ALL',NULL,NULL,FALSE),
('9','PT','ALL',NULL,NULL,FALSE),
('9','SA','ALL',NULL,NULL,FALSE),
('9','CO','ALL',NULL,NULL,FALSE),
('9','GS','ALL',NULL,NULL,FALSE);
#######################insert contact relationships############################
INSERT INTO CONTACT_RELATIONS(RELATION_CODE,RELATION_NAME)
VALUES('FA','Father'),
    ('MO','Mother'),
    ('BR','Brother'),
    ('GM','GrandMother'),
    ('GF','GrandFather');
INSERT INTO REGION(REGION_CODE,REGION_NAME)
VALUES('RMR','Rocky Mountain Region'),
('NER','Northeast Region'),
('MER','Midwest Region'),
('SER','Southeast Region'),
('SWR','Southwest Region'),
('GLR','Great Lakes Region'),
('NCR','North Central Region'),
('PCR','Pacific Region');
INSERT INTO WING(WING,WING_NAME)
VALUES ('DC','District of Columbia'), --Not technically a state!
('AL','Alabama'),
('AK','Alaska'),
('AZ','Arizona'),
('AR','Arkansas'),
('CA','California'),
('CO','Colorado'),
('CT','Connecticut'),
('DE','Delaware'),
('FL','Florida'),
('GA','Georgia'),
('HI','Hawaii'),
('ID','Idaho'),
('IL','Illinois'),
('IN','Indiana'),
('IA','Iowa'),
('KS','Kansas'),
('KY','Kentucky'),
('LA','Louisiana'),
('ME','Maine'),
('MD','Maryland'),
('MA','Massachusetts'),
('MI','Michigan'),
('MN','Minnesota'),
('MS','Mississippi'),
('MO','Missouri'),
('MT','Montana'),
('NE','Nebraska'),
('NV','Nevada'),
('NH','New Hampshire'),
('NJ','New Jersey'),
('NM','New Mexico'),
('NY','New York'),
('NC','North Carolina'),
('ND','North Dakota'),
('OH','Ohio'),
('OK','Oklahoma'),
('OR','Oregon'),
('PA','Pennsylvania'),
('RI','Rhode Island'),
('SC','South Carolina'),
('SD','South Dakota'),
('TN','Tennessee'),
('TX','Texas'),
('UT','Utah'),
('VT','Vermont'),
('VA','Virginia'),
('WA','Washington'),
('WV','West Virginia'),
('WI','Wisconsin'),
('WY','Wyoming'),
('PC','Puerto Rico');
###################################INSERT ACTION TYPES########################
INSERT INTO TASK_TYPE (TYPE_CODE, TYPE_NAME)
VALUES('ME','Membership Action'),
    ('TP','Testing and Promotions'),
    ('EV','Event Management'),
    ('DA','Discipline action'),
    ('RE','Squadron Reports'),
    ('AD','Administration');
##############################insert tasks#################################
INSERT INTO TASKS(TASK_CODE, TASK_NAME, TYPE_CODE, URL, NEW_TAB, UNGRANTABLE,GET_FIELD)
VALUES('MEA','Approve Added Members','ME','member/approve.php',FALSE,FALSE,NULL),
        ('HOM','Home','ME','home.php',FALSE,FALSE,NULL),
        ('MSE','Member Search','ME','member/search.php',FALSE,FALSE,NULL),
 #       ('DDE','Find Discipline Event','DA','discipline/details.php',FALSE,FALSE,NULL),
        ('EVR','Find Event Report','EV','attendance/event.php',FALSE,FALSE,NULL),
        ('NEV','Create a new Event','EV','attendance/new.php',FALSE,FALSE,NULL),
        ('EVI','Insert attendance for an Event','EV','attendance/add.php',FALSE,FALSE,NULL),
        ('TSE','View Testing Sign-up','TP','testing/testSignUp.php',FALSE,FALSE,NULL),
        ('PSE','Promotion Sign-up','TP','testing/promotionSign.php',FALSE,TRUE,NULL),
        ('PTT','Manage CPFT Testing','TP','testing/PTtest.php',FALSE,FALSE,NULL),
        ('PRR','Edit Promotion Record','TP','testing/promoRecord.php',FALSE,TRUE,NULL),
        ('EMR','Emergency Contact Information','RE','reporting/emergency_contact.php',true,FALSE,NULL),
        ('PAS','Change your Password','AD','adminis/pswdChange.php',false,FALSE,NULL),
        ('NME','Create Staff Member','AD','adminis/newMember.php',false,true,NULL),
        ('SPM','Change Staff Permissions','AD','adminis/staffPerm.php',false,true,NULL),
        ('ADL','View site Logs','AD','adminis/auditLog.php',false,true,NULL),
        ('LLO','Login Logs and Locks','AD','adminis/loginLog.php',false,true,NULL),
        ('CLO','Clear System Logs','AD','adminis/clearLog.php',false,true,NULL),
        ('CAO','Cadet Oath and Grooming Standards','TP','testing/cadetOath.php',false, false,NULL),
        ('DRT','Drill Testing Sign-Up','TP','testing/testSignUp.php',false,false,'DT'),
        ('CPS','Cadet Promotion Sign-Up','TP','testing/promotionSign.php',false,true,'C'),
        ('SPS','Senior Member Promotions','TP','testing/promotionSign.php',false,true,'S'),
        ('CPR','Cadet Promotion Record','TP','testing/promoRecord.php',false,true,'C'),
        ('SPR','Senior Member Promotion Record','TP','testing/promoRecord.php',false,true,'S'),
        ('PRB','Manage Promotion Boards','TP','testing/promoBoard.php',false,false,null),
        ('ESR','Eservice Report','RE','reporting/eservReport.php',true,false,null), 
        ('EST','Enter Online Testing','TP','testing/onlineTesting.php',false, false,null),
        ('TME','Terminate Members','ME','member/termMembership.php',false,true,null),
        ('DME','Delete Member record','AD','adminis/deleteRecord.php',false,true,null),
        ('PPC','Cadet Promotion Sign-up-no approval','TP','testing/promotionSign.php',false,false,'CL');
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
        ('CTO','CLO'),
        ('CTO','CAO'),
        ('CTO','DRT'),
        ('CTO','CPS'),
        ('CTO','CPR');
###########################INSERT INTO PERMANENT_VISIT##############
INSERT INTO NEXT_VISIT(LAST_CODE,NEXT_URL)
VALUES('MEA','member/finalApprove.php'),
        ('MSE','member/report.php'),
     #   ('discipline/details.php','member/report.php'),
        ('EVR','member/report.php'),
        ('TSE','member/report.php'),
        ('PSE','member/report.php'),
        ('PTT','testing/ptCSV.php'),
        ('SPM','member/report.php'),
        ('LLO','member/report.php'),
        ('ADL','member/report.php'),
        ('CLO','adminis/deleteIt.php'),
        ('CLO','member/research.php'),
        ('DME','adminis/finishRecordDel.php');
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