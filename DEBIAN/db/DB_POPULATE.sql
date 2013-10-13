# ###############################TO INSERT IGNORE DATA INTO THE DATABASE##################################
# #############INSERT IGNORE MEMBERHIP TYPES#################
USE SQUADRON_MANAGER;
INSERT IGNORE INTO MEMBERSHIP_TYPES (MEMBER_TYPE_CODE, MEMBER_TYPE_NAME)
VALUES ('C', 'cadet'),
        ('S','senior member'),
    ('P','Cadet Sponsor Member'),
    ('L','Legislative and Congressional Member'),
    ('A','Aerospace Educator Member');
# ############INSERT IGNORE VALUES FOR TYPES OF OFFENSES########
INSERT IGNORE INTO DISCIPLINE_OFFENSES (OFFENSE_CODE, OFFENSE_NAME)
 VALUES ('DIS','disrespectful behavior'),
        ('UNP','Unproffesional behavior'),
        ('LGS','Not meeting the grooming standards'),
        ('SPE','Speaking out of turn');
# #############INSERT IGNORE EVENT TYPES########################
INSERT IGNORE INTO EVENT_TYPES (EVENT_TYPE_CODE, EVENT_TYPE_NAME)
VALUES ('M','Meeting'),
        ('SE','Squadron Events'),
        ('HG','Honor Guard'),
        ('CG','Color Guard'),
        ('CP','CyberPatriot'),
        ('SARX','Search and Rescue Exercise'),
        ('ENC','Basic Encampment');
##############INSERT IGNORE SUB EVENT TYPE######################
INSERT IGNORE INTO SUBEVENT_TYPE (SUBEVENT_TYPE, SUBEVENT_NAME)
VALUES ('SAF','Safety'),
        ('ES','Emergency Services'),
        ('AE','Aerospace Education'),
        ('CD','Character Development');
#################INSERT IGNORE VALUES FOR CPFT TYPE#############
INSERT IGNORE INTO CPFT_TEST_TYPES (TEST_CODE, TEST_NAME,IS_RUNNING)
VALUES ('SR','Sit and Reach',FALSE),
        ('CU','Curl-ups',FALSE),
        ('PU','Push-ups',FALSE),
        ('RS','Shuttle Run',TRUE),
        ('MR','Mile Run',TRUE);
-- INSERT IGNORE INTO CPFT_REQUIREMENTS(PHASE,GENDER,AGE,TEST_TYPE,REQUIREMENT,START_ACHIEV,END_ACHIEV)
-- VALUES (1,'M',12,'SR',21,'1',NULL);
#################INSERT IGNORE INTRUSION TYPES##################
INSERT IGNORE  INTO INTRUSION_TYPE (INTRUSION_CODE,INTRUSION_NAME)
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
        ('DR','Delete Record'),
        ('RS','Re-signin');
################INSERT IGNORE PHASES ############################
INSERT IGNORE INTO PHASES (PHASE_NUM, PHASE_NAME, MEMBER_TYPE)
VALUES (1,'The Learning Phase','C'),
        (2,'The Leadership Phase','C'),
        (3, 'The command Phase','C'),
        (4, 'The Executive Phase','C'),
        (5, 'Spaatz','C'),
        (6,'Senior Member','S');
#################INSERT IGNORE cadet grades###############
INSERT IGNORE INTO GRADE (GRADE_ABREV,MEMBER_TYPE,GRADE_NAME)
VALUES ('C/AB' ,'C', 'Airman Basic'),
('C/Amn','C','Airman'),
('C/A1C' ,'C', 'Airman 1st Class'),
('C/SrA' ,'C', 'Senior Airman'),
('C/SSgt' ,'C', 'C/Staff Sergeant'),
('C/TSgt' ,'C', 'C/Technical Sergeant'),
('C/MSgt' ,'C', 'C/Master Sergeant'),
('C/SMSgt' ,'C', 'C/Senior Master Sergeant'),
('C/CMSgt' ,'C', 'C/Chief Master Sergeant'),
('C/2d Lt' ,'C', 'C/2nd Lieutenant'),
('C/1st Lt' ,'C', 'C/1st Lieutenant'),
('C/Capt' ,'C', 'C/Captain'),
('C/Maj' ,'C', 'C/Major'),
('C/Lt Col' ,'C', 'C/Lieutenant Colonel'),
('C/Col' ,'C', 'C/Colonel'),
('SM','S','Senior Member'),
('SSgt','S','Staff Sergeant'),
('TSgt','S','Technical Sergeant'),
('MSgt','S','Master Sergeant'),
('SMSgt','S','Senior Master Sergeant'),
('CMSgt','S','Chief Master Sergeant'),
('2d_Lt','S','Second Lieutenant'),
('1st_Lt','S','First Lieutenant'),
('Capt','S','Captain'),
('Maj','S','Major'),
('Lt_Col','S','Lieutenant Colonel'),
('Col','S','Colonel'),
('Brig_Gen','S','Brigadier General'),
('Maj_Gen','S','Major Gneral'),
('FO','S','Flight Officer'),
('TFO','S','Technical Flight Officer'),
('SFO','S','Senior Flight Officer');
###################INSERT IGNORE CADET ACHIEVEMENTS###################
INSERT IGNORE INTO ACHIEVEMENT (ACHIEV_CODE,MEMBER_TYPE,ACHIEV_NAME,GRADE,PHASE,NEXT_ACHIEV,ACHIEV_NUM)
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
('0','C','Airman Basic','C/AB',1,'1',0),
('MAG','S','Major General','Maj_Gen',6,null,10),
('BRG','S','Brigadier General','Brig_Gen',6,'MAG',9),
('COL','S','Colonel','Col',6,'BRG',8),
('LCL','S','Lieutenant Colonel','Lt_Col',6,'COL',7),
('MAJ','S','Major','Maj',6,'LCL',6),
('CAP','S','Captain','Capt',6,'MAJ',5),
('1LT','S','First Lieutenant','1st_Lt',6,'CAP',4),
('2LT','S','Second Lieutenant','2d_Lt',6,'1LT',3),
('SFO','S','Senior Flight Officer','SFO',6,'2LT',2),
('TFO','S','Techincal Flight Officer','TFO',6,'SFO',1),
('FO','S','Flight Officer','FO',6,'TFO',0),
('CMS','S','Chief Master Sergeant','CMSgt',6,null,-1),
('SMS','S','Senior Master Sergeant','SMSgt',6,null,-2),
('MS','S','Master Sergeant','MSgt',6,null,-3),
('TS','S','Technical Sergeant','TSgt',6,null,-4),
('SS','S','Staff Sergeant','SSgt',6,null,-5); 
###########################INSERT IGNORE TEXT SET#########################
INSERT IGNORE INTO TEXT_SETS (TEXT_SET_CODE, TEXT_SET_NAME)
VALUES ('L21C','Leadership for the 21st Century'),
('L2L','Learn to Lead'),
('ALL','All text sets');
##########################insert test types########################
INSERT IGNORE INTO REQUIREMENT_TYPE (TYPE_CODE,TYPE_NAME, MEMBER_TYPE)
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
    ('LA','Leadership Academy','C');
#########################insert promotion requirements##########################
INSERT IGNORE INTO PROMOTION_REQUIREMENT (ACHIEV_CODE, REQUIREMENT_TYPE, TEXT_SET, NAME, PASSING_PERCENT, IS_ONLINE)
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
('9','GS','ALL',NULL,NULL,FALSE),
('10','LT','L2L','Chapter 10',0.8,true),
('10','AE','ALL','Chapters 2,9,10',0.8,true),
('10','SD','ALL','Administrative Officer',null,false),
('10','CD','ALL',NULL,NULL,FALSE),
('10','AC','ALL',NULL,NULL,FALSE),
('10','ME','ALL',NULL,NULL,FALSE),
('10','PT','ALL',NULL,NULL,FALSE),
('10','SA','ALL',NULL,NULL,FALSE),
('10','CO','ALL',NULL,NULL,FALSE),
('10','GS','ALL',NULL,NULL,FALSE),
('11','LT','L2L','Chapter 11',0.8,true),
('11','AE','L2L','Chapters 3,18,19',0.8,true),
('11','SD','ALL','Public Affairs Officer',null,false),
('11','CD','ALL',NULL,NULL,FALSE),
('11','AC','ALL',NULL,NULL,FALSE),
('11','ME','ALL',NULL,NULL,FALSE),
('11','PT','ALL',NULL,NULL,FALSE),
('11','SA','ALL',NULL,NULL,FALSE),
('11','CO','ALL',NULL,NULL,FALSE),
('11','GS','ALL',NULL,NULL,FALSE),
('EAR','LT','L2L','Chapters 9-11',0.8,true),
('EAR','AE','ALL','Modules 1-6',0.8,true),
('EAR','SS','ALL','Flight CC, Admin, or PAO',null,false),
('EAR','CD','ALL',NULL,NULL,FALSE),
('EAR','AC','ALL',NULL,NULL,FALSE),
('EAR','ME','ALL',NULL,NULL,FALSE),
('EAR','PT','ALL',NULL,NULL,FALSE),
('EAR','SA','ALL',NULL,NULL,FALSE),
('EAR','CO','ALL',NULL,NULL,FALSE),
('EAR','GS','ALL',NULL,NULL,FALSE),
('12','LT','L2L','Chapters 12',0.8,true),
('12','SDA','ALL','Leadership Officer',null, false),
('12','CD','ALL',NULL,NULL,FALSE),
('12','AC','ALL',NULL,NULL,FALSE),
('12','ME','ALL',NULL,NULL,FALSE),
('12','PT','ALL',NULL,NULL,FALSE),
('12','SA','ALL',NULL,NULL,FALSE),
('12','CO','ALL',NULL,NULL,FALSE),
('12','GS','ALL',NULL,NULL,FALSE),
('13','LT','L2L','Chapters 13',0.8,true),
('13','SDA','ALL','Aerospace Education Officer',null, false),
('13','CD','ALL',NULL,NULL,FALSE),
('13','AC','ALL',NULL,NULL,FALSE),
('13','ME','ALL',NULL,NULL,FALSE),
('13','PT','ALL',NULL,NULL,FALSE),
('13','SA','ALL',NULL,NULL,FALSE),
('13','CO','ALL',NULL,NULL,FALSE),
('13','GS','ALL',NULL,NULL,FALSE),
('14','LT','L2L','Chapters 14',0.8,true),
('14','AE','ALL','Chapters 4,21,23',0.8,true),
('14','SDA','ALL','Operations Officer',null, false),
('14','CD','ALL',NULL,NULL,FALSE),
('14','AC','ALL',NULL,NULL,FALSE),
('14','ME','ALL',NULL,NULL,FALSE),
('14','PT','ALL',NULL,NULL,FALSE),
('14','SA','ALL',NULL,NULL,FALSE),
('14','CO','ALL',NULL,NULL,FALSE),
('14','GS','ALL',NULL,NULL,FALSE),
('15','LT','L2L','Chapters 15',0.8,true),
('15','AE','ALL','Chapters 5,24,25',0.8,true),
('15','SDA','ALL','Logistics Officer',null, false),
('15','CD','ALL',NULL,NULL,FALSE),
('15','AC','ALL',NULL,NULL,FALSE),
('15','ME','ALL',NULL,NULL,FALSE),
('15','PT','ALL',NULL,NULL,FALSE),
('15','SA','ALL',NULL,NULL,FALSE),
('15','CO','ALL',NULL,NULL,FALSE),
('15','GS','ALL',NULL,NULL,FALSE),
('16','LT','L2L','Chapters 16',0.8,true),
('16','AE','ALL','Chapters 6,26,27',0.8,true),
('16','SDA','ALL','Cadet Commander',null, false),
('16','CD','ALL',NULL,NULL,FALSE),
('16','AC','ALL',NULL,NULL,FALSE),
('16','ME','ALL',NULL,NULL,FALSE),
('16','PT','ALL',NULL,NULL,FALSE),
('16','SA','ALL',NULL,NULL,FALSE),
('16','CO','ALL',NULL,NULL,FALSE),
('16','GS','ALL',NULL,NULL,FALSE),
('EAK','LA','ALL','COS or RCLS',null,false),
('EAK','SS','ALL','LG, AE, DO, C/CC',null, false),
('EAK','CD','ALL',NULL,NULL,FALSE),
('EAK','AC','ALL',NULL,NULL,FALSE),
('EAK','ME','ALL',NULL,NULL,FALSE),
('EAK','PT','ALL',NULL,NULL,FALSE),
('EAK','SA','ALL',NULL,NULL,FALSE),
('EAK','CO','ALL',NULL,NULL,FALSE),
('EAK','GS','ALL',NULL,NULL,FALSE),
('SPA','AE','ALL','Chapters 1-27',0.8,false),
('SPA','LT','L2L','Chatpers 1-16',0.8,false),
('SPA','CD','ALL','Write an Essay',null,false),
('SPA','PT','ALL',null,null,false);
#######################insert contact relationships############################
INSERT IGNORE INTO CONTACT_RELATIONS(RELATION_CODE,RELATION_NAME)
VALUES('FA','Father'),
    ('MO','Mother'),
    ('BR','Brother'),
    ('GM','GrandMother'),
    ('GF','GrandFather'),
    ('BR','Brother'),
    ('SI','Sister');
INSERT IGNORE INTO REGION(REGION_CODE,REGION_NAME)
VALUES('RMR','Rocky Mountain Region'),
('NER','Northeast Region'),
('MER','Midwest Region'),
('SER','Southeast Region'),
('SWR','Southwest Region'),
('GLR','Great Lakes Region'),
('NCR','North Central Region'),
('PCR','Pacific Region');
INSERT IGNORE INTO WING(WING,WING_NAME)
VALUES ('DC','District of Columbia'), 
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
###################################INSERT IGNORE ACTION TYPES########################
INSERT IGNORE INTO TASK_TYPE (TYPE_CODE, TYPE_NAME)
VALUES('ME','Membership Action'),
    ('TP','Testing and Promotions'),
    ('EV','Event Management'),
    ('DA','Discipline action'),
    ('RE','Squadron Reports'),
    ('AD','Administration');
##############################insert tasks#################################
INSERT IGNORE INTO TASKS(TASK_CODE, TASK_NAME, TYPE_CODE, URL, NEW_TAB, UNGRANTABLE,GET_FIELD)
VALUES('MEA','Approve Added Members','ME','member/approve.php',FALSE,FALSE,NULL),
        ('HOM','Home','ME','home.php',FALSE,FALSE,NULL),
        ('MSE','Member Search','ME','member/search.php',FALSE,FALSE,NULL),
        ('DDE','Find Discipline Event','DA','discipline/details.php',FALSE,FALSE,NULL),
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
        ('CAO','Cadet Oath and Grooming','TP','testing/cadetOath.php',false, false,NULL),
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
        ('PPC','Cadet Promotion Sign-up-locked','TP','testing/promotionSign.php',false,false,'CL');
#############################INSERT IGNORE STAFF POSITIONS##################
INSERT IGNORE INTO STAFF_POSITIONS(STAFF_CODE, STAFF_NAME, MEMBER_TYPE)
VALUES('CC','Squadron Commander','S'),
        ('DCC','Deputy Commander of Cadets','S'),
        ('CCC','Cadet Commander','C'),
        ('CDC','Cadet Deputy Commander','C'),
        ('CXO','Cadet Executive Officer','C'),
        ('FLC','Flight Commander','C'),
        ('FLS','Flight Sergeant','C'),
        ('CTO','Cadet Information Technology Officer','C'),
        ('AL','All staff positions','A');
############################INSERT IGNORE STAFF_PERMISSIONS######################
INSERT IGNORE INTO STAFF_PERMISSIONS(STAFF_CODE,TASK_CODE)
VALUES('CTO','MEA'),
        ('CTO','MSE'),
        ('AL','HOM'),
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
###########################INSERT IGNORE INTO PERMANENT_VISIT##############
INSERT IGNORE INTO NEXT_VISIT(LAST_CODE,NEXT_URL)
VALUES('MEA','member/finalApprove.php'),
        ('MSE','member/report.php'),
        ('DDE','member/report.php'),
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