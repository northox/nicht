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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
