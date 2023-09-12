<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct(){
	    
		header("Access-Control-Allow-Origin: *"); 
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); 
        header("Access-Control-Allow-Headers: *"); 
        header("Access-Control-Max-Age: 86400");
        header("Access-Control-Allow-Credentials: true");
        $method = $_SERVER['REQUEST_METHOD']; 
        if($method == "OPTIONS") { die(); }
		
		
		parent:: __construct();
		
		$this->load->helper(array('url','form','html'));   
		$this->load->database();
		$this->load->helper('date');
		$this->load->helper('cookie');
		$this->load->helper('number');
		$this->load->library('session');
		$this->load->library('user_agent');
		$this->load->helper('file');
		
		
	} 
	public function index()
	{
		$this->load->view('welcome_message');
	}
	public function selectSidebar()
	{
		$query = "select * from mastersidebar";
		$res = $this->db->query($query)->result();
		echo json_encode($res);
	}
	public function NewGenId($tbl, $code)
	{
		date_default_timezone_set('Asia/Jakarta');
		$date = date("ymd");
		$query = "SELECT Id FROM ".$tbl." ORDER BY CreateDate DESC LIMIT 1";
		$resCheck = $this->db->query($query)->row();
		if(empty($resCheck))
		{
			return strtoupper($code) . "-".$date."0001";
		}
		else
		{
			$getInt = str_replace(strtoupper($code)."-".$date, "", strtoupper($resCheck->Id));
			$toInt = (int)$getInt + 1;
			return strtoupper($code) . "-".$date. sprintf('%04d', $toInt);
		}
	}
	public function returnId($tbl, $code)
	{
		$getId = $this->GenId($tbl, $code);
		echo $getId;
	}
	public function GenId($tbl, $code)
	{
		date_default_timezone_set('Asia/Jakarta');
		$date = date("ymd");
		$query = "SELECT Id FROM ".$tbl." ORDER BY CreateDate DESC LIMIT 1";
		$resCheck = $this->db->query($query)->row();
		if(empty($resCheck))
		{
			return strtoupper($code) . "-".$date."0001";
		}
		else
		{
			$getInt = str_replace(strtoupper($code)."-".$date, "", strtoupper($resCheck->Id));
			$toInt = (int)$getInt + 1;
			return strtoupper($code) . "-".$date. sprintf('%04d', $toInt);
		}
		
		/*
		$query = "SELECT CONCAT(UPPER('".$code."-'), UPPER(MD5(CONVERT(NOW(), CHAR(255))))) Id";
		$resCheck = $this->db->query($query)->row();
		return $resCheck->Id;
		*/
	}
	
	public function GenInvoice()
	{
		$query = "SELECT IF(
								(
									SELECT Id  FROM masterso WHERE CONVERT(TglInvoice, DATE) = CURDATE() AND IFNULL(StatusBatal, '') = '' ORDER BY TglInvoice DESC LIMIT 1 
								) != '' , 
								(
									SELECT 
										CONCAT('INV/', date_format(curdate(), '%Y%m%d'), '/', LPAD(CONVERT( CONVERT(
										REPLACE(Invoice,CONCAT('INV/', date_format(curdate(), '%Y%m%d'), '/'), '')
										, INT) + 1, CHAR(100)), 4, 0) )
										Id
									FROM masterso 
									WHERE CONVERT(TglInvoice, DATE) = CURDATE()
										AND IFNULL(StatusBatal, '') = ''
									ORDER BY TglInvoice DESC LIMIT 1
								), 
								CONCAT('INV/', date_format(curdate(), '%Y%m%d'), '/', LPAD('1',4,0) )
							) newId";
		$resQuery = $this->db->query($query)->row();
		return $resQuery->newId;
	}
	public function selectDb()
	{
		/* Kode */
		// 1 = results()
		// 2 = row()
		// 3 = num_rows()
		
		$tbl = $this->input->post("table");
		$selects = $this->input->post("selects");
		$wheres = $this->input->post("wheres");
		$kode = $this->input->post("kode");
		$orderBy = $this->input->post("order");
		$wheres = str_replace("%", "'", $wheres);
		
		if(!empty($tbl) && !empty($selects))
		{
			
			
			$query = "SELECT ". $selects . " FROM " . $tbl . " " . $wheres;
			
			if(!empty($orderBy))
			{
				$query .= " ORDER BY " . $orderBy;
			}
			
			if($kode == "1")
			{
				$resQuery = $this->db->query($query)->result();
			}
			if($kode == "2")
			{
				$resQuery = $this->db->query($query)->row();
			}
			if($kode == "3")
			{
				$resQuery = $this->db->query($query)->num_rows();
			}
			if($resQuery)
			{
				echo json_encode($resQuery);
			}
			
		}
		else
		{
			echo "Failed, Lengkapi Parameter";
		}
	}
	public function insertDb()
	{
		/* 
			table = table yg di pakai
			kode = kode/id dari table 
			insert = apa yg di insert
		*/
		$tbl = $this->input->post("table");
		$kode = $this->input->post("kode");
		$inserts = json_decode($this->input->post("inserts"), true);
		
		$newId = $this->GenId($tbl, $kode);
		$keys = implode(",", array_keys($inserts));
		$values = implode("','", array_values($inserts));
		
		$query = "INSERT INTO " . $tbl . "(Id, " . $keys . ") VALUES('" . $newId ."','". $values . "')";
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
	//sales order baru
	public function testNewId()
	{
		$queryNew = "SET @newIdSo = ( SELECT IF((select count(Id) from masterso) > 0, 
						(
						SELECT CONCAT('MSO-',DATE_FORMAT(NOW(), '%y%m%d'), 
							LPAD(CONVERT((CONVERT(RIGHT(Id, 4), INT) + 1), VARCHAR(50)), 4, '0')
							) Id 
						FROM masterso
						WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE) 
						ORDER BY CreateDate DESC LIMIT 1
						), 
						(SELECT CONCAT('MSO-',DATE_FORMAT(NOW(), '%y%m%d'), '0001')) ) );";
		$this->db->query($queryNew);
		$query = "SET @newIdDso = ( SELECT IF((select count(Id) from detailso) > 0, 
					(
						SELECT (CONVERT(RIGHT(Id, 4), INT) + 1) Id 
						FROM detailso WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE) 
						ORDER BY CreateDate DESC LIMIT 1
					), 
					(SELECT 1 ) ) );";
		$this->db->query($query);
		$query = "DROP TABLE IF EXISTS tblInDSO;";
		$this->db->query($query);
		$query = "CREATE TEMPORARY TABLE tblInDSO(
					   Id VARCHAR(50), IdSo VARCHAR(50), IdProduct VARCHAR(50), Jml INT, Harga INT, Disc INT, 
						 SubTotal INT, OngkosKuli INT, CreateBy VARCHAR(50)
					); ";
		$this->db->query($query);
		$query = "INSERT INTO tblInDSO(Id, IdSo, IdProduct, Jml, Harga, Disc, SubTotal, OngkosKuli, CreateBy)
					VALUES
					( CONCAT('MSO-',DATE_FORMAT(NOW(), '%y%m%d'),
							LPAD(CONVERT(CONVERT('0', INT) + CONVERT(@newIdDso, INT), VARCHAR(50)), 4, '0')
						), 
						@newIdSo, 'idp', 1,2,3,4,5,'SA'),
					( CONCAT('MSO-',DATE_FORMAT(NOW(), '%y%m%d'),
							LPAD(CONVERT(CONVERT('1', INT) + CONVERT(@newIdDso, INT), VARCHAR(50)), 4, '0')
						), 
						@newIdSo, 'idp', 1,2,3,4,5,'SA'),
					( CONCAT('MSO-',DATE_FORMAT(NOW(), '%y%m%d'),
							LPAD(CONVERT(CONVERT('2', INT) + CONVERT(@newIdDso, INT), VARCHAR(50)), 4, '0')
						), 
						@newIdSo, 'idp', 1,2,3,4,5,'SA');";
		$this->db->query($query);
		$query = "SELECT * FROM tblInDSO";
		$resNew = $this->db->query($query)->result();
		echo json_encode($resNew);
	}
	
	public function insertSalesOrder()
	{
		$inserts = json_decode($this->input->post("inserts"), true);
		
		$newId = $this->GenId("detailso", "DSO");
		$keys = implode(",", array_keys($inserts));
		$values = implode("','", array_values($inserts));
		$query = "";
		$queryCheck = "SELECT Id, IdSo, IdProduct, Jml, Harga, Disc, SubTotal, OngkosKuli FROM detailso 
						WHERE CreateBy = '".$inserts["CreateBy"]."' AND IdProduct = '".$inserts["IdProduct"]."' 
							AND IFNULL(StatusBatal,0) = 0
							AND IFNULL(IdSo,'') = ''";
		
		$resCheck = $this->db->query($queryCheck)->row();
		
		if($resCheck)
		{
			$resJml = (int)$resCheck->Jml + (int)$inserts["Jml"];
			$resDisc = (int)$resCheck->Disc + (int)$inserts["Disc"];
			$resSubTotal = (int)$resCheck->SubTotal + (int)$inserts["SubTotal"];
			$resOngkosKuli = (int)$resCheck->OngkosKuli + (int)$inserts["OngkosKuli"];
			$query = "UPDATE detailso SET Jml = ".(string)$resJml." ,Disc = ".(string)$resDisc.", 
						SubTotal = ".(string)$resSubTotal.", OngkosKuli = ".(string)$resOngkosKuli."
						WHERE Id = '".$resCheck->Id."'";
			
		}
		else
		{
			$query = "INSERT INTO detailso(Id, " . $keys . ") VALUES('" . $newId ."','". $values . "')";
		}
		
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
	public function updateDb()
	{
		$tbl = $this->input->post("table");
		$updates = json_decode($this->input->post("updates"), true);
		$wheres = $this->input->post("wheres");
		$wheres = str_replace("%", "'", $wheres);
		
		$keys = array_keys($updates);
		$values = array_values($updates);
		
		$dataUpdate = "";
		for($xx = 0; $xx < count($keys); $xx++ )
		{
			$dataUpdate .= $keys[$xx] . " = '" . $values[$xx] . "',";
		}
		$dataUpdate = substr($dataUpdate, 0, -1);
		
		$query = "UPDATE " . $tbl . " SET " . $dataUpdate . " " . $wheres;
		
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
	public function deleteDb()
	{
		$tbl = $this->input->post("table");
		$wheres = $this->input->post("wheres");
		$wheres = str_replace("%", "'", $wheres);
		$query = "DELETE FROM " . $tbl . " " . $wheres;
		
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
	public function GetAllEmploye()
	{
		$query = "SELECT me.*, mj.Nama AS NamaJabatan
					FROM masteremploye AS me
					LEFT JOIN masterjabatan AS mj ON mj.Id = me.Jabatan";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function getProduct($params = "", $params2 = "")
	{
		$isApprove = "";
		$isBlock = "";
		if($params == "NOTAPPROVE")
		{
			$isApprove = " AND IFNULL(mp.ApproveBy,'') = '' ";
		}
		if($params == "APPROVE")
		{
			$isApprove = " AND IFNULL(mp.ApproveBy,'') != '' ";
		}
		if($params2 == "NOBLOCK")
		{
			$isBlock = " AND IFNULL(mp.IsBlock, '0') = '0' ";
		}
		$query = "SELECT ss.*, me.Username AS NamaCreate, mee.Username AS NamaApprove, mc.Nama AS NamaCV FROM (
					SELECT mp.Id, s.Id IdSupplier, s.NamaSupplier, mp.NameProduct, 
					mp.Harga, mp.Id AS ProdId, (IFNULL(mp.Stok, 0) - IFNULL(mp.StokRusak, 0)) Stok, mp.Satuan,
					mp.CreateBy, mp.CreateDate, mp.OngkosKuli, mp.ApproveBy, mp.ApproveDate, mp.CV, mp.Status,
					CASE IFNULL(mp.IsBlock, '0') WHEN '0' THEN '-' ELSE 'BLOCK' END StatusBlock
					FROM mastersupplier AS s 
					JOIN masterproduct AS mp ON mp.Supplier = s.Id 
					WHERE s.Aktif = '1' ". $isApprove ." ". $isBlock ."
				) ss 
				LEFT JOIN masteremploye AS me ON me.Id = ss.CreateBy
				LEFT JOIN masteremploye AS mee ON mee.Id = ss.ApproveBy
				LEFT JOIN mastercv AS mc ON mc.Id = ss.CV";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function getReportProduct()
	{
		$query = "SELECT rp.Id, rp.Jml, rp.SupplierId, rp.ProductId, rp.JmlRusak, rp.Harga, rp.CreateBy, rp.CreateDate, rp.ApproveBy, 
						rp.Keterangan, rp.ApproveDate, mp.NameProduct, ms.NamaSupplier, rp.JmlRusak
					FROM reportproduct AS rp 
					LEFT JOIN masterproduct AS mp ON mp.Id = rp.ProductId
					LEFT JOIN mastersupplier AS ms ON ms.Id = rp.SupplierId";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function getStock()
	{
		$query = "SELECT mp.Id , mp.Id ProductId, mp.NameProduct, mp.Satuan, mp.Harga, mp.Stok Jml, mp.StokRusak JmlRusak, mp.OngkosKuli, 
					IFNULL(CASE 
						WHEN rep.ApproveBy IS NULL AND mp.ApproveBy IS NOT NULL THEN rep.ApproveBy
						WHEN rep.ApproveBy IS NOT NULL AND mp.ApproveBy IS NULL THEN mp.ApproveBy
						WHEN rep.ApproveBy IS NOT NULL AND mp.ApproveBy IS NOT NULL THEN rep.ApproveBy
					END, '-') ApproveBy,
					CASE 
						WHEN IFNULL(rep.CreateBy,'') != '' THEN rep.CreateBy
						WHEN IFNULL(mp.CreateBy,'') != '' THEN mp.CreateBy
						ELSE '-'
					END CreateBy,
					CASE 
						WHEN IFNULL(rep.CreateDate,'') != '' THEN rep.CreateDate
						WHEN IFNULL(mp.CreateDate,'') != '' THEN mp.CreateDate
						ELSE '-'
					END CreateDate,
					IFNULL(CASE 
						WHEN rep.ApproveDate IS NULL AND mp.ApproveDate IS NOT NULL THEN rep.ApproveDate
						WHEN rep.ApproveDate IS NOT NULL AND mp.ApproveDate IS NULL THEN mp.ApproveDate
						WHEN rep.ApproveDate IS NOT NULL AND mp.ApproveDate IS NOT NULL THEN rep.ApproveDate
					END, '-') ApproveDate,
					ms.NamaSupplier, rep.Keterangan, ms.Id SupplierId
				FROM masterproduct AS mp
				LEFT JOIN mastersupplier AS ms ON ms.Id = mp.Supplier
				LEFT JOIN (
					SELECT * FROM (
						SELECT ROW_NUMBER() OVER (Partition By ProductId  ORDER BY CreateDate DESC) Urutan, Id, ProductId, 
							Keterangan, ApproveDate, CreateDate, ApproveBy, CreateBy
						FROM reportproduct
					) repProd 
					WHERE Urutan = 1
				) rep ON rep.ProductId = mp.Id
				WHERE IFNULL(Status,0) = 1 AND IFNULL(IsBlock,0) = 0
				ORDER BY ms.NamaSupplier ASC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function getStatusOrder()
	{
		$staff = $this->input->post("staff");
		$query = "SELECT mp.NameProduct, mp.Satuan, so.Id, so.IdSo, so.IdProduct, (so.Jml - IFNULL(ret.JmlEdit, 0)) Jml,
						so.Harga, so.Disc, ((so.Jml - IFNULL(ret.JmlEdit, 0)) * so.Harga) - so.Disc SubTotal, so.CreateBy, so.CreateDate,
						so.StatusBatal, so.BatalDate, so.OngkosKuli, so.BatalBy
					FROM detailso AS so
					LEFT JOIN masterproduct AS mp ON mp.Id = so.IdProduct
					LEFT JOIN (
						SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
					) ret ON ret.IdProduct = so.IdProduct AND ret.IdSo = so.IdSo
					WHERE so.CreateBy = '".$staff."' AND (so.Jml - IFNULL(ret.JmlEdit, 0)) > 0 
						AND IFNULL(so.StatusBatal,'') = '' AND IFNULL(so.IdSo, '') = ''";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function updateStatusOrder()
	{
		$data["json"] = $this->input->post("dataUpdate");
		$data["custId"] = $this->input->post("dataCustomer");
		$data["createBy"] = $this->input->post("createBy");
		$data["metodeBayar"] = $this->input->post("metodeBayar");
		$data["isBatal"] = $this->input->post("isBatal");
		$data["Pph"] = $this->input->post("pph");
		$data["IdSales"] = $this->input->post("idSales");
		$data["Dp"] = $this->input->post("Dp");
		$data["isBlocked"] = $this->input->post("isBlocked");
		
		$newId = $this->GenId('masterso', 'mso');
		if($data["isBatal"] == "true")
		{
			$queryInsertDO = "INSERT INTO masterso(Id, IdPelanggan, IdStaff, MetodeBayar, JatuhTempo, StatusBatal) 
							VALUES( '".$newId."', '".$data["custId"]."', '".$data["createBy"]."', '".$data["metodeBayar"]."', 
								(SELECT JatuhTempo FROM masterjenisbayar WHERE Id = '".$data["metodeBayar"]."'), '1'
							)";
		}
		else
		{
			$queryInsertDO = "INSERT INTO masterso(Id, IdPelanggan, IdStaff, MetodeBayar, JatuhTempo, Pph, IdSales, Dp, IsBlocked) 
							VALUES( '".$newId."', '".$data["custId"]."', '".$data["createBy"]."', '".$data["metodeBayar"]."', 
								(SELECT JatuhTempo FROM masterjenisbayar WHERE Id = '".$data["metodeBayar"]."'), '".$data["Pph"]."',
								'".$data["IdSales"]."', '".$data["Dp"]."', '".$data["isBlocked"]."'
							)";
			
		}
		
		//echo $queryInsertDO;
		$resInsertDO = $this->db->query($queryInsertDO);
		if($resInsertDO)
		{
			$dataArray = json_decode($data["json"]);
			$query = "UPDATE detailso s JOIN ( ";
			foreach($dataArray as $so)
			{
				$query .= " SELECT '".$so->Id."' as Id, '".$newId."' as IdSo, '".$so->Jml."' as Jml, 
							'".$so->StatusBatal."' as StatusBatal, '".$so->SubTotal."' as SubTotal ";
				if($so->StatusBatal == '1')
				{
					$query .= " , NOW() as BatalDate UNION ALL ";
				}
				else
				{
					$query .= " , NULL as BatalDate UNION ALL ";
				}
			}
			$query = substr($query, 0, -10);
			$query .= " ) vals ON s.Id = vals.Id
						SET s.IdSo = vals.IdSo, s.Jml = vals.Jml, s.StatusBatal = vals.StatusBatal, s.BatalDate = vals.BatalDate,
							s.SubTotal = vals.SubTotal";
			//echo $query;
			
			$resQuery = $this->db->query($query);
			if($resQuery) { echo "Success"; }
			else 
			{
				$queryFailed = "DELETE FROM masterso WHERE Id = '".$newId."'";
				$resFailed = $this->db->query($queryFailed);
				
				echo "Failed Insert Detail DO, Please Check Your Connection!"; 
			}
			
		}
		else
		{
			echo "Failed Insert DO, Please Check Your Connection!";
		}
		
	}
	//StatusOrder, OrderMasuk
	
	public function getProses()
	{
		$idStaff = $this->input->post("codeStaff");
		$query = "SELECT so.Id , so.IdPelanggan, mc.NamaPelanggan, mjb.JenisBayar, mjb.JatuhTempo, so.CreateDate,
						IFNULL(so.IsCetak, 0) IsCetak, so.Invoice,
						CASE 
							WHEN IFNULL(so.IsBlocked, '0') = '1' THEN 'BLOCKED'
							WHEN IFNULL(so.StatusBatal,'') = '' AND IFNULL(ApproveBy,'') = '' THEN 'KONFIRMASI'
							WHEN IFNULL(so.StatusBatal,'') != '' THEN 'BATAL'
							WHEN IFNULL(ApproveBy,'') != '' AND IFNULL(IsCetak, '') = '' && IFNULL(Invoice, '') = '' THEN 'APPROVED'
							WHEN IFNULL(IsCetak,'') != '' AND IFNULL(Invoice,'') = '' THEN 'DO'
							WHEN IFNULL(Invoice,'') != '' THEN 'TERINVOICE'
						END Status
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mc ON mc.Id = so.IdPelanggan
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					WHERE IFNULL(StatusBatal, '') = ''
					ORDER BY so.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//OrderMasuk
	public function detailProses()
	{
		$invoice = $this->input->post("invoice");
		$idStaff = $this->input->post("idStaff");
		$isAllCreateBy = "";
		if($idStaff != "*")
		{
			$isAllCreateBy = " so.CreateBy = '".$idStaff."' AND  "; 
		}
		
		$query = "SELECT mp.NameProduct, (so.Jml - IFNULL(ret.JmlEdit, 0)) Jml, so.Harga, so.Disc, 
					(((so.Jml - IFNULL(ret.JmlEdit, 0)) * so.Harga) - so.Disc) SubTotal, IFNULL(ms.Pph, 0) Pph, 
					IFNULL((((so.Jml - IFNULL(ret.JmlEdit, 0)) * so.Harga) - so.Disc)
					+
					(((so.Jml - IFNULL(ret.JmlEdit, 0)) * so.Harga) - so.Disc) * (IFNULL(ms.Pph, 0) / 100), 0) withPph
										
					FROM detailso AS so
					LEFT JOIN masterproduct AS mp ON mp.Id = so.IdProduct
					LEFT JOIN masterso AS ms ON ms.Id = so.IdSo
					LEFT JOIN (
						SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						WHERE rb.IdSo = '".$invoice."'
					) ret ON ret.IdProduct = so.IdProduct AND ret.IdSo = so.IdSo
					WHERE IFNULL(so.StatusBatal, 0) = 0
						AND so.IdSo = '".$invoice."'
						AND (so.Jml - IFNULL(ret.JmlEdit, 0)) > 0";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function getStaffDo()
	{
		$query = "SELECT m.Id, m.Username, m.Jabatan, Nama
					FROM masteremploye AS m
					LEFT JOIN masterjabatan AS mj ON mj.Id = m.Jabatan
					WHERE Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'DRIVER')";
		$resQuery["drivers"] = $this->db->query($query)->result();
		$queryInvoices = "SELECT so.Id, so.Invoice, mp.NamaPelanggan
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					WHERE so.Id NOT IN ( SELECT IdMso FROM detailpengiriman ) AND IFNULL(StatusBatal, '') = '' 
						AND IFNULL(so.Invoice, '') != ''";
		$resQuery["invoices"] = $this->db->query($queryInvoices)->result();
		$queryVeh = "SELECT mos.Id, mos.Nama, mos.Ongkos, mv.Jenis, mv.NoPol 
						FROM masterongkossopir AS mos
						LEFT JOIN mastervehicle AS mv ON mv.Id = mos.Keterangan
						WHERE mos.Aktif = 1";
		$resQuery["vehicles"] = $this->db->query($queryVeh)->result();
		echo json_encode($resQuery);
	}
	public function getPenagihan()
	{
		$idStaff = $this->input->post("codeStaff");
		$query = "SELECT mp.NamaPelanggan, me.Username Kolektor, mjb.JenisBayar, jso.JmlHarga, 
					CASE WHEN jso.Kekurangan <= 0 THEN 0 ELSE jso.Kekurangan END Kekurangan, so.Invoice,
						so.CreateDate, so.JatuhTempo, mpu.LastSj,
						st.StatusSelesai, IFNULL(lastCheck.CheckDate, '') CheckDate, IFNULL(lastCheck.IdKolektor, '') IdKolektor
						,IFNULL(lastCheck.KolektorDate, '') KolektorDate, mee.Username NamaSales, mee.Id IdSales
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdStaff
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					LEFT JOIN masteremploye AS mee ON mee.Id = so.IdSales
					LEFT JOIN (
						SELECT so.*, (CONVERT(so.JmlHarga, INT) - CONVERT(IFNULL(mp.Nominal, 0), INT)) Kekurangan
						FROM (
							SELECT mso.Invoice, ss.* 
							FROM masterso AS mso 
							JOIN 
							(
								SELECT MAX(dso.IdSo) IdSo,
									SUM(
									(((dso.Jml - IFNULL(ret.JmlEdit, 0) ) * dso.Harga ) - dso.Disc) 
									+
									(((dso.Jml - IFNULL(ret.JmlEdit, 0) ) * dso.Harga ) - dso.Disc) * (mso.Pph / 100) ) JmlHarga
								FROM detailso AS dso
								LEFT JOIN masterso AS mso ON mso.Id = dso.IdSo
								LEFT JOIN (
									SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
									FROM returnbarang AS rb
									LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
								) ret ON ret.IdSo = dso.IdSo AND ret.IdProduct = dso.IdProduct
								WHERE IFNULL(dso.StatusBatal,'') = '' AND IFNULL(mso.StatusBatal, '') = ''
									AND (dso.Jml - IFNULL(ret.JmlEdit, 0) ) > 0
								GROUP BY dso.IdSo 
							) AS ss ON ss.IdSo = mso.Id 
							WHERE IFNULL(mso.StatusBatal, '') = ''
						) so
						LEFT JOIN 
						(
							SELECT MAX(mpu.InvSo) InvSo, SUM(mpu.Nominal) Nominal
							FROM masterpiutang AS mpu
							WHERE IFNULL(mpu.CheckBy,'') != ''
							GROUP BY mpu.InvSo 
						) AS mp ON mp.InvSo = so.Invoice
					) AS jso ON jso.Invoice = so.Invoice 
					LEFT JOIN (
						SELECT xx.InvSo, xx.IdSj, mp.CreateDate LastSj
						FROM
						( 
							SELECT InvSo, MAX(Id) IdSj, CreateDate
							FROM mastersjkolektor 
							GROUP BY InvSo
							ORDER BY CreateDate DESC
						) xx
						LEFT JOIN mastersjkolektor AS mp ON mp.Id = xx.IdSj
					) mpu ON mpu.InvSo = so.Invoice
					LEFT JOIN (
						SELECT * FROM (
							SELECT InvSo, CheckDate, IdKolektor, KolektorDate,
								ROW_NUMBER() OVER (Partition By InvSo  ORDER BY CreateDate DESC) Urutan
							FROM masterpiutang 
						) ckDate WHERE urutan = 1
					) lastCheck ON lastCheck.InvSo = so.Invoice
					LEFT JOIN (
						SELECT so.Invoice, 'Belum Ada' StatusSelesai 
						FROM masterso AS so WHERE so.Invoice NOT IN( SELECT InvSo FROM masterpiutang )
							AND IFNULL(StatusBatal, '') = ''
												AND IFNULL(so.ApproveBy,'') != '' AND IFNULL(so.IsCetak,'') != '' 
												AND IFNULL(so.Invoice, '') != ''
						UNION ALL
						SELECT InvSo, CASE IFNULL(CheckDate,'') WHEN '' THEN 'Belum Selesai' 
							ELSE 'Selesai' END StatusSelesai  
						FROM (
							SELECT InvSo, CheckDate, ROW_NUMBER() OVER (Partition By InvSo  ORDER BY CreateDate DESC) Urutan
							FROM masterpiutang 
						) raw WHERE Urutan = 1
					) AS st ON st.Invoice = so.Invoice
					WHERE IFNULL(so.StatusBatal,'') = ''
						AND IFNULL(so.ApproveBy,'') != '' AND IFNULL(so.IsCetak,'') != '' 
						AND IFNULL(so.Invoice, '') != ''
					ORDER BY so.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function insertSj()
	{
		$data = array(
			"InvSo" => $this->input->post("InvSo"),
			"IdStaff" => $this->input->post("IdStaff"),
			"SisaBayar" => $this->input->post("SisaBayar"),
			"CreateBy" => $this->input->post("CreateBy")
		);
		$newId = $this->GenId('masterpiutang', 'mpu');
		
		$query = "INSERT INTO masterpiutang(Id, InvSo, CreateDate, CreateBy, IdKolektor)
						VALUES('".$newId."', '".$data["InvSo"]."', NOW(), '".$data["CreateBy"]."', '".$data["IdStaff"]."')";
		$resQuery = $this->db->query($query);
			
		
		if($resQuery)
		{
			$newId = $this->GenId('mastersjkolektor', 'msj');
			$queryInsertSj = "INSERT INTO mastersjkolektor(Id, InvSo, IdStaff, SisaBayar, CreateBy, CreateDate)
							VALUES('".$newId."', '".$data["InvSo"]."', '".$data["IdStaff"]."', '".$data["SisaBayar"]."',
							'".$data["CreateBy"]."', NOW())";
			$resInsertSj = $this->db->query($queryInsertSj);
			
			if($resInsertSj)
			{
				echo "Success";
			}
			else{
				echo "Failed";
			}
		}
		else {
			echo "Failed";
		}
	}
	public function GetSjToInvoice()
	{
		$query = "SELECT sj.Id, sj.IdKolektor, sj.SisaBayar, sj.IsCetak, sj.Keterangan, l.*
					FROM mastersjkolektor AS sj
					LEFT JOIN 
					(
						SELECT MAX(dso.IdSo) IdSo, dso.KodeSJ, MAX(nn.NamaPelanggan) NamaPelanggan, MAX(nn.Dp) Dp, MAX(nn.Username) Sales,
						MAX(JenisBayar) JenisBayar, MAX(JatuhTempo) JatuhTempo
						FROM detailso AS dso
						LEFT JOIN (
							SELECT mso.Id, mp.NamaPelanggan, mso.Dp, me.Username, mj.JenisBayar, mj.JatuhTempo
							FROM masterso AS mso
							LEFT JOIN masteremploye AS me ON me.Id = mso.IdSales 
							LEFT JOIN masterpelanggan AS mp ON mp.Id = mso.IdPelanggan
							LEFT JOIN masterjenisbayar AS mj ON mj.Id = mso.MetodeBayar
						) nn ON nn.Id = dso.IdSo
						WHERE IFNULL(dso.KodeSJ,'') != ''
						GROUP BY dso.KodeSj
					) l ON l.KodeSJ = sj.Id
					WHERE sj.Id NOT IN (SELECT IdSj FROM masterinvoice)";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function GetSjToday()
	{
		$data = $this->input->post("IdStaff");
		$query = "SELECT sj.Id, sj.InvSo, so.IdPelanggan, mp.NamaPelanggan, sj.SisaBayar, so.MetodeBayar, mb.JenisBayar, ss.IdKolektor Kosong
					FROM mastersjkolektor AS sj
					LEFT JOIN masterso AS so ON so.Invoice = sj.InvSo
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masterjenisbayar AS mb ON mb.Id = so.MetodeBayar
					LEFT JOIN (
						SELECT Id, InvSo, IdKolektor FROM masterpiutang WHERE IFNULL(IdKolektor,'') = ''
					) ss ON ss.Id = sj.Id
					WHERE sj.IdStaff = '".$data."' AND CONVERT(sj.CreateDate, DATE) = CURDATE() AND IFNULL(so.StatusBatal, '') = '' ";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function UpdatePembayaranKolektor()
	{
		$data = array(
			"invoice" => $this->input->post("invoice"),
			"idCustomer" => $this->input->post("idCustomer"),
			"SisaBayar" => $this->input->post("SisaBayar"),
			"staff" => $this->input->post("staff"),
			"Ttd" => $this->input->post("Ttd"),
			"Photo" => $this->input->post("Photo"),
			"IdMpu" => $this->input->post("IdMpu"),
		);
		
		$query = "UPDATE masterpiutang SET Nominal = '".$data["SisaBayar"]."', 
					IdKolektor = '".$data["staff"]."', KolektorDate = NOW(),
					Photo = '".$data["Photo"]."', Ttd = '".$data["Ttd"]."'
					WHERE Id = '".$data["IdMpu"]."'";
		
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
	public function UpdateStokFromApprove()
	{
		$inv = $this->input->post("invoice");
		$query = "UPDATE masterproduct AS mp JOIN (
						SELECT dso.IdProduct Id, (mp.Stok - dso.Jml) Stok
						FROM detailso AS dso
						LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
						WHERE dso.IdSo = '".$inv."'
						
					) vals ON vals.Id = mp.Id
					SET mp.Stok = vals.Stok";
		$resQuery = $this->db->query($query);
		if($resQuery){ echo "Success"; } else { echo "Failed"; }
	}
	public function GetSelectedAccess()
	{
		$rawData = $this->input->post("codeAccess");
		$data = str_replace("%", "'", $rawData);
		
		$query = "SELECT CASE IFNULL(ms.Id,'') WHEN '' THEN '' ELSE 'selected' END Status, me.* 
					FROM masteremploye AS me 
					LEFT JOIN (
						SELECT * FROM masteremploye WHERE Id IN ".$data."
					) ms ON ms.Id = me.Id";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
		
	}
	public function UpdateAccess()
	{
		$data = array(
			"Id" => $this->input->post("Id"),
			"Value" => $this->input->post("Value"),
		);
		$query = "UPDATE mastersidebar SET Access = '".$data["Value"]."' WHERE Id = '".$data["Id"]."'";
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
	public function GetApproveBayar()
	{
		$query = "SELECT mpu.Id, mpu.InvSo, mpu.Nominal, mp.NamaPelanggan, me.Username, mpu.CreateDate, so.IdPelanggan,
						mpu.Ttd, mpu.Photo, mpu.CreateDate
					FROM masterpiutang AS mpu 
					LEFT JOIN masterso AS so ON so.Invoice = mpu.InvSo
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = mpu.IdKolektor
					WHERE IFNULL(mpu.IdKolektor,'') != '' AND IFNULL(mpu.CheckBy,'') = '' AND IFNULL(so.StatusBatal, '') = '' 
					ORDER BY CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function SetApproveBayar()
	{
		$data = array(
			"CheckBy" => $this->input->post("IdStaff"),
			"IdPiutang" => $this->input->post("IdPiutang"),
			"IsBatal" => $this->input->post("IsBatal")
		);
		$query = "UPDATE masterpiutang SET CheckBy = '".$data["CheckBy"]."', CheckDate = NOW(), IsBatal = '".$data["IsBatal"]."'
					WHERE Id = '".$data["IdPiutang"]."'"; 
		
		$resQuery = $this->db->query($query);
		if($resQuery) { echo "Success"; } else { echo "Failed"; } 
	}
	//RetrunBarang 
	
	public function GetAllSoReturn()
	{
		$query = "SELECT so.Id, so.IdPelanggan, mp.NamaPelanggan, so.IdStaff CreateBy, me.Username NamaCreate, so.CreateDate, so.IdSales, mee.Username NamaSales
					FROM masterso AS so 
					LEFT JOIN masteremploye AS me ON me.Id = so.IdStaff
					LEFT JOIN masteremploye AS mee ON mee.Id = so.IdStaff
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					WHERE IFNULL(so.StatusBatal, 0) = 0 AND IFNULL(ApproveBy,'') != '' 
						AND IFNULL(so.Invoice,'') = '' AND IFNULL(so.IsBlocked, 0) = 0
						AND IFNULL(so.KeteranganSj, '') != ''
						AND so.Id NOT IN (SELECT DISTINCT(IdSo) IdSo FROM returnbarang)";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function GetAllSo() 
	{
		$query = "SELECT *, IFNULL(JmlBayar, 0) - IFNULL(SudahBayar, 0) AS KurangBayar FROM (
					SELECT so.Id, so.Invoice, mp.NamaPelanggan, me.Username Sales, mee.Username ApproveBy, mjb.JenisBayar, 
					dso.JmlBayar + ((dso.JmlBayar * so.Pph) / 100) JmlBayar, so.CreateDate, IFNULL(sby.SudahBayar, 0) SudahBayar, so.CreateCloseDate
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdStaff
					LEFT JOIN masteremploye AS mee ON mee.Id = so.ApproveBy
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					LEFT JOIN (	
						SELECT IdSo, SUM(SubTotal) JmlBayar FROM detailso
						GROUP BY IdSo
					) AS dso ON dso.IdSo = so.Id
					LEFT JOIN (
						SELECT InvSo AS Invoice, SUM(Nominal) SudahBayar FROM masterpiutang
						GROUP BY InvSo
					) sby ON sby.Invoice = so.Invoice
					WHERE IFNULL(so.Invoice,'') != '' AND IFNULL(so.StatusBatal, '') = '' AND IFNULL(ApproveBy, '') != ''
				) resTbl";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM NewReturnBarang
	
	public function GetDetailReturnBarang()
	{
		$data = array(
			"IdSo" => $this->input->post("IdSo")
		);
		$query = "SELECT dso.*, mp.NameProduct, mpe.Username, 0 JmlEdit, 0 HargaEdit, mp.Stok, mp.StokRusak, 0 JmlEditRusak
					FROM detailso AS dso
					LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
					LEFT JOIN masteremploye AS mpe ON mpe.Id = dso.CreateBy
					WHERE IdSo = '".$data["IdSo"]."' AND dso.Jml > 0";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);

	}
	public function updateInvoice()
	{
		$data = array(
			"Id" => $this->input->post("Id"),
			"InvoiceBy" => $this->input->post("InvoiceBy"),
		);
		$newInvoice = $this->GenInvoice();
		$query = "UPDATE masterso SET Invoice = '".$newInvoice."', TglInvoice = NOW(), InvoiceBy = '".$data["InvoiceBy"]."' 
				WHERE Id = '".$data["Id"]."'";
		$resQuery = $this->db->query($query);
		if($resQuery) { echo "Success"; } else { echo "Failed"; }
		
	}
	
	public function EditInvoice()
	{
		$newId = $this->GenId('masterso', 'mso');
		
		$data = $this->input->post("jsonData");
		$jsonDecode = json_decode($data, true);
		
		
		$flagDetail = false;
		
		$lenJson = count($jsonDecode);
		if($lenJson > 0 ) 
		{
			$queryBatal = "UPDATE  detailso SET StatusBatal = '1', BatalDate = NOW(), BatalBy = '".$jsonDecode[0]["CreateBy"]."'
							WHERE IdSo = '".$jsonDecode[0]["IdSo"]."'";
			$resBatal = $this->db->query($queryBatal);
			if($resBatal)
			{
				$queryDetail = "INSERT INTO detailso(Id, IdSo, IdProduct, Jml, Harga, Disc, SubTotal, CreateBy, CreateDate, OngkosKuli) VALUES ";
				$queryUpdateStok = "INSERT INTO masterproduct (Id,Stok, StokRusak) VALUES ";
				$count = 1;
				foreach($jsonDecode as $json)
				{
					$newIdDetail = $this->GenId('detailso', 'DSO');
					$newIdDetail .= "-" . $count;
					$queryDetail .= "('".$newIdDetail."','".$newId."','".$json["IdProduct"]."','".$json["JmlEdit"]."',
								'".$json["Harga"]."','".$json["Disc"]."','".(string)((int)$json["Harga"] * (int)$json["JmlEdit"])."','".$json["CreateBy"]."',
								NOW(), '".$json["OngkosKuli"]."' ),"; 
					$queryUpdateStok .= "( '".$json["IdProduct"]."', 
										( ".$json["Stok"]." + ".$json["Jml"].") - ".$json["JmlEdit"].",
										".$json["StokRusak"]." + ".$json["JmlEditRusak"]."
										),";
					
					$count++;
				}
				$queryDetail = substr($queryDetail, 0 , -1);
				$queryUpdateStok = substr($queryUpdateStok, 0 , -1);
				
				$queryUpdateStok .= " ON DUPLICATE KEY UPDATE Stok = VALUES(Stok), StokRusak = VALUES(StokRusak) ";
								
				$resStok = $this->db->query($queryUpdateStok);
				
				$resDetail = $this->db->query($queryDetail);
				if($resDetail)
				{
					$flagDetail = true;
				}
			}
		}
		if($flagDetail == true)
		{
			$queryGet = "SELECT * FROM masterso WHERE Id = '".$jsonDecode[0]["IdSo"]."'";
			$resQuery = $this->db->query($queryGet)->row();
			if($resQuery)
			{
				$queryBatal = "UPDATE masterso SET StatusBatal = '1', KeteranganBatal = '<EDIT>', BatalBy = '".$jsonDecode[0]["CreateBy"]."', BatalDate = NOW()
								WHERE Id = '".$jsonDecode[0]["IdSo"]."'";
				$resBatal = $this->db->query($queryBatal);
				if($resBatal)
				{
					$queryInsert = "INSERT INTO masterso(Id, IdPelanggan, IdStaff, CreateDate, MetodeBayar, JatuhTempo, Pph, Invoice, TglInvoice, IdSales, InvoiceBy)
								VALUES ('".$newId."', '".$resQuery->IdPelanggan."', '".$jsonDecode[0]["CreateBy"]."', NOW(), '".$resQuery->MetodeBayar."', 
									'".$resQuery->JatuhTempo."', '".$resQuery->Pph."', '".$resQuery->Invoice."', '".$resQuery->TglInvoice."', '".$resQuery->IdSales."', 
									'".$resQuery->InvoiceBy."' )";
					$resInsert = $this->db->query($queryInsert);
					if($resInsert)
					{
						echo "Success";
					}
					else
					{
						echo "Failed masterso";
					}
				}
			}
			else
			{
				echo "Data Kosong";
			}
		}
		else
		{
			echo "Failed detailso";
		}
		
	}
	//FROM NewReturnBarang
	public function InsertReturnBarang()
	{
		$data = $this->input->post("jsonData");
		$dataMaster = array(
			"IdSo" => $this->input->post("IdSo"),
			"IdPelanggan" => $this->input->post("IdPelanggan"),
			"StatusReturn" => $this->input->post("StatusReturn"),
			"KeteranganReturn" => $this->input->post("KeteranganReturn"),
			"CreateBy" => $this->input->post("CreateBy")
		);
		$getId = $this->GenId("returnbarang", "RET");
		$query = "INSERT INTO returnbarang(Id, IdSo, IdPelanggan, StatusReturn, Keterangan, CreateBy) 
					VALUES('".$getId."', '".$dataMaster["IdSo"]."', '".$dataMaster["IdPelanggan"]."', '".$dataMaster["StatusReturn"]."'
					, '".$dataMaster["KeteranganReturn"]."', '".$dataMaster["CreateBy"]."')";
		$resQuery = $this->db->query($query);
		if($resQuery) {
			
			$jsonDecode = json_decode($data, true);
			$query = "INSERT INTO detailreturnbarang(IdReturn, IdProduct, JmlEdit, JmlRusakEdit)
						VALUES";
			foreach($jsonDecode as $json)
			{
				$query .= "('".$getId."'," . "'".$json["IdProduct"]."'," . "'".$json["JmlEdit"]."'," . "'".$json["JmlEditRusak"]."' ),";  
			}
			$query = substr($query, 0 , -1);
			$resQuery = $this->db->query($query);
			if($resQuery) { echo "Success"; } else { echo "Failed"; }
			
		}
		else
		{
			echo "Failed Insert Return Barang";
		}
		
	}
	public function ContentDashboard()
	{
		$queryPelanggan = "SELECT mp.*, xx.Username AS NamaSales
							FROM masterpelanggan AS mp
							LEFT JOIN (
								SELECT Id, Username FROM masteremploye 
								WHERE Aktif = 1 AND Jabatan = ( SELECT Id FROM masterjabatan WHERE Nama = 'sales' )
							) xx ON xx.Id = mp.Sales 
							WHERE mp.Aktif = 1
							ORDER BY mp.CreateDate DESC";
		$results["customers"] = $this->db->query($queryPelanggan)->result();
		$queryKolektor = "SELECT SUM(mp.Nominal) Tagihan, mp.IdKolektor, MAX(me.Username) Username 
							FROM masterpiutang AS mp
							LEFT JOIN masteremploye AS me ON me.Id = mp.IdKolektor
							WHERE IFNULL(mp.IdKolektor,'') != '' && me.Aktif = 1
							GROUP BY mp.IdKolektor
							ORDER BY mp.KolektorDate";
		$results["colectors"] = $this->db->query($queryKolektor)->result();
		$queryPemasukan = "SELECT mp.InvSo, mp.Nominal, mp.CreateDate, mp.CreateBy, me.UserName AdminCreate, 
								mp.CheckBy, mee.UserName CheckedBy, mp.CheckDate, mp.IdKolektor, meee.Username Kolektor, 
								mp.KolektorDate
							FROM masterpiutang AS mp 
							LEFT JOIN masteremploye AS me ON me.Id = mp.Createby
							LEFT JOIN masteremploye AS mee ON mee.Id = mp.Createby
							LEFT JOIN masteremploye AS meee ON meee.Id = mp.Createby
							ORDER BY mp.CreateDate DESC";
		$results["transactions"] = $this->db->query($queryPemasukan)->result();
		$queryCard = "SELECT (
						SELECT IFNULL(SUM(Nominal), 0) NominalToday FROM masterpiutang 
						WHERE IFNULL(IdKolektor,'') != '' AND CONVERT(KolektorDate, DATE) = CURDATE() ) Today,
						( SELECT SUM(Nominal) NominalLastMont FROM masterpiutang 
						WHERE IFNULL(IdKolektor,'') != '' AND CONVERT(KolektorDate, DATE) BETWEEN CURDATE() - 30 AND CURDATE() ) LastMont ";
		$results["dataCards"] = $this->db->query($queryCard)->row();
		echo json_encode($results);
	}
	public function GetSalesPerson()
	{
		$query = "SELECT * 
					FROM masteremploye
					WHERE Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'SALES')";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function GetMasterPengiriman()
	{
		$query = "SELECT dp.Id, me.Username AS Sopir, mv.Jenis, mv.NoPol, mp.NamaPelanggan, mee.Username AS CreateSoBy, 
						mee.Username AS NamaSales, so.Invoice, so.TglInvoice
					FROM detailpengiriman AS dp
					LEFT JOIN masteremploye AS me ON me.Id = dp.Sopir
					LEFT JOIN mastervehicle AS mv ON mv.Id = dp.Kendaraan
					LEFT JOIN masterso AS so ON so.Id = dp.IdMso
					LEFT JOIN masteremploye AS mee ON mee.Id = so.IdStaff
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					/* WHERE IFNULL(IsCetak,0) <> 0 */";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function GetOngkosKuli()
	{
		$data = $this->input->post("IdMso");
		$data = str_replace(",", "','", $data);
		$query = "SELECT IFNULL(SUM((dso.Jml *  dso.OngkosKuli)), 0) ongkos
					FROM detailso AS dso
					WHERE IdSo IN (
						SELECT so.Id 
						FROM masterso AS so
						WHERE so.Id IN ('".$data."')
					) AND IFNULL(StatusBatal, '') = ''";
		$resQuery = $this->db->query($query)->row();
		echo json_encode($resQuery);
		
	}
	public function GetOngkosSopir()
	{
		$query = "SELECT me.Jenis, me.NoPol , mos.*
					FROM masterongkossopir AS mos
					LEFT JOIN mastervehicle AS me ON me.Id = mos.Keterangan
					WHERE mos.Aktif = 1 AND me.Aktif = 1";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	
	public function InsertDetailPengiriman()
	{
		$newId = $this->GenId("detailpengiriman", "CRG");
		$data = array(
			"IdMso" => $this->input->post("Invoices"),
			"Sopir" => $this->input->post("Sopir"),
			"Kendaraan" => $this->input->post("Kendaraan"),
			"Kuli" => $this->input->post("OngkosKuli"),
			"Akomodasi" => $this->input->post("OngkosAkomodasi"),
			"CreateBy" => $this->input->post("CreateBy"),
			"OngkosSopir" => $this->input->post("OngkosSopir"),
		);
		$query = "INSERT INTO detailpengiriman(Id, IdMso, Sopir, Kendaraan, OngkosKuli, OngkosAkomodasi, CreateBy, OngkosSopir)
					VALUES('".$newId."', '".$data["IdMso"]."', '".$data["Sopir"]."', '".$data["Kendaraan"]."', '".$data["Kuli"]."',
					'".$data["Akomodasi"]."', '".$data["CreateBy"]."', '".$data["OngkosSopir"]."')";
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
	public function GetOngkosPengiriman()
	{
		$query = "SELECT dp.Id, me.Username, mos.Nama, mv.Jenis, mv.NoPol, mee.Username AS CreateBy, dp.CreateDate,
						con.IdMso, con.NamaPelanggan, con.Phone, con.Alamat, con.Invoice
					FROM detailpengiriman AS dp
					LEFT JOIN masteremploye AS me ON me.Id = dp.Sopir
					LEFT JOIN masterongkossopir AS mos ON mos.Id = dp.Kendaraan
					LEFT JOIN mastervehicle AS mv ON mv.Id = mos.Keterangan
					LEFT JOIN masteremploye AS mee ON mee.Id = dp.CreateBy
					LEFT JOIN (
						SELECT dp.Id, so.Id AS IdMso, so.IdPelanggan, mp.NamaPelanggan, mp.Phone, mp.Alamat, so.Invoice
						FROM detailpengiriman AS dp
						LEFT JOIN masterso AS so ON dp.IdMso LIKE CONCAT('%',so.Id,'%')
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					) AS con ON con.Id = dp.Id
					ORDER BY dp.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function GetMasterCV()
	{
		$query = "SELECT cv.*, me.Username
					FROM mastercv AS cv
					LEFT JOIN masteremploye AS me ON me.Id = cv.CreateBy";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//tidak di pakai
	public function getDataInvoice()
	{
		$myInvoice = $this->input->post("Invoice");
		$myInvoice = "INV/20221229/0001";
		
		$query = "SELECT so.Invoice, mp.NamaPelanggan, mp.Alamat, me.Username CreateBy, mee.Username ApproveBy, 
						mjb.JenisBayar, emp.Username NamaSales, inv.Username CreateInvoiceBy, 
						so.CreateDate, so.ApproveDate, so.TglInvoice, (
							SELECT emp.Username 
							FROM detailpengiriman AS dp
							LEFT JOIN masteremploye AS emp ON emp.Id = dp.Sopir
							WHERE IdMso LIKE CONCAT('%', (SELECT Id FROM masterso WHERE Invoice = '".$myInvoice."' LIMIT 1),'%')
						) NamaSopir, NOW() AS curDate, so.Dp,
						DATE_ADD(CURRENT_DATE, INTERVAL CONVERT(mjb.JatuhTempo, INT) DAY) jatuhTempo
					FROM masterso AS so 
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdStaff
					LEFT JOIN masteremploye AS mee ON mee.Id = so.ApproveBy 
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					LEFT JOIN masteremploye AS emp ON emp.Id = so.IdSales 
					LEFT JOIN masteremploye AS inv ON inv.Id = so.IdSales
					WHERE so.Invoice = '".$myInvoice."' AND IFNULL(so.StatusBatal, '') = '' ";
		$resQuery["kop"] = $this->db->query($query)->row();
		$queryDetail = "SELECT mp.Id, mp.NameProduct, cv.Nama AS NamaCV, dso.Jml, dso.Harga Satuan, dso.Disc, dso.SubTotal Harga,
							((dso.Harga * dso.Jml) - dso.Disc) Total
						FROM detailso AS dso
						LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
						LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
						WHERE dso.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$myInvoice."') 
							AND IFNULL(StatusBatal,'') = ''";
		$resQuery["details"] = $this->db->query($queryDetail)->result();
	
		echo json_encode($resQuery);
	}
	public function updateInvoiceClose()
	{
		$data = array(
			"Invoice" => $this->input->post("Invoice"),
			"CreateCloseBy" => $this->input->post("CreateCloseBy")
		);
		$queryCheck = "SELECT rso.Invoice, IFNULL((rso.Total - IFNULL(rpu.Total, 0)), 0) AS SisaBayar
						FROM (
							SELECT so.Invoice, 
								((dso.Jml - IFNULL(ret.JmlEdit, 0)) * dso.Harga ) - dso.Disc Total
							FROM masterso AS so 
							LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
							LEFT JOIN (
								SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
								FROM returnbarang AS rb
								LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
								WHERE rb.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$data["Invoice"]."' LIMIT 1 )
							) ret ON ret.IdProduct = dso.IdProduct AND ret.IdSo = dso.IdSo
							WHERE so.Invoice = '".$data["Invoice"]."' AND IFNULL(so.StatusBatal, '') = ''
								AND (dso.Jml - IFNULL(ret.JmlEdit, 0)) > 0
							GROUP BY so.Invoice
						) rso 
						LEFT JOIN (
							SELECT InvSo AS Invoice, SUM(Nominal) AS Total 
							FROM masterpiutang 
							WHERE IFNULL(CheckDate,'') != '' AND InvSo = '".$data["Invoice"]."'
							GROUP BY InvSo
						) rpu ON rpu.Invoice = rso.Invoice";
		$resCheck = $this->db->query($queryCheck)->row();
		if($resCheck->SisaBayar != '0')
		{
			$queryUpdate = "UPDATE masterso SET IsClose = '1', CreateCloseBy = '".$data["CreateCloseBy"]."', CreateCloseDate = NOW()
							WHERE Invoice = '".$data["Invoice"]."' AND IFNULL(StatusBatal, '') = '' ";
			$resUpdate = $this->db->query($queryUpdate);
			if($resUpdate)
			{
				echo "Success";
			}
			else{
				echo "Gagal Update Data";
			}
		}
		else
		{
			echo "Masih Ada Sisa Pembayaran Sebesar ". $resCheck->SisaBayar;
		}
			
	}
	public function getCvSJ()
	{
		$mso = $this->input->post("mso");
		/*
		$query = "SELECT c.Id, MAX(c.Nama) Nama, MAX(d.Id) IdSo, MAX(d.CreateDate) CreateDate, MAX(d.IdSales) IdSales, MAX(d.UserName) Username,
						MAX(d.NamaPelanggan) NamaPelanggan, MAX(d.Alamat) Alamat, MAX(d.JatuhTempo) JatuhTempo, d.NamaSopir,
						e.*, CURDATE() curDate, DATE_ADD(CURDATE(), INTERVAL CONVERT(MAX(d.JatuhTempo), INT) DAY) tglJatuhTempo,
						MAX(d.JenisBayar) JenisBayar, MAX(d.IdPelanggan) IdPelanggan,
						(SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) Pph,
						MAX(d.KeteranganSj) KeteranganSJ, MAX(d.KeteranganInv) KeteranganInv
					FROM (
						SELECT cv.Id, cv.Nama 
						FROM mastercv AS cv 
						WHERE cv.Id IN (
							SELECT CV 
							FROM masterproduct 
							WHERE Id IN (
								SELECT IdProduct 
								FROM detailso 
								WHERE IdSo = '".$mso."'
								GROUP BY IdProduct 
							) 
							GROUP BY CV 
						)
					) c
					LEFT JOIN (
						SELECT so.Id, so.CreateDate, so.IdSales, me.Username,  mp.Id IdPelanggan, mp.NamaPelanggan,  mp.Alamat, sop.NamaSopir,
							so.JatuhTempo JatuhTempoSo, mjb.JenisBayar, mjb.JatuhTempo, dso.IdProduct, mt.CV, so.KeteranganSj, so.KeteranganInv
						FROM masterso AS so
						LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						LEFT JOIN masterproduct AS mt ON mt.Id = dso.IdProduct
						LEFT JOIN (
							SELECT mee.Username NamaSopir, mee.Id, dp.IdMSo
							FROM masteremploye AS mee
							JOIN detailpengiriman AS dp ON dp.Sopir = mee.Id
						) AS sop ON sop.IdMso = so.Id
						WHERE so.Id = '".$mso."' AND IFNULL(so.ApproveDate, '') != ''
							AND IFNULL(dso.BatalDate,'') = '' AND IFNULL(so.StatusBatal, '') = ''
					) AS d ON d.CV = c.Id
					LEFT JOIN (	
						SELECT CV, IdCV, NamaCv, Jml, 
							((SubTotal - Disc) * 
								((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100)
								) + (SubTotal - Disc) SubTotal, Disc FROM (
							SELECT mp.CV, cv.Id IdCV, cv.Nama NamaCv, SUM(dso.Jml) Jml, SUM(dso.SubTotal) SubTotal, SUM(dso.Disc) Disc
							FROM detailso AS dso
							LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
							LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
							WHERE IdSo = '".$mso."'
							GROUP BY cv.Id
						) ss
					) AS e ON e.IdCV = c.Id
					GROUP BY d.CV";
		*/
		$query = "SELECT c.Id, MAX(c.Nama) Nama, MAX(d.Id) IdSo, MAX(d.CreateDate) CreateDate, MAX(d.IdSales) IdSales, MAX(d.UserName) Username,
						MAX(d.NamaPelanggan) NamaPelanggan, MAX(d.Alamat) Alamat, MAX(d.JatuhTempo) JatuhTempo, d.NamaSopir,
						e.*, CURDATE() curDate, DATE_ADD(CURDATE(), INTERVAL CONVERT(MAX(d.JatuhTempo), INT) DAY) tglJatuhTempo,
						MAX(d.JenisBayar) JenisBayar, MAX(d.IdPelanggan) IdPelanggan,
						(SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) Pph,
						MAX(d.KeteranganSj) KeteranganSJ, MAX(d.KeteranganInv) KeteranganInv
					FROM (
						SELECT cv.Id, cv.Nama 
						FROM mastercv AS cv 
						WHERE cv.Id IN (
							SELECT CV 
							FROM masterproduct 
							WHERE Id IN (
								SELECT IdProduct 
								FROM detailso 
								WHERE IdSo = '".$mso."'
								GROUP BY IdProduct 
							) 
							GROUP BY CV 
						)
					) c
					LEFT JOIN (
						SELECT so.Id, so.CreateDate, so.IdSales, me.Username,  mp.Id IdPelanggan, mp.NamaPelanggan,  mp.Alamat, sop.NamaSopir,
							so.JatuhTempo JatuhTempoSo, mjb.JenisBayar, mjb.JatuhTempo, dso.IdProduct, mt.CV, so.KeteranganSj, so.KeteranganInv
						FROM masterso AS so
						LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						LEFT JOIN masterproduct AS mt ON mt.Id = dso.IdProduct
						LEFT JOIN (
							SELECT mee.Username NamaSopir, mee.Id, dp.IdMSo
							FROM masteremploye AS mee
							JOIN detailpengiriman AS dp ON dp.Sopir = mee.Id
						) AS sop ON sop.IdMso = so.Id
						WHERE so.Id = '".$mso."' AND IFNULL(so.ApproveDate, '') != ''
							AND IFNULL(dso.BatalDate,'') = '' AND IFNULL(so.StatusBatal, '') = ''
					) AS d ON d.CV = c.Id
					LEFT JOIN (	
						SELECT CV, IdCV, NamaCv, Jml, 
							((SubTotal - Disc) * 
								((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100)
								) + (SubTotal - Disc) SubTotal, Disc FROM (
							SELECT mp.CV, cv.Id IdCV, cv.Nama NamaCv, SUM(dso.Jml - ret.JmlEdit) Jml, 
								SUM(((dso.Jml - ret.JmlEdit) * dso.Harga ) - dso.Disc) SubTotal, SUM(dso.Disc) Disc
							FROM detailso AS dso
							LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
							LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
							LEFT JOIN (
								SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
								FROM returnbarang AS rb
								LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
								WHERE rb.IdSo = '".$mso."'
							) AS ret ON ret.IdProduct = mp.Id
							WHERE dso.IdSo = '".$mso."' AND (dso.Jml - ret.JmlEdit) > 0
							GROUP BY cv.Id
						) ss
					) AS e ON e.IdCV = c.Id
					WHERE e.Jml > 0
					GROUP BY d.CV";
		$resQuery["header"] = $this->db->query($query)->result();
		/*
		$resIsi = "SELECT mp.Id, mp.NameProduct, cv.Nama AS NamaCV, dso.Jml, dso.Harga Satuan, dso.Disc, dso.SubTotal Harga,
					((dso.Harga * dso.Jml) - dso.Disc) Total,
					(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * dso.Jml) - dso.Disc) ) Pph,
					(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * dso.Jml) - dso.Disc) ) + ((dso.Harga * dso.Jml) - dso.Disc) TotalPph
						FROM detailso AS dso
						LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
						LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
						WHERE dso.IdSo = '".$mso."'
							AND IFNULL(StatusBatal,'') = ''";
		*/
		$resIsi = "SELECT mp.Id, mp.NameProduct, cv.Nama AS NamaCV, (dso.Jml - ret.JmlEdit) Jml, 
						dso.Harga Satuan, dso.Disc, ((dso.Jml - ret.JmlEdit) * dso.Harga) Harga,
						((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) Total,
						(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) ) Pph,
						(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) ) + ((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) TotalPph
					FROM detailso AS dso
					LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
					LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
					LEFT JOIN (
						SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						WHERE rb.IdSo = '".$mso."'
					) ret ON ret.IdProduct = mp.Id
					WHERE dso.IdSo = '".$mso."'
						AND IFNULL(StatusBatal,'') = '' 
						AND (dso.Jml - ret.JmlEdit) > 0";
		$resQuery["details"] = $this->db->query($resIsi)->result();
		
		$owner = "SELECT me.Username, me.NamaBank, me.NoRek, mcv.Nama
					FROM masteremploye AS me
					LEFT JOIN mastercv AS mcv ON mcv.Id = me.IdCV
					WHERE me.Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'OWNER' ORDER BY CreateDate LIMIT 1)
						AND IFNULL(me.Aktif,0) = 1";
		$resQuery["owners"] = $this->db->query($owner)->result();
		echo json_encode($resQuery);
	}
	//from PalingBaruInvoice.vue
	public function getCvInvoice()
	{
		$invoice = $this->input->post("invoice");
		/*
		$query = "SELECT c.Id, MAX(c.Nama) Nama, MAX(d.Invoice) Invoice, MAX(d.CreateDate) CreateDate, MAX(d.IdSales) IdSales, MAX(d.UserName) Username,
						MAX(d.NamaPelanggan) NamaPelanggan, MAX(d.Alamat) Alamat, MAX(d.JatuhTempo) JatuhTempo, d.NamaSopir,
						e.*, CURDATE() curDate, DATE_ADD(CURDATE(), INTERVAL CONVERT(MAX(d.JatuhTempo), INT) DAY) tglJatuhTempo,
						MAX(d.JenisBayar) JenisBayar, MAX(d.IdPelanggan) IdPelanggan,
						(SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) Pph,
						MAX(d.KeteranganSj) KeteranganSJ, MAX(d.KeteranganInv) KeteranganInv, MAX(d.PotonganReturnOrSisa) PotonganReturnOrSisa
					FROM (
						SELECT cv.Id, cv.Nama 
						FROM mastercv AS cv 
						WHERE cv.Id IN (
							SELECT CV 
							FROM masterproduct 
							WHERE Id IN (
								SELECT IdProduct 
								FROM detailso 
								WHERE IdSo = (
									SELECT Id FROM masterso WHERE Invoice = '".$invoice."' AND IFNULL(StatusBatal, '') = '' LIMIT 1
								)
								GROUP BY IdProduct 
							) 
							GROUP BY CV 
						)
					) c
					LEFT JOIN (
						SELECT so.invoice, so.CreateDate, so.IdSales, me.Username,  mp.Id IdPelanggan, mp.NamaPelanggan,  mp.Alamat, sop.NamaSopir,
							so.JatuhTempo JatuhTempoSo, mjb.JenisBayar, mjb.JatuhTempo, dso.IdProduct, mt.CV, so.KeteranganSj, so.KeteranganInv,
							so.PotonganReturnOrSisa
						FROM masterso AS so
						LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						LEFT JOIN masterproduct AS mt ON mt.Id = dso.IdProduct
						LEFT JOIN (
							SELECT mee.Username NamaSopir, mee.Id, dp.IdMSo
							FROM masteremploye AS mee
							JOIN detailpengiriman AS dp ON dp.Sopir = mee.Id
						) AS sop ON sop.IdMso = so.Id
						WHERE so.Invoice = '".$invoice."' AND IFNULL(so.ApproveDate, '') != ''
							AND IFNULL(dso.BatalDate,'') = '' AND IFNULL(so.StatusBatal, '') = ''
					) AS d ON d.CV = c.Id
					LEFT JOIN (	
						SELECT CV, IdCV, NamaCv, Jml, 
							((SubTotal - Disc) * 
								((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100)
								) + (SubTotal) SubTotal, Disc FROM (
							SELECT mp.CV, cv.Id IdCV, cv.Nama NamaCv, SUM(dso.Jml) Jml, SUM(dso.SubTotal) SubTotal, SUM(dso.Disc) Disc
							FROM detailso AS dso
							LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
							LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
							WHERE IdSo = (
								SELECT Id FROM masterso WHERE Invoice = '".$invoice."' AND IFNULL(StatusBatal, '') = '' LIMIT 1
							) 
							GROUP BY cv.Id
						) ss
					) AS e ON e.IdCV = c.Id
					GROUP BY d.CV";
		*/
		$query = "SELECT c.Id, MAX(c.Nama) Nama, MAX(d.Invoice) Invoice, MAX(d.CreateDate) CreateDate, MAX(d.IdSales) IdSales, MAX(d.UserName) Username,
						MAX(d.NamaPelanggan) NamaPelanggan, MAX(d.Alamat) Alamat, MAX(d.JatuhTempo) JatuhTempo, d.NamaSopir,
						e.*, CURDATE() curDate, DATE_ADD(CURDATE(), INTERVAL CONVERT(MAX(d.JatuhTempo), INT) DAY) tglJatuhTempo,
						MAX(d.JenisBayar) JenisBayar, MAX(d.IdPelanggan) IdPelanggan,
						(SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) Pph,
						MAX(d.KeteranganSj) KeteranganSJ, MAX(d.KeteranganInv) KeteranganInv, MAX(d.PotonganReturnOrSisa) PotonganReturnOrSisa
					FROM (
						SELECT cv.Id, cv.Nama 
						FROM mastercv AS cv 
						WHERE cv.Id IN (
							SELECT CV 
							FROM masterproduct 
							WHERE Id IN (
								SELECT IdProduct 
								FROM detailso 
								WHERE IdSo = (
									SELECT Id FROM masterso WHERE Invoice = '".$invoice."' AND IFNULL(StatusBatal, '') = '' LIMIT 1
								)
								GROUP BY IdProduct 
							) 
							GROUP BY CV 
						)
					) c
					LEFT JOIN (
						SELECT so.invoice, so.CreateDate, so.IdSales, me.Username,  mp.Id IdPelanggan, mp.NamaPelanggan,  mp.Alamat, sop.NamaSopir,
							so.JatuhTempo JatuhTempoSo, mjb.JenisBayar, mjb.JatuhTempo, dso.IdProduct, mt.CV, so.KeteranganSj, so.KeteranganInv,
							so.PotonganReturnOrSisa
						FROM masterso AS so
						LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						LEFT JOIN masterproduct AS mt ON mt.Id = dso.IdProduct
						LEFT JOIN (
							SELECT mee.Username NamaSopir, mee.Id, dp.IdMSo
							FROM masteremploye AS mee
							JOIN detailpengiriman AS dp ON dp.Sopir = mee.Id
						) AS sop ON sop.IdMso = so.Id
						WHERE so.Invoice = '".$invoice."' AND IFNULL(so.ApproveDate, '') != ''
							AND IFNULL(dso.BatalDate,'') = '' AND IFNULL(so.StatusBatal, '') = ''
					) AS d ON d.CV = c.Id
					LEFT JOIN (	
						SELECT CV, IdCV, NamaCv, Jml, 
							((SubTotal - Disc) * 
								((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100)
								) + (SubTotal) SubTotal, Disc FROM (
							SELECT mp.CV, cv.Id IdCV, cv.Nama NamaCv, SUM(dso.Jml - ret.JmlEdit) Jml, 
								SUM(((dso.Jml - ret.JmlEdit) * dso.Harga) - dso.Disc ) SubTotal, SUM(dso.Disc) Disc
							FROM detailso AS dso
							LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
							LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
							LEFT JOIN (
								SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
								FROM returnbarang AS rb
								LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
								WHERE rb.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$invoice."' LIMIT 1 )
							) AS ret ON ret.IdProduct = mp.Id
							WHERE dso.IdSo = (
								SELECT Id FROM masterso WHERE Invoice = '".$invoice."' AND IFNULL(StatusBatal, '') = '' LIMIT 1
							) AND (dso.Jml - ret.JmlEdit) > 0
							GROUP BY cv.Id
						) ss
					) AS e ON e.IdCV = c.Id
					WHERE e.Jml IS NOT NULL
					GROUP BY d.CV";
		$resQuery["header"] = $this->db->query($query)->result();
		/*
		$resIsi = "SELECT mp.Id, mp.NameProduct, cv.Nama AS NamaCV, dso.Jml, dso.Harga Satuan, dso.Disc, (dso.Harga * dso.Jml)  Harga,
					((dso.Harga * dso.Jml) - dso.Disc) Total,
					(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * dso.Jml) - dso.Disc) ) Pph,
					(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * dso.Jml) - dso.Disc) ) + ((dso.Harga * dso.Jml) - dso.Disc) TotalPph
						FROM detailso AS dso
						LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
						LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
						WHERE dso.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$invoice."' AND IFNULL(StatusBatal, '') = '' ) 
							AND IFNULL(StatusBatal,'') = ''";
							*/
		$resIsi = "SELECT mp.Id, mp.NameProduct, cv.Nama AS NamaCV, (dso.Jml - ret.JmlEdit) Jml, 
					dso.Harga Satuan, dso.Disc, (dso.Harga * (dso.Jml - ret.JmlEdit))  Harga,
					((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) Total,
					(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) ) Pph,
					(((SELECT CONVERT(IFNULL(Pph, 0), FLOAT) Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1) / 100) * 
						((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) ) + 
							((dso.Harga * (dso.Jml - ret.JmlEdit)) - dso.Disc) TotalPph
					FROM detailso AS dso
					LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
					LEFT JOIN mastercv AS cv ON cv.Id = mp.CV
					LEFT JOIN (
						SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						WHERE rb.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$invoice."' LIMIT 1 )
					) AS ret ON ret.IdProduct = mp.Id
					WHERE dso.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$invoice."' AND IFNULL(StatusBatal, '') = '' ) 
						AND IFNULL(StatusBatal,'') = '' 
						AND (dso.Jml - ret.JmlEdit) > 0";
		$resQuery["details"] = $this->db->query($resIsi)->result();
		$owner = "SELECT me.Username, me.NamaBank, me.NoRek, mcv.Nama
					FROM masteremploye AS me
					LEFT JOIN mastercv AS mcv ON mcv.Id = me.IdCV
					WHERE me.Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'OWNER' ORDER BY CreateDate LIMIT 1)
						AND IFNULL(me.Aktif,0) = 1";
		$resQuery["owners"] = $this->db->query($owner)->result();
		echo json_encode($resQuery);
	}
	
	public function GetSisaBayar()
	{
		$invoice = $this->input->post("invoice");		
		$query = "SELECT mp.NamaPelanggan, (
							SELECT (( 
								SELECT  IFNULL(SUM((((( dso.Jml - IFNULL(ret.JmlEdit, 0) ) * dso.Harga ) - dso.Disc) * mso.Pph)
									+ ((( dso.Jml - IFNULL(ret.JmlEdit, 0) ) * dso.Harga ) - dso.Disc) ), 0)
								FROM detailso AS dso
								LEFT JOIN masterso AS mso ON mso.Id = dso.IdSo
								LEFT JOIN (
									SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
									FROM returnbarang AS rb
									LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
									WHERE rb.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$invoice."' LIMIT 1 )
								) AS ret ON ret.IdProduct = dso.IdProduct AND ret.IdSo = dso.IdSo
								WHERE dso.IdSo IN 
									( 
										SELECT Id FROM masterso 
										WHERE Invoice = '".$invoice."' 
									) AND (dso.Jml - IFNULL(ret.JmlEdit, 0)) > 0
								) - 
								IFNULL((SELECT SUM(IFNULL(Nominal,0)) SudahBayar FROM masterpiutang 
							WHERE InvSo = '".$invoice."' AND IFNULL(CheckBy,'') != '' ), 0) )
						) SisaBayar,  so.IdPelanggan,
						( SELECT Id FROM masterpiutang WHERE InvSo = '".$invoice."' ORDER BY CreateDate DESC LIMIT 1 ) IdMpu
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					WHERE so.Invoice = '".$invoice."' LIMIT 1";
		$resQuery = $this->db->query($query)->row(); 
		echo json_encode($resQuery);
	}
	public function ReportToko()
	{
		/*
		$data = array(
			"StartDate" => $this->input->post("StartDate"),
			"EndDate" => $this->input->post("EndDate"),
			"Customer" => $this->input->post("Customer"),
			"Sales" => $this->input->post("Sales"),
			"Product" => $this->input->post("Product"),
			
		);
		
		$query = "SELECT mp.Id IdPelanggan, mp.NamaPelanggan, me.Username NamaSales, mjb.JenisBayar, so.JatuhTempo, so.Invoice, 
						msp.NameProduct, dso.Jml, dso.Harga, dso.Disc, so.Pph, dso.SubTotal,
						so.CreateDate, CASE IFNULL(so.IsClose,'') WHEN '' THEN 'Belum Close' ELSE 'Sudah Close' END StatusClose,
						mee.Username CloseBy
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					LEFT JOIN masteremploye AS mee ON mee.Id = so.CreateCloseBy
					LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
					LEFT JOIN masterproduct AS msp ON msp.Id = dso.IdProduct
					WHERE IFNULL(so.StatusBatal,'') = '' AND IFNULL(so.ApproveDate,'') != ''
						AND IFNULL(so.Invoice,'') != '' AND CONVERT(so.TglInvoice, DATE) BETWEEN 
							CASE IFNULL('".$data["StartDate"]."', '') WHEN '' THEN CURRENT_DATE() 
								ELSE '".$data["StartDate"]."' END 
						AND CASE IFNULL('".$data["EndDate"]."', '') WHEN '' THEN CURRENT_DATE()
								ELSE '".$data["EndDate"]."' END
						AND me.Username LIKE '%".$data["Sales"]."%'
						AND mp.NamaPelanggan LIKE '%".$data["Customer"]."%'
						AND IFNULL(msp.ApproveBy, '') != '' AND msp.Status = '1'
						AND msp.NameProduct LIKE '%".$data["Product"]."%'";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
		*/
		
		$data = array(
			"StartDate" => $this->input->post("StartDate"),
			"EndDate" => $this->input->post("EndDate"),
			
		);
		$query = "SELECT IdPelanggan, NamaPelanggan, COUNT(IFNULL(Invoice, 0)) jmlInvoice, SUM(IFNULL(TotalHutang, 0)) TotalHutang, 
						SUM(IFNULL(SudahBayar, 0)) SudahBayar, 
						IFNULL(SUM( CASE IFNULL(SisaBayar, 0) WHEN 0 THEN TotalHutang ELSE SisaBayar END  ), 0) SisaBayar
					FROM (
						SELECT mp.Id IdPelanggan, mp.NamaPelanggan, me.Username NamaSales, mjb.JenisBayar, so.JatuhTempo, so.Invoice, 
							so.CreateDate, CASE IFNULL(so.IsClose,'') WHEN '' THEN 'Belum Close' ELSE 'Sudah Close' END StatusClose,
							mee.Username CloseBy, det.TotalBayar AS TotalHutang, kurang.TotalBayar AS SudahBayar,
							det.TotalBayar - kurang.TotalBayar AS Sisabayar
						FROM masterso AS so
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
						LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
						LEFT JOIN masteremploye AS mee ON mee.Id = so.CreateCloseBy
						LEFT JOIN (
							SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) TotalBayar 
							FROM detailso AS ds
							LEFT JOIN (
								SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
								FROM returnbarang AS rb
								LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
							) ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
							GROUP BY ds.IdSo
						) det ON det.IdSo = so.Id
						LEFT JOIN (
							SELECT InvSo, SUM(Nominal) TotalBayar FROM masterpiutang WHERE IFNULL(CheckDate,'') != '' GROUP BY InvSo
						) kurang ON kurang.InvSo = so.Invoice
						WHERE IFNULL(so.StatusBatal,'') = '' AND IFNULL(so.ApproveDate,'') != ''
							AND IFNULL(so.Invoice,'') != '' AND CONVERT(so.TglInvoice, DATE) BETWEEN '".$data["StartDate"]."' AND '".$data["EndDate"]."' 
							AND me.Username LIKE '%%'
							AND mp.NamaPelanggan LIKE '%%'
					) xx GROUP BY IdPelanggan";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
		
	}
	public function DetailReportToko($toko, $start, $end)
	{
		$query = "SELECT so.Id, so.IdPelanggan, mp.NamaPelanggan, me.Username CreateBy, mee.Username ApproveBy, 
					CONVERT(so.ApproveDate, DATE) ApproveDate, 
					mjs.JenisBayar, so.Invoice, CONVERT(so.TglInvoice, DATE) TglInvoice, so.JatuhTempo, 
					DATEDIFF(DATE_ADD(CONVERT(so.TglInvoice, DATE) , INTERVAL so.JatuhTempo DAY), CONVERT(NOW(), DATE)) tglJatuhTempo,
					mse.Username NamaSales, msee.Username InvoiceBy, so.Dp, 
					CASE 
						WHEN (ds.Total - IFNULL(sb.Nominal, 0)) <= 0 OR IFNULL(so.IsClose, 0) = 1 THEN 'Close'
						ELSE 'Progress' 
					END StatusClose , 
					ccb.Username CreateCloseBy, CONVERT(so.CreateCloseDate, DATE) CreateCloseDate,
					ds.SubTotal, ds.Disc, ds.Total, ds.OngkosKuli, IFNULL(sb.Nominal, 0) TelahBayar, 
					IFNULL(pb.Nominal, 0) TelahBayarBlmCheck, (ds.Total - IFNULL(sb.Nominal, 0)) KurangBayar
				FROM masterso AS so
				LEFT JOIN masteremploye AS me ON me.Id = so.IdStaff
				LEFT JOIN masteremploye AS mee ON mee.Id = so.ApproveBy
				LEFT JOIN masterjenisbayar AS mjs ON mjs.Id = so.MetodeBayar 
				LEFT JOIN masteremploye AS mse ON mse.Id = so.IdSales
				LEFT JOIN masteremploye AS msee ON msee.Id = so.InvoiceBy
				LEFT JOIN masteremploye AS ccb ON ccb.Id = so.CreateCloseBy
				LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
				LEFT JOIN (
					SELECT dso.IdSo, ((dso.Jml - IFNULL(ret.JmlEdit, 0)) * dso.Harga) SubTotal,  
						dso.Disc, (((dso.Jml - IFNULL(ret.JmlEdit, 0)) * dso.Harga) - dso.Disc) Total, 
						SUM(dso.OngkosKuli) OngkosKuli
					FROM detailso AS dso 
					LEFT JOIN (
						SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						WHERE rb.IdPelanggan = '".$toko."'
					) ret ON ret.IdProduct = dso.IdProduct AND dso.IdSo = ret.IdSo
					WHERE IFNULL(dso.StatusBatal,0) = 0
					GROUP BY dso.IdSo
				) ds ON ds.IdSo = so.Id
				LEFT JOIN (
					SELECT mp.InvSo, SUM(mp.Nominal) Nominal
					FROM masterpiutang AS mp
					WHERE IFNULL(CheckBy,'') != ''
					GROUP BY mp.InvSo
				) sb ON sb.InvSo = so.Invoice
				LEFT JOIN (
					SELECT mp.InvSo, SUM(mp.Nominal) Nominal
					FROM masterpiutang AS mp
					WHERE IFNULL(CheckBy,'') = ''
					GROUP BY mp.InvSo
				) pb ON pb.InvSo = so.Invoice
				WHERE IFNULL(StatusBatal, 0) = 0 AND IdPelanggan = '".$toko."'
					AND CONVERT(so.TglInvoice, DATE) BETWEEN 
						CASE IFNULL('".$start."', '') WHEN '' THEN CURRENT_DATE() 
							ELSE '".$start."' END 
					AND CASE IFNULL('".$end."', '') WHEN '' THEN CURRENT_DATE()
							ELSE '".$end."' END
				ORDER BY so.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function ReportSales()
	{
		$data = array(
			"StartDate" => $this->input->post("StartDate"),
			"EndDate" => $this->input->post("EndDate"),
			
		);
		$query = "SELECT IdSales, NamaSales, COUNT(IdPelanggan) JmlToko, COUNT(Invoice) JmlInvoice, SUM(TotalHutang) TotalHutang,
					SUM(SudahBayar) SudahBayar, 
					SUM( CASE IFNULL(SisaBayar, 0) WHEN 0 THEN TotalHutang ELSE SisaBayar END  ) SisaBayar FROM (
					SELECT mp.Id IdPelanggan, mp.NamaPelanggan, me.Id IdSales, me.Username NamaSales, mjb.JenisBayar, so.JatuhTempo, so.Invoice, 
						so.CreateDate, CASE IFNULL(so.IsClose,'') WHEN '' THEN 'Belum Close' ELSE 'Sudah Close' END StatusClose,
						mee.Username CloseBy, det.TotalBayar AS TotalHutang, IFNULL(kurang.TotalBayar, 0) AS SudahBayar,
						det.TotalBayar - kurang.TotalBayar AS Sisabayar
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					LEFT JOIN masteremploye AS mee ON mee.Id = so.CreateCloseBy
					LEFT JOIN (
						SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga) - ds.Disc)  TotalBayar 
						FROM detailso AS ds
						LEFT JOIN (
							SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
							FROM returnbarang AS rb
							LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
						GROUP BY ds.IdSo
					) det ON det.IdSo = so.Id
					LEFT JOIN (
						SELECT InvSo, SUM(Nominal) TotalBayar FROM masterpiutang WHERE IFNULL(CheckDate,'') != '' GROUP BY InvSo
					) kurang ON kurang.InvSo = so.Invoice
					WHERE IFNULL(so.StatusBatal,'') = '' AND IFNULL(so.ApproveDate,'') != '' 
						AND IFNULL(so.Invoice,'') != '' AND CONVERT(so.TglInvoice, DATE) BETWEEN 
								CASE IFNULL('".$data["StartDate"]."', '') WHEN '' THEN CURRENT_DATE() 
									ELSE '".$data["StartDate"]."' END 
							AND CASE IFNULL('".$data["EndDate"]."', '') WHEN '' THEN CURRENT_DATE()
									ELSE '".$data["EndDate"]."' END
				) xx 
				GROUP BY IdSales";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function DetailReportSales($sales, $start, $end)
	{
		$query = "SELECT so.Id, so.IdPelanggan, me.Username CreateBy, mee.Username ApproveBy, 
						CONVERT(so.ApproveDate, DATE) ApproveDate, 
						mjs.JenisBayar, so.Invoice, CONVERT(so.TglInvoice, DATE) TglInvoice, so.JatuhTempo, 
						DATEDIFF(DATE_ADD(CONVERT(so.TglInvoice, DATE) , INTERVAL so.JatuhTempo DAY), CONVERT(NOW(), DATE)) tglJatuhTempo,
						mse.Id IdSales, mse.Username NamaSales, msee.Username InvoiceBy, so.Dp, 
						CASE 
							WHEN (ds.Total - IFNULL(sb.Nominal, 0)) <= 0 OR IFNULL(so.IsClose, 0) = 1 THEN 'Close'
							ELSE 'Progress'  
						END StatusClose , 
						ccb.Username CreateCloseBy, CONVERT(so.CreateCloseDate, DATE) CreateCloseDate,
						ds.SubTotal, ds.Disc, ds.Total, ds.OngkosKuli, IFNULL(sb.Nominal, 0) TelahBayar, 
						IFNULL(pb.Nominal, 0) TelahBayarBlmCheck, (ds.Total - IFNULL(sb.Nominal, 0)) KurangBayar
					FROM masterso AS so
					LEFT JOIN masteremploye AS me ON me.Id = so.IdStaff
					LEFT JOIN masteremploye AS mee ON mee.Id = so.ApproveBy
					LEFT JOIN masterjenisbayar AS mjs ON mjs.Id = so.MetodeBayar 
					LEFT JOIN masteremploye AS mse ON mse.Id = so.IdSales
					LEFT JOIN masteremploye AS msee ON msee.Id = so.InvoiceBy
					LEFT JOIN masteremploye AS ccb ON ccb.Id = so.CreateCloseBy
					LEFT JOIN (
						SELECT dso.IdSo, ((dso.Jml - IFNULL(ret.JmlEdit, 0)) * dso.Harga) SubTotal, 
							dso.Disc, (((dso.Jml - IFNULL(ret.JmlEdit, 0)) * dso.Harga) - dso.Disc) Total, 
							SUM(dso.OngkosKuli) OngkosKuli
						FROM detailso AS dso 
						LEFT JOIN (
							SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
							FROM returnbarang AS rb
							LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						) AS ret ON ret.IdSo = dso.IdSo AND dso.IdProduct = ret.IdProduct
						WHERE IFNULL(dso.StatusBatal,0) = 0
						GROUP BY dso.IdSo
					) ds ON ds.IdSo = so.Id
					LEFT JOIN (
						SELECT mp.InvSo, SUM(mp.Nominal) Nominal
						FROM masterpiutang AS mp
						WHERE IFNULL(CheckBy,'') != ''
						GROUP BY mp.InvSo
					) sb ON sb.InvSo = so.Invoice
					LEFT JOIN (
						SELECT mp.InvSo, SUM(mp.Nominal) Nominal
						FROM masterpiutang AS mp
						WHERE IFNULL(CheckBy,'') = ''
						GROUP BY mp.InvSo
					) pb ON pb.InvSo = so.Invoice
					WHERE IFNULL(StatusBatal, 0) = 0 AND IdSales = '".$sales."'
						AND CONVERT(so.TglInvoice, DATE) BETWEEN 
							CASE IFNULL('".$start."', '') WHEN '' THEN CURRENT_DATE() 
								ELSE '".$start."' END 
						AND CASE IFNULL('".$end."', '') WHEN '' THEN CURRENT_DATE()
								ELSE '".$end."' END";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function checkDelMasterJabatan($id)
	{
		$query = "SELECT * FROM masteremploye WHERE Jabatan = '".$id."' AND IFNULL(Aktif,'0') = '1'";
		$resQuery = $this->db->query($query)->num_rows();
		if($resQuery)
		{
			echo $resQuery;
		}
		else
		{
			echo "0";
		}
	}
	public function checkDelMasterSupplier($id)
	{
		$query = "SELECT * FROM masterproduct WHERE Supplier = '".$id."' AND IFNULL(Status, '0' ) = '1'";
		$resQuery = $this->db->query($query)->num_rows();
		if($resQuery)
		{
			echo $resQuery;
		}
		else
		{
			echo "0";
		}
	}
	public function custIsBlocked($id)
	{
		/*
		$query = "SELECT COUNT(tglJatuhTempo) jmlJatuhTempo, 
						IFNULL(( SELECT IFNULL(LimitPlafon,0) LimitPlafon FROM masterpelanggan WHERE Id = '".$id."' ), 0) limitPlafon 
					FROM (
						SELECT DATEDIFF(DATE_ADD(CONVERT(so.TglInvoice, DATE) , INTERVAL so.JatuhTempo DAY), 
											CONVERT(NOW(), DATE)) tglJatuhTempo
						FROM masterso AS so
						WHERE IdPelanggan = '".$id."' AND IFNULL(so.StatusBatal, '0') = '0'
							AND IFNULL(Invoice,'') != ''
					) ss WHERE tglJatuhTempo < 0";
		*/
		$query = "SELECT COUNT(tglJatuhTempo) jmlJatuhTempo, IFNULL(( SELECT IFNULL(LimitPlafon,0) LimitPlafon FROM masterpelanggan WHERE Id = '".$id."' ), 0) limitPlafon 
					FROM (	
						SELECT so.Id, so.IdPelanggan, dso.Id IdDso, dso.KodeSJ, inv.Id Invoice, inv.JatuhTempo, inv.CreateDate,
							DATEDIFF(DATE_ADD(CONVERT(inv.CreateDate, DATE) , INTERVAL inv.JatuhTempo DAY), CONVERT(NOW(), DATE)) tglJatuhTempo
						FROM masterso AS so
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						LEFT JOIN masterinvoice AS inv ON inv.IdSJ = dso.Id
						WHERE so.IdPelanggan = '".$id."' AND IFNULL(so.StatusBatal, 0) = 0 
							AND IFNULL(inv.Id, '') != ''
					) ss WHERE tglJatuhTempo < 0";
		$resQuery = $this->db->query($query)->row();
		if($resQuery)
		{
			echo json_encode($resQuery);
		}
		else
		{
			echo "Failed";
		}
	}
	public function UpdateStokOpname()
	{
		$data = $this->input->post("jsonData");
		$jsonDecode = json_decode($data, true);
		
		$query = "INSERT INTO reportproduct (Id, ProductId, Jml, JmlRusak, Harga, TotalHarga, CreateBy, 
						Keterangan, SupplierId, JmlSebelumnya, JmlRusakSebelumnya)
					VALUES ";
		$count = 1;
		foreach($jsonDecode as $json)
		{
			$newId = $this->GenId("reportproduct", "REP");
			$newId .= $count;
			
			$query .= " ('".$newId."', '".$json["Id"]."', '".$json["JmlEdit"]."', '".$json["JmlEditRusak"]."', '".$json["Harga"]."',
						'".(int)$json["JmlEdit"] * (int)$json["Harga"]."', '".$json["CreateBy"]."', 
						'STOK-OPNAME ".$json["Keterangan"]."', '".$json["SupplierId"]."', '".$json["JmlSebelumnya"]."', 
						'".$json["JmlRusakSebelumnya"]."'),";
			
			$count++;
		}
		
		$query = substr($query , 0, -1);
		
		$resInsert = $this->db->query($query);
		if($resInsert)
		{
			echo "Success";
		}
		else
		{
			echo "Failed Stok Opname";
		}
	}
	public function GetAccStokOpname()
	{
		$query = "SELECT rp.Id, mp.Id IdProduct, mp.NameProduct, IFNULL(rp.Jml, 0) Jml, IFNULL(rp.JmlSebelumnya, 0) JmlSebelumnya, 
						IFNULL(rp.JmlRusak, 0) JmlRusak, IFNULL(rp.JmlRusakSebelumnya, 0) JmlRusakSebelumnya, 
						REPLACE(rp.Keterangan, 'STOK-OPNAME', '') Keterangan, me.Username CreateBy, rp.CreateDate
					FROM reportproduct AS rp
					LEFT JOIN masterproduct AS mp ON mp.Id = rp.ProductId
					LEFT JOIN masteremploye AS me ON me.Id = rp.CreateBy
					WHERE IFNULL(rp.ApproveBy, '') = ''";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function ACCtokOpname() //kurang edit di report product dan hilangkan update di master product atas
	{
		$data = $this->input->post("jsonData");
		$jsonDecode = json_decode($data, true);
		
		$queryUpdate = "UPDATE masterproduct mp JOIN ( ";
		$queryUpdateReport = "UPDATE reportproduct rp JOIN ( ";
		$count = 1;
		foreach($jsonDecode as $json)
		{
			$queryUpdate .= " SELECT '".$json["IdProduct"]."' Id, '".$json["JmlEdit"]."' Stok, '".$json["JmlEditRusak"]."' StokRusak
							  UNION ALL ";
			$queryUpdateReport .= " SELECT '".$json["Id"]."' Id, '".$json["ApproveBy"]."' ApproveBy, NOW() ApproveDate UNION ALL ";
			
			$count++;
			
		}
		
		$queryUpdate = substr($queryUpdate, 0, -10);
		$queryUpdateReport = substr($queryUpdateReport, 0, -10);
		$queryUpdate .= ") newVals ON newVals.Id = mp.Id SET mp.Stok = newVals.Stok, mp.StokRusak = newVals.StokRusak ";
		$queryUpdateReport .= ") vals ON vals.Id = rp.Id SET rp.ApproveBy = vals.ApproveBy, rp.ApproveDate = vals.ApproveDate ";
		
		$resUpdateStok = $this->db->query($queryUpdate);
		$resUpdateReport = $this->db->query($queryUpdateReport);
		if($resUpdateStok)
		{
			if($resUpdateReport)
			{
				echo "Success";
			}
			else
			{
				echo "Failed Update Report Product";
			}
		}
		else
		{
			echo "Failed Update Stok";
		}
		
	}
	public function insertProduct()
	{
		$data = array(
			"Id" => $this->input->post("Id"),
			"NameProduct" => $this->input->post("NameProduct"),
			"Supplier" => $this->input->post("Supplier"),
			"Satuan" => $this->input->post("Satuan"),
			"Harga" => $this->input->post("Harga"),
			"CreateBy" => $this->input->post("CreateBy"),
			"OngkosKuli" => $this->input->post("OngkosKuli"),
			"Cv" => $this->input->post("Cv"),
		);
		$queryCheck = "SELECT * FROM masterproduct WHERE Id = '".$data["Id"]."'";
		$resCheck = $this->db->query($queryCheck)->num_rows();
		if($resCheck > 0)
		{
			echo "Failed, Id Sudah Terpakai";
		}
		else
		{
			$query = "INSERT INTO masterproduct(Id, NameProduct, Supplier, Satuan, Harga, CreateBy, OngkosKuli, Cv)
						VALUES('".$data["Id"]."', '".$data["NameProduct"]."', '".$data["Supplier"]."', '".$data["Satuan"]."',
						'".$data["Harga"]."', '".$data["CreateBy"]."', '".$data["OngkosKuli"]."', '".$data["Cv"]."')";
			$resInsert = $this->db->query($query);
			if($resInsert)
			{
				echo "Success";
			}
			else
			{
				echo "Failed, Gagal Insert";
			}
		}
	}
	public function insertSupplier()
	{
		$data = array(
			"Id" => $this->input->post("Id"),
			"NamaSupplier" => $this->input->post("NamaSupplier"),
			"Alamat" => $this->input->post("Alamat"),
			"Phone" => $this->input->post("Phone"),
			"Aktif" => $this->input->post("Aktif"),
			"Email" => $this->input->post("Email"),
			"CreateBy" => $this->input->post("CreateBy"),
		);
		$queryCheck = "SELECT * FROM mastersupplier WHERE Id = '".$data["Id"]."'";
		$resCheck = $this->db->query($queryCheck)->num_rows();
		if($resCheck > 0)
		{
			echo "Failed, Id Sudah Terpakai";
		}
		else
		{
			$query = "INSERT INTO mastersupplier(Id, NamaSupplier, Alamat, Phone, Aktif, Email, CreateBy)
						VALUES('".$data["Id"]."', '".$data["NamaSupplier"]."', '".$data["Alamat"]."', '".$data["Phone"]."',
						'".$data["Aktif"]."', '".$data["Email"]."', '".$data["CreateBy"]."')";
			$resInsert = $this->db->query($query);
			if($resInsert)
			{
				echo "Success";
			}
			else
			{
				echo "Failed, Gagal Insert";
			}
		}
	}
	public function insertPelanggan()
	{
		$data = array(
			"NamaPelanggan" => $this->input->post("NamaPelanggan"),
			"Alamat" => $this->input->post("Alamat"),
			"Phone" => $this->input->post("Phone"),
			"Aktif" => $this->input->post("Aktif"),
			"Email" => $this->input->post("Email"),
			"CreateBy" => $this->input->post("CreateBy"),
			"NIK" => $this->input->post("NIK"),
			"TglLahir" => $this->input->post("TglLahir"),
			"Owner" => $this->input->post("Owner"),
			"LimitPlafon" => $this->input->post("LimitPlafon"),
		);
		$newId = $this->GenId("masterpelanggan", "CUST");
		$queryInsert = "INSERT INTO masterpelanggan(Id, NamaPelanggan, Alamat, Phone, Aktif, Email, CreateBy,
						NIK, TglLahir, Owner, LimitPlafon)
						VALUES('".$newId."', '".$data["NamaPelanggan"]."','".$data["Alamat"]."','".$data["Phone"]."',
						'".$data["Aktif"]."', '".$data["Email"]."', '".$data["CreateBy"]."', '".$data["NIK"]."', 
						'".$data["TglLahir"]."', '".$data["Owner"]."', '".$data["LimitPlafon"]."')";
						
		$resQuery = $this->db->query($queryInsert);
		if($resQuery)
		{
			echo "Success";
		}
		else
		{
			echo "Failed, Gagal Insert";
		}
	}
	
	
	public function HistoryProduct()
	{
		$data = array(
			"idProduct" => $this->input->post("idProduct"),
			"namaProduct" => $this->input->post("namaProduct"),
			"startDate" => $this->input->post("startDate"),
			"finishDate" => $this->input->post("finishDate"),
			"pelanggan" => $this->input->post("pelanggan"),
		);
		
		$query = "SELECT me.Username NamaSales, mp.NamaPelanggan, so.Invoice, so.CreateDate, 
						dso.IdProduct, prod.NameProduct, prod.Satuan, dso.Harga, dso.Disc, so.IdPelanggan, 
						so.IdSales
					FROM masterso AS so
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
					LEFT JOIN masterproduct AS prod ON prod.Id = dso.IdProduct
					WHERE IFNULL(so.StatusBatal, '0') = '0' AND IFNULL(so.Invoice,'') != '' AND IFNULL(so.IsCetak,'') != ''
						AND IFNULL(dso.StatusBatal, '0') = '0'
						AND CONVERT(so.CreateDate, DATE) BETWEEN '".$data["startDate"]."' AND '".$data["finishDate"]."'
						AND dso.IdProduct LIKE '%".$data["idProduct"]."%'
						AND prod.NameProduct LIKE '%".$data["namaProduct"]."%'
						AND mp.NamaPelanggan LIKE '%".$data["pelanggan"]."%'
					ORDER BY so.CreateDate DESC";
		$resQuery = $this->db->query($query)->result(); 
		echo json_encode($resQuery); 
	}
	//OrderMasuk
	public function updateKeteranganSJ()
	{
		$data = array(
			"idSo" => $this->input->post("idSo"),
			"keterangan" => $this->input->post("keterangan")
		);
		$query = "UPDATE masterso SET KeteranganSJ = '".$data["keterangan"]."' WHERE Id = '".$data["idSo"]."'";
		
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
	// FROM order masuk 
	public function updateKeteranganInv()
	{
		$data = array(
			"idSo" => $this->input->post("IdSo"),
			"keterangan" => $this->input->post("Keterangan"),
			"isCetak" => $this->input->post("IsCetak"),
			"tglCetak" => $this->input->post("TglCetak"),
			"cetakBy" => $this->input->post("CetakBy"),
			"PotonganReturnOrSisa" => $this->input->post("PotonganReturnOrSisa"),
		);
		$newInvoice = $this->GenInvoice();
		$queryInvoice = "UPDATE masterso SET Invoice = '".$newInvoice."', TglInvoice = NOW(), InvoiceBy = '".$data["cetakBy"]."',
						KeteranganInv = '".$data["keterangan"]."', IsCetak = '".$data["isCetak"]."', TglCetak = NOW(), 
						CetakBy = '".$data["cetakBy"]."',
						PotonganReturnOrSisa = '".$data["PotonganReturnOrSisa"]."'
						WHERE Id = '".$data["idSo"]."'";
		$resQueryInv = $this->db->query($queryInvoice);
		if($resQueryInv)
		{
			$queryUpdateStok = "UPDATE masterproduct AS mp JOIN (
						SELECT dso.IdProduct Id, (mp.Stok - dso.Jml) Stok
						FROM detailso AS dso
						LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
						WHERE dso.IdSo = '".$data["idSo"]."' AND (mp.Stok - dso.Jml) >= 0
						
					) vals ON vals.Id = mp.Id
					SET mp.Stok = vals.Stok";
			$resQueryUpdate = $this->db->query($queryUpdateStok);
			if($resQueryUpdate)
			{
				$myinvoice["invoice"] = $newInvoice;
				echo json_encode($myinvoice);
			}
			else
			{
				$myinvoice["invoice"] = "Failed";
				echo json_encode($myinvoice);
			}
		}
		/*
		$query = "UPDATE masterso SET KeteranganInv = '".$data["keterangan"]."' WHERE Invoice = '".$data["invoice"]."'";
		
		$resQuery = $this->db->query($query);
		if($resQuery)
		{
			echo "Success";
		}
		else
		{
			echo "Failed";
		}
		*/
	}
	public function HistoryPiutang()
	{
		$data = array(
			"idPelanggan" => $this->input->post("idPelanggan"),
			"pelanggan" => $this->input->post("pelanggan")
		);
		
		/*
		$query = "SELECT kred.IdPelanggan, kred.NamaPelanggan, kred.SubTotal AS Kredit, 
	IFNULL(temp.SubTotal, 0) BelumJt, IFNULL(temp.NominalBayar, 0) NominalBayarJt, IFNULL(temp.LebihBayar, 0) LebihBayarJt,
	IFNULL(temp2.SubTotal, 0) Umur30, IFNULL(temp2.NominalBayar, 0) NominalBayar30, IFNULL(temp2.LebihBayar, 0) LebihBayar30,
	IFNULL(temp3.SubTotal, 0) Umur60, IFNULL(temp3.NominalBayar, 0) NominalBayar60, IFNULL(temp3.LebihBayar, 0) LebihBayar60,
	IFNULL(temp4.SubTotal, 0) Umur90, IFNULL(temp4.NominalBayar, 0) NominalBayar90, IFNULL(temp4.LebihBayar, 0) LebihBayar90,
	IFNULL(temp5.SubTotal, 0) Umur120, IFNULL(temp5.NominalBayar, 0) NominalBayar120, IFNULL(temp5.LebihBayar, 0) LebihBayar120
FROM (
	SELECT mp.Id IdPelanggan, IFNULL(so.SubTotal, 0) SubTotal, mp.NamaPelanggan
	FROM masterpelanggan AS mp
	LEFT JOIN (
		SELECT so.IdPelanggan, SUM(dso.SubTotal) SubTotal 
		FROM masterso AS so
		LEFT JOIN (
			SELECT IdSo, SUM(SubTotal) SubTotal 
			FROM detailso 
			WHERE IFNULL(StatusBatal,0) = 0 AND IdSo IS NOT NULL
			GROUP BY IdSo
		) dso ON dso.IdSo = so.Id
		WHERE IFNULL(StatusBatal,0) = 0 AND Invoice IS NOT NULL
			AND IFNULL(IsCetak,0) != 0
		GROUP BY IdPelanggan
	) AS so ON mp.Id = so.IdPelanggan
	
) kred
LEFT JOIN (
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT IdSo, SUM(SubTotal) SubTotal
			FROM detailso 
			WHERE IFNULL(StatusBatal,0) = 0
			GROUP BY IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo) <= so.JatuhTempo
	) res GROUP BY IdPelanggan
) temp ON temp.IdPelanggan = kred.IdPelanggan
LEFT JOIN (
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT IdSo, SUM(SubTotal) SubTotal
			FROM detailso 
			WHERE IFNULL(StatusBatal,0) = 0
			GROUP BY IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND ( (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > so.JatuhTempo 
						AND 
						(DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) <= (so.JatuhTempo * 2)
					)
	) AS res GROUP BY IdPelanggan
) temp2 ON temp2.IdPelanggan = kred.IdPelanggan
LEFT JOIN (
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM ( 
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT IdSo, SUM(SubTotal) SubTotal
			FROM detailso 
			WHERE IFNULL(StatusBatal,0) = 0
			GROUP BY IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND ( (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > (so.JatuhTempo * 2) 
						AND 
						(DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) <= (so.JatuhTempo * 3)
					)
	) res GROUP BY IdPelanggan
) temp3 ON temp3.IdPelanggan = kred.IdPelanggan
LEFT JOIN (
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT IdSo, SUM(SubTotal) SubTotal
			FROM detailso 
			WHERE IFNULL(StatusBatal,0) = 0
			GROUP BY IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND ( (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > (so.JatuhTempo * 3) 
						AND 
						(DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) <= (so.JatuhTempo * 4)
					)
	) res GROUP BY IdPelanggan
) temp4 ON temp4.IdPelanggan = kred.IdPelanggan
LEFT JOIN (  
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0   
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar 
		FROM masterso AS so
		LEFT JOIN (
			SELECT IdSo, SUM(SubTotal) SubTotal
			FROM detailso 
			WHERE IFNULL(StatusBatal,0) = 0
			GROUP BY IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > (so.JatuhTempo * 4) 
	) res GROUP BY IdPelanggan
) temp5 ON temp5.IdPelanggan = kred.IdPelanggan
WHERE kred.IdPelanggan LIKE '%".$data["idPelanggan"]."%' AND kred.NamaPelanggan LIKE '%".$data["pelanggan"]."%'
ORDER BY kred.NamaPelanggan";
*/
		$query = "SELECT kred.IdPelanggan, kred.NamaPelanggan, kred.SubTotal AS Kredit, 
	IFNULL(temp.SubTotal, 0) BelumJt, IFNULL(temp.NominalBayar, 0) NominalBayarJt, IFNULL(temp.LebihBayar, 0) LebihBayarJt,
	IFNULL(temp2.SubTotal, 0) Umur30, IFNULL(temp2.NominalBayar, 0) NominalBayar30, IFNULL(temp2.LebihBayar, 0) LebihBayar30,
	IFNULL(temp3.SubTotal, 0) Umur60, IFNULL(temp3.NominalBayar, 0) NominalBayar60, IFNULL(temp3.LebihBayar, 0) LebihBayar60,
	IFNULL(temp4.SubTotal, 0) Umur90, IFNULL(temp4.NominalBayar, 0) NominalBayar90, IFNULL(temp4.LebihBayar, 0) LebihBayar90,
	IFNULL(temp5.SubTotal, 0) Umur120, IFNULL(temp5.NominalBayar, 0) NominalBayar120, IFNULL(temp5.LebihBayar, 0) LebihBayar120
FROM (
	/* TOTAL KREDIT */
	SELECT mp.Id IdPelanggan, IFNULL(so.SubTotal, 0) SubTotal, mp.NamaPelanggan
	FROM masterpelanggan AS mp
	LEFT JOIN (
		SELECT so.IdPelanggan, SUM(dso.SubTotal) SubTotal 
		FROM masterso AS so
		LEFT JOIN (
			SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) SubTotal 
			FROM detailso AS ds
			LEFT JOIN (
				SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
				FROM returnbarang AS rb
				LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
			) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
			WHERE IFNULL(ds.StatusBatal,0) = 0 AND ds.IdSo IS NOT NULL
			GROUP BY ds.IdSo
		) dso ON dso.IdSo = so.Id
		WHERE IFNULL(StatusBatal,0) = 0 AND Invoice IS NOT NULL
			AND IFNULL(IsCetak,0) != 0
		GROUP BY IdPelanggan
	) AS so ON mp.Id = so.IdPelanggan
	
) kred
LEFT JOIN (
	/* INVOICE BELUM JATUH TEMPO */
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) SubTotal 
			FROM detailso AS ds
			LEFT JOIN (
				SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
				FROM returnbarang AS rb
				LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
			) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
			WHERE IFNULL(ds.StatusBatal,0) = 0
			GROUP BY ds.IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo) <= so.JatuhTempo
	) res GROUP BY IdPelanggan
) temp ON temp.IdPelanggan = kred.IdPelanggan
LEFT JOIN (
	/* JATUH TEMPO 2X */ 
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) SubTotal 
			FROM detailso AS ds
			LEFT JOIN (
				SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
				FROM returnbarang AS rb
				LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
			) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
			WHERE IFNULL(ds.StatusBatal,0) = 0
			GROUP BY ds.IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND ( (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > so.JatuhTempo 
						AND 
						(DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) <= (so.JatuhTempo * 2)
					)
	) AS res GROUP BY IdPelanggan
) temp2 ON temp2.IdPelanggan = kred.IdPelanggan
LEFT JOIN (
	/* JATUH TEMPO 3X */
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM ( 
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) SubTotal 
			FROM detailso AS ds
			LEFT JOIN (
				SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
				FROM returnbarang AS rb
				LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
			) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
			WHERE IFNULL(ds.StatusBatal,0) = 0
			GROUP BY ds.IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND ( (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > (so.JatuhTempo * 2) 
						AND 
						(DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) <= (so.JatuhTempo * 3)
					)
	) res GROUP BY IdPelanggan
) temp3 ON temp3.IdPelanggan = kred.IdPelanggan
LEFT JOIN (
	/* JATUH TEMPO 4X */ 
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0 
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar
		FROM masterso AS so
		LEFT JOIN (
			SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) SubTotal 
			FROM detailso AS ds
			LEFT JOIN (
				SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
				FROM returnbarang AS rb
				LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
			) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
			WHERE IFNULL(ds.StatusBatal,0) = 0
			GROUP BY ds.IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND ( (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > (so.JatuhTempo * 3) 
						AND 
						(DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) <= (so.JatuhTempo * 4)
					)
	) res GROUP BY IdPelanggan
) temp4 ON temp4.IdPelanggan = kred.IdPelanggan
LEFT JOIN (  
	/* JATUH TEMPO >4X */ 
	SELECT IdPelanggan, SUM(SubTotal) SubTotal, SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar,
		SUM(LebihBayar) LebihBayar
	FROM (
		SELECT so.Id, so.Invoice, so.IdPelanggan, DATEDIFF(CURDATE(), 
			CONVERT(so.TglCetak, DATE)) - so.JatuhTempo Selisih, dso.SubTotal, IFNULL(mpu.NominalBayar, 0) NominalBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) >= 0 THEN (dso.SubTotal - mpu.NominalBayar)
				ELSE 0   
			END KurangBayar,
			CASE 
				WHEN (dso.SubTotal - mpu.NominalBayar) < 0 THEN (mpu.NominalBayar - dso.SubTotal)
				ELSE 0 
			END LebihBayar 
		FROM masterso AS so
		LEFT JOIN (
			SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) SubTotal 
			FROM detailso AS ds
			LEFT JOIN (
				SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
				FROM returnbarang AS rb
				LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
			) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
			WHERE IFNULL(ds.StatusBatal,0) = 0
			GROUP BY ds.IdSo
		) AS dso ON dso.IdSo = so.Id
		LEFT JOIN (
			SELECT InvSo, SUM(IFNULL(Nominal, 0)) NominalBayar
			FROM masterpiutang 
			WHERE IFNULL(IsBatal,0) = 0 AND CheckBy IS NOT NULL
			GROUP BY InvSo
		) AS mpu ON mpu.InvSo = so.Invoice
		WHERE IFNULL(so.StatusBatal,0) = 0 AND so.Invoice IS NOT NULL
			AND IFNULL(so.IsCetak,0) != 0 
			AND (DATEDIFF(CURDATE(), CONVERT(so.TglCetak, DATE)) - so.JatuhTempo ) > (so.JatuhTempo * 4) 
	) res GROUP BY IdPelanggan
) temp5 ON temp5.IdPelanggan = kred.IdPelanggan
WHERE kred.IdPelanggan LIKE '%".$data["idPelanggan"]."%' AND kred.NamaPelanggan LIKE '%".$data["pelanggan"]."%'
ORDER BY kred.NamaPelanggan";

		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	
	public function DetailSoByInvoice($invoice)
	{
		$invoice = str_replace("-", "/", $invoice);
		$query = "SELECT mp.Id IdProduct, mp.NameProduct, mp.Satuan, dso.Id, (dso.Jml - IFNULL(ret.JmlEdit, 0)) Jml, 
				dso.Harga, dso.Disc, (((dso.Jml - IFNULL(ret.JmlEdit, 0) ) * dso.Harga ) - dso.Disc ) Subtotal, 
				dso.CreateDate, me.Username
			FROM detailso AS dso
			LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
			LEFT JOIN masteremploye AS me ON me.Id = dso.CreateBy
			LEFT JOIN (
				SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
				FROM returnbarang AS rb
				LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
				WHERE rb.IdSo = ( SELECT Id FROM masterso WHERE Invoice = '".$invoice."' LIMIT 1 )
			) ret ON ret.IdProduct = mp.Id AND ret.IdSo = dso.IdSo
			WHERE dso.IdSo = (
				SELECT Id FROM masterso WHERE Invoice = '".$invoice."' AND IFNULL(StatusBatal,0) = 0 ) 
				AND (dso.Jml - IFNULL(ret.JmlEdit, 0)) > 0";
		$resQuery = $this->db->query($query)->result(); 
		echo json_encode($resQuery);
	}
	
	public function getKomisi()
	{
		$data = array(
			"startDate" => $this->input->post("tgl"),
			"range" => $this->input->post("hari")
		);
		$query = "SELECT *, DATEDIFF(TglCetak, DateClose) Hari FROM (
					SELECT so.Id, so.IdPelanggan, mp.NamaPelanggan, 
						CASE 
							WHEN so.TglCetak IS NOT NULL THEN so.TglCetak
							WHEN so.CreateDate IS NOT NULL THEN so.CreateDate
						END TglCetak, 
						so.Invoice, so.IdSales, me.Username NamaSales, so.Dp, u.Total TotalUtang, 
						b.NominalBayar, (b.NominalBayar - u.Total) LebihBayar, b.CreateDate DateClose
					FROM masterso AS so
					LEFT JOIN (
						SELECT ds.IdSo, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0)) * ds.Harga ) - ds.Disc) Total
						FROM detailso AS ds
						LEFT JOIN (
							SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
							FROM returnbarang AS rb
							LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
						WHERE IFNULL(ds.StatusBatal,0) = 0
						GROUP BY ds.IdSo
					) AS u ON u.IdSo = so.Id
					LEFT JOIN (
						SELECT InvSo, SUM(Nominal) NominalBayar, MAX(CreateDate) CreateDate
						FROM masterpiutang
						WHERE IFNULL(CheckBy, '') != '' AND (IFNULL(IsBatal,'') = '' OR IsBatal = '0')
						GROUP BY InvSo
					) AS b ON b.InvSo = so.Invoice
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					WHERE IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.ApproveBy,'') != ''
						AND IFNULL(so.IsBlocked,'0') = '0' AND IFNULL(so.Invoice,'') != ''
						AND b.NominalBayar >= u.Total
						AND CONVERT(b.CreateDate, DATE) BETWEEN DATE_SUB('".$data["startDate"]."', INTERVAL ".$data["range"]." DAY) AND '".$data["startDate"]."'
				) r " ;
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
		
	}
	
	//from menu ReportReturnBarang
	public function GetAllReturn()
	{
		$in = array(
			"startDate" => $this->input->post("startDate"),
			"endDate" => $this->input->post("endDate"),
			"idSo" => $this->input->post("idSo"),
		);
		$query = "SELECT rd.IdSo, so.IdPelanggan, mp.NamaPelanggan, so.IdStaff, so.CreateDate, so.IdSales, rd.StatusReturn, rd.IdProduct, 
						mpd.NameProduct, rd.CreateBy, me.Username NamaStaff, rd.CreateDate CreateDateReturn, rd.JmlEdit, 
						rd.JmlRusakEdit, rd.Harga, rd.TotalHarga
					FROM (
						SELECT rb.IdSo, rb.StatusReturn, rb.CreateBy, rb.CreateDate, drb.IdProduct,
							drb.JmlEdit, drb.JmlRusakEdit, dso.Harga, ((drb.JmlEdit + drb.JmlRusakEdit) * dso.Harga) TotalHarga
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						LEFT JOIN detailso AS dso ON dso.IdSo = rb.IdSo AND dso.IdProduct = drb.IdProduct
						WHERE IFNULL(dso.StatusBatal, 0) = 0
					) AS rd
					LEFT JOIN (
						SELECT * FROM masterso 
						WHERE IFNULL(StatusBatal,0) = 0 AND IFNULL(ApproveBy,'') != ''
						AND IFNULL(IsBlocked,'0') = '0'
					) AS so ON so.Id = rd.IdSo
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = rd.CreateBy
					LEFT JOIN masterproduct AS mpd ON mpd.Id = rd.IdProduct
					WHERE rd.CreateDate BETWEEN '".$in["startDate"]."' AND '".$in["endDate"]."' OR rd.IdSo LIKE '%".$in["idSo"]."%'
					ORDER BY so.IdPelanggan, rd.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	
	//from HistoryPiutang
	public function DetailAgingOutstanding($idPelanggan)
	{
		$queryHeader = "SELECT so.Id, so.IdStaff, me.Username CreateBy, so.CreateDate, 
							so.ApproveBy, mee.Username NameApprove, so.ApproveDate, so.Invoice, 
							so.IdSales, ms.Username NameSales, tot.Total, u.TotalBayar, mp.NamaPelanggan,
							so.IdPelanggan
						FROM masterso AS so
						LEFT JOIN (
							SELECT ds.IdSo, SUM(ds.SubTotal) SubTotal, SUM(((ds.Jml - IFNULL(ret.JmlEdit, 0) ) * ds.Harga ) - ds.Disc) Total
							FROM detailso AS ds
							LEFT JOIN (
								SELECT rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
								FROM returnbarang AS rb
								LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
								WHERE rb.IdPelanggan = '".$idPelanggan."'
							) AS ret ON ret.IdSo = ds.IdSo AND ret.IdProduct = ds.IdProduct
							WHERE IFNULL(ds.StatusBatal,0) = 0
							GROUP BY ds.IdSo
						) AS tot ON tot.IdSo = so.Id
						LEFT JOIN (
							SELECT InvSo, SUM(Nominal) TotalBayar 
							FROM masterpiutang
							WHERE IFNULL(CheckBy,'') != '' AND IFNULL(IsBatal,'') != '1' 
							GROUP BY InvSo
						) AS u ON u.InvSo = so.Invoice
						LEFT JOIN masteremploye AS me ON me.Id = so.IdStaff
						LEFT JOIN masteremploye AS mee ON mee.Id = so.ApproveBy
						LEFT JOIN masteremploye AS ms ON ms.Id = so.IdSales
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						WHERE so.IdPelanggan = '".$idPelanggan."' AND IFNULL(so.Invoice,'') != '' 
							AND IFNULL(so.StatusBatal, '') = '' AND IFNULL(so.IsBlocked,'0') = '0'
							AND IFNULL(so.IsBlocked,'0') = '0'";
		$resAging["header"] = $this->db->query($queryHeader)->result();
		
		$queryDetail = "SELECT so.IdPelanggan, mpu.* 
						FROM (
							SELECT mp.InvSo, mp.Nominal, mp.CreateDate, mp.CreateBy, me.Username CreateName,
								mp.CheckBy, mee.Username CheckName, 
								mp.CheckDate, mp.IdKolektor, mk.Username KolektorName, mp.KolektorDate
							FROM masterpiutang AS mp
							LEFT JOIN masteremploye AS me ON me.Id = mp.CreateBy
							LEFT JOIN masteremploye AS mee ON mee.Id = mp.CheckBy
							LEFT JOIN masteremploye AS mk ON mk.Id = mp.IdKolektor
							WHERE IFNULL(mp.CheckBy,'') != '' AND IFNULL(mp.IsBatal,'') != '1' 
						) mpu
						LEFT JOIN masterso AS so ON so.Invoice = mpu.InvSo
						WHERE so.IdPelanggan = '".$idPelanggan."'
						ORDER BY KolektorDate DESC";
		$resAging["detail"] = $this->db->query($queryDetail)->result();
		echo json_encode($resAging);
	}
	
	//batal do from OrderMasuk
	public function updateBatalDo()
	{
		$in = array(
			"IdSo" => $this->input->post("IdSo"),
			"StatusBatal" => $this->input->post("StatusBatal"),
			"KeteranganBatal" => $this->input->post("KeteranganBatal"),
			"BatalBy" => $this->input->post("BatalBy"),
			"BatalDate" => $this->input->post("BatalDate"),
		);
		$queryUpdateSo = "UPDATE masterso SET StatusBatal = 1, KeteranganBatal = '<BATAL>".$in["KeteranganBatal"]."',
							BatalBy = '".$in["BatalBy"]."', BatalDate = '".$in["BatalDate"]."'
							WHERE Id = '".$in["IdSo"]."'";
		$resSo = $this->db->query($queryUpdateSo);
		$queryUpdateDso = "UPDATE detailso SET StatusBatal = 1, BatalDate = '".$in["BatalDate"]."', BatalBy = '".$in["BatalBy"]."'
							WHERE IdSo = '".$in["IdSo"]."'";
		$resDso = $this->db->query($queryUpdateDso);
		if($resSo && $resDso)
		{
			echo "Success";
		}
		else
		{
			echo "Failed";
		}
	}
	// Batal invoice from MasterInvoice (return barang)
	public function updateBatalInv()
	{
		$in = array(
			"Invoice" => $this->input->post("Invoice"),
			"StatusBatal" => $this->input->post("StatusBatal"),
			"KeteranganBatal" => $this->input->post("KeteranganBatal"),
			"BatalBy" => $this->input->post("BatalBy"),
			"BatalDate" => $this->input->post("BatalDate"),
		);
		$queryUpdateSo = "UPDATE masterso SET StatusBatal = 1, KeteranganBatal = '<BATAL>".$in["KeteranganBatal"]."',
							BatalBy = '".$in["BatalBy"]."', BatalDate = '".$in["BatalDate"]."'
							WHERE Invoice = '".$in["Invoice"]."'";
		$resSo = $this->db->query($queryUpdateSo);
		if($resSo)
		{
			echo "Success";
		}
		else
		{
			echo "Failed";
		}
	}
	// Get Potongan Invoice dari return dan sisa bayar FROM Order Masuk
	public function getPotonganInvoice()
	{
		$in = array(
			"IdPelanggan" => $this->input->post("IdPelanggan"),
		);
		$query = "SELECT retTotal.IdPelanggan, SUM(retTotal.LebihBayar) LebihBayar FROM (
					/* Cari di lebih bayar */
					SELECT hutang.*, IFNULL(mpu.Nominal,0) SudahBayar, (IFNULL(mpu.Nominal,0) - hutang.TotalBayar) LebihBayar
					FROM (
						SELECT so.Id, so.IdPelanggan, so.Invoice, SUM(dso.SubTotal) TotalBayar
						FROM masterso AS so 
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						WHERE IFNULL(so.StatusBatal, '0') = '0' AND IFNULL(so.Invoice,'') != ''
								AND IFNULL(so.IsBlocked,'0') = '0' AND IFNULL(dso.StatusBatal,'0') = '0'
								AND dso.SubTotal > 0
						GROUP BY so.Id
					) hutang
					LEFT JOIN (
						SELECT InvSo, SUM(Nominal) Nominal
						FROM masterpiutang
						WHERE IFNULL(CheckBy,'') != '' AND IFNULL(IsBatal,'') != '1'
						GROUP BY InvSo
					) AS mpu ON mpu.InvSo = hutang.Invoice
					WHERE (IFNULL(mpu.Nominal,0) - hutang.TotalBayar) > 0 
						AND IdPelanggan = '".$in["IdPelanggan"]."' 

					UNION ALL
 
					/* dari return barang */
					SELECT rb.IdSo, rb.IdPelanggan, dsso.Invoice, 0 TotalBayar, 0 SudahBayar, 
						(drb.JmlEdit * (IFNULL(dsso.Harga, 0)) ) + (drb.JmlRusakEdit * (IFNULL(dsso.Harga, 0)) ) LebihBayar
					FROM returnbarang AS rb
					LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
					LEFT JOIN (
						SELECT so.Id, so.Invoice, so.IdPelanggan, dso.IdSo, dso.IdProduct, dso.Harga
						FROM masterso AS so
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						WHERE IFNULL(so.StatusBatal, '0') = '0' AND IFNULL(so.Invoice,'') != ''
								AND IFNULL(so.IsBlocked,'0') = '0' AND IFNULL(dso.StatusBatal,'0') = '0'
								AND so.IdPelanggan = '".$in["IdPelanggan"]."'
					) dsso ON dsso.Id = rb.IdSo AND dsso.IdProduct = drb.IdProduct
					WHERE rb.IdPelanggan = '".$in["IdPelanggan"]."'
					GROUP BY rb.IdSo

					) retTotal
					GROUP BY IdPelanggan";
		$resSisa = $this->db->query($query)->result();
		echo json_encode($resSisa);
	}
	
	
	
	//report batal from /ReportBatal
	public function ReportBatal()
	{
		$data = array(
			"StartDate" => $this->input->post("startDate"),
			"EndDate" => $this->input->post("endDate"),
			"IdSo" => $this->input->post("idSo"),
		);
		
		$query = "SELECT so.Id, so.IdPelanggan, so.BatalBy, so.BatalDate, REPLACE(so.KeteranganBatal, '<BATAL>', '') KeteranganBatal,
						IFNULL(ds.Subtotal, 0) SubTotal,
						CASE IFNULL(so.Invoice,'') WHEN '' THEN 'Batal SO' ELSE 'Batal DO' END BatalDari,
						mp.NamaPelanggan
					FROM masterso AS so
					LEFT JOIN 
					(
						SELECT so.Id, MAX(IdSo) IdSo, SUM(IFNULL(dso.SubTotal, 0)) Subtotal 
						FROM masterso AS so 
						LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
						WHERE so.KeteranganBatal LIKE '%<BATAL>%' AND IFNULL(dso.StatusBatal,0) = 0 AND dso.IdSo IS NOT NULL
						GROUP BY so.Id 
					) ds ON ds.IdSo = so.Id
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					WHERE so.KeteranganBatal LIKE '%<BATAL>%' AND ( so.BatalDate BETWEEN '".$data["StartDate"]."' AND '".$data["EndDate"]."'
						AND so.Id LIKE '%".$data["IdSo"]."%' )";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
		
	}
	public function DetailBatal ($idso)
	{
		$query = "SELECT dso.*, mp.NameProduct 
					FROM detailso AS dso
					LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
					WHERE IFNULL(dso.StatusBatal,0) = 0 AND dso.Jml > 0 AND dso.IdSo = '".$idso."'";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function NewSalesOrder()
	{
		$query = "SELECT * FROM masterpelanggan ORDER BY CreateDate DESC";
		$resQuery["customers"] = $this->db->query($query)->result();
		$querySales = "SELECT Id, Username FROM masteremploye WHERE Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'SALES') AND Aktif = 1";
		$resQuery["sales"] = $this->db->query($querySales)->result();
		$queryProduct = "SELECT *, Harga MinHarga FROM masterproduct WHERE IsBlock = 0 AND Status = 1";
		$resQuery["products"] = $this->db->query($queryProduct)->result();
		$queryBayar = "SELECT * FROM masterjenisbayar WHERE Aktif = 1";
		$resQuery["bayars"] = $this->db->query($queryBayar)->result();
		echo json_encode($resQuery);
	}
	
	
	
	
	
	public function masterReport()
	{
		$query = "SELECT Id, Nama FROM masterreport ORDER BY CreateDate DESC";
		$res = $this->db->query($query)->result();
		
		if($res)
		{
			echo json_encode($res);
		}
		else
		{
			echo "Failed";
		}
	}
	public function detailReport()
	{
		$query = "";
	}
}
