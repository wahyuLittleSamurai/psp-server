<?php 

//FROM pembayaran 
	public function getPenagihan()
	{
		$idStaff = $this->input->post("codeStaff");
		$query = "SELECT hr.*, IFNULL(byr.Nominal, 0) SudahBayar, st.StatusSelesai, IFNULL(lastCheck.CheckDate, '') CheckDate,
						(hr.JmlHarga - IFNULL(byr.Nominal, 0)) Kekurangan, so.IdPelanggan, mp.NamaPelanggan, mjb.JenisBayar, so.JatuhTempo,
						lastCheck.KolektorDate, lastCheck.IdKolektor
				FROM (
					#CARI YG HARUS DI BAYAR Sudah Dikurang DP
					SELECT Invoice, MAX(IdSo) IdSo, MAX(IdSJ) IdSJ, MAX(IFNULL(Dp,0)) Dp, (SUM(ResSubTotal) - MAX(IFNULL(Dp,0))) JmlHarga, MAX(CreateDate) CreateDate
					FROM (
						SELECT inv.Id Invoice, so.Id IdSo, mj.Id IdSJ, dsj.IdDso, dsj.IdProduct, dsj.JmlTerkirim JmlSj, dso.Harga, dso.Disc,
							ret.JmlEdit, ret.JmlRusakEdit, (IFNULL(dsj.JmlTerkirim,0) - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) ResJml,
							IFNULL(so.Pph, 0) Pph, 
							( (((IFNULL(dsj.JmlTerkirim,0) - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) * dso.Harga ) - dso.Disc) * IFNULL(so.Pph,0)
							+ (((IFNULL(dsj.JmlTerkirim,0) - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) * dso.Harga ) - dso.Disc) ) ResSubTotal,
							so.Dp, inv.CreateDate
						FROM detailsjkolektor AS dsj
						LEFT JOIN detailso AS dso ON dso.Id = dsj.IdDso AND dso.IdProduct = dsj.IdProduct
						LEFT JOIN masterso AS so ON so.Id = dso.IdSo
						LEFT JOIN mastersjkolektor AS mj ON mj.IdSo = so.Id
						LEFT JOIN (
							SELECT r.IdSo IdSJ, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
							FROM returnbarang AS r
							LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
							WHERE r.StatusReturn = 'Return'
						) ret ON ret.IdSJ = mj.Id AND ret.IdProduct = dsj.IdProduct
						LEFT JOIN masterinvoice AS inv ON inv.IdSJ = mj.Id
						WHERE dso.StatusBatal = 0 AND IFNULL(so.StatusBatal,0) = 0
							AND (IFNULL(dsj.JmlTerkirim,0) - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) > 0
							AND IFNULL(inv.BatalBy,'') = ''
					) iv 
					GROUP BY Invoice	
				) hr 
				LEFT JOIN (
					#CARI YG SUDAH DI BAYAR
					SELECT ut.InvSo, SUM(IFNULL(ut.Nominal, 0)) Nominal FROM masterpiutang AS ut
					WHERE (IFNULL(ut.IsBatal, '0') = '0' OR IFNULL(ut.IsBatal, '') = '') AND IFNULL(ut.CheckBy,'') != ''
					GROUP BY ut.InvSo				
				) byr ON byr.InvSo = hr.Invoice
				LEFT JOIN (
					#cari status invoice sudah selesai atau belum atau masih proses
					SELECT Id, 'Belum Ada' StatusSelesai  FROM masterinvoice 
					WHERE IFNULL(BatalBy,'') = '' AND IFNULL(IsCetak,0) = 1 AND 
						Id NOT IN ( SELECT InvSo FROM masterpiutang WHERE IFNULL(IsBatal, '0') = '0' OR IFNULL(IsBatal, '') = '')
					UNION ALL	
					SELECT InvSo, CASE IFNULL(CheckDate,'') WHEN '' THEN 'Belum Selesai' 
						ELSE 'Selesai' END StatusSelesai  
					FROM (
						SELECT InvSo, CheckDate, ROW_NUMBER() OVER (Partition By InvSo  ORDER BY CreateDate DESC) Urutan
						FROM masterpiutang 
					) raw WHERE Urutan = 1
				) AS st ON st.Id = hr.Invoice
				LEFT JOIN (
					#cari terakhir di bayar dan check date
					SELECT * FROM (
						SELECT InvSo, CheckDate, IdKolektor, KolektorDate,
							ROW_NUMBER() OVER (Partition By InvSo  ORDER BY CreateDate DESC) Urutan
						FROM masterpiutang 
					) ckDate WHERE urutan = 1
				) lastCheck ON lastCheck.InvSo = hr.Invoice
				LEFT JOIN masterso AS so ON so.Id = hr.IdSo
				LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
				LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
				ORDER BY hr.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}

	
	//FROM Pembayaran 
	public function InsertPembayaranKolektor()
	{
		$data = array(
			"invoice" => $this->input->post("invoice"),
			"idCustomer" => $this->input->post("idCustomer"),
			"SisaBayar" => $this->input->post("SisaBayar"),
			"staff" => $this->input->post("staff"),
			"Ttd" => $this->input->post("Ttd"),
			"Photo" => $this->input->post("Photo"),
		);
		
		$newId = $this->GenId('masterpiutang', 'mpu');
		
		$query = "INSERT INTO masterpiutang(Id, InvSo, CreateDate, CreateBy, Nominal, IdKolektor, KolektorDate, Ttd, Photo)
						VALUES('".$newId."', '".$data["invoice"]."', NOW(), '".$data["staff"]."', '".$data["SisaBayar"]."',
						'".$data["staff"]."', NOW(), '".$data["Ttd"]."', '".$data["Photo"]."')";
		
		$resQuery = $this->db->query($query);
		if($resQuery)
		{
			echo "Success";
		}
		else
		{
			echo "Failed";
		}
	}
	
	
?>
	