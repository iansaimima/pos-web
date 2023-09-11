-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 05 Jan 2022 pada 22.52
-- Versi server: 10.4.10-MariaDB
-- Versi PHP: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `app_pos`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cabang`
--

DROP TABLE IF EXISTS `cabang`;
CREATE TABLE IF NOT EXISTS `cabang` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) NOT NULL DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `kode` varchar(2) DEFAULT '',
  `nama` varchar(200) DEFAULT '',
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(100) DEFAULT '',
  `keterangan` text DEFAULT NULL,
  `bulan_mulai_penggunaan_aplikasi` varchar(2) NOT NULL DEFAULT '',
  `tahun_mulai_penggunaan_aplikasi` varchar(4) NOT NULL,
  `persediaan_stock_awal_default_gudang_uuid` varchar(40) NOT NULL,
  `persediaan_stock_opname_default_gudang_uuid` varchar(40) NOT NULL,
  `transaksi_pembelian_default_gudang_uuid` varchar(40) NOT NULL,
  `transaksi_pembelian_default_kas_akun_uuid` varchar(40) NOT NULL,
  `transaksi_penjualan_default_gudang_uuid` varchar(40) NOT NULL,
  `transaksi_penjualan_default_kas_akun_uuid` varchar(40) NOT NULL,
  `transaksi_penjualan_default_pelanggan_uuid` varchar(40) DEFAULT '',
  `transaksi_pembelian_default_pemasok_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `cabang`
--

INSERT INTO `cabang` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `last_updated`, `last_updated_user_uuid`, `last_updated_user_name`, `kode`, `nama`, `alamat`, `no_telepon`, `keterangan`, `bulan_mulai_penggunaan_aplikasi`, `tahun_mulai_penggunaan_aplikasi`, `persediaan_stock_awal_default_gudang_uuid`, `persediaan_stock_opname_default_gudang_uuid`, `transaksi_pembelian_default_gudang_uuid`, `transaksi_pembelian_default_kas_akun_uuid`, `transaksi_penjualan_default_gudang_uuid`, `transaksi_penjualan_default_kas_akun_uuid`, `transaksi_penjualan_default_pelanggan_uuid`, `transaksi_pembelian_default_pemasok_uuid`) VALUES
('84ef8a73-d5b1-11eb-ae0c-7cb27de7c399', '2021-06-25 12:32:56', '', '', '2022-01-05 22:52:19', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', '11', 'Kantor Pusat', '-', '-', '-', '01', '2022', '25c951a8-536c-4b58-8287-16cbc892af07', '25c951a8-536c-4b58-8287-16cbc892af07', '25c951a8-536c-4b58-8287-16cbc892af07', 'dc6126c3-65b7-420e-82c2-a4858da64baa', '25c951a8-536c-4b58-8287-16cbc892af07', 'dc6126c3-65b7-420e-82c2-a4858da64baa', 'fe42010b-ecd8-4577-ba9b-6dd3ac3ff78a', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `gudang`
--

DROP TABLE IF EXISTS `gudang`;
CREATE TABLE IF NOT EXISTS `gudang` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) NOT NULL DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `kode` varchar(50) DEFAULT '',
  `nama` varchar(200) DEFAULT '',
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(100) DEFAULT '',
  `keterangan` text DEFAULT NULL,
  `fungsi` varchar(100) DEFAULT '',
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `gudang`
--

INSERT INTO `gudang` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `last_updated`, `last_updated_user_uuid`, `last_updated_user_name`, `kode`, `nama`, `alamat`, `no_telepon`, `keterangan`, `fungsi`, `cabang_uuid`) VALUES
('25c951a8-536c-4b58-8287-16cbc892af07', '2022-01-05 22:51:56', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', '2022-01-05 22:51:56', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'UTM', '-', '-', '-', '-', '-', '84ef8a73-d5b1-11eb-ae0c-7cb27de7c399');

-- --------------------------------------------------------

--
-- Struktur dari tabel `item`
--

DROP TABLE IF EXISTS `item`;
CREATE TABLE IF NOT EXISTS `item` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `kode` varchar(50) DEFAULT '',
  `barcode` varchar(250) DEFAULT '',
  `nama` varchar(200) DEFAULT '',
  `keterangan` text DEFAULT NULL,
  `struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `tipe` varchar(50) DEFAULT '',
  `cek_stock_saat_penjualan` tinyint(1) DEFAULT 1,
  `minimum_stock` int(11) DEFAULT 0,
  `margin_persen` double DEFAULT 0,
  `margin_nilai` double DEFAULT 0,
  `jenis_perhitungan_harga_jual` varchar(50) DEFAULT '',
  `cache_harga_pokok` double DEFAULT 0,
  `harga_jual_tipe_jasa` double DEFAULT 0,
  `satuan_tipe_jasa` varchar(50) DEFAULT '',
  `cache_stock` int(11) DEFAULT 0,
  `arsip` tinyint(1) DEFAULT 0,
  `arsip_date` datetime DEFAULT NULL,
  `arsip_user_uuid` varchar(40) DEFAULT '',
  `arsip_user_name` varchar(100) DEFAULT '',
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_kategori`
--

DROP TABLE IF EXISTS `item_kategori`;
CREATE TABLE IF NOT EXISTS `item_kategori` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `nama` varchar(200) DEFAULT '',
  `keterangan` text DEFAULT NULL,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_riwayat_stuktur_harga`
--

DROP TABLE IF EXISTS `item_riwayat_stuktur_harga`;
CREATE TABLE IF NOT EXISTS `item_riwayat_stuktur_harga` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `item_uuid` varchar(40) DEFAULT '',
  `struktur_harga_json` longtext DEFAULT NULL,
  PRIMARY KEY (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_transfer`
--

DROP TABLE IF EXISTS `item_transfer`;
CREATE TABLE IF NOT EXISTS `item_transfer` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(11) DEFAULT 0,
  `dari_gudang_uuid` varchar(40) DEFAULT '',
  `dari_gudang_kode` varchar(50) DEFAULT '',
  `dari_gudang_nama` varchar(100) DEFAULT '',
  `ke_gudang_uuid` varchar(40) DEFAULT '',
  `ke_gudang_kode` varchar(50) DEFAULT '',
  `ke_gudang_nama` varchar(100) DEFAULT '',
  `keterangan` text DEFAULT NULL,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_transfer_detail`
--

DROP TABLE IF EXISTS `item_transfer_detail`;
CREATE TABLE IF NOT EXISTS `item_transfer_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `item_transfer_uuid` varchar(40) DEFAULT '',
  `item_uuid` varchar(40) DEFAULT '',
  `item_kode` varchar(50) DEFAULT '',
  `item_barcode` varchar(100) DEFAULT '',
  `item_nama` varchar(100) DEFAULT '',
  `item_struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_tipe` varchar(50) DEFAULT '',
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `item_kategori_nama` varchar(100) DEFAULT '',
  `struktur_satuan_harga_quantity_total_json` longtext DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `satuan` varchar(100) DEFAULT '',
  `harga_beli_satuan` double DEFAULT 0,
  `harga_beli_satuan_terkecil` double DEFAULT 0,
  `jumlah_satuan_terkecil` int(11) DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kas_akun`
--

DROP TABLE IF EXISTS `kas_akun`;
CREATE TABLE IF NOT EXISTS `kas_akun` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(50) DEFAULT '',
  `last_updated` datetime DEFAULT NULL,
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(50) DEFAULT '',
  `nama` varchar(100) DEFAULT '',
  `keterangan` varchar(250) DEFAULT '',
  `arsip` tinyint(1) DEFAULT 0,
  `arsip_date` datetime DEFAULT NULL,
  `arsip_user_uuid` varchar(40) DEFAULT '',
  `arsip_user_name` varchar(50) DEFAULT '',
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `kas_akun`
--

INSERT INTO `kas_akun` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `last_updated`, `last_updated_user_uuid`, `last_updated_user_name`, `nama`, `keterangan`, `arsip`, `arsip_date`, `arsip_user_uuid`, `arsip_user_name`, `cabang_uuid`) VALUES
('dc6126c3-65b7-420e-82c2-a4858da64baa', '2022-01-05 22:50:49', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', '2022-01-06 05:50:49', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Kas Tunai', '-', 0, NULL, '', '', '84ef8a73-d5b1-11eb-ae0c-7cb27de7c399');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kas_alur`
--

DROP TABLE IF EXISTS `kas_alur`;
CREATE TABLE IF NOT EXISTS `kas_alur` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(50) DEFAULT '',
  `last_updated` datetime DEFAULT NULL,
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(50) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` date DEFAULT NULL,
  `kas_akun_uuid` varchar(40) DEFAULT '',
  `kas_akun_nama` varchar(100) DEFAULT '',
  `kas_kategori_uuid` varchar(40) DEFAULT '',
  `kas_kategori_nama` varchar(100) DEFAULT '',
  `alur_kas` varchar(10) DEFAULT '',
  `jumlah_masuk` double DEFAULT 0,
  `jumlah_keluar` double DEFAULT 0,
  `kas_transfer_uuid` varchar(40) DEFAULT '',
  `kas_transfer_number_formatted` varchar(50) DEFAULT '',
  `transaksi_pembelian_uuid` varchar(40) DEFAULT '',
  `transaksi_pembelian_number_formatted` varchar(50) DEFAULT '',
  `transaksi_pembelian_retur_uuid` varchar(40) DEFAULT '',
  `transaksi_pembelian_retur_number_formatted` varchar(50) DEFAULT '',
  `transaksi_penjualan_uuid` varchar(40) DEFAULT '',
  `transaksi_penjualan_number_formatted` varchar(50) DEFAULT '',
  `transaksi_penjualan_retur_uuid` varchar(40) DEFAULT '',
  `transaksi_penjualan_retur_number_formatted` varchar(50) DEFAULT '',
  `transaksi_pembayaran_piutang_uuid` varchar(40) DEFAULT '',
  `transaksi_pembayaran_piutang_number_formatted` varchar(50) DEFAULT '',
  `keterangan` varchar(250) DEFAULT '',
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kas_kategori`
--

DROP TABLE IF EXISTS `kas_kategori`;
CREATE TABLE IF NOT EXISTS `kas_kategori` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(50) DEFAULT '',
  `last_updated` datetime DEFAULT NULL,
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(50) DEFAULT '',
  `nama` varchar(100) DEFAULT '',
  `keterangan` varchar(250) DEFAULT '',
  `alur_kas` varchar(10) DEFAULT '',
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `kas_kategori`
--

INSERT INTO `kas_kategori` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `last_updated`, `last_updated_user_uuid`, `last_updated_user_name`, `nama`, `keterangan`, `alur_kas`, `cabang_uuid`) VALUES
('9d52b7bb-5d80-470a-86b7-465837a4343a', '2022-01-05 22:51:05', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', '2022-01-06 05:51:05', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Pemasukkan Lain-lain', '-', 'masuk', '84ef8a73-d5b1-11eb-ae0c-7cb27de7c399'),
('f37f1169-9c78-41b4-968b-93b4ed91dbfc', '2022-01-05 22:51:14', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', '2022-01-06 05:51:14', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Pengeluaran Lain-lain', '-', 'keluar', '84ef8a73-d5b1-11eb-ae0c-7cb27de7c399');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

DROP TABLE IF EXISTS `pelanggan`;
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `nama` varchar(200) DEFAULT '',
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(100) DEFAULT '',
  `potongan_persen` double DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `last_updated`, `last_updated_user_uuid`, `last_updated_user_name`, `number`, `number_formatted`, `nama`, `alamat`, `no_telepon`, `potongan_persen`, `keterangan`, `cabang_uuid`) VALUES
('fe42010b-ecd8-4577-ba9b-6dd3ac3ff78a', '2022-01-05 22:51:38', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', '2022-01-05 22:51:38', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 1, 'CUS/11/00001', 'UMUM', '-', '-', 0, '-', '84ef8a73-d5b1-11eb-ae0c-7cb27de7c399');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemasok`
--

DROP TABLE IF EXISTS `pemasok`;
CREATE TABLE IF NOT EXISTS `pemasok` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `nama` varchar(200) DEFAULT '',
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(100) DEFAULT '',
  `keterangan` text DEFAULT NULL,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pemasok`
--

INSERT INTO `pemasok` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `last_updated`, `last_updated_user_uuid`, `last_updated_user_name`, `number`, `number_formatted`, `nama`, `alamat`, `no_telepon`, `keterangan`, `cabang_uuid`) VALUES
('cccb409f-2508-4129-8af7-77de41dcfabb', '2022-01-05 22:51:26', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', '2022-01-05 22:51:26', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 1, 'SUP/11/00001', 'Umum', '-', '-', '-', '84ef8a73-d5b1-11eb-ae0c-7cb27de7c399');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_hutang`
--

DROP TABLE IF EXISTS `pembayaran_hutang`;
CREATE TABLE IF NOT EXISTS `pembayaran_hutang` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `pemasok_uuid` varchar(40) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(4) DEFAULT 0,
  `jumlah` double DEFAULT 0,
  `sisa_jumlah_bayar` double DEFAULT 0,
  `cara_bayar` varchar(50) DEFAULT '',
  `kas_akun_uuid` varchar(40) DEFAULT '',
  `kas_alur_uuid` varchar(40) DEFAULT '',
  `keterangan` text DEFAULT '',
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_hutang_detail`
--

DROP TABLE IF EXISTS `pembayaran_hutang_detail`;
CREATE TABLE IF NOT EXISTS `pembayaran_hutang_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `pembayaran_hutang_uuid` varchar(40) DEFAULT '',
  `pembelian_uuid` varchar(40) DEFAULT '',
  `sisa_piutang` double DEFAULT 0,
  `potongan` double DEFAULT 0,
  `jumlah` double DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_piutang`
--

DROP TABLE IF EXISTS `pembayaran_piutang`;
CREATE TABLE IF NOT EXISTS `pembayaran_piutang` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `pelanggan_uuid` varchar(40) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(4) DEFAULT 0,
  `jumlah` double DEFAULT 0,
  `sisa_jumlah_bayar` double DEFAULT 0,
  `cara_bayar` varchar(50) DEFAULT '',
  `kas_akun_uuid` varchar(40) DEFAULT '',
  `kas_alur_uuid` varchar(40) DEFAULT '',
  `keterangan` text DEFAULT '',
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_piutang_detail`
--

DROP TABLE IF EXISTS `pembayaran_piutang_detail`;
CREATE TABLE IF NOT EXISTS `pembayaran_piutang_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `pembayaran_piutang_uuid` varchar(40) DEFAULT '',
  `penjualan_uuid` varchar(40) DEFAULT '',
  `sisa_piutang` double DEFAULT 0,
  `potongan` double DEFAULT 0,
  `jumlah` double DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

DROP TABLE IF EXISTS `pembelian`;
CREATE TABLE IF NOT EXISTS `pembelian` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `no_nota_vendor` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(11) DEFAULT 0,
  `pemasok_uuid` varchar(40) DEFAULT '',
  `pemasok_number_formatted` varchar(50) DEFAULT '',
  `pemasok_nama` varchar(100) DEFAULT '',
  `pemasok_alamat` text DEFAULT NULL,
  `pemasok_no_telepon` varchar(100) DEFAULT '',
  `gudang_uuid` varchar(40) DEFAULT '',
  `gudang_kode` varchar(50) DEFAULT '',
  `gudang_nama` varchar(100) DEFAULT '',
  `kas_akun_uuid` varchar(40) DEFAULT '',
  `kas_alur_uuid` varchar(40) DEFAULT '',
  `sub_total` double DEFAULT 0,
  `potongan` double DEFAULT 0,
  `total_akhir` double DEFAULT 0,
  `bayar` double DEFAULT 0,
  `sisa` double DEFAULT 0,
  `lunas` tinyint(1) DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian_detail`
--

DROP TABLE IF EXISTS `pembelian_detail`;
CREATE TABLE IF NOT EXISTS `pembelian_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `pembelian_uuid` varchar(40) DEFAULT '',
  `item_uuid` varchar(40) DEFAULT '',
  `item_kode` varchar(50) DEFAULT '',
  `item_barcode` varchar(100) DEFAULT '',
  `item_nama` varchar(100) DEFAULT '',
  `item_struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_tipe` varchar(50) DEFAULT '',
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `item_kategori_nama` varchar(100) DEFAULT '',
  `struktur_satuan_harga_quantity_total_json` longtext DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `satuan` varchar(100) DEFAULT '',
  `harga_beli_satuan` double DEFAULT 0,
  `harga_beli_satuan_terkecil` double DEFAULT 0,
  `jumlah_satuan_terkecil` int(11) DEFAULT 0,
  `potongan_persen` float DEFAULT 0,
  `potongan_harga` double DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian_retur`
--

DROP TABLE IF EXISTS `pembelian_retur`;
CREATE TABLE IF NOT EXISTS `pembelian_retur` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `pembelian_uuid` varchar(40) DEFAULT '',
  `pembelian_number_formatted` varchar(50) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(11) DEFAULT 0,
  `pemasok_uuid` varchar(40) DEFAULT '',
  `gudang_uuid` varchar(40) DEFAULT '',
  `gudang_kode` varchar(50) DEFAULT '',
  `gudang_nama` varchar(100) DEFAULT '',
  `kas_akun_uuid` varchar(40) DEFAULT '',
  `kas_alur_uuid` varchar(40) DEFAULT '',
  `sub_total` double DEFAULT 0,
  `potongan` double DEFAULT 0,
  `total_akhir` double DEFAULT 0,
  `bayar` double DEFAULT 0,
  `sisa` double DEFAULT 0,
  `lunas` tinyint(1) DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian_retur_detail`
--

DROP TABLE IF EXISTS `pembelian_retur_detail`;
CREATE TABLE IF NOT EXISTS `pembelian_retur_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `pembelian_retur_uuid` varchar(40) DEFAULT '',
  `item_uuid` varchar(40) DEFAULT '',
  `item_kode` varchar(50) DEFAULT '',
  `item_barcode` varchar(100) DEFAULT '',
  `item_nama` varchar(100) DEFAULT '',
  `item_struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_tipe` varchar(50) DEFAULT '',
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `item_kategori_nama` varchar(100) DEFAULT '',
  `struktur_satuan_harga_quantity_total_json` longtext DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `satuan` varchar(100) DEFAULT '',
  `harga_beli_satuan` double DEFAULT 0,
  `harga_beli_satuan_terkecil` double DEFAULT 0,
  `jumlah_satuan_terkecil` int(11) DEFAULT 0,
  `potongan_persen` float DEFAULT 0,
  `potongan_harga` double DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
CREATE TABLE IF NOT EXISTS `penjualan` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(11) DEFAULT 0,
  `pelanggan_uuid` varchar(40) DEFAULT '',
  `pelanggan_number_formatted` varchar(50) DEFAULT '',
  `pelanggan_nama` varchar(100) DEFAULT '',
  `pelanggan_alamat` text DEFAULT NULL,
  `pelanggan_no_telepon` varchar(100) DEFAULT '',
  `pelanggan_potongan_persen` float DEFAULT 0,
  `gudang_uuid` varchar(40) DEFAULT '',
  `gudang_kode` varchar(50) DEFAULT '',
  `gudang_nama` varchar(100) DEFAULT '',
  `kas_akun_uuid` varchar(40) DEFAULT '',
  `kas_alur_uuid` varchar(40) DEFAULT '',
  `sub_total` double DEFAULT 0,
  `potongan` double DEFAULT 0,
  `total_akhir` double DEFAULT 0,
  `metode_pembayaran` varchar(50) DEFAULT '',
  `jatuh_tempo` datetime DEFAULT NULL,
  `bayar` double DEFAULT 0,
  `kembali` double DEFAULT 0,
  `sisa` double DEFAULT 0,
  `lunas` tinyint(1) DEFAULT 0,
  `cache_status` varchar(50) DEFAULT '',
  `cache_sisa_piutang` double DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan_detail`
--

DROP TABLE IF EXISTS `penjualan_detail`;
CREATE TABLE IF NOT EXISTS `penjualan_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `penjualan_uuid` varchar(40) DEFAULT '',
  `item_uuid` varchar(40) DEFAULT '',
  `item_kode` varchar(50) DEFAULT '',
  `item_barcode` varchar(100) DEFAULT '',
  `item_nama` varchar(100) DEFAULT '',
  `item_struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_tipe` varchar(50) DEFAULT '',
  `item_cek_stock_saat_penjualan` tinyint(1) DEFAULT 0,
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `item_kategori_nama` varchar(100) DEFAULT '',
  `struktur_satuan_harga_quantity_total_json` longtext DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `satuan` varchar(100) DEFAULT '',
  `harga_jual_satuan` double DEFAULT 0,
  `margin_jual_satuan` double DEFAULT 0,
  `harga_jual_satuan_terkecil` double DEFAULT 0,
  `margin_jual_satuan_terkecil` double DEFAULT 0,
  `jumlah_satuan_terkecil` int(11) DEFAULT 0,
  `potongan_persen` float DEFAULT 0,
  `potongan_harga` double DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan_retur`
--

DROP TABLE IF EXISTS `penjualan_retur`;
CREATE TABLE IF NOT EXISTS `penjualan_retur` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `penjualan_uuid` varchar(40) DEFAULT '',
  `penjualan_number_formatted` varchar(50) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(11) DEFAULT 0,
  `pelanggan_uuid` varchar(40) DEFAULT '',
  `gudang_uuid` varchar(40) DEFAULT '',
  `gudang_kode` varchar(50) DEFAULT '',
  `gudang_nama` varchar(100) DEFAULT '',
  `kas_akun_uuid` varchar(40) DEFAULT '',
  `kas_alur_uuid` varchar(40) DEFAULT '',
  `sub_total` double DEFAULT 0,
  `potongan` double DEFAULT 0,
  `total_akhir` double DEFAULT 0,
  `bayar` double DEFAULT 0,
  `sisa` double DEFAULT 0,
  `lunas` tinyint(1) DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan_retur_detail`
--

DROP TABLE IF EXISTS `penjualan_retur_detail`;
CREATE TABLE IF NOT EXISTS `penjualan_retur_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `penjualan_retur_uuid` varchar(40) DEFAULT '',
  `item_uuid` varchar(40) DEFAULT '',
  `item_kode` varchar(50) DEFAULT '',
  `item_barcode` varchar(100) DEFAULT '',
  `item_nama` varchar(100) DEFAULT '',
  `item_struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_tipe` varchar(50) DEFAULT '',
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `item_kategori_nama` varchar(100) DEFAULT '',
  `struktur_satuan_harga_quantity_total_json` longtext DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `satuan` varchar(100) DEFAULT '',
  `harga_jual_satuan` double DEFAULT 0,
  `margin_jual_satuan` double DEFAULT 0,
  `harga_jual_satuan_terkecil` double DEFAULT 0,
  `margin_jual_satuan_terkecil` double DEFAULT 0,
  `jumlah_satuan_terkecil` int(11) DEFAULT 0,
  `potongan_persen` float DEFAULT 0,
  `potongan_harga` double DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `_key` varchar(250) NOT NULL DEFAULT '',
  `_label` varchar(250) DEFAULT '',
  `_value` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`_key`, `_label`, `_value`) VALUES
('BULAN_MULAI_PENGGUNAAN_APLIKASI', 'Bulan Mulai Penggunaan Aplikasi', '06'),
('PERSEDIAAN_STOCK_AWAL_DEFAULT_GUDANG_ID', 'Default Gudang Persediaan Stock Awal', '56bd8bfe-d408-11eb-ae0c-7cb27de7c399'),
('PERSEDIAAN_STOCK_OPNAME_DEFAULT_GUDANG_ID', 'Default Gudang Persediaan Stock Opname', '56bd8bfe-d408-11eb-ae0c-7cb27de7c399'),
('TAHUN_MULAI_PENGGUNAAN_APLIKASI', 'Tahun Mulai Penggunaan Aplikasi', '2021'),
('TOKO_ALAMAT', 'Alamat', 'jl. asdasd sd'),
('TOKO_NAMA', 'Nama', 'Toko Sumber Jaya'),
('TOKO_NO_TELEPON', 'No. Telp', '123123213123'),
('TRANSAKSI_PEMBELIAN_DEFAULT_GUDANG_ID', 'Default Gudang Transaksi Pembelian', '56bd8bfe-d408-11eb-ae0c-7cb27de7c399'),
('TRANSAKSI_PEMBELIAN_DEFAULT_KAS_AKUN_ID', 'Default Akun Kas Transaksi Pembelian', 'f14ca498-8dc0-435c-86c5-3ebbbcb96e18'),
('TRANSAKSI_PENJUALAN_DEFAULT_GUDANG_ID', 'Default Gudang Transaksi Penjualan', '56bd8bfe-d408-11eb-ae0c-7cb27de7c399'),
('TRANSAKSI_PENJUALAN_DEFAULT_KAS_AKUN_ID', 'Default Akun Kas Transaksi Penjualan', 'f14ca498-8dc0-435c-86c5-3ebbbcb96e18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_awal`
--

DROP TABLE IF EXISTS `stock_awal`;
CREATE TABLE IF NOT EXISTS `stock_awal` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `item_uuid` varchar(40) DEFAULT '',
  `item_kode` varchar(50) DEFAULT '',
  `item_barcode` varchar(100) DEFAULT '',
  `item_nama` varchar(100) DEFAULT '',
  `item_struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_tipe` varchar(50) DEFAULT '',
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `item_kategori_nama` varchar(100) DEFAULT '',
  `jumlah` int(11) DEFAULT 0,
  `satuan` varchar(100) DEFAULT '',
  `harga_beli_satuan` double DEFAULT 0,
  `harga_beli_satuan_terkecil` double DEFAULT 0,
  `jumlah_satuan_terkecil` int(11) DEFAULT 0,
  `gudang_uuid` varchar(40) DEFAULT '',
  `gudang_kode` varchar(50) DEFAULT '',
  `gudang_nama` varchar(100) DEFAULT '',
  `total` double DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_opname`
--

DROP TABLE IF EXISTS `stock_opname`;
CREATE TABLE IF NOT EXISTS `stock_opname` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `number` int(11) DEFAULT 0,
  `number_formatted` varchar(50) DEFAULT '',
  `tanggal` datetime DEFAULT NULL,
  `tahun` int(11) DEFAULT 0,
  `gudang_uuid` varchar(40) DEFAULT '',
  `gudang_kode` varchar(50) DEFAULT '',
  `gudang_nama` varchar(100) DEFAULT '',
  `keterangan` text DEFAULT NULL,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_opname_detail`
--

DROP TABLE IF EXISTS `stock_opname_detail`;
CREATE TABLE IF NOT EXISTS `stock_opname_detail` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_user_uuid` varchar(40) DEFAULT '',
  `last_updated_user_name` varchar(100) DEFAULT '',
  `stock_opname_uuid` varchar(40) DEFAULT '',
  `item_uuid` varchar(40) DEFAULT '',
  `item_kode` varchar(50) DEFAULT '',
  `item_barcode` varchar(100) DEFAULT '',
  `item_nama` varchar(100) DEFAULT '',
  `item_struktur_satuan_harga_json` longtext DEFAULT NULL,
  `item_tipe` varchar(50) DEFAULT '',
  `item_kategori_uuid` varchar(40) DEFAULT '',
  `item_kategori_nama` varchar(100) DEFAULT '',
  `satuan_terkecil` varchar(100) DEFAULT '',
  `stock_system_satuan_terkecil` int(11) DEFAULT 0,
  `stock_fisik_satuan_terkecil` int(11) DEFAULT 0,
  `stock_selisih_satuan_terkecil` int(11) DEFAULT 0,
  `cabang_uuid` varchar(40) DEFAULT '',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `name` varchar(100) DEFAULT '',
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `user_role_uuid` varchar(40) DEFAULT '',
  `cabang_uuid_list_json` longtext DEFAULT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `name`, `username`, `password`, `user_role_uuid`, `cabang_uuid_list_json`) VALUES
('3c747568-a6a5-470e-b387-0cb88c09ab69', '2022-01-05 22:50:34', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Admin', 'admin', '5d5839c425be8e462e18ee06d5e1a26b', '68dad749-2ce6-4c5e-bdfa-ae9e694f1ca7', '[\"84ef8a73-d5b1-11eb-ae0c-7cb27de7c399\"]'),
('be062314-716d-473f-ab63-96e2eb8ebfbc', '2022-01-05 22:50:36', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Kasir', 'kasir', '81d01315a9e23d67ebc9af7b99b84054', 'f0c4e2b1-b31c-4153-8da3-6cebd2213266', '[\"84ef8a73-d5b1-11eb-ae0c-7cb27de7c399\"]'),
('c877b010-d0de-11eb-85bd-7cb27de7c399', '2021-06-19 09:14:39', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Super Administrator', 'superadmin', '432358337d69f5ada1bf1cde0269b9b4', '7a5e2088-d0df-11eb-85bd-7cb27de7c399', '[\"84ef8a73-d5b1-11eb-ae0c-7cb27de7c399\"]');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_role`
--

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE IF NOT EXISTS `user_role` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `creator_user_uuid` varchar(40) DEFAULT '',
  `creator_user_name` varchar(100) DEFAULT '',
  `name` varchar(100) DEFAULT '',
  `privilege_json` longtext DEFAULT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `user_role`
--

INSERT INTO `user_role` (`uuid`, `created`, `creator_user_uuid`, `creator_user_name`, `name`, `privilege_json`) VALUES
('68dad749-2ce6-4c5e-bdfa-ae9e694f1ca7', '2022-01-02 16:29:30', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Administrator', '[{\"name\":\"allow_beranda\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori\",\"allow\":\"1\"},{\"name\":\"allow_item\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname\",\"allow\":\"1\"},{\"name\":\"allow_item_transfer\",\"allow\":\"1\"},{\"name\":\"allow_laporan_laba_jual\",\"allow\":\"1\"},{\"name\":\"allow_laporan_alur_kas\",\"allow\":\"1\"},{\"name\":\"allow_laporan_piutang_aktif\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pembelian_rekap\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pembelian_detail\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pembelian_harian\",\"allow\":\"1\"},{\"name\":\"allow_laporan_penjualan_rekap\",\"allow\":\"1\"},{\"name\":\"allow_laporan_penjualan_detail\",\"allow\":\"1\"},{\"name\":\"allow_laporan_penjualan_harian\",\"allow\":\"1\"},{\"name\":\"allow_laporan_item\",\"allow\":\"1\"},{\"name\":\"allow_laporan_kartu_stock\",\"allow\":\"1\"},{\"name\":\"allow_laporan_persediaan_stock_opname\",\"allow\":\"1\"},{\"name\":\"allow_laporan_item_transfer\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pelanggan\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pemasok\",\"allow\":\"1\"},{\"name\":\"allow_pemasok\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan\",\"allow\":\"1\"},{\"name\":\"allow_gudang\",\"allow\":\"1\"},{\"name\":\"allow_cabang\",\"allow\":\"1\"},{\"name\":\"allow_edit_pengaturan_lain_lain\",\"allow\":\"1\"},{\"name\":\"allow_gudang_delete\",\"allow\":\"1\"},{\"name\":\"allow_gudang_update\",\"allow\":\"1\"},{\"name\":\"allow_item_create\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori_create\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori_delete\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori_update\",\"allow\":\"1\"},{\"name\":\"allow_item_set_aktif\",\"allow\":\"1\"},{\"name\":\"allow_item_set_arsip\",\"allow\":\"1\"},{\"name\":\"allow_item_ubah_struktur_satuan_harga\",\"allow\":\"1\"},{\"name\":\"allow_item_update\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun_create\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun_delete\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun_update\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur_create\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur_delete\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur_update\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori_create\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori_delete\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori_update\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan_create\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan_delete\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan_update\",\"allow\":\"1\"},{\"name\":\"allow_pemasok_create\",\"allow\":\"1\"},{\"name\":\"allow_pemasok_delete\",\"allow\":\"1\"},{\"name\":\"allow_pemasok_update\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal_create\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal_delete\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal_update\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_create\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_delete\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_print\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_print_nota\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_print_riwayat_pembayaran_piutang\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_update\",\"allow\":\"1\"},{\"name\":\"allow_user_akses\",\"allow\":\"1\"},{\"name\":\"allow_user_akses_create\",\"allow\":\"1\"},{\"name\":\"allow_user_akses_delete\",\"allow\":\"1\"},{\"name\":\"allow_user_akses_update\",\"allow\":\"1\"},{\"name\":\"allow_user_login_create\",\"allow\":\"1\"},{\"name\":\"allow_user_login_delete\",\"allow\":\"1\"},{\"name\":\"allow_user_login_reset_password\",\"allow\":\"1\"},{\"name\":\"allow_user_login_update\",\"allow\":\"1\"},{\"name\":\"allow_gudang_create\",\"allow\":\"1\"},{\"name\":\"allow_user_login\",\"allow\":\"1\"},{\"name\":\"allow_user_role_privilege_create\",\"allow\":0},{\"name\":\"allow_user_role_privilege_delete\",\"allow\":0},{\"name\":\"allow_user_role_privilege_update\",\"allow\":0},{\"name\":\"allow_user_role_privilege\",\"allow\":0},{\"name\":\"allow_cabang_update\",\"allow\":\"1\"},{\"name\":\"allow_item_transfer_create\",\"allow\":\"1\"},{\"name\":\"allow_item_transfer_delete\",\"allow\":\"1\"},{\"name\":\"allow_item_transfer_update\",\"allow\":\"1\"},{\"name\":\"allow_cabang_create\",\"allow\":\"1\"},{\"name\":\"allow_cabang_delete\",\"allow\":\"1\"}]'),
('7a5e2088-d0df-11eb-85bd-7cb27de7c399', '2021-06-19 09:19:38', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Super Administrator', '[{\"name\":\"allow_beranda\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori\",\"allow\":\"1\"},{\"name\":\"allow_item\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname\",\"allow\":\"1\"},{\"name\":\"allow_laporan_kartu_stock\",\"allow\":\"1\"},{\"name\":\"allow_laporan_laba_jual\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pembelian_rekap\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pembelian_detail\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pembelian_harian\",\"allow\":\"1\"},{\"name\":\"allow_laporan_penjualan_rekap\",\"allow\":\"1\"},{\"name\":\"allow_laporan_penjualan_detail\",\"allow\":\"1\"},{\"name\":\"allow_laporan_penjualan_harian\",\"allow\":\"1\"},{\"name\":\"allow_laporan_piutang_aktif\",\"allow\":\"1\"},{\"name\":\"allow_laporan_alur_kas\",\"allow\":\"1\"},{\"name\":\"allow_laporan_persediaan_stock_opname\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pelanggan\",\"allow\":\"1\"},{\"name\":\"allow_laporan_pemasok\",\"allow\":\"1\"},{\"name\":\"allow_pemasok\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan\",\"allow\":\"1\"},{\"name\":\"allow_gudang\",\"allow\":\"1\"},{\"name\":\"allow_cabang\",\"allow\":\"1\"},{\"name\":\"allow_edit_pengaturan_lain_lain\",\"allow\":\"1\"},{\"name\":\"allow_user_login\",\"allow\":\"1\"},{\"name\":\"allow_user_akses\",\"allow\":\"1\"},{\"name\":\"allow_cabang_create\",\"allow\":\"1\"},{\"name\":\"allow_cabang_delete\",\"allow\":\"1\"},{\"name\":\"allow_cabang_update\",\"allow\":\"1\"},{\"name\":\"allow_gudang_create\",\"allow\":\"1\"},{\"name\":\"allow_gudang_delete\",\"allow\":\"1\"},{\"name\":\"allow_gudang_update\",\"allow\":\"1\"},{\"name\":\"allow_item_create\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori_create\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori_delete\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori_update\",\"allow\":\"1\"},{\"name\":\"allow_item_set_aktif\",\"allow\":\"1\"},{\"name\":\"allow_item_set_arsip\",\"allow\":\"1\"},{\"name\":\"allow_item_ubah_struktur_satuan_harga\",\"allow\":\"1\"},{\"name\":\"allow_item_update\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun_create\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun_delete\",\"allow\":\"1\"},{\"name\":\"allow_kas_akun_update\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur_create\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur_delete\",\"allow\":\"1\"},{\"name\":\"allow_kas_alur_update\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori_create\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori_delete\",\"allow\":\"1\"},{\"name\":\"allow_kas_kategori_update\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan_create\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan_delete\",\"allow\":\"1\"},{\"name\":\"allow_pelanggan_update\",\"allow\":\"1\"},{\"name\":\"allow_pemasok_create\",\"allow\":\"1\"},{\"name\":\"allow_pemasok_delete\",\"allow\":\"1\"},{\"name\":\"allow_pemasok_update\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal_create\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal_delete\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal_update\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_create\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_delete\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_print\",\"allow\":\"1\"},{\"name\":\"allow_stock_opname_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_retur_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembelian_update\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_delete\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_print_nota\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_print_riwayat_pembayaran_piutang\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_update\",\"allow\":\"1\"},{\"name\":\"allow_user_akses_create\",\"allow\":\"1\"},{\"name\":\"allow_user_akses_delete\",\"allow\":\"1\"},{\"name\":\"allow_user_akses_update\",\"allow\":\"1\"},{\"name\":\"allow_user_login_create\",\"allow\":\"1\"},{\"name\":\"allow_user_login_delete\",\"allow\":\"1\"},{\"name\":\"allow_user_login_update\",\"allow\":\"1\"}]'),
('f0c4e2b1-b31c-4153-8da3-6cebd2213266', '2022-01-05 22:50:03', 'c877b010-d0de-11eb-85bd-7cb27de7c399', 'Super Administrator', 'Kasir', '[{\"name\":\"allow_beranda\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian_retur\",\"allow\":0},{\"name\":\"allow_transaksi_penjualan\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_pembayaran_piutang\",\"allow\":0},{\"name\":\"allow_kas_alur\",\"allow\":0},{\"name\":\"allow_kas_akun\",\"allow\":0},{\"name\":\"allow_kas_kategori\",\"allow\":0},{\"name\":\"allow_item\",\"allow\":\"1\"},{\"name\":\"allow_item_kategori\",\"allow\":\"1\"},{\"name\":\"allow_stock_awal\",\"allow\":0},{\"name\":\"allow_stock_opname\",\"allow\":0},{\"name\":\"allow_item_transfer\",\"allow\":0},{\"name\":\"allow_laporan_laba_jual\",\"allow\":0},{\"name\":\"allow_laporan_alur_kas\",\"allow\":0},{\"name\":\"allow_laporan_piutang_aktif\",\"allow\":0},{\"name\":\"allow_laporan_pembelian_rekap\",\"allow\":0},{\"name\":\"allow_laporan_pembelian_detail\",\"allow\":0},{\"name\":\"allow_laporan_pembelian_harian\",\"allow\":0},{\"name\":\"allow_laporan_penjualan_rekap\",\"allow\":0},{\"name\":\"allow_laporan_penjualan_detail\",\"allow\":0},{\"name\":\"allow_laporan_penjualan_harian\",\"allow\":0},{\"name\":\"allow_laporan_item\",\"allow\":0},{\"name\":\"allow_laporan_kartu_stock\",\"allow\":0},{\"name\":\"allow_laporan_persediaan_stock_opname\",\"allow\":0},{\"name\":\"allow_laporan_item_transfer\",\"allow\":0},{\"name\":\"allow_laporan_pelanggan\",\"allow\":0},{\"name\":\"allow_laporan_pemasok\",\"allow\":0},{\"name\":\"allow_pemasok\",\"allow\":0},{\"name\":\"allow_pelanggan\",\"allow\":\"1\"},{\"name\":\"allow_gudang\",\"allow\":\"1\"},{\"name\":\"allow_cabang\",\"allow\":0},{\"name\":\"allow_edit_pengaturan_lain_lain\",\"allow\":0},{\"name\":\"allow_gudang_delete\",\"allow\":0},{\"name\":\"allow_gudang_update\",\"allow\":0},{\"name\":\"allow_item_create\",\"allow\":0},{\"name\":\"allow_item_kategori_create\",\"allow\":0},{\"name\":\"allow_item_kategori_delete\",\"allow\":0},{\"name\":\"allow_item_kategori_update\",\"allow\":0},{\"name\":\"allow_item_set_aktif\",\"allow\":0},{\"name\":\"allow_item_set_arsip\",\"allow\":0},{\"name\":\"allow_item_ubah_struktur_satuan_harga\",\"allow\":0},{\"name\":\"allow_item_update\",\"allow\":0},{\"name\":\"allow_kas_akun_create\",\"allow\":0},{\"name\":\"allow_kas_akun_delete\",\"allow\":0},{\"name\":\"allow_kas_akun_update\",\"allow\":0},{\"name\":\"allow_kas_alur_create\",\"allow\":0},{\"name\":\"allow_kas_alur_delete\",\"allow\":0},{\"name\":\"allow_kas_alur_update\",\"allow\":0},{\"name\":\"allow_kas_kategori_create\",\"allow\":0},{\"name\":\"allow_kas_kategori_delete\",\"allow\":0},{\"name\":\"allow_kas_kategori_update\",\"allow\":0},{\"name\":\"allow_pelanggan_create\",\"allow\":0},{\"name\":\"allow_pelanggan_delete\",\"allow\":0},{\"name\":\"allow_pelanggan_update\",\"allow\":0},{\"name\":\"allow_pemasok_create\",\"allow\":0},{\"name\":\"allow_pemasok_delete\",\"allow\":0},{\"name\":\"allow_pemasok_update\",\"allow\":0},{\"name\":\"allow_stock_awal_create\",\"allow\":0},{\"name\":\"allow_stock_awal_delete\",\"allow\":0},{\"name\":\"allow_stock_awal_update\",\"allow\":0},{\"name\":\"allow_stock_opname_create\",\"allow\":0},{\"name\":\"allow_stock_opname_delete\",\"allow\":0},{\"name\":\"allow_stock_opname_print\",\"allow\":0},{\"name\":\"allow_stock_opname_update\",\"allow\":0},{\"name\":\"allow_transaksi_pembayaran_piutang_create\",\"allow\":0},{\"name\":\"allow_transaksi_pembayaran_piutang_delete\",\"allow\":0},{\"name\":\"allow_transaksi_pembayaran_piutang_update\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian_create\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian_delete\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian_retur_create\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian_retur_delete\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian_retur_update\",\"allow\":0},{\"name\":\"allow_transaksi_pembelian_update\",\"allow\":0},{\"name\":\"allow_transaksi_penjualan_create\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_delete\",\"allow\":0},{\"name\":\"allow_transaksi_penjualan_print_nota\",\"allow\":\"1\"},{\"name\":\"allow_transaksi_penjualan_print_riwayat_pembayaran_piutang\",\"allow\":0},{\"name\":\"allow_transaksi_penjualan_update\",\"allow\":0},{\"name\":\"allow_user_akses\",\"allow\":0},{\"name\":\"allow_user_akses_create\",\"allow\":0},{\"name\":\"allow_user_akses_delete\",\"allow\":0},{\"name\":\"allow_user_akses_update\",\"allow\":0},{\"name\":\"allow_user_login_create\",\"allow\":0},{\"name\":\"allow_user_login_delete\",\"allow\":0},{\"name\":\"allow_user_login_reset_password\",\"allow\":0},{\"name\":\"allow_user_login_update\",\"allow\":0},{\"name\":\"allow_gudang_create\",\"allow\":0},{\"name\":\"allow_user_login\",\"allow\":0},{\"name\":\"allow_user_role_privilege_create\",\"allow\":0},{\"name\":\"allow_user_role_privilege_delete\",\"allow\":0},{\"name\":\"allow_user_role_privilege_update\",\"allow\":0},{\"name\":\"allow_user_role_privilege\",\"allow\":0},{\"name\":\"allow_cabang_update\",\"allow\":0},{\"name\":\"allow_item_transfer_create\",\"allow\":0},{\"name\":\"allow_item_transfer_delete\",\"allow\":0},{\"name\":\"allow_item_transfer_update\",\"allow\":0},{\"name\":\"allow_cabang_create\",\"allow\":0},{\"name\":\"allow_cabang_delete\",\"allow\":0}]');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_role_privilege`
--

DROP TABLE IF EXISTS `user_role_privilege`;
CREATE TABLE IF NOT EXISTS `user_role_privilege` (
  `uuid` varchar(40) NOT NULL DEFAULT '',
  `name` varchar(100) DEFAULT '',
  `description` varchar(200) DEFAULT '',
  `parent_uuid` varchar(40) DEFAULT '',
  `order_number` int(11) DEFAULT 0,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `user_role_privilege`
--

INSERT INTO `user_role_privilege` (`uuid`, `name`, `description`, `parent_uuid`, `order_number`) VALUES
('00f59723-4d9d-4245-81ee-57a0341d92df', 'allow_cabang_update', 'Boleh mengubah cabang yang sudah ada', 'a82b2a29-63aa-4700-a3d2-27f6e4d268fd', 36),
('01859431-023d-440c-babd-bd73319d2e19', 'allow_kas_akun_delete', 'Boleh menghapus akun kas yang sudah ada', '717abe36-380c-4b70-b42c-824febace98f', 33),
('03f902cc-dbb2-41e4-93ea-c46074015e41', 'allow_transaksi_penjualan_print_riwayat_pembayaran_piutang', 'Boleh cetak riwayat pembayaran piutang penjualan', '9b20b8da-8350-4033-bd00-800be375f588', 33),
('05a7a813-a316-4e53-bc50-7dce1ecb0aad', 'allow_item_transfer_delete', 'Boleh menghapus item transfer yang sudah ada', 'c3ed9dce-de7a-4876-a197-c019b0030936', 36),
('07047ec7-9088-40f7-bd53-939cf19235d7', 'allow_laporan_pelanggan', 'Boleh akses laporan pelanggan', '07047ec7-9088-40f7-bd53-939cf19235d7', 27),
('07ff3089-1f9b-4688-bde6-469729b99e4f', 'allow_transaksi_penjualan_print_nota', 'Boleh cetak nota penjualan / Invoice', '9b20b8da-8350-4033-bd00-800be375f588', 33),
('0ceb700c-e8a5-4211-95c2-84ace6a0915f', 'allow_stock_awal_delete', 'Boleh menghapus stock awal yang sudah ada', '6490c6ed-1e67-4b0f-9a72-44410ebcd603', 33),
('0cee5ef5-b2ec-4b88-9f1e-ea42749a338b', 'allow_stock_opname_print', 'Boleh cetak stock opname', 'bc928362-c29d-497e-a808-a3df4cb901a9', 33),
('0f9f6dd9-5574-4241-ab4f-5e60ebe7909e', 'allow_pelanggan', 'Boleh akses pelanggan', '0f9f6dd9-5574-4241-ab4f-5e60ebe7909e', 30),
('1006e111-a808-421e-85ba-531eb9ff1b0e', 'allow_laporan_pembelian_harian', 'Boleh akses laporan pembelian harian', '1006e111-a808-421e-85ba-531eb9ff1b0e', 19),
('121b59a5-528e-4084-88ea-4d93e6fbda78', 'allow_pemasok_create', 'Boleh menambah pemasok baru', '754e7e56-fce6-4658-aa41-a68c7aa4c3f6', 33),
('1d917c3a-5bac-4e76-8e48-30e738e9798b', 'allow_transaksi_pembayaran_piutang_create', 'Boleh menambah transaksi pembayaran piutang baru', '7684f163-2131-43a5-a7fa-83e08ac83449', 33),
('1f9d0c44-208e-49c3-837a-3ce234325003', 'allow_transaksi_pembelian', 'Boleh akses transaksi pembelian', '1f9d0c44-208e-49c3-837a-3ce234325003', 2),
('1fedc00d-46c3-4387-a11c-d74e85674882', 'allow_pemasok_delete', 'Boleh menghapus pemasok yang sudah ada', '754e7e56-fce6-4658-aa41-a68c7aa4c3f6', 33),
('21e7145a-dbd1-4950-8379-7561a91eb245', 'allow_user_akses_update', 'Boleh mengubah user akses yang sudah ada', 'cc571203-461f-4d87-b819-23620bdc2581', 33),
('265ea432-f461-4794-a3a4-f993c3130a08', 'allow_user_akses_delete', 'Boleh menghapus user akses yang sudah ada', 'cc571203-461f-4d87-b819-23620bdc2581', 33),
('28d232ee-ee00-447c-b79a-f780de43c6d7', 'allow_cabang_create', 'Boleh menambahkan cabang baru', 'a82b2a29-63aa-4700-a3d2-27f6e4d268fd', 37),
('2c43f40c-9587-4a43-8f51-39ca7ab6f2cd', 'allow_laporan_piutang_aktif', 'Boleh akses laporan piutang', '2c43f40c-9587-4a43-8f51-39ca7ab6f2cd', 16),
('2c52a9b9-82b4-4def-9bd2-f851549aa330', 'allow_transaksi_pembelian_delete', 'Boleh menghapus transaksi pembelian yang sudah ada', '1f9d0c44-208e-49c3-837a-3ce234325003', 33),
('2f2bf6c2-171f-4f57-bcaf-73b745336916', 'allow_laporan_item_transfer', 'Boleh akses laporan item transfer', '2f2bf6c2-171f-4f57-bcaf-73b745336916', 26),
('2fd5b2ab-3083-4d56-a800-a09fe3a87846', 'allow_user_role_privilege_create', 'Boleh menambahkan user role privilege', 'af6591b0-26dd-4be2-b645-5ca6ff26d914', 34),
('30775213-9802-4e71-ae0e-287be24551e3', 'allow_kas_kategori', 'Boleh akses kategori kas', '30775213-9802-4e71-ae0e-287be24551e3', 8),
('31dd6d24-6b9a-47af-8445-0b70b622abde', 'allow_pelanggan_create', 'Boleh menambah pelanggan baru', '0f9f6dd9-5574-4241-ab4f-5e60ebe7909e', 33),
('3289a090-2c15-4f82-908f-286e07e5fab2', 'allow_transaksi_pembelian_create', 'Boleh menambah transaksi pembelian baru', '1f9d0c44-208e-49c3-837a-3ce234325003', 33),
('34545a8b-95fe-40df-9f30-44d864fd3ca4', 'allow_stock_awal_update', 'Boleh mengubah stock awal yang sudah ada', '6490c6ed-1e67-4b0f-9a72-44410ebcd603', 33),
('423b1363-f18f-440c-8cd6-dd31bebab4c9', 'allow_laporan_pembelian_rekap', 'Boleh akses laporan pembelian rekap', '423b1363-f18f-440c-8cd6-dd31bebab4c9', 17),
('4650be1c-5c59-408c-aa8c-1701bd1ce9ff', 'allow_item_update', 'Boleh mengubah item yang sudah ada', 'a8bed64e-f701-484c-88a5-9b7ae681e1da', 33),
('4964f361-14c4-459a-81ea-2db79c7b8aef', 'allow_user_login_reset_password', 'Boleh reset password user login', '54da9d3c-bd27-44ff-bc11-647fe37fbbf9', 33),
('4b25b807-8690-4b62-9cee-bf19f68b2301', 'allow_transaksi_pembelian_retur_update', 'Boleh mengubah transaksi retur pembelian yang sudah ada', '87f44bae-fe25-401d-a25f-285183fbb999', 33),
('4e4fb03d-77b4-4592-835c-df6e7ed00ee6', 'allow_user_role_privilege_update', 'Boleh mengubah user role privilege yang sudah ada', 'af6591b0-26dd-4be2-b645-5ca6ff26d914', 34),
('52d68539-20e0-4768-9dc5-739047bbcd4a', 'allow_user_login_update', 'Boleh mengubah user login yang sudah ada', '54da9d3c-bd27-44ff-bc11-647fe37fbbf9', 33),
('54da9d3c-bd27-44ff-bc11-647fe37fbbf9', 'allow_user_login', 'Boleh akses user login', '54da9d3c-bd27-44ff-bc11-647fe37fbbf9', 34),
('57e0cffa-79e1-4411-a6e2-b4974a7c94aa', 'allow_kas_alur_create', 'Boleh menambah alur kas baru', '5f27d1f1-bd39-4220-a112-efa3e8be9747', 33),
('5d0748f8-3021-4300-a14e-8a66eeb370a9', 'allow_user_akses_create', 'Boleh menambah user akses baru', 'cc571203-461f-4d87-b819-23620bdc2581', 33),
('5f27d1f1-bd39-4220-a112-efa3e8be9747', 'allow_kas_alur', 'Boleh akses alur kas', '5f27d1f1-bd39-4220-a112-efa3e8be9747', 6),
('61b4f0ca-b943-4a94-a0f5-96d11a9aaf93', 'allow_item_transfer_update', 'Boleh mengubah item transfer yang sudah ada', 'c3ed9dce-de7a-4876-a197-c019b0030936', 36),
('6490c6ed-1e67-4b0f-9a72-44410ebcd603', 'allow_stock_awal', 'Boleh akses stock awal', '6490c6ed-1e67-4b0f-9a72-44410ebcd603', 11),
('6a27d10c-d7db-47ba-ae1d-95d936de9cd0', 'allow_stock_opname_create', 'Boleh menambah stock opname baru', 'bc928362-c29d-497e-a808-a3df4cb901a9', 33),
('717abe36-380c-4b70-b42c-824febace98f', 'allow_kas_akun', 'Boleh akses akun kas', '717abe36-380c-4b70-b42c-824febace98f', 7),
('754e7e56-fce6-4658-aa41-a68c7aa4c3f6', 'allow_pemasok', 'Boleh akses pemasok', '754e7e56-fce6-4658-aa41-a68c7aa4c3f6', 29),
('75df70d3-fece-4022-bbb3-9e13e54c914a', 'allow_stock_awal_create', 'Boleh menambah stock awal baru', '6490c6ed-1e67-4b0f-9a72-44410ebcd603', 33),
('7684f163-2131-43a5-a7fa-83e08ac83449', 'allow_transaksi_pembayaran_piutang', 'Boleh akses transaksi pembayaran piutang', '7684f163-2131-43a5-a7fa-83e08ac83449', 5),
('78ed412e-78ea-428e-9eaa-b5430137fcc2', 'allow_pelanggan_delete', 'Boleh menghapus pelanggan yang sudah ada', '0f9f6dd9-5574-4241-ab4f-5e60ebe7909e', 33),
('79fd3da5-469f-466c-8bea-c3a82481ae2d', 'allow_kas_kategori_delete', 'Boleh menghapus kategori kas yang sudah ada', '30775213-9802-4e71-ae0e-287be24551e3', 33),
('7afdbce7-6e6a-41de-9d97-9f4c01989745', 'allow_edit_pengaturan_lain_lain', 'Boleh akses pengaturan lain-lain', '7afdbce7-6e6a-41de-9d97-9f4c01989745', 33),
('7cbcbdcb-9015-4c64-89c2-ed60832387e2', 'allow_item_ubah_struktur_satuan_harga', 'Boleh mengubah stuktur satuan harga item', 'a8bed64e-f701-484c-88a5-9b7ae681e1da', 33),
('816d290b-644c-4d57-8f78-2e0d43431e04', 'allow_user_login_delete', 'Boleh menghapus user login yang sudah ada', '54da9d3c-bd27-44ff-bc11-647fe37fbbf9', 33),
('81eca0b1-5694-402e-be9e-e5ef7920dc88', 'allow_beranda', 'Boleh akses beranda', '81eca0b1-5694-402e-be9e-e5ef7920dc88', 1),
('824485bc-d95d-48d0-a9bc-2a145888ff0d', 'allow_kas_alur_update', 'Boleh mengubah alur kas yang sudah ada', '5f27d1f1-bd39-4220-a112-efa3e8be9747', 33),
('857d5546-1344-46a7-aae1-b7d44070a1ab', 'allow_transaksi_pembelian_retur_delete', 'Boleh menghapus transaksi retur pembelian yang sudah ada', '87f44bae-fe25-401d-a25f-285183fbb999', 33),
('87f44bae-fe25-401d-a25f-285183fbb999', 'allow_transaksi_pembelian_retur', 'Boleh akses transaksi pembelian retur', '87f44bae-fe25-401d-a25f-285183fbb999', 3),
('8d2424de-9128-4a78-9d98-df5666743261', 'allow_transaksi_pembelian_update', 'Boleh mengubah transaksi pembelian yang sudah ada', '1f9d0c44-208e-49c3-837a-3ce234325003', 33),
('8e367550-9c3d-4873-957f-030d95d8a02b', 'allow_kas_alur_delete', 'Boleh menghapus alur kas yang sudah ada', '5f27d1f1-bd39-4220-a112-efa3e8be9747', 33),
('8ea83524-aa95-452f-a8a3-4f000a31a3d3', 'allow_item_kategori_create', 'Boleh menambah kategori item baru', 'a7c376cd-20a4-405f-abd0-33ef4f0f3784', 33),
('8fc5e603-9403-4eb6-b5be-0b12e9ae5354', 'allow_item_kategori_update', 'Boleh mengubah kategori item yang sudah ada', 'a7c376cd-20a4-405f-abd0-33ef4f0f3784', 33),
('9006d013-d93c-4696-bed3-a6523fcd7407', 'allow_pelanggan_update', 'Boleh mengubah pelanggan yang sudah ada', '0f9f6dd9-5574-4241-ab4f-5e60ebe7909e', 33),
('9252a6d2-9681-49c9-b0f0-8616db7143dc', 'allow_user_role_privilege_delete', 'Boleh menghapus user role privilege yang sudah ada', 'af6591b0-26dd-4be2-b645-5ca6ff26d914', 34),
('92a8f948-e81f-4067-838e-7c7b3b7c2c6a', 'allow_laporan_penjualan_rekap', 'Boleh akses laporan penjualan rekap', '92a8f948-e81f-4067-838e-7c7b3b7c2c6a', 20),
('97f5f19f-7892-4c71-89f3-476e7258a5a1', 'allow_laporan_pemasok', 'Boleh akses laporan pemasok', '97f5f19f-7892-4c71-89f3-476e7258a5a1', 28),
('9b20b8da-8350-4033-bd00-800be375f588', 'allow_transaksi_penjualan', 'Boleh akses transaksi penjualan', '9b20b8da-8350-4033-bd00-800be375f588', 4),
('9c09c912-99e1-4096-ac97-fa42e9d6c054', 'allow_laporan_item', 'Boleh akses laporan item', '9c09c912-99e1-4096-ac97-fa42e9d6c054', 23),
('9ef6ffab-343c-468c-b16c-d4ed5c9ed396', 'allow_laporan_pembelian_detail', 'Boleh akses laporan pembelian detail', '9ef6ffab-343c-468c-b16c-d4ed5c9ed396', 18),
('a09cf2ed-838f-49ba-a077-64d872d74efa', 'allow_laporan_alur_kas', 'Boleh akses laporan alur kas', 'a09cf2ed-838f-49ba-a077-64d872d74efa', 15),
('a47d1c68-0e59-46e6-99cd-8a185888953b', 'allow_stock_opname_delete', 'Boleh menghapus stock opname yang sudah ada', 'bc928362-c29d-497e-a808-a3df4cb901a9', 33),
('a7c376cd-20a4-405f-abd0-33ef4f0f3784', 'allow_item_kategori', 'Boleh akses kategori item', 'a7c376cd-20a4-405f-abd0-33ef4f0f3784', 10),
('a80be409-4c16-4e88-b962-4f95e605955b', 'allow_transaksi_penjualan_update', 'Boleh mengubah transaksi penjualan yang sudah ada', '9b20b8da-8350-4033-bd00-800be375f588', 33),
('a82b2a29-63aa-4700-a3d2-27f6e4d268fd', 'allow_cabang', 'Boleh akses cabang', 'a82b2a29-63aa-4700-a3d2-27f6e4d268fd', 32),
('a8bed64e-f701-484c-88a5-9b7ae681e1da', 'allow_item', 'Boleh akses item', 'a8bed64e-f701-484c-88a5-9b7ae681e1da', 9),
('ad236b57-a3ef-4cd3-9b9d-c1779d3fa6ba', 'allow_transaksi_pembelian_retur_create', 'Boleh membuat transaksi retur pembelian baru', '87f44bae-fe25-401d-a25f-285183fbb999', 33),
('af6591b0-26dd-4be2-b645-5ca6ff26d914', 'allow_user_role_privilege', 'Boleh akses user role privilege', 'af6591b0-26dd-4be2-b645-5ca6ff26d914', 35),
('b0d9bfec-8c48-42b1-909f-faf19c76fcaf', 'allow_gudang', 'Boleh akses gudang', 'b0d9bfec-8c48-42b1-909f-faf19c76fcaf', 31),
('b1a05196-132b-445d-b44e-e8893eeb291b', 'allow_laporan_persediaan_stock_opname', 'Boleh akses laporan stock opname', 'b1a05196-132b-445d-b44e-e8893eeb291b', 25),
('b925a8d1-1af7-417f-ac11-58dec0b9b3e3', 'allow_gudang_create', 'Boleh menambah gudang baru', 'b0d9bfec-8c48-42b1-909f-faf19c76fcaf', 34),
('bc928362-c29d-497e-a808-a3df4cb901a9', 'allow_stock_opname', 'Boleh akses stock opname', 'bc928362-c29d-497e-a808-a3df4cb901a9', 12),
('bd13c6a7-1264-4dc7-83c5-1c361bddac17', 'allow_item_transfer_create', 'Boleh menambah item transfer baru', 'c3ed9dce-de7a-4876-a197-c019b0030936', 36),
('bff4816d-100d-4ac5-9a7a-50d2c6158007', 'allow_transaksi_penjualan_create', 'Boleh menambah transaksi penjualan baru', '9b20b8da-8350-4033-bd00-800be375f588', 33),
('c02b3240-bd21-454f-be83-cb432c425ae5', 'allow_laporan_penjualan_detail', 'Boleh akses laporan penjualan detail', 'c02b3240-bd21-454f-be83-cb432c425ae5', 21),
('c229932d-4382-470a-b1a1-2bbc8e00b062', 'allow_user_login_create', 'Boleh menambah user login baru', '54da9d3c-bd27-44ff-bc11-647fe37fbbf9', 33),
('c3ed9dce-de7a-4876-a197-c019b0030936', 'allow_item_transfer', 'Boleh akses item transfer', 'c3ed9dce-de7a-4876-a197-c019b0030936', 13),
('ca84b710-b7e4-4f25-a1f5-e2e418cba7f5', 'allow_stock_opname_update', 'Boleh mengubah stock opname yang sudah ada', 'bc928362-c29d-497e-a808-a3df4cb901a9', 33),
('caa3dc60-737b-4a1a-bd3b-d6cad4d3eda7', 'allow_pemasok_update', 'Boleh mengubah pemasok yang sudah ada', '754e7e56-fce6-4658-aa41-a68c7aa4c3f6', 33),
('cc571203-461f-4d87-b819-23620bdc2581', 'allow_user_akses', 'Boleh akses user akses', 'cc571203-461f-4d87-b819-23620bdc2581', 33),
('cf417041-b329-4617-a21f-af7c67321f99', 'allow_kas_kategori_update', 'Boleh mengubah kategori kas yang sudah ada', '30775213-9802-4e71-ae0e-287be24551e3', 33),
('d1896e52-0271-4c10-9d3c-43bc67b2a446', 'allow_kas_akun_update', 'Boleh mengubah akun kas yang sudah ada', '717abe36-380c-4b70-b42c-824febace98f', 33),
('d4d26b1b-da55-41cb-b901-d8df0e78e67e', 'allow_laporan_laba_jual', 'Boleh akses laporan laba jual', 'd4d26b1b-da55-41cb-b901-d8df0e78e67e', 14),
('d718c3ec-81cd-4a04-8caa-446b3796ac57', 'allow_cabang_delete', 'Boleh menghapus cabang yang sudah ada', 'a82b2a29-63aa-4700-a3d2-27f6e4d268fd', 37),
('da92304d-eca0-4b51-ba51-8093c59022ed', 'allow_item_create', 'Boleh menambah item baru', 'a8bed64e-f701-484c-88a5-9b7ae681e1da', 33),
('e1135663-1567-4c13-bf5f-20d233e5eac6', 'allow_item_set_arsip', 'Boleh arsip item yang sudah ada', 'a8bed64e-f701-484c-88a5-9b7ae681e1da', 33),
('e3aea711-f7b4-483f-80ac-50952fe0bd4c', 'allow_laporan_penjualan_harian', 'Boleh akses laporan penjualan harian', 'e3aea711-f7b4-483f-80ac-50952fe0bd4c', 22),
('e43f7c16-a2ee-425b-a640-e0914ed1d33a', 'allow_gudang_update', 'Boleh mengubah gudang yang sudah ada', 'b0d9bfec-8c48-42b1-909f-faf19c76fcaf', 33),
('e6329c33-57b7-475d-a26f-b67253d2626b', 'allow_laporan_kartu_stock', 'Boleh akses laporan kartu stock', 'e6329c33-57b7-475d-a26f-b67253d2626b', 24),
('efc90235-81c2-43a7-a2f3-9c28a664a7dd', 'allow_transaksi_pembayaran_piutang_update', 'Boleh mengubah transaksi pembayaran piutang yang sudah ada', '7684f163-2131-43a5-a7fa-83e08ac83449', 33),
('efe8bcac-0f98-48ad-a98b-7ebe218bba34', 'allow_item_kategori_delete', 'Boleh menghapus kategori item yang sudah ada', 'a7c376cd-20a4-405f-abd0-33ef4f0f3784', 33),
('f04c4376-ff6b-49e8-81de-da7c3110846d', 'allow_transaksi_pembayaran_piutang_delete', 'Boleh menghapus transaksi pembayaran piutang yang sudah ada', '7684f163-2131-43a5-a7fa-83e08ac83449', 33),
('f4e37274-2742-489e-b90c-062064e8f630', 'allow_item_set_aktif', 'Boleh set aktif item yang sudah ada', 'a8bed64e-f701-484c-88a5-9b7ae681e1da', 33),
('f6b394fe-eab7-4652-b1b6-3a3a70307680', 'allow_kas_kategori_create', 'Boleh menambah kategori kas baru', '30775213-9802-4e71-ae0e-287be24551e3', 33),
('fb6f8abb-68fa-4ccc-9b86-e1f85fd6bfc5', 'allow_kas_akun_create', 'Boleh menambah akun kas yang baru', '717abe36-380c-4b70-b42c-824febace98f', 33),
('fc6099af-94ee-4901-b384-cc354f811290', 'allow_transaksi_penjualan_delete', 'Boleh menghapus transaksi penjualan yang sudah ada', '9b20b8da-8350-4033-bd00-800be375f588', 33),
('fd180df1-0a80-42ec-a138-49e343002a25', 'allow_gudang_delete', 'Boleh menghapus gudang yang sudah ada', 'b0d9bfec-8c48-42b1-909f-faf19c76fcaf', 33);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
