-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost:8889
-- 生成日時: 2019 年 8 月 11 日 18:16
-- サーバのバージョン： 5.7.26
-- PHP のバージョン: 7.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `teacher_muchoco`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `board`
--

CREATE TABLE `board` (
  `no` int(11) NOT NULL,
  `poster` int(11) NOT NULL,
  `sentence` text CHARACTER SET utf8mb4,
  `pict` text CHARACTER SET utf8mb4,
  `comment` text CHARACTER SET utf8mb4,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `board`
--

INSERT INTO `board` (`no`, `poster`, `sentence`, `pict`, `comment`, `timestamp`) VALUES
(37, 6, 'すれっど！！！', NULL, NULL, '2019-08-11 15:18:20'),
(41, 7, 'コメントを寄せてみる', NULL, '37', '2019-08-11 15:21:43'),
(53, 7, NULL, './image/image20190811175603-7.jpg', NULL, '2019-08-11 15:56:03');

-- --------------------------------------------------------

--
-- テーブルの構造 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `mail` text CHARACTER SET utf8mb4 NOT NULL,
  `password` text CHARACTER SET utf8mb4 NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '名無し'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `user`
--

INSERT INTO `user` (`id`, `mail`, `password`, `name`) VALUES
(6, 'test@com', '$2y$10$H8l/trWNIP5GsESFmqc6zusD1q0XOfIdUqL4tFgwip2Q5BnPOgGna', '名無し'),
(7, 'tes@com', '$2y$10$M/tl9xFoPDZTLf5mBNtImOEnKo69cRK5XLfxj50B64L1DcXjGVPMe', '名無し'),
(9, 'daiki@com', '$2y$10$qyNr9t5svak2zjUbqEXFOOdQPm7nCcd0B.4vAseAHU0ou59oaSpJ2', '名無し');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `board`
--
ALTER TABLE `board`
  ADD PRIMARY KEY (`no`),
  ADD KEY `board_ibfk_1` (`poster`) USING BTREE;

--
-- テーブルのインデックス `user`
--
ALTER TABLE `user`
  ADD UNIQUE KEY `mail` (`id`) USING BTREE;

--
-- ダンプしたテーブルのAUTO_INCREMENT
--

--
-- テーブルのAUTO_INCREMENT `board`
--
ALTER TABLE `board`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- テーブルのAUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `board`
--
ALTER TABLE `board`
  ADD CONSTRAINT `board_ibfk_1` FOREIGN KEY (`poster`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
