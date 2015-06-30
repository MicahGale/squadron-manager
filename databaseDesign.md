# Introduction #

This describes all the tables used by this project, all of their columns, what data they hold, and their relations. Currently the name for the default database is SQUADRON\_INFO. **Note:** In mysql this all must be handled by INNODB.


# Tables #
Here are all the tables used and a brief description of what they are used for.
|[ACCOUNT\_LOCKS](#ACCOUNT_LOCKS.md)|Is used to store information about a login being locked for a set time|
|:----------------------------------|:---------------------------------------------------------------------|
|[ACHIEVEMENT](#ACHIEVEMENT.md)     |Holds all of the possible promotions and their associated rank        |
|[ATTENDANCE](#ATTENDANCE.md)       |Holds who attended what events                                        |
|[AUDIT\_DUMP](#AUDIT_DUMP.md)      |Holds any variables that are relevant to an audit-able event          |
|[AUDIT\_LOG](#AUDIT_LOG.md)        |Holds the basic info for audit-able events such as an attempted security exploit, or a DB error|
|[CAP\_UNIT](#CAP_UNIT.md)          |Holds all the CAP units that have signed into your squadron           |
|[CHAIN\_OF\_COMMAND](#CHAIN_OF_COMMAND.md)|Tells what staff position is under what other staff position _is position dependent **not** people dependent_|
|[CONTACT\_RELATIONS](#CONTACT_RELATIONS.md)|All the various relations an emergency contact can have with a member (i.e. Mother, Brother)|
|[CPFT\_REQUIREMENTS](#CPFT_REQUIREMENTS.md)|The Requirements for all of the CPFTs                                 |
|[CPFT\_TEST\_TYPES](#CPFT_TEST_TYPES.md)|The various tests for CPFT's (i.e. Shuttle Run)                       |
|[DISCIPLINE\_LOG](#DISCIPLINE_LOG.md)|Logs all discipline action given to Members                           |
|[DISCIPLINE\_OFFFENSES](#DISCIPLINE_OFFENSES.md)|The type of offense that disciplinary action was given for (i.e. Disrespectful behavior)|
|[DISCIPLINE\_TYPE](#DISCIPLINE_TYPE.md)|The type of Discipline Action given (i.e. verbal warning)             |
|[EMERGENCY\_CONTACT](#EMERGENCY_CONTACT.md)|Holds emergency contact information for members                       |
|[EVENT](#EVENTS.md)                |Holds the Events that members sign into                               |
|[EVENT\_LOCATION](#EVENT_LOCATION.md)|The location the events are held at                                   |
|[EVENT\_TYPES](#EVENT_TYPES.md)    |The type of the event (i.e. meeting, volunteering)                    |
|[FLIGHTS](#FLIGHTS.md)             |Your flights in your squadron                                         |
|[GRADE](#GRADE.md)                 | The various Ranks                                                    |
|[INTRUSION\_TYPE](#INTRUSION_TYPE.md)|The type of audit-able event for AUDIT\_LOG                           |
|[LOGIN\_LOG](#LOGIN_LOG.md)        |Logs all login attempts to prevent brute force attacks                |
|[MEMBER](#MEMBER.md)               |Holds the information for the Actual Members (their names)            |
|[MEMBERSHIP\_TYPES](#MEMBERSHIP_TYPES.md)|The types of membership (i.e. cadet, senior member, etc)              |
|[NEW\_MEMBER](#NEW_MEMBER.md)      |Sign-in for perspective members who don't have CAPIDS                 |
|[NEXT\_VISIT](#NEXT_VISIT.md)      |Pages in /login that can be accessed based upon the page they were just on|
|[PHASES](#PHASES.md)               |The various phases for promotions (i.e. Phase I C/AB-C/ssgt)          |
|[PROMOTION\_BOARD](#PROMOTION_BOARD.md)|Tracks all promotion boards given                                     |
|[PROMOTION\_RECORD](#PROMTION_RECORD.md)|Holds the record of each promotion                                    |
|[PROMOTION\_SIGN\_UP](#PROMOTION_SIGN_UP.md)|Holds requests to promote                                             |
|[REGION](#REGION.md)               |Holds all the CAP regions for use with the CAP Units                  |
|[REQUIREMENTS\_PASSED](#REQUIREMENTS_PASSED.md)|All the promotion requirements that have been passed (i.e. Passing the written Wright Brothers test)|
|REQUIREMENT\_TYPE                  |The types of promotion requirements (i.e. CPFTs Drill Tests)          |
|RIBBON                             |Holds all the ribbons you can be awarded                              |
|RIBBON\_REQUEST                    |Holds all the requests for receiving a ribbon, **note** requests do not need to be approved, because only staff can enter the requests|
|SPECIAL\_PERMISSION                |Holds extra task permissions granted to staff, that is not included with their staff position|
|STAFF\_PERMISSIONS                 |Task Permissions specific to that Staff Position (i.e. CDC's can edit member information)|
|STAFF\_POSITIONS\_HELD             |Tells who has what job (i.e. who is the CDC)                          |
|SUBEVENT                           |Tells what sub-events happened at an event (i.e. having a Safety class at a meeting)|
|SUBEVENT\_TYPE                     |The various sub-events                                                |
|TASKS                              |The actual thing staff have permission to do, gives the URL           |
|TASK\_TYPE                         |The type of the task (i.e. member management)                         |
|TESTING\_SIGN\_UP                  |Sign up for taking tests                                              |
|TEXT\_SET                          |The various textbook sets (i.e. Leadership for the 21st Century or Learn to Lead|
|WING                               |All the CAP Wings for the Units                                       |

## CAP\_UNIT ##