USE `pcc-110`;

INSERT INTO users (username,password,nama,role,regu_id) VALUES
('operator', '$2y$12$3Z7vxcKhfnN9yClHFv5LeersuXOOFXSxwmZHij2TMva7KMG9EyRJO', 'Operator', 'operator', NULL),
('pamapta',  '$2y$12$3Z7vxcKhfnN9yClHFv5LeersuXOOFXSxwmZHij2TMva7KMG9EyRJO', 'Pamapta', 'pamapta', NULL),
('pimpinan', '$2y$12$3Z7vxcKhfnN9yClHFv5LeersuXOOFXSxwmZHij2TMva7KMG9EyRJO', 'Pimpinan', 'pimpinan', NULL)
ON DUPLICATE KEY UPDATE username=username;

-- Optional generic dummy records without real personnel/team names.
INSERT INTO laporan
(tiket,nomor_laporan,token_akses,lokasi,jenis_kejadian,prioritas,deskripsi,waktu_laporan,waktu_input,status)
VALUES
(CONCAT('TIKET-',DATE_FORMAT(CURDATE(),'%Y%m%d'),'-9001'),
 CONCAT('LP-',DATE_FORMAT(CURDATE(),'%Y%m%d'),'-9001'),
 REPLACE(UUID(),'-',''),
 'Lokasi Dummy 01','Gangguan Ketertiban','sedang','DATA DUMMY UNTUK PENGUJIAN',
 NOW()-INTERVAL 20 MINUTE,NOW()-INTERVAL 19 MINUTE,'baru')
ON DUPLICATE KEY UPDATE tiket=tiket;
