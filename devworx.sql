SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `devworx`
--

CREATE TABLE `user` (
  `uid` bigint(20) NOT NULL,
  `login` varchar(128) NOT NULL,
  `name` varchar(32) NOT NULL,
  `salutation` varchar(10) NOT NULL,
  `firstName` varchar(32) NOT NULL,
  `lastName` varchar(32) NOT NULL,
  `address` varchar(64) NOT NULL,
  `address2` varchar(64) NOT NULL,
  `zip` varchar(6) NOT NULL,
  `city` varchar(64) NOT NULL,
  `country` varchar(2) NOT NULL,
  `email` varchar(64) NOT NULL,
  `tel` varchar(64) NOT NULL,
  `lastLogin` timestamp NULL DEFAULT NULL,
  `cruser` int(11) NOT NULL DEFAULT 0,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hidden` tinyint(1) NOT NULL DEFAULT 0,
  `deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- loginname: admin
-- loginpw: devworx
-- hash: md5(admin|devworx)
INSERT INTO `user` (`uid`, `login`, `name`, `salutation`, `firstName`, `lastName`, `address`, `address2`, `zip`, `city`, `country`, `email`, `tel`) VALUES
(1, '579f92e55fedaa462bc45b91bde26a91', 'Developer', 'Mr.', 'John', 'Doe', 'Test Avenue', '2c', '00000', 'Somecity', 'XX', 'me@myorganisation.de', '+01');

ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `login` (`login`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
