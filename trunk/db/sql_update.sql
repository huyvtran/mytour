-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jan 30, 2013 at 08:43 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `mytour2`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `comments`
-- 

CREATE TABLE `comments` (
  `ID` bigint(20) NOT NULL auto_increment,
  `hotel_id` bigint(20) default NULL,
  `customer_id` bigint(20) default NULL,
  `time` datetime default NULL,
  `comment` varchar(500) default NULL,
  `user_id` bigint(20) default NULL,
  `status` enum('0','1') default '0',
  `root` bigint(20) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# daint : 06/02/2013
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `commission` (
  `ID` bigint(20) NOT NULL auto_increment,
  `partner_id` bigint(20) default NULL,
  `hotel_id` bigint(20) default NULL,
  `percent` int(11) default NULL,
  `date_start` date default NULL,
  `date_end` date default NULL,
  `desc` varchar(300) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# daint : 06/02/2013

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `partners` (
  `ID` bigint(20) NOT NULL auto_increment,
  `title` varchar(300) collate utf8_unicode_ci default NULL,
  `hotel_id` bigint(20) default NULL,
  `desc` varchar(300) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

# daint : 17/02/2013 thêm bảng view_stats

CREATE TABLE  `view_stats` (
 `ID` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `hotel_id` BIGINT NULL ,
 `room_type_id` BIGINT NULL ,
 `customer_id` BIGINT NULL ,
 `time` DATETIME NULL
) ENGINE = INNODB;

# daint : 18/02/2013 thêm trường has_children, gender, age
ALTER TABLE  `hotel_orders` ADD  `has_children` ENUM(  '0',  '1' ) NULL DEFAULT  '0' COMMENT  '''0'' : Có, ''1'' : Không' AFTER  `status`;
ALTER TABLE  `customers` ADD  `gender` ENUM(  '0',  '1' ) NULL AFTER  `fullname`;
ALTER TABLE  `customers` ADD  `age` TINYINT( 4 ) NULL AFTER  `gender`;

# giangnt : 18/02/2013 
ALTER TABLE  `hotel_orders` ADD  `extrabed_price` decimal(10,0) NULL AFTER  `has_extrabed` ;
ALTER TABLE  `hotel_orders` ADD  `extrabed_currency_id` int(11)  NULL AFTER  `extrabed_price` ;

# daint : 18/02/2013 thêm bảng surcharge_types

CREATE TABLE `surcharge_types` (
  `ID` bigint(20) NOT NULL auto_increment,
  `title` varchar(300) default NULL,
  `desc` varchar(300) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# daint : 18/02/2013 sửa bảng hotel_surcharge

DROP TABLE `hotel_surcharge`;

CREATE TABLE `hotel_surcharge` (
  `ID` bigint(20) NOT NULL auto_increment,
  `surcharge_id` bigint(20) default NULL,
  `date_start` date default NULL,
  `date_end` date default NULL,
  `desc` varchar(300) default NULL,
  `days` varchar(300) default NULL,
  `money` bigint(20) default NULL,
  `currency` enum('1','2') default NULL,
  `hotel_id` bigint(20) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;