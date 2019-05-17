UPDATE pages
SET pages.body = REPLACE(pages.body, '/uploads/', 'https://s3.eu-central-1.amazonaws.com/org.domcek.public/reduced/')
WHERE pages.body LIKE '%/uploads/%';

UPDATE news_items
SET news_items.body = REPLACE(news_items.body, '/uploads/', 'https://s3.eu-central-1.amazonaws.com/org.domcek.public/reduced/')
WHERE news_items.body LIKE '%/uploads/%';

UPDATE news_items
SET news_items.image = CONCAT('https://s3.eu-central-1.amazonaws.com/org.domcek.public/reduced/', news_items.image_file_name)
where news_items.image_file_name IS NOT NULL;

UPDATE slider_images
SET slider_images.image = CONCAT('https://s3.eu-central-1.amazonaws.com/org.domcek.public/reduced/', slider_images.image_file_name)
where slider_images.image_file_name IS NOT NULL;


DELIMITER $$
CREATE FUNCTION `SPLIT_STRING`( s VARCHAR(1024) , del CHAR(1) , i INT) RETURNS varchar(1024) CHARSET utf8
    DETERMINISTIC
BEGIN

    DECLARE n INT ;

    -- get max number of items
    SET n = LENGTH(s) - LENGTH(REPLACE(s, del, '')) + 1;

    IF i > n THEN
        RETURN NULL ;
    ELSE
        RETURN SUBSTRING_INDEX(SUBSTRING_INDEX(s, del, i) , del , -1 ) ;
    END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE `ACTION_VOLUNTEER_TYPE_BY_INDEX`(i INT)
BEGIN
    INSERT INTO volunteer_types (volunteer_types.name, volunteer_types.active)
    SELECT SPLIT_STRING(actions.action_volunteers_types, ',', i), 1
    FROM actions
    WHERE SPLIT_STRING(actions.action_volunteers_types, ',', i) IS NOT NULL and LENGTH(SPLIT_STRING(actions.action_volunteers_types, ',', i)) > 0;

END$$
DELIMITER ;

CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(1);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(2);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(3);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(4);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(5);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(6);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(7);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(8);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(9);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(10);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(11);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(12);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(13);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(14);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(15);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(16);
CALL ACTION_VOLUNTEER_TYPE_BY_INDEX(17);

DELETE t1 FROM volunteer_types t1 INNER JOIN volunteer_types t2
WHERE t1.id < t2.id AND t1.name = t2.name;

INSERT INTO events (events.id, events.name, events.theme, events.type, events.start_date,
                    events.start_registration, events.end_volunteer_registration,
                    events.end_registration, events.end_date, events.deposit, events.need_pay)
SELECT actions.action_id, actions.action_name, actions.action_theme, actions.action_type, actions.action_start,
       DATE_SUB(actions.action_reg_end, INTERVAL 14 DAY), actions.action_volunt_reg_end,
       actions.action_reg_end, actions.action_end, 0, 0 FROM  actions;

### MIGRATE USER MANUALY

INSERT INTO volunteers (
    event_id,
    user_id,
    volunteer_type_id,
    was_on_event)
SELECT action_id,
       75,
       (SELECT id FROM volunteer_types
        WHERE
                pilgrims.role = volunteer_types.name ) as vol_id,
       2
FROM pilgrims WHERE user_id = 9658 AND pilgrims.role != '';


INSERT INTO participants (
      participants.user_id,
      participants.event_id,
      participants.note,
      participants.transport_in,
      participants.transport_out,
      participants.was_on_event,
      participants.subscribed
      )
SELECT 75,
       pilgrims.action_id,
       note,
       bus_in,
       bus_out,
       1,
       1
FROM pilgrims WHERE user_id = 9658;

INSERT INTO payments (user_id, payment_number, paid, on_registration, need_pay, event_id)
SELECT 76, FLOOR(RAND()*100000000), pilgrims.payedDeposit, pilgrims.payedReg, (pilgrims.payedDeposit + pilgrims.payedReg), action_id FROM pilgrims WHERE pilgrims.user_id = 9658;
