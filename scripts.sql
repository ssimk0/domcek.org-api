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

UPDATE pages
SET pages.body = REPLACE(pages.body, '/uploads/', 'https://s3.eu-central-1.wasabisys.com/org.domcek.public/media/')
WHERE pages.body LIKE '%/uploads/%';

UPDATE news_items
SET news_items.body = REPLACE(news_items.body, '/uploads/', 'https://s3.eu-central-1.wasabisys.com/org.domcek.public/media/')
WHERE news_items.body LIKE '%/uploads/%';

UPDATE news_items
SET news_items.image = CONCAT('https://s3.eu-central-1.wasabisys.com/org.domcek.public/media/', news_items.image_file_name)
where news_items.image_file_name IS NOT NULL;

UPDATE slider_images
SET slider_images.image = CONCAT('https://s3.eu-central-1.wasabisys.com/org.domcek.public/media/', slider_images.image_file_name)
where slider_images.image_file_name IS NOT NULL;