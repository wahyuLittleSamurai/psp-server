/*
 Navicat Premium Data Transfer

 Source Server         : local
 Source Server Type    : MySQL
 Source Server Version : 100424
 Source Host           : localhost:3306
 Source Schema         : dbpsp

 Target Server Type    : MySQL
 Target Server Version : 100424
 File Encoding         : 65001

 Date: 05/10/2022 16:26:10
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for detailso
-- ----------------------------
DROP TABLE IF EXISTS `detailso`;
CREATE TABLE `detailso`  (
  `Id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `IdSo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `IdProduct` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Jml` int NULL DEFAULT NULL,
  `Harga` int NULL DEFAULT NULL,
  `Disc` int NULL DEFAULT NULL,
  `SubTotal` int NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `StatusBatal` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `BatalDate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of detailso
-- ----------------------------
INSERT INTO `detailso` VALUES ('DSO-1FACAF77DEC0FD34081E56FBBCDD7BDF', NULL, 'prod-0002', 1, 100000, 0, 100000, 'emp-0004', '2022-10-05 15:56:18', NULL, NULL);
INSERT INTO `detailso` VALUES ('DSO-891246D2EE3871EAAD40340A63C8C197', NULL, 'prod-0001', 2, 100000, 10000, 190000, 'emp-0004', '2022-10-05 16:20:00', NULL, NULL);

-- ----------------------------
-- Table structure for masteremploye
-- ----------------------------
DROP TABLE IF EXISTS `masteremploye`;
CREATE TABLE `masteremploye`  (
  `Id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Phone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `TokenFB` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Aktif` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `IdDevice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `Gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Jabatan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masteremploye
-- ----------------------------
INSERT INTO `masteremploye` VALUES ('emp-0002', 'asdf', 'asdf', '0858585858', 'jamilwahyu53@gmail.com', 'sokosari', NULL, '1', NULL, NULL, '2022-09-17 11:39:38', 'Laki-Laki', 'jbtn-0001');
INSERT INTO `masteremploye` VALUES ('emp-0003', 'dina', 'asdf', '909090', 'dina@gmail.com', 'soko', NULL, '1', NULL, NULL, '2022-09-18 08:43:24', 'Perempuan', 'jbtn-0001');
INSERT INTO `masteremploye` VALUES ('emp-0004', 'steve job', 'asdf', '123132', 'steve@apple.com', 'tuban', NULL, '1', NULL, NULL, '2022-09-27 17:02:17', 'Laki-Laki', 'jbtn-0003');

-- ----------------------------
-- Table structure for masterjabatan
-- ----------------------------
DROP TABLE IF EXISTS `masterjabatan`;
CREATE TABLE `masterjabatan`  (
  `Id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Nama` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterjabatan
-- ----------------------------
INSERT INTO `masterjabatan` VALUES ('jbtn-0001', 'spv', '2022-09-17 10:06:52');
INSERT INTO `masterjabatan` VALUES ('jbtn-0002', 'sales', '2022-09-17 10:07:18');
INSERT INTO `masterjabatan` VALUES ('jbtn-0003', 'kepala gudang', '2022-09-27 17:01:25');

-- ----------------------------
-- Table structure for masterpelanggan
-- ----------------------------
DROP TABLE IF EXISTS `masterpelanggan`;
CREATE TABLE `masterpelanggan`  (
  `Id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `NamaPelanggan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Phone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Aktif` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterpelanggan
-- ----------------------------
INSERT INTO `masterpelanggan` VALUES ('cust-0001', 'abp', 'sokosari', '0999', '1', 'abp@gmail.com', '2022-09-18 08:36:12');

-- ----------------------------
-- Table structure for masterproduct
-- ----------------------------
DROP TABLE IF EXISTS `masterproduct`;
CREATE TABLE `masterproduct`  (
  `Id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NameProduct` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Supplier` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Satuan` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Harga` decimal(65, 0) NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `Stok` int NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterproduct
-- ----------------------------
INSERT INTO `masterproduct` VALUES ('prod-0001', 'semen', 'sup-0001', 'sak', 100000, 'ADMIN', '2022-09-27 14:37:05', 5);
INSERT INTO `masterproduct` VALUES ('prod-0002', 'cat meraah', 'sup-0002', 'kg', 100000, 'ADMIN', '2022-09-27 17:04:20', 7);

-- ----------------------------
-- Table structure for mastersidebar
-- ----------------------------
DROP TABLE IF EXISTS `mastersidebar`;
CREATE TABLE `mastersidebar`  (
  `Id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Menu` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Access` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `Link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of mastersidebar
-- ----------------------------
INSERT INTO `mastersidebar` VALUES ('1', 'Master Akses', 'ALL', 'mdi-gauge', '2022-09-13 19:22:14', NULL);
INSERT INTO `mastersidebar` VALUES ('2', 'Karyawan', 'ALL', 'mdi-gauge', '2022-09-16 05:07:59', 'MasterKaryawan');
INSERT INTO `mastersidebar` VALUES ('3', 'Master Supplier & Product', 'ALL', 'mdi-gauge', '2022-09-27 13:04:42', 'MasterSupplier');
INSERT INTO `mastersidebar` VALUES ('4', 'Master Kendaraan', 'ALL', 'mdi-gauge', '2022-09-27 14:48:20', 'MasterVehicle');
INSERT INTO `mastersidebar` VALUES ('5', 'Stok', 'ALL', 'mdi-gauge', '2022-09-27 15:12:06', 'DataStok');
INSERT INTO `mastersidebar` VALUES ('6', 'Sales Order', 'ALL', 'mdi-gauge', '2022-09-30 20:04:06', 'SalesOrder');
INSERT INTO `mastersidebar` VALUES ('7', 'Status Order', 'ALL', 'mdi-gauge', '2022-10-05 15:24:30', 'StatusOrder');

-- ----------------------------
-- Table structure for masterso
-- ----------------------------
DROP TABLE IF EXISTS `masterso`;
CREATE TABLE `masterso`  (
  `Id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `IdPelanggan` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `IdStaff` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `StatusBatal` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `KeteranganBatal` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ApproveBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ApproveDate` datetime NULL DEFAULT NULL,
  `JanjiBayar` date NULL DEFAULT NULL,
  `Invoice` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterso
-- ----------------------------

-- ----------------------------
-- Table structure for mastersupplier
-- ----------------------------
DROP TABLE IF EXISTS `mastersupplier`;
CREATE TABLE `mastersupplier`  (
  `Id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NamaSupplier` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Phone` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Alamat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `Aktif` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of mastersupplier
-- ----------------------------
INSERT INTO `mastersupplier` VALUES ('sup-0001', '3 roda', '234', '3roda@roda.com', 'asdf', 'ADMIN', '2022-09-27 13:52:01', '1');
INSERT INTO `mastersupplier` VALUES ('sup-0002', 'dulux', '34234', 'dulux@dulux.com', 'surabaya', 'ADMIN', '2022-09-27 17:03:21', '1');

-- ----------------------------
-- Table structure for mastervehicle
-- ----------------------------
DROP TABLE IF EXISTS `mastervehicle`;
CREATE TABLE `mastervehicle`  (
  `Id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NoPol` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Jenis` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Aktif` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of mastervehicle
-- ----------------------------
INSERT INTO `mastervehicle` VALUES ('veh-0001', 'N123A', 'truck', '1', 'ADMIN', '2022-09-27 15:08:49');
INSERT INTO `mastervehicle` VALUES ('veh-0002', 'S123NN', 'Truck Engkel', '1', 'ADMIN', '2022-09-27 17:04:58');

-- ----------------------------
-- Table structure for reportproduct
-- ----------------------------
DROP TABLE IF EXISTS `reportproduct`;
CREATE TABLE `reportproduct`  (
  `Id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ProductId` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Jml` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Harga` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `TotalHarga` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `ApproveBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ApproveDate` timestamp NULL DEFAULT NULL,
  `Keterangan` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `SupplierId` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of reportproduct
-- ----------------------------
INSERT INTO `reportproduct` VALUES ('rep-0001', 'prod-0001', '2', '100000', '200000', 'ADMIN', '2022-09-27 16:38:52', NULL, NULL, 'PRODUCT IN', 'sup-0001');
INSERT INTO `reportproduct` VALUES ('rep-0002', 'prod-0002', '100', '100000', '10000000', 'ADMIN', '2022-09-27 17:07:02', NULL, NULL, 'PRODUCT IN', 'sup-0002');

SET FOREIGN_KEY_CHECKS = 1;
