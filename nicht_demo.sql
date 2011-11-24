SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `Nicht_Acl` (
  `name` varchar(128) NOT NULL,
  `members` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Nicht_Acl` (`name`, `members`) VALUES
('administrator', '|3|'),
('authenticated', '-special group-');

CREATE TABLE IF NOT EXISTS `Nicht_AntiBruteForce` (
  `count` tinyint(2) NOT NULL COMMENT 'bruteforce attempt',
  `user` varchar(320) NOT NULL COMMENT 'username bruteforced. Can be a simple username to an email address: 64char (local part) + 1char (@) + 255char (domain)',
  `ip` varchar(15) NOT NULL COMMENT 'ip address of the bruteforce source: 192.168.222.222',
  `utime` int(10) NOT NULL COMMENT 'unix timestamp',
  PRIMARY KEY (`user`,`ip`),
  KEY `user` (`user`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COMMENT='Anti bruteforce';

CREATE TABLE IF NOT EXISTS `Nicht_Nav` (
  `name` varchar(255) NOT NULL COMMENT 'page name',
  `id` int(11) NOT NULL COMMENT 'page id',
  `members` text NOT NULL COMMENT 'who can access',
  `filename` varchar(255) NOT NULL COMMENT 'filesystem filename',
  PRIMARY KEY (`name`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Navigation system: list of webpage, access name/id and membe';

INSERT INTO `Nicht_Nav` (`name`, `id`, `members`, `filename`) VALUES
('admin', 4, '|administrator|', 'admin'),
('home', 3, '', 'home'),
('login', 2, '', 'login'),
('logout', 5, '', 'logout'),
('unauthorize', 403, '', 'unauthorize'),
('unavailable', 404, '', 'unavailable'),
('welcome', 1, '', 'welcome');

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(15) CHARACTER SET ucs2 NOT NULL,
  `password` varchar(50) CHARACTER SET utf8 NOT NULL,
  `salt` varchar(8) CHARACTER SET utf8 NOT NULL,
  `role` varchar(55) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

INSERT INTO `user` (`id`, `username`, `password`, `salt`, `role`) VALUES
(3, 'admin', 'l+IukedPZBClDedBWs3ezOoadurdrb7V57XeV9bTHbI=', 'e6205374', 'administrator'),
(4, 'jsmith', '6JvPF12hxGtlxkJDDorAQb3JAukMdruEipMryiS2j7c=', '00a5fe44', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
