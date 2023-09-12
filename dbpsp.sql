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

 Date: 05/11/2022 15:00:57
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for detailpengiriman
-- ----------------------------
DROP TABLE IF EXISTS `detailpengiriman`;
CREATE TABLE `detailpengiriman`  (
  `Id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `IdMso` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Sopir` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Kendaraan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `OngkosKuli` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `OngkosAkomodasi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of detailpengiriman
-- ----------------------------
INSERT INTO `detailpengiriman` VALUES ('KRR-C47E3E25EA6BF9F069DC13621E044AE6', 'MSO-EE40593C37AFC65C5FAAA7395E45DE56', 'emp-0002', 'veh-0002', '123', '23', '2022-10-26 19:35:06');

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
INSERT INTO `detailso` VALUES ('DSO-3236C2A0E242735DA2D85E81BF7CC9E1', 'MSO-EE40593C37AFC65C5FAAA7395E45DE56', 'prod-0002', 1, 100000, 0, 100000, 'emp-0002', '2022-10-26 19:16:24', '', NULL);
INSERT INTO `detailso` VALUES ('DSO-CEB748B067EBD0495D667BCDCE47B53E', 'MSO-4C7484509A76D257226AC30918CE058D', 'prod-0001', 1, 100000, 0, 100000, 'emp-0002', '2022-11-05 13:07:55', '', NULL);

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
INSERT INTO `masteremploye` VALUES ('emp-0003', 'dina', 'asdf', '909090', 'dina@gmail.com', 'soko', NULL, '1', NULL, NULL, '2022-09-18 08:43:24', 'Perempuan', 'jbtn-0002');
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
INSERT INTO `masterjabatan` VALUES ('jbtn-0002', 'Kolektor', '2022-09-17 10:07:18');
INSERT INTO `masterjabatan` VALUES ('jbtn-0003', 'kepala gudang', '2022-09-27 17:01:25');

-- ----------------------------
-- Table structure for masterjenisbayar
-- ----------------------------
DROP TABLE IF EXISTS `masterjenisbayar`;
CREATE TABLE `masterjenisbayar`  (
  `Id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `JenisBayar` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `JatuhTempo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Aktif` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of masterjenisbayar
-- ----------------------------
INSERT INTO `masterjenisbayar` VALUES ('MJB-43611DCF9FD7F86F49E311F7BCA05CB3', 'CBD', '0', '1', 'emp-0004', '2022-10-06 14:49:01');

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
  `Sales` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterpelanggan
-- ----------------------------
INSERT INTO `masterpelanggan` VALUES ('cust-0001', 'abp', 'sokosari', '0999', '1', 'abp@gmail.com', '2022-09-18 08:36:12', 'emp-0003');

-- ----------------------------
-- Table structure for masterpiutang
-- ----------------------------
DROP TABLE IF EXISTS `masterpiutang`;
CREATE TABLE `masterpiutang`  (
  `Id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `InvSo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Nominal` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `CreateBy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CheckBy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CheckDate` timestamp NULL DEFAULT NULL,
  `Image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `IdKolektor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `KolektorDate` timestamp NULL DEFAULT NULL,
  `Ttd` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `Photo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of masterpiutang
-- ----------------------------
INSERT INTO `masterpiutang` VALUES ('MPU-357264FAD0EAF0E83842B24E91EFA4A2', 'INV/20221026/0001', NULL, '2022-11-05 14:36:36', 'emp-0002', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `masterpiutang` VALUES ('MPU-6392F8E2BE713D93879196F2D700B009', 'INV/20221018/0001', '234', '2022-10-18 11:06:57', 'emp-0002', 'emp-0002', '2022-10-18 12:32:06', NULL, 'emp-0002', '2022-10-18 11:46:40', NULL, NULL);
INSERT INTO `masterpiutang` VALUES ('MPU-ED207A1D3D47BF03C66D48CD86AFA81B', 'INV/20221026/0001', '9000', '2022-10-26 20:32:39', 'emp-0002', 'emp-0002', '2022-10-26 20:43:56', NULL, 'emp-0002', '2022-10-26 20:38:48', NULL, NULL);

-- ----------------------------
-- Table structure for masterpph
-- ----------------------------
DROP TABLE IF EXISTS `masterpph`;
CREATE TABLE `masterpph`  (
  `Id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Pph` int NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterpph
-- ----------------------------
INSERT INTO `masterpph` VALUES ('PPH-001', 11, 'admin', '2022-10-24 20:01:58');
INSERT INTO `masterpph` VALUES ('PPH-2BAEA841C0ED469D68F83641F1669F68', 12, 'emp-0002', '2022-10-24 20:19:50');
INSERT INTO `masterpph` VALUES ('PPH-E1CDA7F5B332C0A0AF8D2D8087C88430', 11, 'emp-0002', '2022-10-24 20:20:03');

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
INSERT INTO `masterproduct` VALUES ('prod-0001', 'semen', 'sup-0001', 'sak', 100000, 'ADMIN', '2022-09-27 14:37:05', 4);
INSERT INTO `masterproduct` VALUES ('prod-0002', 'cat meraah', 'sup-0002', 'kg', 100000, 'ADMIN', '2022-09-27 17:04:20', 4);
INSERT INTO `masterproduct` VALUES ('PROD-6F763F4EACBF0CAC925F94C12E70BE38', 'pertalite', 'SUP-894D1E0CD838D9106E9EB7DA36112B51', 'liter', 10000, 'ADMIN', '2022-10-24 21:20:57', NULL);

-- ----------------------------
-- Table structure for masterreturnbarang
-- ----------------------------
DROP TABLE IF EXISTS `masterreturnbarang`;
CREATE TABLE `masterreturnbarang`  (
  `Invoice` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `IdProduct` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Jml` int NULL DEFAULT NULL,
  `Keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Nominal` int NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  `Jenis` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`Id`, `Invoice`, `IdProduct`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterreturnbarang
-- ----------------------------
INSERT INTO `masterreturnbarang` VALUES ('MSO-EE40593C37AFC65C5FAAA7395E45DE56', 'prod-0002', 1, '33', 2, 'emp-0002', '2022-10-28 21:53:35', NULL, 4);

-- ----------------------------
-- Table structure for mastersidebar
-- ----------------------------
DROP TABLE IF EXISTS `mastersidebar`;
CREATE TABLE `mastersidebar`  (
  `Id` int NOT NULL,
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
INSERT INTO `mastersidebar` VALUES (1, 'Master Akses', 'emp-0002,emp-0003', 'mdi-gauge', '2022-09-13 19:22:14', 'MasterAkses');
INSERT INTO `mastersidebar` VALUES (2, 'Karyawan', 'ALL', 'mdi-gauge', '2022-09-16 05:07:59', 'MasterKaryawan');
INSERT INTO `mastersidebar` VALUES (3, 'Master Supplier & Product', 'ALL', 'mdi-gauge', '2022-09-27 13:04:42', 'MasterSupplier');
INSERT INTO `mastersidebar` VALUES (4, 'Master Kendaraan', 'ALL', 'mdi-gauge', '2022-09-27 14:48:20', 'MasterVehicle');
INSERT INTO `mastersidebar` VALUES (5, 'Stok', 'ALL', 'mdi-gauge', '2022-09-27 15:12:06', 'DataStok');
INSERT INTO `mastersidebar` VALUES (6, 'Sales Order', 'ALL', 'mdi-gauge', '2022-09-30 20:04:06', 'SalesOrder');
INSERT INTO `mastersidebar` VALUES (7, 'Status Order', 'ALL', 'mdi-gauge', '2022-10-05 15:24:30', 'StatusOrder');
INSERT INTO `mastersidebar` VALUES (8, 'Order Masuk', 'ALL', 'mdi-gauge', '2022-10-07 15:17:12', 'OrderMasuk');
INSERT INTO `mastersidebar` VALUES (9, 'Create SJ Kolektor', 'ALL', 'mdi-gauge', '2022-10-10 14:47:03', 'TagihanKolektor');
INSERT INTO `mastersidebar` VALUES (10, 'Pembayaran', 'ALL', 'mdi-gauge', '2022-10-10 15:12:16', 'Pembayaran');
INSERT INTO `mastersidebar` VALUES (11, 'Approve Pembayaran', 'ALL', 'mdi-gauge', '2022-10-18 11:51:16', 'ApprovePembayaran');
INSERT INTO `mastersidebar` VALUES (12, 'Master Invoice', 'ALL', 'mdi-gauge', '2022-10-18 12:38:11', 'ReturnBarang');

-- ----------------------------
-- Table structure for mastersjkolektor
-- ----------------------------
DROP TABLE IF EXISTS `mastersjkolektor`;
CREATE TABLE `mastersjkolektor`  (
  `Id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `InvSo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `IdStaff` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `SisaBayar` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateBy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `CreateDate` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mastersjkolektor
-- ----------------------------
INSERT INTO `mastersjkolektor` VALUES ('MPU-357264FAD0EAF0E83842B24E91EFA4A2', 'INV/20221026/0001', 'emp-0002', '102000', 'emp-0002', '2022-11-05 14:36:36');
INSERT INTO `mastersjkolektor` VALUES ('MPU-6392F8E2BE713D93879196F2D700B009', 'INV/20221018/0001', 'emp-0002', '100000', 'emp-0002', '2022-10-18 11:06:57');
INSERT INTO `mastersjkolektor` VALUES ('MPU-ED207A1D3D47BF03C66D48CD86AFA81B', 'INV/20221026/0001', 'emp-0002', '100000', 'emp-0002', '2022-10-26 20:32:39');

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
  `MetodeBayar` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `JatuhTempo` int NULL DEFAULT NULL,
  `IsCetak` int NULL DEFAULT NULL,
  `TglCetak` timestamp NULL DEFAULT NULL,
  `CetakBy` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Pph` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Invoice` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `TglInvoice` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of masterso
-- ----------------------------
INSERT INTO `masterso` VALUES ('MSO-4C7484509A76D257226AC30918CE058D', 'cust-0001', 'emp-0002', '2022-11-05 13:08:05', NULL, NULL, 'emp-0002', '2022-10-05 13:08:22', 'MJB-43611DCF9FD7F86F49E311F7BCA05CB3', 0, NULL, NULL, NULL, '11', NULL, NULL);
INSERT INTO `masterso` VALUES ('MSO-EE40593C37AFC65C5FAAA7395E45DE56', 'cust-0001', 'emp-0002', '2022-10-26 19:25:20', NULL, NULL, 'emp-0002', '2022-09-26 19:25:48', 'MJB-43611DCF9FD7F86F49E311F7BCA05CB3', 0, 1, '2022-09-26 19:35:07', 'emp-0002', '11', 'INV/20221026/0001', '2022-10-26 20:05:19');

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
INSERT INTO `mastersupplier` VALUES ('SUP-894D1E0CD838D9106E9EB7DA36112B51', 'pertamina', '098098098', 'pertamina@pertamina,com', 'tuban', 'ADMIN', '2022-10-24 21:19:24', '1');

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
