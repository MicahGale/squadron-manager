USE SQUADRON_INFO;
#########################grant permissions to log security breaches###################
GRANT INSERT ON TABLE AUDIT_LOG TO 'Logger'@'localhost';
GRANT INSERT ON TABLE AUDIT_DUMP TO 'Logger'@'localhost';
GRANT INSERT,UPDATE,Select ON TABLE LOGIN_LOG TO 'Logger'@'localhost';
GRANT INSERT, delete, select ON TABLE ACCOUNT_LOCKS TO 'Logger'@'localhost';
##############################GRANT PERMISSIONS TO VIEWER TO GET CURRENT EVENT INFO##########
GRANT SELECT ON TABLE EVENT TO 'Viewer'@'localhost';
GRANT SELECT ON TABLE SUBEVENT TO 'Viewer'@'localhost';
GRANT SELECT ON TABLE SUBEVENT_TYPE TO 'Viewer'@'localhost';
GRANT SELECT ON TABLE CURRENT_SUBEVENT TO 'Viewer'@'localhost';
###############################GRANT SIGN IN PRIVILEGES #######################
GRANT INSERT ON TABLE INSERT_CURRENT TO 'Sign-in'@'localhost';
GRANT SELECT, INSERT, UPDATE ON TABLE MEMBER TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE ACHIEVEMENT TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE GRADE TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE MEMBERSHIP_TYPES TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE TEXT_SETS TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE CAP_UNIT TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE PROTECTED_REQUIRE_PASSED TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE REQUIREMENT_TYPE TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE PROMOTION_REQUIREMENT TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE CONTACT_RELATIONS TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE REGION TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE WING TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE CURRENT_EVENT TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE EVENT TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE PROMOTION_SIGN_UP TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE REQUIREMENTS_PASSED TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE PROMOTION_RECORD TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE ATTENDANCE TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE SUBEVENT TO 'Sign-in'@'localhost';
GRANT INSERT ON TABLE PROMOTION_SIGN_UP TO 'Sign-in'@'localhost';
GRANT INSERT ON TABLE TESTING_SIGN_UP TO 'Sign-in'@'localhost';
GRANT INSERT, SELECT, UPDATE ON TABLE EMERGENCY_CONTACT TO 'Sign-in'@'localhost';
GRANT INSERT ON TABLE NEW_MEMBER TO 'Sign-in'@'localhost';
GRANT SELECT ON TABLE PROMOTION_BOARD TO 'Sign-in'@'localhost';
###########################grant prediction privileges#####################
GRANT SELECT ON TABLE NEXT_VISIT TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE TASK_TYPE TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE TASKS TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE STAFF_POSITIONS TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE STAFF_POSITIONS_HELD TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE CHAIN_OF_COMMAND TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE SPECIAL_PERMISSION TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE ACCOUNT_LOCKS TO 'ViewNext'@'localhost';
GRANT SELECT ON TABLE LOGIN_LOG TO 'ViewNext'@'localhost';
############################grant login priviledges#################3
GRANT SELECT, UPDATE, INSERT ON SQUADRON_INFO.* TO 'login'@'localhost';
GRANT DELETE, select ON SQUADRON_INFO.* TO 'delete'@'localhost';