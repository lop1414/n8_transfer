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

 Date: 19/01/2021 16:34:52
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '名称',
  `cp_type` varchar(50) NOT NULL COMMENT '平台类型',
  `cp_secret` varchar(255) DEFAULT NULL COMMENT '平台产品密钥',
  `cp_product_alias` varchar(50) NOT NULL COMMENT '平台产品标识',
  `type` varchar(50) DEFAULT NULL COMMENT '产品类型',
  `status` varchar(50) NOT NULL COMMENT '状态',
  `secret` varchar(255) NOT NULL COMMENT '密钥',
  `account_id` int(11) DEFAULT NULL COMMENT 'cp账号id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `cp_product_alias` (`cp_type`,`cp_product_alias`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品';

SET FOREIGN_KEY_CHECKS = 1;
