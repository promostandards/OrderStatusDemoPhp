create database order_status;

CREATE TABLE `order_status`.`order_status_types` (
  `order_status_type_id` INT NOT NULL AUTO_INCREMENT,
  `order_status_id` INT(11) NOT NULL,
  `order_status_name` VARCHAR(256) NULL,
  PRIMARY KEY (`order_status_type_id`),
  UNIQUE INDEX `order_status_id_UNIQUE` (`order_status_id` ASC),
  UNIQUE INDEX `order_status_name_UNIQUE` (`order_status_name` ASC));


CREATE TABLE `order_status`.`order_status_vendor` (
  `order_status_vendor_id` INT NOT NULL AUTO_INCREMENT,
  `vendor_number` VARCHAR(45) NULL,
  `wsdl_end_point` VARCHAR(256) NULL,
  `user_id` VARCHAR(45) NULL,
  `credentials` VARCHAR(256) NULL,
  `active` TINYINT NULL,
  PRIMARY KEY (`order_status_vendor_id`),
  UNIQUE INDEX `vendor_number_UNIQUE` (`vendor_number` ASC));


CREATE TABLE `order_status`.`order_status_detail` (
  `order_status_detail_id` INT NOT NULL AUTO_INCREMENT,
  `po_number` VARCHAR(45) NULL,
  `factory_order_number` VARCHAR(45) NULL,
  `expected_ship_date_utc` DATETIME NULL,
  `expected_delivery_date_utc` DATETIME NULL,
  `additional_explanation` VARCHAR(256) NULL,
  `response_required` TINYINT NULL,
  `valid_time_stamp_utc` DATETIME NULL,
  `order_status_types_id` INT NOT NULL,
  `order_status_vendor_id` INT NULL,
  PRIMARY KEY (`order_status_detail_id`),
  INDEX `fk_order_status_detail_order_status_types1_idx` (`order_status_types_id` ASC),d
  INDEX `fk_order_status_detail_order_status_vendor1_idx` (`order_status_vendor_id` ASC),
  CONSTRAINT `fk_order_status_detail_order_status_types1`
  FOREIGN KEY (`order_status_types_id`)
  REFERENCES `order_status`.`order_status_types` (`order_status_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_status_detail_order_status_vendor1`
  FOREIGN KEY (`order_status_vendor_id`)
  REFERENCES `order_status`.`order_status_vendor` (`order_status_vendor_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


CREATE TABLE `order_status`.`order_status_respond_to` (
  `order_status_respond_to_id` INT NOT NULL AUTO_INCREMENT,
  `csr_name` VARCHAR(256) NULL,
  `csr_email` VARCHAR(256) NULL,
  `csr_phone` VARCHAR(45) NULL,
  `order_status_detail_id` INT NOT NULL,
  PRIMARY KEY (`order_status_respond_to_id`),
  INDEX `fk_order_status_respond_to_order_status_detail1_idx` (`order_status_detail_id` ASC),
  CONSTRAINT `fk_order_status_respond_to_order_status_detail1`
  FOREIGN KEY (`order_status_detail_id`)
  REFERENCES `order_status`.`order_status_detail` (`order_status_detail_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);
