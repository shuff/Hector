SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `street1` varchar(256) COLLATE utf8_bin NOT NULL,
  `street2` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `city` varchar(256) COLLATE utf8_bin NOT NULL,
  `district` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `region` varchar(256) COLLATE utf8_bin NOT NULL,
  `postcode` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'US',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=205 ;


CREATE TABLE IF NOT EXISTS `carriers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;


CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8_bin NOT NULL,
  `phone` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=17 ;


CREATE TABLE IF NOT EXISTS `dispatches` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `from_yard` int(10) NOT NULL,
  `to_yard` int(10) NOT NULL,
  `invoice` float(15,2) DEFAULT NULL,
  `driver_pay` float(15,2) DEFAULT NULL,
  `truck_pay` float(15,2) DEFAULT NULL,
  `truck_count` int(11) DEFAULT NULL,
  `pickup_date` datetime DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;


CREATE TABLE IF NOT EXISTS `dispatches_freight_bills` (
  `dispatch_id` int(10) NOT NULL,
  `freight_bill_id` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `dispatches_images` (
  `dispatch_id` int(11) NOT NULL,
  `image_id` varchar(36) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# ############################

#
# Table structure for table `dispatches_materials`
#

CREATE TABLE IF NOT EXISTS `dispatches_materials` (
  `dispatch_id` int(10) NOT NULL,
  `material_id` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

# ############################

#
# Table structure for table `drivers`
#

CREATE TABLE IF NOT EXISTS `drivers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `number` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `birthdate` datetime NOT NULL,
  `hiredate` datetime NOT NULL,
  `type` int(3) NOT NULL,
  `active` int(3) NOT NULL DEFAULT '1',
  `license_id` int(10) DEFAULT NULL,
  `login_id` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_id` (`login_id`),
  UNIQUE KEY `license_id` (`license_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=61 ;

# ############################

#
# Table structure for table `freight_bills`
#

CREATE TABLE IF NOT EXISTS `freight_bills` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `from_yard` int(10) NOT NULL,
  `to_yard` int(10) NOT NULL,
  `truck_id` varchar(17) COLLATE utf8_bin NOT NULL,
  `trailer_id` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `driver_id` int(10) DEFAULT NULL,
  `status` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

# ############################

#
# Table structure for table `freight_bills_materials`
#

CREATE TABLE IF NOT EXISTS `freight_bills_materials` (
  `freight_bill_id` int(10) NOT NULL,
  `material_id` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

# ############################

#
# Table structure for table `images`
#

CREATE TABLE IF NOT EXISTS `images` (
  `id` varchar(36) COLLATE utf8_bin NOT NULL,
  `path` varchar(256) COLLATE utf8_bin NOT NULL,
  `description` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# ############################

#
# Table structure for table `lading_bills`
#

CREATE TABLE IF NOT EXISTS `lading_bills` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `vessel_date_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `from_yard` int(10) NOT NULL,
  `to_yard` int(10) NOT NULL,
  `po_number` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `reference` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

# ############################

#
# Table structure for table `lading_bills_freight_bills`
#

CREATE TABLE IF NOT EXISTS `lading_bills_freight_bills` (
  `lading_bill_id` int(10) NOT NULL,
  `freight_bill_id` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

# ############################

#
# Table structure for table `lading_bills_materials`
#

CREATE TABLE IF NOT EXISTS `lading_bills_materials` (
  `lading_bill_id` int(10) NOT NULL,
  `material_id` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

# ############################

#
# Table structure for table `licenses`
#

CREATE TABLE IF NOT EXISTS `licenses` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `region` varchar(16) COLLATE utf8_bin NOT NULL,
  `issued` datetime NOT NULL,
  `expiry` datetime NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=61 ;

# ############################

#
# Table structure for table `materials`
#

CREATE TABLE IF NOT EXISTS `materials` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `quantity` int(10) NOT NULL DEFAULT '1',
  `units` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `weight` float(15,2) NOT NULL,
  `current_yard` int(10) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;


# ############################

#
# Table structure for table `trailers`
#

CREATE TABLE IF NOT EXISTS `trailers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` int(10) unsigned NOT NULL,
  `platenumber` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `plateregion` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `plateexpiry` datetime DEFAULT NULL,
  `weight` int(10) DEFAULT NULL,
  `dotexpiry` datetime DEFAULT NULL,
  `status` int(3) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=31 ;

# ############################

#
# Table structure for table `trucks`
#

CREATE TABLE IF NOT EXISTS `trucks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vin` varchar(17) COLLATE utf8_bin NOT NULL,
  `unitnumber` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `carrier_id` int(10) unsigned NOT NULL,
  `make` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `model` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `engine` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `platenumber` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `plateregion` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `plateexpiry` datetime DEFAULT NULL,
  `weight` int(10) DEFAULT NULL,
  `dotexpiry` datetime DEFAULT NULL,
  `status` int(3) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=31 ;

# ############################

#
# Table structure for table `trucks_images`
#

CREATE TABLE IF NOT EXISTS `trucks_images` (
  `truck_id` varchar(17) COLLATE utf8_bin NOT NULL,
  `image_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

# ############################

#
# Table structure for table `users`
#

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` char(40) NOT NULL,
  `role` varchar(20) NOT NULL,
  `customer_id` int(11) NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

# ############################

#
# Table structure for table `vessels`
#

CREATE TABLE IF NOT EXISTS `vessels` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=11 ;

# ############################

#
# Table structure for table `vessel_dates`
#

CREATE TABLE IF NOT EXISTS `vessel_dates` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `vessel_id` int(10) NOT NULL,
  `arrival_est` datetime NOT NULL,
  `arrival_act` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=41 ;

# ############################

#
# Table structure for table `workorders`
#

CREATE TABLE IF NOT EXISTS `workorders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` text,
  `price` float(15,2) DEFAULT NULL,
  `status` int(10) unsigned DEFAULT NULL,
  `type` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

# ############################

#
# Table structure for table `yards`
#

CREATE TABLE IF NOT EXISTS `yards` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8_bin NOT NULL,
  `phone` varchar(32) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `address_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=45 ;
