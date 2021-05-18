/*
 Navicat Premium Data Transfer

 Source Server         : 虚拟机 192.168.10.10
 Source Server Type    : MySQL
 Source Server Version : 50731
 Source Host           : localhost:3306
 Source Schema         : n8_transfer

 Target Server Type    : MySQL
 Target Server Version : 50731
 File Encoding         : 65001

 Date: 18/05/2021 14:18:39
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `group` varchar(50) NOT NULL DEFAULT '',
  `k` varchar(50) NOT NULL,
  `v` mediumtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`group`,`k`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
