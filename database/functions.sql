DELIMITER $$
CREATE DEFINER=`d01f5afe`@`%` FUNCTION `VendorId`(val INTEGER) RETURNS char(255) CHARSET latin1
BEGIN
	DECLARE res char(255);
	SELECT name from vendorids where id = val into res;
    select ifnull(res, '') into res;
	return res;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`d01f5afe`@`%` FUNCTION `VkFormat`(val INTEGER) RETURNS char(255) CHARSET utf8
BEGIN
	DECLARE res char(255);
	SELECT name from VkFormat where value = val into res;
	
	IF (res = '') THEN
		RETURN 'unknown';
	ELSE
		RETURN res;
	END IF;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`d01f5afe`@`%` FUNCTION `VkPhysicalDeviceType`(val INTEGER) RETURNS char(255) CHARSET utf8
BEGIN
	DECLARE res char(255);
	SELECT name from VkPhysicalDeviceType where value = val into res;
	
	IF (res = '') THEN
		RETURN 'unknown';
	ELSE
		RETURN res;
	END IF;

END$$
DELIMITER ;
