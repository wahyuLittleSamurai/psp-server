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
		$query = "select * from mastersidebar where Aktif = 1";
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
						SELECT Id  FROM masterinvoice WHERE CONVERT(CreateDate, DATE) = CURDATE() AND BatalDate = 0 ORDER BY CreateDate DESC LIMIT 1 
					) != '' , 
					(
						SELECT 
							CONCAT('INV/', date_format(curdate(), '%Y%m%d'), '/', LPAD(CONVERT( CONVERT(
							REPLACE(Id,CONCAT('INV/', date_format(curdate(), '%Y%m%d'), '/'), '')
							, INT) + 1, CHAR(100)), 4, 0) )
							Id
						FROM masterinvoice 
						WHERE CONVERT(CreateDate, DATE) = CURDATE()
							AND BatalDate = 0
						ORDER BY CreateDate DESC LIMIT 1
					), 
					CONCAT('INV/', date_format(curdate(), '%Y%m%d'), '/', LPAD('1',4,0) )
				) newId";
		$resQuery = $this->db->query($query)->row();
		return $resQuery->newId;
	}
	public function GenInvoiceOld()
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
	//FROM salesOrder check jika ada outstanding dari barang yg dikirim
	public function CheckOutStandingSO()
	{
		$data = array(
			"IdCust" => $this->input->post("IdCust"),
			"IdProduct" => $this->input->post("IdProduct"),
		);
		$query = "SELECT * FROM (
					SELECT dso.Id, dso.IdProduct, dso.Jml, dsj.JmlTerkirim, 
						CASE
							WHEN (IFNULL(dsj.JmlTerkirim,0) - IFNULL(ret.JmlRusakEdit, 0)) < dso.Jml 
								THEN (dso.Jml - (IFNULL(dsj.JmlTerkirim,0) - IFNULL(ret.JmlRusakEdit, 0)))
							ELSE 0
						END BlmKirim
					FROM detailso AS dso 
					LEFT JOIN masterso AS so ON so.Id = dso.IdSo
					LEFT JOIN mastersjkolektor AS sj ON sj.IdSo = so.Id
					LEFT JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dso.IdProduct
					LEFT JOIN 
					(
						SELECT r.IdSo IdSJ, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
						FROM returnbarang AS r 
						LEFT JOIN detailreturnbarang AS dr ON r.Id = dr.IdReturn
						WHERE IFNULL(dr.IdProduct,'') != '' 
					) AS ret ON ret.IdSJ = sj.Id AND ret.IdProduct = dso.IdProduct
					WHERE IFNULL(dso.StatusBatal,0) = 0 AND dso.Jml > 0 
						AND dso.IdSo IN (
							SELECT so.Id 
							FROM masterso AS so
							WHERE IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.IsBlocked,0) = 0
								AND IdPelanggan = '".$data["IdCust"]."'
						)
						AND dso.IdProduct = '".$data["IdProduct"]."' 
						AND (dso.Jml - IFNULL(dsj.JmlTerkirim,0)) > 0 
						AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.IsBlocked,0) = 0
				) rr WHERE rr.BlmKirim > 0";
		$resQuery = $this->db->query($query)->row();
		echo json_encode($resQuery);
	}
	//TEST SALES ORDER baru
	public function insertSalesOrderNew()
	{
		$data["products"] = $this->input->post("jsonData");
		$data["customer"] = $this->input->post("cust");
		
		
		$jsonDecodeProd = json_decode($data["products"], true);
		$jsonDecodeCust = json_decode($data["customer"], true);
		$keys = array_keys($jsonDecodeProd);
		$values = array_values($jsonDecodeProd);
		
		
		$insertData = "";
		$createTmpUpdate = "CREATE TEMPORARY TABLE tmpInput
								SELECT * FROM (";
		for($xx = 0; $xx < count($keys); $xx++ )
		{
			$createTmpUpdate .= "SELECT '".$values[$xx]["Id"]."' IdProduct, ".$values[$xx]["Jml"]." Jml UNION ALL ";
			$insertData .= "( CONCAT('DSO-',DATE_FORMAT(NOW(), '%y%m%d'),
								LPAD(CONVERT(CONVERT('".$xx."', INT) + CONVERT(@newIdDso, INT), VARCHAR(50)), 4, '0')
							), 
							@newIdSo, '".$values[$xx]["Id"]."', '".$values[$xx]["Jml"]."','".$values[$xx]["Harga"]."',
							'".$values[$xx]["Disc"]."','".$values[$xx]["SubTotal"]."','".$values[$xx]["OngkosKuli"]."',
							'".$values[$xx]["CreateBy"]."'),";
		}
		$createTmpUpdate = substr($createTmpUpdate, 0, -10);
		$createTmpUpdate .= " ) mm";
		$isSuccess = $this->db->query($createTmpUpdate);
		
		$insertData = substr($insertData, 0, -1);
		
		$queryNew = "SET @newIdSo = ( SELECT IF((select count(Id) from masterso WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE)) > 0, 
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
		$query = "SET @newIdDso = ( SELECT IF((select count(Id) from detailso WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE)) > 0, 
					(
						SELECT MAX(Id) Id FROM ( SELECT (CONVERT(RIGHT(Id, 4), INT) + 1) Id, CreateDate 
						FROM detailso WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE) 
						ORDER BY CreateDate DESC ) ss
					), 
					(SELECT 1 ) ) );";
		$this->db->query($query);
		$query = "CREATE TEMPORARY TABLE tblInDSO(
					   Id VARCHAR(50), IdSo VARCHAR(50), IdProduct VARCHAR(50), Jml INT, Harga INT, Disc INT, 
						 SubTotal INT, OngkosKuli INT, CreateBy VARCHAR(50)
					); ";
		$this->db->query($query);
		$query = "INSERT INTO tblInDSO(Id, IdSo, IdProduct, Jml, Harga, Disc, SubTotal, OngkosKuli, CreateBy)
					VALUES " . $insertData;
		$this->db->query($query);
		
		$query = "INSERT INTO detailso(Id, IdSo, IdProduct, Jml, Harga, Disc, SubTotal, OngkosKuli, CreateBy) 
					SELECT * FROM tblInDSO";
		$resData = $this->db->query($query);
		if($resData)
		{
			$queryMasterSO = "INSERT INTO masterso(Id, IdPelanggan, Dp, MetodeBayar, IsBLocked, CreateBy, IdSales, Pph, JatuhTempo) VALUES(
						@newIdSo , '".$jsonDecodeCust["Id"]."', '".$jsonDecodeCust["DP"]."', '".$jsonDecodeCust["JenisBayar"]."', 
						'".$jsonDecodeCust["IsBlocked"]."', '".$jsonDecodeCust["CreateBy"]."', '".$jsonDecodeCust["Sales"]."',
						(SELECT Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1), 
						(SELECT IFNULL(JatuhTempo, 0) JatuhTempo FROM masterjenisbayar WHERE Id = '".$jsonDecodeCust["JenisBayar"]."' ))";
			$resMaster = $this->db->query($queryMasterSO);
			if($resMaster)
			{
				if($isSuccess)
				{
					$createTmpUpdateDso = "CREATE TEMPORARY TABLE tmpUpdateDso
										SELECT res.IdDso, res.IdProduct, res.Jml, res.JmlTerkirim, res.BlmKirim, inp.Jml JmlInput,
											CASE 
												WHEN IFNULL(inp.Jml,0) >= IFNULL(res.BlmKirim,0) THEN 
													res.JmlTerkirim
												ELSE
													(IFNULL(res.BlmKirim,0) - IFNULL(inp.Jml,0)) + IFNULL(res.JmlTerkirim,0)
											END ValUpdate
										FROM (
											SELECT *, ROW_NUMBER() OVER (Partition By IdProduct  ORDER BY CreateDate DESC) Urutan FROM (
												SELECT so.Id, dso.Id IdDso, dso.IdProduct, dso.Jml, dsj.JmlTerkirim, ret.JmlEdit, ret.JmlRusakEdit,
													CASE
														WHEN (IFNULL(dsj.JmlTerkirim,0) - IFNULL(ret.JmlRusakEdit, 0)) < dso.Jml 
															THEN (dso.Jml - (IFNULL(dsj.JmlTerkirim,0) - IFNULL(ret.JmlRusakEdit, 0)))
														ELSE 0
													END BlmKirim, dso.CreateDate
												FROM masterso AS so
												LEFT JOIN mastersjkolektor AS sj ON sj.IdSo = so.Id
												LEFT JOIN detailso AS dso ON dso.IdSo = so.Id 
												LEFT JOIN 
												(
													SELECT IdDso, IdProduct, SUM(IFNULL(JmlTerkirim, 0)) JmlTerkirim FROM detailsjkolektor 
													GROUP BY IdDso, IdProduct
												) AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dso.IdProduct
												LEFT JOIN 
												(
													SELECT r.IdSo IdSJ, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
													FROM returnbarang AS r 
													LEFT JOIN detailreturnbarang AS dr ON r.Id = dr.IdReturn
													WHERE IFNULL(dr.IdProduct,'') != '' 
												) AS ret ON ret.IdSJ = sj.Id AND ret.IdProduct = dso.IdProduct
												WHERE so.IdPelanggan = '".$jsonDecodeCust["Id"]."' AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.IsBlocked,0) = 0
													AND dso.StatusBatal = 0 AND dso.Jml > 0 
													AND dso.IdProduct IN ( SELECT IdProduct FROM tmpInput )
											) rr WHERE rr.BlmKirim > 0
										) res 
										LEFT JOIN tmpInput AS inp ON inp.IdProduct = res.IdProduct
										WHERE res.urutan = 1";
					$resTmpUpdate = $this->db->query($createTmpUpdateDso);
					if($resTmpUpdate)
					{
						$getUpdate = "UPDATE detailso AS dso 
										JOIN tmpUpdateDso AS udso ON udso.IdProduct = dso.IdProduct AND udso.IdDso = dso.Id
										SET dso.Jml = udso.VAlUpdate;";
						$resUpdateDso = $this->db->query($getUpdate);
						if($resUpdateDso)
						{
							$queryInHistory = "INSERT INTO historyperpindahandso(Id, IdDsoLama, IdDsoBaru, IdProduct, JmlDsoLama, JmlBlmKirimLama, JmlDsoBaru, ValueUpdateDsoLama, CreateBy)
												SELECT CONCAT(db.IdDso, '/', inp.Id) Id, db.IdDso IdDsoLama, inp.Id IdDsoBaru, 
													db.IdProduct, db.Jml JmlDsoLama, db.Blmkirim JmlBlmKirimLama, inp.Jml JmlDsoBaru, db.ValUpdate ValueUpdateDsoLama, '".$jsonDecodeCust["CreateBy"]."'
												FROM tmpUpdateDso AS db
												LEFT JOIN tblInDSO AS inp ON inp.IdProduct = db.IdProduct";
							$getHistory = $this->db->query($queryInHistory);
							if($getHistory)
							{
								echo "Success";
							}
							else
							{
								echo "Failed Insert History";
							}
						}
						else
						{
							echo "Gagal Update Dso";
						}
					}
					else
					{
						echo "Gagal Create TMP TABLE";
					}
				}
				else
				{
					echo "Gagal Create TMP TABLE";
				}
			}
			else
			{
				echo "Failed";
			}
		}
		else
		{
			echo "Failed";
		}
		
		
	}
	//FROM sales order baru
	public function insertSalesOrder()
	{
		$data["products"] = $this->input->post("jsonData");
		$data["customer"] = $this->input->post("cust");
		//"{"Id":"CUST-2306270004","Username":"BERKAH JAYA SUMBERREJO","Phone":"081335430053","Email":"","Alamat":"SUMBERREJO (500M SELATAN PONDOK TALUN)","Owner":"NN","Sales":"SUGIONO"}"
		//"[{"Id":"PROD-230001","NameProduct":"ATAP GALVALUM KR-5 TBL 0.25 AURI UK 4M","Supplier":"SUP-230001","Satuan":"LMBR","Harga":"96000","CreateBy":"EMP-55E7B147297B6E1D6CB9C6F336CD69A1","CreateDate":"2023-02-17 15:41:54","Stok":"30","StokRusak":"0","OngkosKuli":350,"ApproveBy":"EMP-55E7B147297B6E1D6CB9C6F336CD69A1","ApproveDate":"2023-01-20 15:45:10","Status":"1","CV":"MCV-AC647D5EB841AC1CC92A46A0B736132E","IsBlock":"0","Jml":2,"Disc":0,"SubTotal":192000,"Diskon":0},{"Id":"PROD-230003","NameProduct":"ATAP GALVALUM KR-5 TBL 0.25 AURI UK 3M","Supplier":"SUP-230001","Satuan":"LEMBAR","Harga":"96000","CreateBy":"EMP-55E7B147297B6E1D6CB9C6F336CD69A1","CreateDate":"2023-02-17 16:05:42","Stok":"9","StokRusak":"4","OngkosKuli":"350","ApproveBy":"EMP-55E7B147297B6E1D6CB9C6F336CD69A1","ApproveDate":"2023-01-20 15:44:32","Status":"1","CV":"MCV-AC647D5EB841AC1CC92A46A0B736132E","IsBlock":"0","Jml":1,"Disc":0,"SubTotal":96000}]"
		
		$jsonDecodeProd = json_decode($data["products"], true);
		$jsonDecodeCust = json_decode($data["customer"], true);
		$keys = array_keys($jsonDecodeProd);
		$values = array_values($jsonDecodeProd);
		
		
		$insertData = "";
		for($xx = 0; $xx < count($keys); $xx++ )
		{
			$insertData .= "( CONCAT('DSO-',DATE_FORMAT(NOW(), '%y%m%d'),
								LPAD(CONVERT(CONVERT('".$xx."', INT) + CONVERT(@newIdDso, INT), VARCHAR(50)), 4, '0')
							), 
							@newIdSo, '".$values[$xx]["Id"]."', '".$values[$xx]["Jml"]."','".$values[$xx]["Harga"]."',
							'".$values[$xx]["Disc"]."','".$values[$xx]["SubTotal"]."','".$values[$xx]["OngkosKuli"]."',
							'".$values[$xx]["CreateBy"]."'),";
		}
		$insertData = substr($insertData, 0, -1);
		
		$queryNew = "SET @newIdSo = ( SELECT IF((select count(Id) from masterso WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE)) > 0, 
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
		$query = "SET @newIdDso = ( SELECT IF((select count(Id) from detailso WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE)) > 0, 
					(
						SELECT MAX(Id) Id FROM ( SELECT (CONVERT(RIGHT(Id, 4), INT) + 1) Id, CreateDate 
						FROM detailso WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE) 
						ORDER BY CreateDate DESC ) ss
					), 
					(SELECT 1 ) ) );";
		$this->db->query($query);
		$query = "DROP TEMPORARY TABLE IF EXISTS tblInDSO;";
		$this->db->query($query);
		$query = "CREATE TEMPORARY TABLE tblInDSO(
					   Id VARCHAR(50), IdSo VARCHAR(50), IdProduct VARCHAR(50), Jml INT, Harga INT, Disc INT, 
						 SubTotal INT, OngkosKuli INT, CreateBy VARCHAR(50)
					); ";
		$this->db->query($query);
		$query = "INSERT INTO tblInDSO(Id, IdSo, IdProduct, Jml, Harga, Disc, SubTotal, OngkosKuli, CreateBy)
					VALUES " . $insertData;
		$this->db->query($query);
		
		$query = "INSERT INTO detailso(Id, IdSo, IdProduct, Jml, Harga, Disc, SubTotal, OngkosKuli, CreateBy) 
					SELECT * FROM tblInDSO";
		$resData = $this->db->query($query);
		if($resData)
		{
			$queryMasterSO = "INSERT INTO masterso(Id, IdPelanggan, Dp, MetodeBayar, IsBLocked, CreateBy, IdSales, Pph, JatuhTempo) VALUES(
						@newIdSo , '".$jsonDecodeCust["Id"]."', '".$jsonDecodeCust["DP"]."', '".$jsonDecodeCust["JenisBayar"]."', 
						'".$jsonDecodeCust["IsBlocked"]."', '".$jsonDecodeCust["CreateBy"]."', '".$jsonDecodeCust["Sales"]."',
						(SELECT Pph FROM masterpph ORDER BY CreateDate DESC LIMIT 1), 
						(SELECT IFNULL(JatuhTempo, 0) JatuhTempo FROM masterjenisbayar WHERE Id = '".$jsonDecodeCust["JenisBayar"]."' ))";
			$resMaster = $this->db->query($queryMasterSO);
			if($resMaster)
			{
				echo "Success";
			}
			else
			{
				echo "Failed";
			}
		}
		
	}
	public function insertSalesOrderOld()
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
					mp.Harga, mp.Id AS ProdId, (IFNULL(mp.Stok, 0) - IFNULL(mp.StokRusak, 0)) Stok, mp.StokRusak, mp.Satuan,
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
		$query = "SELECT inv.Id Invoice, so.Id, so.IdPelanggan, mc.NamaPelanggan, mjb.JenisBayar, mjb.JatuhTempo, so.CreateDate, dso.SubTotal,
					CASE 
						WHEN IFNULL(so.IsBlocked, 0) = 1 THEN 'BLOCKED'
						WHEN IFNULL(so.StatusBatal,0) = 0 AND IFNULL(ApproveBy,'') = '' THEN 'KONFIRMASI'
						WHEN IFNULL(so.StatusBatal,0) != 0 THEN 'BATAL'
						WHEN IFNULL(ApproveBy,'') != '' THEN 'APPROVED'
						WHEN IFNULL(inv.Id,'') != '' THEN 'TERINVOICE'
					END Status,
					IFNULL(msj.Id, '') StatusInSj
				FROM masterso AS so
				LEFT JOIN masterpelanggan AS mc ON mc.Id = so.IdPelanggan
				LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
				LEFT JOIN (
					SELECT IdSo, (SUM(SubTotal) - SUM(Disc)) SubTotal
					FROM detailso
					WHERE StatusBatal = 0
					GROUP BY IdSo
				) dso ON dso.IdSo = so.Id
				LEFT JOIN (
					SELECT inv.Id, sj.Id SjId, sj.IdSo
					FROM masterinvoice AS inv 
					LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
					WHERE IFNULL(inv.BatalBy,'') = ''
				) inv ON inv.IdSo = so.Id
				LEFT JOIN mastersjkolektor AS msj ON msj.IdSo = so.Id
				WHERE IFNULL(so.StatusBatal,0) = 0
				ORDER BY so.CreateDate DESC
				;";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function getProsesOld()
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
		
		$query = "SELECT * FROM (
					SELECT so.Id, so.IdProduct, mp.NameProduct, mp.Stok,
						CASE 
							WHEN mp.Stok > (so.Jml - IFNULL(ret.JmlEdit, 0)) THEN (so.Jml - IFNULL(ret.JmlEdit, 0))
							ELSE mp.Stok
						END Jml, so.Harga, so.Disc, 
					(((so.Jml - IFNULL(ret.JmlEdit, 0)) * so.Harga) - so.Disc) SubTotal, IFNULL(ms.Pph, 0) Pph, 
					IFNULL((((so.Jml - IFNULL(ret.JmlEdit, 0)) * so.Harga) - so.Disc)
					+
					(((so.Jml - IFNULL(ret.JmlEdit, 0)) * so.Harga) - so.Disc) * (IFNULL(ms.Pph, 0) / 100), 0) withPph
										
					FROM detailso AS so
					LEFT JOIN masterproduct AS mp ON mp.Id = so.IdProduct
					LEFT JOIN masterso AS ms ON ms.Id = so.IdSo
					LEFT JOIN 
					(
						SELECT rb.Id, rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						WHERE rb.IdSo = (SELECT Id FROM mastersjkolektor WHERE IdSo = '".$invoice."' LIMIT 1)
					) ret ON ret.IdProduct = so.IdProduct 
					WHERE IFNULL(so.StatusBatal, 0) = 0
						AND so.IdSo = '".$invoice."'
						AND (so.Jml - IFNULL(ret.JmlEdit, 0)) > 0
					UNION ALL	
					SELECT '0001' Id, '0001' IdProduct, 'DP' NameProduct, 0 Stok, 1 Jml, Dp Harga, 0 Disc, Dp SubTotal, 0 Pph, Dp withPph
					FROM masterso WHERE Id = '".$invoice."'
				) res WHERE res.Jml > 0";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM MasterPengiriman
	public function getStaffDo()
	{
		$this->GetSoTerinvoice();
		$query = "SELECT m.Id, m.Username, m.Jabatan, Nama
					FROM masteremploye AS m
					LEFT JOIN masterjabatan AS mj ON mj.Id = m.Jabatan
					WHERE Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'DRIVER')";
		$resQuery["drivers"] = $this->db->query($query)->result();
		$queryInvoices = "SELECT inv.IdSo Id, inv.IdSJ Invoice, NamaPelanggan FROM (						
							SELECT MAX(IdPelanggan) IdPelanggan, IdSJ, IdSo
							FROM tempSoTerinvoice
							GROUP BY IdSo, IdSJ
						) inv 
						LEFT JOIN masterpelanggan AS me ON me.Id = inv.IdPelanggan
						WHERE inv.IdSJ NOT IN (SELECT IdMso FROM detailpengiriman)";	
		$resQuery["invoices"] = $this->db->query($queryInvoices)->result();
		$queryVeh = "SELECT mos.Id, mos.Nama, mos.Ongkos, mv.Jenis, mv.NoPol 
						FROM masterongkossopir AS mos
						LEFT JOIN mastervehicle AS mv ON mv.Id = mos.Keterangan
						WHERE mos.Aktif = 1";
		$resQuery["vehicles"] = $this->db->query($queryVeh)->result();
		echo json_encode($resQuery);
	}
	//FROM TagihanKolektor dan Pembayaran
	public function getPenagihanNew()
	{
		$this->GetSoTerinvoice();
		$query = "SELECT *,  
					CASE 
						WHEN KurangBayar = 0 THEN 'disabled'
						WHEN CreateDate IS NOT NULL AND CheckDate IS NULL THEN 'disabled'
						ELSE 'enabled'
					END StatusCreate,
					CASE 
						WHEN KurangBayar = 0 THEN 'disabled'
						WHEN KolektorDate IS NOT NULL AND CheckDate IS NULL THEN 'disabled'
						WHEN CreateDate IS NULL THEN 'disabled'
						WHEN CreateDate IS NOT NULL AND CheckDate IS NOT NULL AND CheckDate IS NOT NULL THEN 'disabled'
						ELSE 'enabled'
					END StatusBayar
				FROM (


					SELECT iv.*, byr.SudahBayar, 
						CASE 
							WHEN (iv.SubTotal - (IFNULL(iv.Dp,0) + IFNULL(byr.SudahBayar, 0))) > 0 THEN 
								iv.SubTotal - (IFNULL(iv.Dp,0) + IFNULL(byr.SudahBayar, 0))
							ELSE 0 
						END KurangBayar,
						lst.CreateDate, lst.KolektorDate, lst.CheckDate,
						mjb.JenisBayar, IFNULL(so.JatuhTempo, 0) JatuhTempo
					FROM
					( 
						SELECT inv.Invoice, MAX(inv.IdPelanggan) IdPelanggan, MAX(inv.NamaPelanggan) NamaPelanggan, SUM(inv.SubTotal) SubTotal,
							MAX(inv.Dp) Dp, MAX(IdSo) IdSo, MAX(inv.CreateDate) Tgl
						FROM tempSoTerinvoice AS inv
						GROUP BY Invoice
					) iv
					LEFT JOIN (
						#YG SUDAH BAYAR TIDAK BATAL, DAN SUDAH CHECK
						SELECT InvSo, SUM(Nominal) SudahBayar FROM masterpiutang 
						WHERE IFNULL(CheckBy,'') != '' AND ( IFNULL(IsBatal,'') = '' OR IFNULL(IsBatal,'0') = '0' )
						GROUP BY InvSo
					) byr ON byr.InvSo = iv.Invoice
					LEFT JOIN (			
						SELECT * FROM 
						(
							SELECT *, ROW_NUMBER() OVER (Partition By InvSo  ORDER BY CreateDate DESC) Urutan
							FROM masterpiutang 
							WHERE IFNULL(IsBatal,'') = '' OR IFNULL(IsBatal,'0') = '0' 
						) last 
						WHERE Urutan = 1
					) lst ON lst.InvSo = iv.Invoice
					LEFT JOIN masterso AS so ON so.Id = iv.IdSo
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					
				) res";
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
	//from OrderMasuk
	public function GetSjToInvoice()
	{
		$query = "SELECT sj.Id, sj.IdSo, sj.IdKolektor, sj.IsCetak, sj.Keterangan, me.Username Sales, mjb.JenisBayar, mjb.JatuhTempo, 
						mp.NamaPelanggan, so.Dp
					FROM mastersjkolektor AS sj
					LEFT JOIN masterso AS so ON so.Id = sj.IdSo
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					WHERE IFNULL(so.StatusBatal, 0) = 0 AND so.IsBLocked = 0 AND IFNULL(so.ApproveBy,'') != ''
						AND sj.Id NOT IN ( SELECT IdSJ FROM masterinvoice WHERE IFNULL(BatalBy,'') = '' )";
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
	//FROM TagihanKolektor dan Pembayaran
	public function InsertPembayaranKolektorTagihanKolektor()
	{
		$data = array(
			"invoice" => $this->input->post("invoice"),
			"idCustomer" => $this->input->post("idCustomer"),
			"SisaBayar" => $this->input->post("SisaBayar"),
			"staff" => $this->input->post("staff"),
			"Ttd" => $this->input->post("Ttd"),
			"Photo" => $this->input->post("Photo"),
		);
		$getId = "SELECT Id FROM masterpiutang
					WHERE InvSo = '".$data["invoice"]."' AND KolektorDate IS NULL AND (IFNULL(IsBatal,'') = '' OR IFNULL(IsBatal,'0') = '0')
						AND IFNULL(CheckBy,'') = '' 
					ORDER BY CreateDate DESC
					LIMIT 1";
		$resIdMpu = $this->db->query($getId)->row();
		
		if($resIdMpu)
		{		
			$query = "UPDATE masterpiutang SET Nominal = '".$data["SisaBayar"]."', Ttd = '".$data["Ttd"]."', Photo = '".$data["Photo"]."', IsBatal = '0',
						KolektorDate = NOW()
						WHERE Id = '".$resIdMpu->Id."'"; 
			$resQuery = $this->db->query($query);
			if($resQuery) { echo "Success"; } else { echo "Failed"; }
		}
		else
		{
			echo "Failed";
		}
	}
	//FROM HistoryPembayaran
	public function GetHistoryPembayaran()
	{
		$data = $this->input->post("Invoice");
		$query = "SELECT mpu.*, me.Username NamaKolektor, mee.Username AdminCheck
					FROM masterpiutang AS mpu
					LEFT JOIN masteremploye AS me ON me.Id = mpu.IdKolektor
					LEFT JOIN masteremploye AS mee ON mee.id = mpu.CheckBy
					WHERE mpu.InvSo = '".$data."' AND ( IFNULL(mpu.IsBatal,'') = '' OR IFNULL(mpu.IsBatal,'0') = '0' )
					ORDER BY mpu.CreateDate DESC";
		$resData = $this->db->query($query)->result();
		echo json_encode($resData);
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
	//FROM ApprovePembayaran
	public function GetApproveBayar()
	{
		$query = "SELECT mpu.*, iv.IdSJ, iv.IdPelanggan, iv.NamaPelanggan, iv.Username 
					FROM masterpiutang AS mpu 
					LEFT JOIN (
						SELECT inv.Id, inv.IdSJ, so.IdPelanggan, mp.NamaPelanggan, me.Username 
						FROM masterinvoice AS inv 
						LEFT JOIN mastersjkolektor AS mj ON mj.Id = inv.IdSJ
						LEFT JOIN masterso AS so ON so.Id = mj.IdSo
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						LEFT JOIN masteremploye AS me ON me.id = mj.IdKolektor
						WHERE IFNULL(inv.BatalBy,'') = '' AND IFNULL(so.BatalBy,'') = '' AND IFNULL(so.IsBlocked,0) = 0
					) iv ON iv.Id = mpu.InvSo
					WHERE (IFNULL(mpu.IsBatal,'') = '' OR IFNULL(mpu.IsBatal,'0') = '0') AND IFNULL(mpu.CheckBy,'') = ''
					ORDER BY mpu.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM ApprovePembayaran
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
	//ReturnBarang ASLI
	public function GetAllSoReturnBarang()
	{
		$query = "SELECT sj.Id, so.IdPelanggan, mp.NamaPelanggan, so.IdSales, me.Username NamaSales, sj.CreateDate, sj.CreateBy
					FROM mastersjkolektor AS sj
					LEFT JOIN masterso AS so ON so.Id = sj.IdSo
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					WHERE sj.Id NOT IN (SELECT IdSJ FROM masterinvoice WHERE IFNULL(BatalBy,'') = '')
						AND sj.Id NOT IN (SELECT IdSo FROM returnbarang)
						AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.IsBlocked,0) = 0";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//NewReturnBarang
	public function GetAllSoReturn()
	{
		$query = "SELECT inv.*, mp.Id IdPelanggan, mp.NamaPelanggan, so.IdSales, me.Username NamaSales, mee.Username CreateInvBy
					FROM masterinvoice AS inv
					LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
					LEFT JOIN masterso AS so ON so.Id = sj.IdSo
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					LEFT JOIN masteremploye AS mee ON mee.Id = inv.CreateBy
					WHERE IFNULL(inv.BatalBy,'') = '' AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.IsBlocked,0) = 0
					ORDER BY inv.CreateDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function GetAllSoReturnOld()
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
	//GET FROM MasterInvoice [ReturnBarang]
	public function GetAllSo() 
	{
		/*
		$query = "SELECT *, IFNULL(JmlBayar, 0) - IFNULL(SudahBayar, 0) AS KurangBayar FROM (
					SELECT inv.Id Invoice, mp.NamaPelanggan, me.Username Sales, mee.Username ApproveBy, mjb.JenisBayar,
						(sj.SisaBayar - IFNULL(d.Dp, 0)) JmlBayar, d.CreateDate, IFNULL(sby.SudahBayar, 0) SudahBayar, 
						inv.CloseDate CreateCloseDate
					FROM masterinvoice AS inv
					LEFT JOIN (
						SELECT dso.IdSo, dso.KodeSJ, mso.IdPelanggan, mso.IdSales, mso.ApproveBy, mso.Metodebayar, mso.Dp,
							mso.CreateDate
						FROM detailso AS dso 
						LEFT JOIN masterso AS mso ON mso.Id = dso.IdSo
						WHERE IFNULL(dso.KodeSJ,'') != ''
					) d ON d.KodeSJ = inv.IdSJ
					LEFT JOIN masterpelanggan AS mp ON mp.Id = d.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = d.IdSales
					LEFT JOIN masteremploye AS mee ON mee.Id = d.ApproveBy
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = d.MetodeBayar
					LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
					LEFT JOIN (
							SELECT InvSo AS Invoice, SUM(Nominal) SudahBayar FROM masterpiutang
							GROUP BY InvSo
					) sby ON sby.Invoice = inv.Id
					WHERE IFNULL(inv.BatalBy, '') = ''
				) resTbl";
		*/
		$this->GetSoTerinvoice();
		$query = "SELECT inv.*, mp.NamaPelanggan, me.Username, mjb.JenisBayar, byr.SudahBayar, 
							CASE WHEN IFNULL(mi.CloseBy,'') != '' THEN mi.CloseDate ELSE CONVERT('0', DATE) END CloseDate,
							so.Dp, (inv.SubTotal - byr.SudahBayar) KurangBayar,
							mee.Username Sales, meee.Username ApproveBy, inv.SubTotal JmlBayar, mi.CreateDate, inv.KodeSJ Id
					FROM 
					(
						SELECT MAX(IdSo) IdSo, MAX(IdPelanggan) IdPelanggan, MAX(KodeSJ) KodeSJ, Invoice, SUM(SubTotal) SubTotal
						FROM tempSoTerinvoice 
						GROUP BY Invoice
					) inv
					LEFT JOIN masterpelanggan AS mp ON mp.Id = inv.IdPelanggan
					LEFT JOIN masterso AS so ON so.Id = inv.IdSo
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.MetodeBayar
					LEFT JOIN (
						SELECT InvSo AS Invoice, SUM(Nominal) SudahBayar FROM masterpiutang WHERE IFNULL(CheckBy,'') != '' AND IFNULL(IsBatal,'') = '' GROUP BY InvSo
					) byr ON byr.Invoice = inv.Invoice
					LEFT JOIN masterinvoice AS mi ON mi.Id = inv.Invoice
					LEFT JOIN masteremploye AS mee ON mee.Id = so.IdSales
					LEFT JOIN masteremploye AS meee ON meee.Id = so.ApproveBy";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM NewReturnBarang
	public function GetDetailInvoiceReturn()
	{
		$data = array(
			"Invoice" => $this->input->post("Invoice")
		);
		$query = "SELECT inv.Id Invoice, dd.*, mp.NameProduct, mp.Stok Jml
					FROM masterinvoice AS inv
					LEFT JOIN (
						SELECT sj.Id IdSJ, rr.*
						FROM mastersjkolektor AS sj
						LEFT JOIN (
							SELECT dso.Id, dso.IdSo, dso.IdProduct, dsj.Id IdDsj, dsj.JmlTerkirim, ret.JmlEdit, ret.JmlRusakEdit,
								(dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) ResJml,
								ret.Id IdRet
							FROM detailso AS dso
							LEFT JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dsj.IdProduct
							LEFT JOIN mastersjkolektor AS msj ON msj.IdSo = dso.IdSo
							LEFT JOIN (
								SELECT r.*, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
								FROM returnbarang AS r
								LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
							) AS ret ON ret.IdSo = msj.Id AND ret.IdProduct = dsj.IdProduct
							WHERE IFNULL(dso.StatusBatal,0) = 0 AND (dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) > 0
						) rr ON rr.IdSo = sj.IdSo
					) dd ON dd.IdSJ = inv.IdSJ
					LEFT JOIN masterproduct AS mp ON mp.Id = dd.IdProduct
					WHERE IFNULL(inv.BatalBy,'') = '' AND inv.Id = '".$data["Invoice"]."'";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM ReturnBarang ASLI
	public function GetDetailReturnBarangAsli()
	{
		$data = array(
			"KodeSJ" => $this->input->post("KodeSJ")
		);
		$query = "SELECT dsj.Id IdDsj, dsj.JmlTerkirim Jml, dsj.IdProduct, mp.NameProduct, 0 JmlEdit, 0 JmlEditRusak
					FROM detailso AS dso
					LEFT JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id
					LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
					WHERE dso.IdSo = (SELECT IdSo FROM mastersjkolektor WHERE Id = '".$data["KodeSJ"]."' LIMIT 1) 
						AND dso.StatusBatal = 0 AND dso.Jml > 0";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM NewReturnBarang
	public function GetDetailReturnBarang()
	{
		$data = array(
			"KodeSJ" => $this->input->post("KodeSJ")
		);
		$query = "SELECT dso.*, mp.NameProduct, mpe.Username, 0 JmlEdit, 0 HargaEdit, mp.Stok, mp.StokRusak, 0 JmlEditRusak 
					FROM detailso AS dso
					LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
					LEFT JOIN masteremploye AS mpe ON mpe.Id = dso.CreateBy
					WHERE IdSo = ( SELECT IdSo FROM mastersjkolektor WHERE Id = '".$data["KodeSJ"]."' )	
						AND IFNULL(dso.StatusBatal,0) = 0 AND dso.Jml > 0;";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);

	}
	public function GetDetailReturnBarangOld()
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
	public function EditInvoiceNew()
	{
		$data = $this->input->post("jsonData");
		$dataKeterangan = $this->input->post("keterangan");
		$dataInvoice = $this->input->post("invoice");
		
		$jsonDecode = json_decode($data, true);
		$queryNewId = "SELECT IF((SELECT COUNT(Id) from historyeditinvoice WHERE Invoice = '".$dataInvoice."') > 0, 
						(
							SELECT MAX(Id) Id FROM (
								SELECT CONVERT(REPLACE(Id, CONCAT('".$dataInvoice."', '/'), ''), INT) Id
								FROM historyeditinvoice
								WHERE Invoice = '".$dataInvoice."' 
								ORDER BY CreateDate DESC ) nn
						), 0) startId";
		$resNewId = $this->db->query($queryNewId)->row();
		$count = 1;
		$query = "INSERT INTO historyeditinvoice(Id, Invoice, IdProduct, Jmledit, Keterangan, CreateBy) 
					SELECT * FROM ( ";
		
		$this->db->query("DROP TEMPORARY TABLE IF EXISTS tmpEditInv;");
		
		$queryUpdate = "CREATE TEMPORARY TABLE tmpEditInv
						SELECT * FROM (";
		foreach($jsonDecode as $json)
		{
			$resCounting = $count + (int)$resNewId->startId; 
			$query .= "SELECT CONCAT('".$dataInvoice."', '/', LPAD('".$resCounting."',4,'0')) Id, '".$dataInvoice."' Invoice, 
						'".$json["IdProduct"]."' IdProduct, '".$json["JmlEdit"]."' JmlEdit, '".$dataKeterangan."' Keterangan, 
						'".$json["CreateBy"]."' CreateBy UNION ALL ";
						
			$queryUpdate .= "SELECT '".$json["Id"]."' Id, '".$json["IdProduct"]."' IdProduct, ".$json["JmlEdit"]." JmlEditTerkirim UNION ALL ";
			
			$count++;
		}
		$query = substr($query, 0 , -10);
		$query .= " ) mm ";
		$resHistory = $this->db->query($query);
		
		$queryUpdate = substr($queryUpdate, 0 , -10);
		$queryUpdate .= " ) nn ";
		$resTmp = $this->db->query($queryUpdate);
		
		/* TEMPORARY */
		$this->db->query("DROP TEMPORARY TABLE IF EXISTS tmpUpDetailSj;");
		$queryUpdateDetailSj = "CREATE TEMPORARY TABLE tmpUpDetailSj
								SELECT tInv.*, IFNULL(raw.ResJml, 0) ResJml, IFNULL(raw.JmlTerkirim, 0) JmlTerkirim, 
									CASE 
										WHEN tInv.JmlEditTerkirim >= IFNULL(raw.ResJml, 0) THEN (tInv.JmlEditTerkirim - IFNULL(raw.ResJml, 0)) + IFNULL(raw.JmlTerkirim, 0) 
										ELSE IFNULL(raw.JmlTerkirim, 0) - ( IFNULL(raw.ResJml, 0) - tInv.JmlEditTerkirim )
									END resUpdateJmlTerkirim 
								FROM tmpEditInv AS tInv 
								LEFT JOIN (
									SELECT inv.Id Invoice, dd.*, mp.NameProduct, mp.Stok Jml
									FROM masterinvoice AS inv
									LEFT JOIN (
										SELECT sj.Id IdSJ, rr.*
										FROM mastersjkolektor AS sj
										LEFT JOIN (
											SELECT dso.Id, dso.IdSo, dso.IdProduct, dsj.Id IdDsj, dsj.JmlTerkirim, ret.JmlEdit, ret.JmlRusakEdit,
												(dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) ResJml,
												ret.Id IdRet
											FROM detailso AS dso
											LEFT JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dsj.IdProduct
											LEFT JOIN mastersjkolektor AS msj ON msj.IdSo = dso.IdSo
											LEFT JOIN (
												SELECT r.*, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
												FROM returnbarang AS r
												LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
											) AS ret ON ret.IdSo = msj.Id AND ret.IdProduct = dsj.IdProduct
											WHERE IFNULL(dso.StatusBatal,0) = 0 AND (dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) > 0
										) rr ON rr.IdSo = sj.IdSo
									) dd ON dd.IdSJ = inv.IdSJ
									LEFT JOIN masterproduct AS mp ON mp.Id = dd.IdProduct
									WHERE IFNULL(inv.BatalBy,'') = '' AND inv.Id = '".$dataInvoice."'
								) raw ON raw.IdDsj = tInv.Id AND raw.IdProduct = tInv.IdProduct;";
		$this->db->query($queryUpdateDetailSj);
		$this->db->query("DROP TEMPORARY TABLE IF EXISTS tmpUpStok;");
		$queryUpdateDataStok = "CREATE TEMPORARY TABLE tmpUpStok
								SELECT tInv.*, IFNULL(raw.ResJml, 0) ResJml, IFNULL(raw.Jml,0) Stok,
										CASE 
											WHEN tInv.JmlEditTerkirim >= IFNULL(raw.ResJml,0) THEN IFNULL(raw.Jml,0) - (tInv.JmlEditTerkirim - IFNULL(raw.ResJml,0))
											ELSE ((IFNULL(raw.ResJml,0) - tInv.JmlEditTerkirim) + IFNULL(raw.Jml,0))
										END resStok
									FROM tmpEditInv AS tInv 
									LEFT JOIN (
										SELECT inv.Id Invoice, dd.*, mp.NameProduct, mp.Stok Jml
										FROM masterinvoice AS inv
										LEFT JOIN (
											SELECT sj.Id IdSJ, rr.*
											FROM mastersjkolektor AS sj
											LEFT JOIN (
												SELECT dso.Id, dso.IdSo, dso.IdProduct, dsj.Id IdDsj, dsj.JmlTerkirim, ret.JmlEdit, ret.JmlRusakEdit,
													(dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) ResJml,
													ret.Id IdRet
												FROM detailso AS dso
												LEFT JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dsj.IdProduct
												LEFT JOIN mastersjkolektor AS msj ON msj.IdSo = dso.IdSo
												LEFT JOIN (
													SELECT r.*, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
													FROM returnbarang AS r
													LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
												) AS ret ON ret.IdSo = msj.Id AND ret.IdProduct = dsj.IdProduct
												WHERE IFNULL(dso.StatusBatal,0) = 0 AND (dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) > 0
											) rr ON rr.IdSo = sj.IdSo
										) dd ON dd.IdSJ = inv.IdSJ
										LEFT JOIN masterproduct AS mp ON mp.Id = dd.IdProduct
										WHERE IFNULL(inv.BatalBy,'') = '' AND inv.Id = '".$dataInvoice."'
									) raw ON raw.IdDsj = tInv.Id AND raw.IdProduct = tInv.IdProduct;";
		$this->db->query($queryUpdateDataStok);
		

		$this->db->trans_start();
		$lastUpdate = "UPDATE detailsjkolektor AS dsjk 
						JOIN tmpUpDetailSj rw ON rw.Id = dsjk.Id AND rw.IdProduct = dsjk.IdProduct
						SET dsjk.JmlTerkirim = rw.resUpdateJmlTerkirim;";
		$resLastUpdate = $this->db->query($lastUpdate);
		
		$queryUpdateStok = "UPDATE masterproduct AS prod 
								JOIN tmpUpStok rw ON rw.IdProduct = prod.Id 
								SET prod.Stok = rw.resStok";
		$resUpdateStok = $this->db->query($queryUpdateStok);
		$this->db->trans_complete();
		if($resUpdateStok)
		{
			echo "Success";
		}
		else
		{
			echo "Failed";
		}
			
			
		
	}
	//FROM ReturnBarang ASLI
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
		if($getId != "")
		{
			$queryInsert = "INSERT INTO returnbarang(Id, IdSo, IdPelanggan, StatusReturn, Keterangan, CreateBy) 
						VALUES('".$getId."', '".$dataMaster["IdSo"]."', '".$dataMaster["IdPelanggan"]."', '".$dataMaster["StatusReturn"]."'
						, '".$dataMaster["KeteranganReturn"]."', '".$dataMaster["CreateBy"]."')";
			
			$resQuery = $this->db->query($queryInsert);
			
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
		else
		{
			echo "Failed Generate ID";
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
	//FROM MasterPengiriman
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
						LEFT JOIN (
							SELECT inv.Id Invoice, sj.Id, sj.IdSo, so.IdPelanggan
							FROM masterinvoice AS inv 
							LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
							LEFT JOIN masterso AS so ON so.Id = sj.IdSo
							WHERE IFNULL(inv.BatalBy,'') = ''
						) AS so ON dp.IdMso LIKE CONCAT('%',so.Invoice,'%')
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
	//FROM OrderStatus CetakSJ dan NewTemplateSj  
	public function getCvSJ()
	{
		$idso = $this->input->post("idso");
		$query = "SELECT so.Id, so.CreateDate, so.IdSales, mp.NamaPelanggan, mp.Alamat,
						(
							SELECT Keterangan 
							FROM mastersjkolektor 
							WHERE IdSo = '".$idso."'
							LIMIT 1
					) KeteranganSJ, CURDATE() curDate
					FROM masterso AS so 
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					WHERE IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.ApproveBy,'') != ''
						AND so.Id = '".$idso."' AND IFNULL(so.IsBlocked, 0) = 0";
		$resQuery["header"] = $this->db->query($query)->result();
		
		$resIsi = "SELECT * FROM (
						SELECT r.*,
							IFNULL(
								CASE 
									WHEN r.Stok > r.JmlPermintaan THEN r.JmlPermintaan
									ELSE r.Stok
									END
							, 0) ResJmlKirim
						FROM (
							SELECT dso.IdProduct, mt.NameProduct, (IFNULL(dso.Jml, 0) - IFNULL(ret.JmlEdit, 0)) JmlPermintaan, IFNULL(mt.Stok, 0) Stok
							FROM detailso AS dso 
							LEFT JOIN masterproduct AS mt ON mt.Id = dso.IdProduct
							LEFT JOIN 
							(
								SELECT rb.Id, rb.IdSo, rb.IdPelanggan, drb.IdProduct, drb.JmlEdit, drb.JmlRusakEdit
								FROM returnbarang AS rb
								LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
								WHERE rb.IdSo = (SELECT Id FROM mastersjkolektor WHERE IdSo = '".$idso."' LIMIT 1)
							) ret ON ret.IdProduct = dso.IdProduct
							WHERE dso.IdSo = '".$idso."' AND IFNULL(dso.StatusBatal,0) = 0
						) r		
					) rr WHERE rr.ResJmlkirim > 0";
		$resQuery["details"] = $this->db->query($resIsi)->result();
		
		$owner = "SELECT me.Username, me.NamaBank, me.NoRek, mcv.Nama
					FROM masteremploye AS me
					LEFT JOIN mastercv AS mcv ON mcv.Id = me.IdCV
					WHERE me.Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'OWNER' ORDER BY CreateDate LIMIT 1)
						AND IFNULL(me.Aktif,0) = 1
						AND mcv.Nama = 'PSP'";
		$resQuery["owners"] = $this->db->query($owner)->result();
		
		echo json_encode($resQuery);
	}
	//from PalingBaruInvoice.vue
	public function getCvInvoice()
	{
		$invoice = $this->input->post("invoice");
		$query = "SELECT inv.Id, inv.CreateDate, inv.IdSJ, me.Username, mp.NamaPelanggan,
						mp.Alamat, s.Username NamaSopir, CURDATE() curDate, DATE_ADD(CURDATE(), INTERVAL CONVERT(IFNULL(inv.JatuhTempo, 0), INT) DAY) tglJatuhTempo,
						sj.Keterangan KeteranganSJ, soj.*, inv.Keterangan KeteranganInv, tot.Disc, tot.SubTotal
					FROM masterinvoice AS inv
					LEFT JOIN (
						SELECT sj.Id IdSJ, so.*
						FROM masterso AS so
						LEFT JOIN (
							SELECT Id, IdSo FROM mastersjkolektor WHERE Id = ( SELECT IdSJ FROM masterinvoice WHERE Id = '".$invoice."' )
						) AS sj ON sj.IdSo = so.Id
						WHERE so.Id = (
							SELECT IdSo FROM mastersjkolektor WHERE Id = ( SELECT IdSJ FROM masterinvoice WHERE Id = '".$invoice."' )
						)
					) soj ON soj.IdSJ = inv.IdSJ
					LEFT JOIN masteremploye AS me ON me.Id = soj.IdSales
					LEFT JOIN masterpelanggan AS mp ON mp.Id = soj.IdPelanggan
					LEFT JOIN detailpengiriman AS dp ON dp.IdMso = inv.Id
					LEFT JOIN masteremploye AS s ON s.Id = dp.Sopir
					LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
					LEFT JOIN (
						SELECT IdSJ, SUM(Disc) Disc, SUM(SubTotal) SubTotal FROM (
							SELECT n.IdSJ, n.IdProduct, mp.NameProduct, mp.Satuan, (IFNULL(n.JmlSj,0) - (IFNULL(n.JmlEdit,0) + IFNULL(n.JmlRusakEdit,0))) Jml, 
								n.Harga, n.Disc,
								((((IFNULL(n.JmlSj,0) - (IFNULL(n.JmlEdit,0) + IFNULL(n.JmlRusakEdit,0))) * n.Harga) - n.Disc) * pp.Pph)
								+ (((IFNULL(n.JmlSj,0) - (IFNULL(n.JmlEdit,0) + IFNULL(n.JmlRusakEdit,0))) * n.Harga) - n.Disc) SubTotal
							FROM (
								SELECT sj.Id IdSJ, dso.IdProduct, dso.Jml, dso.Harga, dso.Disc, dso.SubTotal, dso.OngkosKuli,
									dsj.JmlTerkirim JmlSJ, ret.JmlEdit, ret.JmlRusakEdit
								FROM mastersjkolektor AS sj
								LEFT JOIN detailso AS dso ON dso.IdSo = sj.IdSo
								LEFT JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dso.IdProduct
								LEFT JOIN (
									SELECT r.IdSo IdSJ, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
									FROM returnbarang AS r
									LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
									WHERE r.StatusReturn = 'Return' AND r.IdSo = ( SELECT IdSJ FROM masterinvoice WHERE Id = '".$invoice."' )
								) ret ON ret.IdSJ = sj.Id AND ret.IdProduct = dsj.IdProduct
								WHERE sj.Id = ( SELECT IdSJ FROM masterinvoice WHERE Id = '".$invoice."' )
									AND dso.StatusBatal = 0
							) n 
							LEFT JOIN masterproduct AS mp ON mp.Id = n.IdProduct
							LEFT JOIN (
								SELECT IdSJ, Pph FROM masterinvoice WHERE Id = '".$invoice."'
							) pp ON pp.IdSJ = n.IdSJ
							WHERE (IFNULL(JmlSj,0) - (IFNULL(JmlEdit,0) + IFNULL(JmlRusakEdit,0))) > 0
						) u
						GROUP BY IdSJ

					) tot on tot.IdSJ = inv.IdSJ
					WHERE inv.Id = '".$invoice."'";
		$resQuery["header"] = $this->db->query($query)->result();
		
		$resIsi = "SELECT n.IdProduct, mp.NameProduct, mp.Satuan, (IFNULL(n.JmlSj,0) - (IFNULL(n.JmlEdit,0) + IFNULL(n.JmlRusakEdit,0))) Jml, n.Harga, n.Disc,
						((((IFNULL(n.JmlSj,0) - (IFNULL(n.JmlEdit,0) + IFNULL(n.JmlRusakEdit,0))) * n.Harga) - n.Disc) * pp.Pph)
						+ (((IFNULL(n.JmlSj,0) - (IFNULL(n.JmlEdit,0) + IFNULL(n.JmlRusakEdit,0))) * n.Harga) - n.Disc) SubTotal
					FROM (
						SELECT sj.Id IdSJ, dso.IdProduct, dso.Jml, dso.Harga, dso.Disc, dso.SubTotal, dso.OngkosKuli,
							dsj.JmlTerkirim JmlSJ, ret.JmlEdit, ret.JmlRusakEdit
						FROM mastersjkolektor AS sj
						LEFT JOIN detailso AS dso ON dso.IdSo = sj.IdSo
						LEFT JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dso.IdProduct
						LEFT JOIN (
							SELECT r.IdSo IdSJ, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
							FROM returnbarang AS r
							LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
							WHERE r.StatusReturn = 'Return' AND r.IdSo = ( SELECT IdSJ FROM masterinvoice WHERE Id = '".$invoice."' )
						) ret ON ret.IdSJ = sj.Id AND ret.IdProduct = dsj.IdProduct
						WHERE sj.Id = ( SELECT IdSJ FROM masterinvoice WHERE Id = '".$invoice."' )
							AND dso.StatusBatal = 0
					) n 
					LEFT JOIN masterproduct AS mp ON mp.Id = n.IdProduct
					LEFT JOIN (
						SELECT IdSJ, Pph FROM masterinvoice WHERE Id = '".$invoice."'
					) pp ON pp.IdSJ = n.IdSJ
					WHERE (IFNULL(JmlSj,0) - (IFNULL(JmlEdit,0) + IFNULL(JmlRusakEdit,0))) > 0";
		$resQuery["details"] = $this->db->query($resIsi)->result();
		
		$owner = "SELECT me.Username, me.NamaBank, me.NoRek, mcv.Nama
					FROM masteremploye AS me
					LEFT JOIN mastercv AS mcv ON mcv.Id = me.IdCV
					WHERE me.Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'OWNER' ORDER BY CreateDate LIMIT 1)
						AND IFNULL(me.Aktif,0) = 1";
		$resQuery["owners"] = $this->db->query($owner)->result();
		
		echo json_encode($resQuery);
	}
	//FROM Pembayaran dan TagihanKolektor
	public function GetSisaBayar()
	{
		$invoice = $this->input->post("invoice");		
		$query = "SELECT inv.Id Invoice, ((det.WithPPH - IFNULL(byr.Nominal,0)) - IFNULL(so.Dp, 0)) SisaBayar, so.IdPelanggan, mp.NamaPelanggan, i.IdMpu
					FROM masterinvoice AS inv
					LEFT JOIN (
						SELECT CONVERT(SUM( ((( d.ResJml * d.Harga ) - d.Disc) * (IFNULL(iv.PPH,0) / 100 )) +
							(( d.ResJml * d.Harga ) - d.Disc) ), INT) WithPPH, d.IdSj
						FROM (
							SELECT dsj.IdDso, dsj.IdProduct, dsj.JmlTerkirim, dso.Harga, dso.Disc, dso.IdSo, 
								msj.Id IdSj, IFNULL(ret.JmlEdit, 0) JmlEdit, IFNULL(ret.JmlRusakEdit, 0) JmlRusakEdit,
								(dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) ResJml
							FROM detailsjkolektor AS dsj
							LEFT JOIN detailso AS dso ON dso.Id = dsj.IdDso AND dsj.IdProduct = dso.IdProduct
							LEFT JOIN mastersjkolektor AS msj ON msj.IdSo = dso.IdSo
							LEFT JOIN (
								SELECT r.*, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
								FROM returnbarang AS r
								LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
							) AS ret ON ret.IdSo = msj.Id AND ret.IdProduct = dsj.IdProduct
							WHERE dso.StatusBatal = 0 AND (dsj.JmlTerkirim - (IFNULL(ret.JmlEdit,0) + IFNULL(ret.JmlRusakEdit,0))) > 0 
						) d 
						LEFT JOIN masterinvoice AS iv ON iv.IdSJ = d.IdSJ
						GROUP BY d.IdSJ
					) AS det ON det.IdSJ = inv.IdSJ
					LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
					LEFT JOIN masterso AS so ON so.Id = sj.IdSo
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN (
						SELECT InvSo, SUM(IFNULL(Nominal,0)) Nominal 
						FROM masterpiutang 
						WHERE InvSo = '".$invoice."' AND IFNULL(CheckBy,'') != '' AND IFNULL(IsBatal,0) = 0
						GROUP BY InvSo
					) byr ON byr.InvSo = inv.Id
					LEFT JOIN (
						SELECT Id IdMpu, InvSo FROM masterpiutang WHERE InvSo = '".$invoice."' AND IsBatal = 0 ORDER BY CreateDate DESC LIMIT 1
					) AS i ON i.InvSo = inv.Id
					WHERE inv.Id = '".$invoice."' AND IFNULL(inv.BatalBy,'') = '' AND IFNULL(inv.IsCetak,0) = 1";
		$resQuery = $this->db->query($query)->row(); 
		echo json_encode($resQuery);
	}
	//FROM ReportToko
	public function ReportToko()
	{
		
		$data = array(
			"StartDate" => $this->input->post("StartDate"),
			"EndDate" => $this->input->post("EndDate"),
			
		);
		$this->GetSoTerinvoice();
		$query = "SELECT iv.*, jInv.jmlInvoice, IFNULL(byr.Nominal,0) SudahBayar, (iv.TotalHutang - IFNULL(byr.Nominal,0)) SisaBayar,
						mpn.NamaPelanggan
					FROM (
						SELECT inv.IdPelanggan, SUM(inv.SubTotal) TotalHutang
						FROM tempSoTerinvoice AS inv
						WHERE IFNULL(inv.BatalBy, '') = ''
						GROUP BY IdPelanggan
					) iv 
					LEFT JOIN (
						SELECT IdPelanggan, COUNT(Invoice) jmlInvoice FROM (
							SELECT inv.IdPelanggan, inv.Invoice
							FROM tempSoTerinvoice AS inv
							WHERE IFNULL(inv.BatalBy, '') = ''
							GROUP BY inv.IdPelanggan, inv.Invoice
						) o GROUP BY IdPelanggan
					) jInv ON jInv.IdPelanggan = iv.IdPelanggan
					LEFT JOIN (
						SELECT pv.*, b.Nominal 
						FROM (
							SELECT MAX(iv.IdPelanggan) IdPelanggan, iv.Invoice, MAX(iv.CetakDate) CreateDate
							FROM tempSoTerinvoice AS iv 
							WHERE IFNULL(iv.BatalBy, '') = ''
							GROUP BY iv.Invoice
						) pv 
						LEFT JOIN (
							#Terbayar
							SELECT InvSo, SUM(Nominal) Nominal 
							FROM masterpiutang
							WHERE IFNULL(CheckBy,'') != '' AND IFNULL(IsBatal,'') = ''
							GROUP BY InvSo
						) b ON b.InvSo = pv.Invoice
					) byr ON byr.IdPelanggan = iv.IdPelanggan
					LEFT JOIN masterpelanggan AS mpn ON mpn.Id = iv.IdPelanggan
					WHERE CONVERT(byr.CreateDate, DATE) BETWEEN '".$data["StartDate"]."' AND '".$data["EndDate"]."'";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
		
	}
	public function DetailReportToko($toko, $start, $end)
	{
		$this->GetSoTerinvoice();
		$query = "SELECT n.*,mp.NamaPelanggan, me.Username InvoiceBy,
						mee.Username ApproveBy, meee.Username NamaSales, emp.Username CreateBy, so.ApproveDate,
						mjb.JenisBayar, n.CetakDate TglInvoice,
						CASE WHEN IFNULL(n.IsClose,0) = 0 THEN 'Proses' ELSE 'Close' END StatusClose,
						IFNULL(byr.SudahBayar, 0) TelahBayar, (SUM(n.SubTotal) - SUM(n.Disc)) - IFNULL(byr.SudahBayar,0) KurangBayar,
						DATEDIFF(DATE_ADD(CONVERT(n.CetakDate, DATE) , INTERVAL n.JatuhTempo DAY), CONVERT(NOW(), DATE)) tglJatuhTempo,
						IFNULL(pb.SudahBayar, 0) TelahBayarBlmCheck
					FROM (

						SELECT MAX(inv.IdPelanggan) IdPelanggan, inv.Invoice, MAX(inv.CetakBy) CetakBy, MAX(inv.CetakDate) CetakDate, 
							MAX(inv.JatuhTempo) JatuhTempo, MAX(inv.IsClose) IsClose, MAX(inv.CloseBy) CloseBy, 
							MAX(inv.CloseDate) CreateCloseDate, SUM(inv.Disc) Disc, SUM(inv.SubTotal) SubTotal,
							MAX(inv.IdSo) IdSo
						FROM tempSoTerinvoice AS inv
						WHERE inv.IdPelanggan = '".$toko."'
						GROUP BY inv.Invoice
						
					) n 
					LEFT JOIN masterpelanggan AS mp ON mp.Id = n.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = n.CetakBy
					LEFT JOIN masterso AS so ON so.Id = n.IdSo
					LEFT JOIN masteremploye AS mee ON mee.Id = so.ApproveBy
					LEFT JOIN masteremploye AS meee ON meee.Id = so.IdSales
					LEFT JOIN masteremploye AS emp ON emp.Id = so.CreateBy
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = so.Metodebayar
					LEFT JOIN (
						SELECT InvSo, SUM(Nominal) SudahBayar FROM masterpiutang 
						WHERE IFNULL(IsBatal,'') = '' AND IFNULL(CheckBy,'') != ''
						GROUP BY InvSo
					) byr ON byr.InvSo = n.Invoice
					LEFT JOIN (
						SELECT InvSo, SUM(Nominal) SudahBayar FROM masterpiutang 
						WHERE IFNULL(IsBatal,'') = '' AND IFNULL(CheckBy,'') = ''
						GROUP BY InvSo
					) pb ON pb.InvSo = n.Invoice
					WHERE IFNULL(so.StatusBatal, 0) = 0
						AND n.IdPelanggan = '".$toko."'
						AND CONVERT(n.CetakDate, DATE) BETWEEN 
							CASE IFNULL('".$start."', '') WHEN '' THEN CURRENT_DATE() 
								ELSE '".$start."' END 
						AND CASE IFNULL('".$end."', '') WHEN '' THEN CURRENT_DATE()
								ELSE '".$end."' END
					ORDER BY n.CetakDate DESC";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM ReportSales
	public function ReportSales()
	{
		$data = array(
			"StartDate" => $this->input->post("StartDate"),
			"EndDate" => $this->input->post("EndDate"),
			
		);
		$this->GetSoTerinvoice();
		$query = "SELECT t.IdSales, SUM(t.TotalHutang) TotalHutang, SUM(t.SudahBayar) SudahBayar, SUM(t.JmlInvoice) JmlInvoice, SUM(t.LebihBayar) LebihBayar,
						jt.JmlToko, me.Username NamaSales
					FROM (
						SELECT iv.*, sb.SubTotal TotalHutang, IFNULL(sib.SudahBayar, 0) SudahBayar, (sb.SubTotal - IFNULL(sib.SudahBayar, 0)) SisaBayar,
							jmlInv.JmlInvoice, CASE WHEN IFNULL(sib.SudahBayar,0) > sb.SubTotal THEN IFNULL(sib.SudahBayar,0) - sb.SubTotal ELSE 0 END LebihBayar
						FROM (
							SELECT inv.IdSo, sos.IdSales
							FROM tempSoTerinvoice AS inv
							JOIN (
								SELECT so.Id, so.IdSales
								FROM masterso AS so 
								WHERE IFNULL(so.IsBLocked,0) = 0 AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.ApproveBy,'') != ''
							) sos ON sos.Id = inv.IdSo
							WHERE IFNULL(inv.BatalBy,'') = ''
								AND CONVERT(inv.CetakDate, DATE) BETWEEN 
													CASE IFNULL('".$data["StartDate"]."', '') WHEN '' THEN CURRENT_DATE() 
														ELSE '".$data["StartDate"]."' END 
												AND CASE IFNULL('".$data["EndDate"]."', '') WHEN '' THEN CURRENT_DATE()
														ELSE '".$data["EndDate"]."' END
							GROUP BY IdSo
						) iv 
						LEFT JOIN (
							SELECT IdSo, SUM(Subtotal) SubTotal
							FROM tempSoTerinvoice
							WHERE IFNULL(BatalBy, '') = ''
							GROUP BY IdSo
						) sb ON sb.IdSo = iv.IdSo
						LEFT JOIN (
							SELECT si.*, ib.Nominal SudahBayar FROM (
								SELECT IdSo, Invoice
								FROM tempSoTerinvoice
								WHERE IFNULL(BatalBy, '') = ''
								GROUP BY IdSo, Invoice
							) si 
							LEFT JOIN (
									#Terbayar
									SELECT InvSo, SUM(Nominal) Nominal 
									FROM masterpiutang
									WHERE IFNULL(CheckBy,'') != '' AND IFNULL(IsBatal,'') = ''
									GROUP BY InvSo
							) ib ON ib.InvSo = si.Invoice
						) sib ON  sib.IdSo = iv.IdSo
						LEFT JOIN (
							SELECT IdSo, COUNT(Invoice) JmlInvoice
							FROM (
								SELECT inv.IdSo, inv.Invoice
								FROM tempSoTerinvoice AS inv
								WHERE IFNULL(inv.BatalBy, '') = ''
								GROUP BY inv.IdSo, inv.Invoice
							) p
						) jmlInv ON jmlInv.IdSo = iv.IdSo
					) t 
					LEFT JOIN (
						SELECT so.IdSales, COUNT(so.IdPelanggan) JmlToko
						FROM masterso AS so 
						WHERE IFNULL(so.IsBLocked,0) = 0 AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.ApproveBy,'') != ''
						GROUP BY so.IdSales
					) jt ON jt.IdSales = t.IdSales
					LEFT JOIN masteremploye AS me ON me.Id = t.IdSales
					GROUP BY t.IdSales";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM ReportSales 
	public function DetailReportSales($sales, $start, $end)
	{
		$this->GetSoTerinvoice();
		$query = "SELECT iv.*, sb.SudahBayar TelahBayar, IFNULL(bc.KurangBayar, 0) TelahBayarBlmCheck, mso.Dp, mso.ApproveBy, mso.ApproveDate,
						mjb.JenisBayar, mso.CreateBy, mso.CreateDate, mso.IdSales, me.Username NamaSales,
						DATEDIFF(DATE_ADD(CONVERT(iv.TglInvoice, DATE) , INTERVAL iv.JatuhTempo DAY), CONVERT(NOW(), DATE)) tglJatuhTempo,
						CASE 
							WHEN (iv.SubTotal - IFNULL(sb.SudahBayar, 0)) <= 0 THEN (IFNULL(sb.SudahBayar, 0) - iv.SubTotal)
							ELSE 0 
						END LebihBayar,
						CASE 
							WHEN (iv.SubTotal - IFNULL(sb.SudahBayar, 0)) <= 0 THEN (iv.SubTotal - IFNULL(sb.SudahBayar, 0))
							ELSE 0 
						END KurangBayar,
						CASE 
							WHEN (iv.SubTotal - IFNULL(sb.SudahBayar, 0)) <= 0 OR IFNULL(iv.IsClose, 0) = 1 THEN 'Close'
							ELSE 'Progress'  
						END StatusClose
					FROM (
						SELECT inv.IdSo, inv.Invoice, MAX(inv.IdPelanggan) IdPelanggan, MAX(inv.JatuhTempo) JatuhTempo,
							MAX(inv.Disc) Disc, SUM(inv.SubTotal) SubTotal, MAX(inv.CetakBy) InvoiceBy, MAX(inv.CetakDate) TglInvoice,
							MAX(inv.IsClose) IsClose, MAX(inv.CloseBy) CloseBy, MAX(inv.CloseDate) CloseDate
						FROM tempSoTerinvoice AS inv
						WHERE IFNULL(inv.BatalBy,'') = ''
						GROUP BY inv.IdSo, inv.Invoice
					) iv 
					LEFT JOIN 
					(
						SELECT InvSo, SUM(Nominal) SudahBayar 
						FROM masterpiutang 
						WHERE IFNULL(IsBatal,'') = '' AND IFNULL(CheckBy,'') != ''
						GROUP BY InvSo
					) sb ON sb.InvSo = iv.Invoice
					LEFT JOIN 
					(
						SELECT InvSo, SUM(Nominal) KurangBayar 
						FROM masterpiutang 
						WHERE IFNULL(IsBatal,'') = '' AND IFNULL(CheckBy,'') = ''
						GROUP BY InvSo
					) bc ON bc.InvSo = iv.Invoice
					LEFT JOIN 
					(
						SELECT Id, Dp, ApproveBy, ApproveDate, MetodeBayar, CreateBy, CreateDate, IdSales
						FROM masterso 
						WHERE IFNULL(StatusBatal,0) = 0 AND IFNULL(IsBLocked,0) = 0 AND IFNULL(ApproveBy,'') != ''
					) mso ON mso.Id = iv.IdSo
					LEFT JOIN masteremploye AS me ON me.Id = mso.IdSales
					LEFT JOIN masterjenisbayar AS mjb ON mjb.Id = mso.Metodebayar
				WHERE mso.IdSales = '".$sales."'
					AND CONVERT(iv.TglInvoice, DATE) BETWEEN 
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
						SELECT so.Id, so.IdPelanggan, dso.Id IdDso, inv.Id Invoice, inv.JatuhTempo, inv.CreateDate,
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
	
	//FROM HistoryProduct
	public function HistoryProduct()
	{
		$data = array(
			"idProduct" => $this->input->post("idProduct"),
			"namaProduct" => $this->input->post("namaProduct"),
			"startDate" => $this->input->post("startDate"),
			"finishDate" => $this->input->post("finishDate"),
			"pelanggan" => $this->input->post("pelanggan"),
		);
		
		$query = "SELECT me.Username NamaSales, mp.NamaPelanggan, so.CreateDate, iv.Id Invoice,
					dso.IdProduct, prod.NameProduct, prod.Satuan, dso.Harga, dso.Disc, so.IdPelanggan, 
					so.IdSales
				FROM masterso AS so
				LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
				LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
				LEFT JOIN detailso AS dso ON dso.IdSo = so.Id
				LEFT JOIN masterproduct AS prod ON prod.Id = dso.IdProduct
				LEFT JOIN (
					SELECT inv.Id, sj.Id IdSJ, sj.IdSo 
					FROM masterinvoice AS inv 
					LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
					WHERE IFNULL(inv.BatalBy,'') = ''
				) iv ON iv.IdSo = so.Id
				WHERE IFNULL(so.StatusBatal, 0) = 0 AND IFNULL(iv.Id,'') != '' 
					AND IFNULL(dso.StatusBatal, 0) = 0
					AND CONVERT(so.CreateDate, DATE) BETWEEN '".$data["startDate"]."' AND '".$data["finishDate"]."'
					AND dso.IdProduct LIKE '%".$data["idProduct"]."%'
					AND prod.NameProduct LIKE '%".$data["namaProduct"]."%'
					AND mp.NamaPelanggan LIKE '%".$data["pelanggan"]."%'
				ORDER BY so.CreateDate DESC";
		$resQuery = $this->db->query($query)->result(); 
		echo json_encode($resQuery); 
	}
	//FROM ordermasuk cetak sj
	public function checkSj()
	{
		$data = array(
			"idSo" => $this->input->post("idSo"),
		);
		$checkSjIsCetak = "SELECT COUNT(Id) IsCetak FROM detailsjkolektor WHERE IdDso IN (
							SELECT Id FROM detailso WHERE IdSo = '".$data["idSo"]."')";
		$resSjIsCetak = $this->db->query($checkSjIsCetak)->row();
		if((int)$resSjIsCetak->IsCetak > 0)
		{
			echo "Success";
		}
		else
		{
			echo "Failed";
		}
	}
	//OrderMasuk Insert ke detailsjkolektor 
	public function updateKeteranganSJ()
	{
		$data = array(
			"idSo" => $this->input->post("idSo"),
			"keterangan" => $this->input->post("keterangan"),
			"createBy" => $this->input->post("createBy")
		);
		$checkSjIsCetak = "SELECT COUNT(Id) IsCetak FROM detailsjkolektor WHERE IdDso IN (
							SELECT Id FROM detailso WHERE IdSo = '".$data["idSo"]."')";
		$resSjIsCetak = $this->db->query($checkSjIsCetak)->row();
		if((int)$resSjIsCetak->IsCetak > 0)
		{
			echo "Success";
		}
		else
		{
			$querySet = "SET @startId = (SELECT IF((SELECT COUNT(Id) from detailsjkolektor WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE)) > 0, 
						(
							SELECT MAX(Id) Id FROM (
								SELECT CONVERT(REPLACE(Id, CONCAT('DSJ-', DATE_FORMAT(CURDATE(), '%y%m%d')), ''), INT) Id
								FROM detailsjkolektor
								WHERE CONVERT(CreateDate, DATE) = CONVERT(NOW(), DATE) 
								ORDER BY CreateDate DESC ) nn
						), 0));";
			$this->db->query($querySet);
			$queryInsertDetailSj = "INSERT INTO detailsjkolektor(Id, IdDso, IdProduct, JmlTerkirim, CreateDate, CreateBy)
									SELECT CONCAT('DSJ-', DATE_FORMAT(CURDATE(), '%y%m%d'),LPAD(CONVERT((@startId + Urutan), VARCHAR(50)), 4, '0')) Id,
											Id IdDso, IdProduct, JmlTerkirim, NOW() CreateDate, '".$data["createBy"]."' CreateBy
									FROM (
										SELECT * FROM (
											SELECT dso.Id, dso.IdSo, dso.IdProduct, dso.Jml,
												IFNULL(CASE WHEN mp.Stok > (dso.Jml - IFNULL(sr.JmlTerkirim,0)) THEN (dso.Jml - IFNULL(sr.JmlTerkirim,0))
												ELSE mp.Stok
												END, 0) JmlTerkirim,
												ROW_NUMBER() OVER (Partition By dso.IdSo  ORDER BY IdProduct DESC) Urutan
											FROM detailso AS dso
											LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
											LEFT JOIN detailsjkolektor sr ON sr.IdDso = dso.Id AND sr.IdProduct = dso.IdProduct
											WHERE dso.IdSo = '".$data["idSo"]."' AND dso.StatusBatal = 0 AND IFNULL(mp.Stok,0) > 0
										) r WHERE r.JmlTerkirim > 0
									) rr;";
			$resInsertDetailSj = $this->db->query($queryInsertDetailSj);
			if($resInsertDetailSj)
			{
				$getId = $this->GenId("mastersjkolektor", "SJ");
				$querySj = "INSERT INTO mastersjkolektor(Id, IdKolektor, SisaBayar, IsCetak, CetakBy, CreateBy, Keterangan, IdSo)
							SELECT '".$getId."' Id, IdKolektor, SUM(SubTotalKirim) SisaBayar, 1 IsCetak, '".$data["createBy"]."' CetakBy, 
								'".$data["createBy"]."' CreateBy, '".$data["keterangan"]."' Keterangan, '".$data["idSo"]."' FROM (
								SELECT dso.Id, dso.IdSo, dso.IdProduct, dso.Jml, dso.SubTotal, mp.Stok,
									CASE WHEN mp.Stok >= dso.Jml THEN dso.Jml ELSE mp.Stok END Terkirim,
									CASE WHEN mp.Stok >= dso.Jml THEN dso.SubTotal ELSE 0 END SubTotalKirim,
									mso.IdSales AS IdKolektor
								FROM detailso AS dso
								LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
								LEFT JOIN masterso AS mso ON mso.Id = dso.IdSo
								WHERE dso.IdSo = '".$data["idSo"]."' AND IFNULL(dso.StatusBatal,0) = 0
							) ss GROUP BY IdSo";
				$resQuery = $this->db->query($querySj);
				if($resQuery)
				{
					echo "Success";
				}
				else
				{
					echo "Failed";
				}
			}
			/* TAMBAHAN UNTUK PENGURANGAN STOK */
			$queryUpStok = "UPDATE masterproduct AS m 
							JOIN (
								SELECT r.*, mp.Stok, (mp.Stok - r.JmlTerkirim) resStok
								FROM (
									SELECT dso.Id, dso.IdSo, dso.IdProduct, dso.Jml,
										IFNULL(CASE WHEN mp.Stok > (dso.Jml - IFNULL(sr.JmlTerkirim,0)) THEN (dso.Jml - IFNULL(sr.JmlTerkirim,0))
										ELSE mp.Stok
										END, 0) JmlTerkirim,
										ROW_NUMBER() OVER (Partition By dso.IdSo  ORDER BY IdProduct DESC) Urutan
									FROM detailso AS dso
									LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
									LEFT JOIN detailsjkolektor sr ON sr.IdDso = dso.Id AND sr.IdProduct = dso.IdProduct
									WHERE dso.IdSo = 'MSO-2308050005' AND dso.StatusBatal = 0 AND IFNULL(mp.Stok,0) > 0
								) r 
								LEFT JOIN masterproduct AS mp ON mp.Id = r.IdProduct
								WHERE r.JmlTerkirim > 0
							) AS rr ON rr.IdProduct = m.Id 
							SET m.Stok = rr.resStok";
			
		}	
	}
	public function updateKeteranganSJOld()
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
			"idSj" => $this->input->post("IdSo"),
			"keterangan" => $this->input->post("Keterangan"),
			"isCetak" => $this->input->post("IsCetak"),
			"tglCetak" => $this->input->post("TglCetak"),
			"cetakBy" => $this->input->post("CetakBy"),
			"PotonganReturnOrSisa" => $this->input->post("PotonganReturnOrSisa"),
		);
		$newInvoice = $this->GenInvoice();
		//INSERT masterinvoice
		$query = "INSERT INTO masterinvoice(Id, IdSJ, PPH, Keterangan, JatuhTempo, Createby, CreateDate, IsCetak, CetakBy, CetakDate)
					SELECT '".$newInvoice."' Id, sj.Id IdSJ, so.Pph PPH, '".$data["keterangan"]."' Keterangan, so.JatuhTempo, 
					'".$data["cetakBy"]."' CreateBy, NOW() CreateDate, 1 IsCetak, 
					'".$data["cetakBy"]."' CetakBy, NOW() CetakDate
					FROM mastersjkolektor AS sj
					LEFT JOIN masterso AS so ON so.Id = sj.IdSo
					WHERE sj.Id = '".$data["idSj"]."'";
		$this->db->query($query);
		//update stok product
		/* NANTI DI SINI DI HILANGKAN UNTUK UPDATENYA */
		$query = "UPDATE masterproduct AS p JOIN (
					SELECT dso.Id, dso.IdProduct, dso.Jml, dsj.JmlTerkirim, ret.JmlEdit, ret.JmlRusakEdit,
						IFNULL(mp.Stok,0) JmlStok, IFNULL(mp.StokRusak, 0) JmlStokRusak,
						((IFNULL(mp.Stok, 0) - IFNULL(dsj.JmlTerkirim, 0)) + IFNULL(ret.JmlEdit,0)) CurStok,
						(IFNULL(mp.StokRusak, 0) + IFNULL(ret.JmlRusakEdit,0)) CurStokRusak
					FROM detailso AS dso
					JOIN detailsjkolektor AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dso.IdProduct
					LEFT JOIN (
						SELECT dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit 
						FROM returnbarang AS r
						LEFT JOIN detailreturnbarang AS dr ON dr.IdReturn = r.Id
						WHERE r.IdSo = '".$data["idSj"]."' 
					) ret ON ret.IdProduct = dso.IdProduct
					LEFT JOIN masterproduct AS mp ON mp.Id = dso.IdProduct
					WHERE dso.IdSo = ( SELECT IdSo FROM mastersjkolektor WHERE Id = '".$data["idSj"]."' )
						AND dso.StatusBatal = 0 
				) rp ON rp.IdProduct = p.Id SET p.Stok = rp.CurStok, p.StokRusak = rp.CurStokRusak";
		$resQuery = $this->db->query($query);
		if($resQuery)
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
	public function updateKeteranganInvOld()
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
	public function GetSoTerinvoice()
	{
		$queryTemp = "DROP TEMPORARY TABLE IF EXISTS tempSoTerinvoice;";
		$resQueryTemp = $this->db->query($queryTemp);
		$queryTemp = "CREATE TEMPORARY TABLE tempSoTerinvoice
						#SO Terinvoice
						SELECT inv.*, det.*, inv.Id Invoice
						FROM masterinvoice AS inv 
						LEFT JOIN (
							SELECT dsj.IdDso, dsj.IdProduct, dsj.JmlTerkirim, re.JmlEdit, dso.IdSo, msj.Id KodeSJ,
								mp.Id IdPelanggan, mp.NamaPelanggan, dso.Harga, dso.Disc, dso.OngkosKuli, so.Dp, 
								(dsj.JmlTerkirim - (IFNULL(re.JmlEdit,0))) JmlBrg,
								( ((((dsj.JmlTerkirim - (IFNULL(re.JmlEdit,0))) * dso.Harga) - dso.Disc) * IFNULL(so.Pph,0)) 
								+ (((dsj.JmlTerkirim - (IFNULL(re.JmlEdit,0))) * dso.Harga) - dso.Disc) ) SubTotal
							FROM detailsjkolektor AS dsj
							LEFT JOIN detailso AS dso ON dso.Id = dsj.IdDso AND dso.IdProduct = dsj.IdProduct
							LEFT JOIN masterso AS so ON so.Id = dso.IdSo
							LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
							LEFT JOIN mastersjkolektor AS msj ON msj.IdSo = dso.IdSo
							LEFT JOIN (
								SELECT rb.Id, rb.IdSo AS IdSJ, db.IdProduct, db.JmlEdit
								FROM returnbarang AS rb
								LEFT JOIN detailreturnbarang AS db ON db.IdReturn = rb.Id
								WHERE rb.StatusReturn = 'Return' AND db.JmlEdit > 0
							) re ON re.IdSJ = msj.Id AND re.IdProduct = dsj.IdProduct
							WHERE (dsj.JmlTerkirim - IFNULL(re.JmlEdit,0)) > 0
								AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.IsBlocked,0) = 0
						) det ON det.KodeSJ = inv.IdSJ
						WHERE IFNULL(inv.BatalBy,'') = '';";
		$resQueryTemp = $this->db->query($queryTemp);
	}
	//FROM HistoryPiutang
	public function HistoryPiutang()
	{
		$data = array(
			"idPelanggan" => $this->input->post("idPelanggan"),
			"pelanggan" => $this->input->post("pelanggan")
		);
		
		$this->GetSoTerinvoice();
		
		$query = "SELECT kred.IdPelanggan, mp.NamaPelanggan, kred.SubTotal AS Kredit, 
						IFNULL(temp.SubTotal, 0) BelumJt, IFNULL(temp.NominalBayar, 0) NominalBayarJt, IFNULL(temp.LebihBayar, 0) LebihBayarJt,
						IFNULL(temp2.SubTotal, 0) Umur30, IFNULL(temp2.NominalBayar, 0) NominalBayar30, IFNULL(temp2.LebihBayar, 0) LebihBayar30,
						IFNULL(temp3.SubTotal, 0) Umur60, IFNULL(temp3.NominalBayar, 0) NominalBayar60, IFNULL(temp3.LebihBayar, 0) LebihBayar60,
						IFNULL(temp4.SubTotal, 0) Umur90, IFNULL(temp4.NominalBayar, 0) NominalBayar90, IFNULL(temp4.LebihBayar, 0) LebihBayar90,
						IFNULL(temp5.SubTotal, 0) Umur120, IFNULL(temp5.NominalBayar, 0) NominalBayar120, IFNULL(temp5.LebihBayar, 0) LebihBayar120
					FROM (
						/* TOTAL KREDIT */
						SELECT mp.Id IdPelanggan, SUM(CONVERT(IFNULL(so.SubTotal, '0'), INT)) SubTotal
						FROM masterpelanggan AS mp
						LEFT JOIN (
							SELECT Id, MAX(IdPelanggan) IdPelanggan, SUM(SubTotal) - MAX(IFNULL(Dp, 0)) SubTotal FROM tempSoTerinvoice
							GROUP BY Id 
						) AS so ON mp.Id = so.IdPelanggan
						GROUP BY mp.Id
					) kred
					LEFT JOIN (
						SELECT IdPelanggan, SUM(Subtotal) SubTotal, 
							SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar, SUM(LebihBayar) LebihBayar
						FROM (
							/* INVOICE BELUM JATUH TEMPO NominalBayar, KurangBayar, LebihBayar  */
							SELECT tst.*, CONVERT(DATE_ADD(tst.CreateDate, INTERVAL tst.JatuhTempo DAY), DATE) JatuhTempoDate,
								IFNULL(byr.NominalBayar, 0) NominalBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) > 0 THEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END KurangBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) < 0 THEN ABS((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END LebihBayar
							FROM 
							(
								SELECT Id Invoice, MAX(IdPelanggan) IdPelanggan, SUM(SubTotal) - MAX(IFNULL(Dp, 0)) SubTotal, MAX(JatuhTempo) JatuhTempo, 
									MAX(CreateDate) CreateDate, MAX(IdSo) IdSo 
								FROM tempSoTerinvoice
								GROUP BY Id
							) AS tst
							#SUDAH BAYAR
							LEFT JOIN (
								SELECT mpu.InvSo, SUM(CONVERT(IFNULL(Nominal,'0'), INT)) NominalBayar 
								FROM masterpiutang AS mpu
								WHERE IFNULL(mpu.CheckBy,'') != '' AND (IFNULL(mpu.IsBatal,'') = '' OR IFNULL(mpu.IsBatal,'0') = '0')
									AND IFNULL(mpu.Nominal,'0') != '0'
								GROUP BY mpu.InvSo
							) AS byr ON byr.InvSo = tst.Invoice
							LEFT JOIN masterso AS soo ON soo.Id = tst.IdSo
							WHERE CURDATE() <= CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) DAY), DATE)
						) t1 GROUP BY IdPelanggan
					) temp ON temp.IdPelanggan = kred.IdPelanggan
					LEFT JOIN (
						/* JATUH TEMPO 2X */ 
						SELECT IdPelanggan, SUM(Subtotal) SubTotal, 
							SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar, SUM(LebihBayar) LebihBayar
						FROM (
							/* INVOICE BELUM JATUH TEMPO NominalBayar, KurangBayar, LebihBayar  */
							SELECT tst.*, CONVERT(DATE_ADD(tst.CreateDate, INTERVAL tst.JatuhTempo DAY), DATE) JatuhTempoDate,
								IFNULL(byr.NominalBayar, 0) NominalBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) > 0 THEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END KurangBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) < 0 THEN ABS((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END LebihBayar
							FROM 
							(
								SELECT Id Invoice, MAX(IdPelanggan) IdPelanggan, SUM(SubTotal) - MAX(IFNULL(Dp, 0)) SubTotal, MAX(JatuhTempo) JatuhTempo, 
									MAX(CreateDate) CreateDate, MAX(IdSo) IdSo 
								FROM tempSoTerinvoice
								GROUP BY Id
							) AS tst
							#SUDAH BAYAR
							LEFT JOIN (
								SELECT mpu.InvSo, SUM(CONVERT(IFNULL(Nominal,'0'), INT)) NominalBayar 
								FROM masterpiutang AS mpu
								WHERE IFNULL(mpu.CheckBy,'') != '' AND (IFNULL(mpu.IsBatal,'') = '' OR IFNULL(mpu.IsBatal,'0') = '0')
									AND IFNULL(mpu.Nominal,'0') != '0'
								GROUP BY mpu.InvSo
							) AS byr ON byr.InvSo = tst.Invoice
							LEFT JOIN masterso AS soo ON soo.Id = tst.IdSo
							WHERE CURDATE() > CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) DAY), DATE)
								AND CURDATE() <= CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) * 2 DAY), DATE)
						) t1 GROUP BY IdPelanggan
					) temp2 ON temp2.IdPelanggan = kred.IdPelanggan
					LEFT JOIN (
						/* JATUH TEMPO 3X */
						SELECT IdPelanggan, SUM(Subtotal) SubTotal, 
							SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar, SUM(LebihBayar) LebihBayar
						FROM (
							/* INVOICE BELUM JATUH TEMPO NominalBayar, KurangBayar, LebihBayar  */
							SELECT tst.*, CONVERT(DATE_ADD(tst.CreateDate, INTERVAL tst.JatuhTempo DAY), DATE) JatuhTempoDate,
								IFNULL(byr.NominalBayar, 0) NominalBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) > 0 THEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END KurangBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) < 0 THEN ABS((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END LebihBayar
							FROM 
							(
								SELECT Id Invoice, MAX(IdPelanggan) IdPelanggan, SUM(SubTotal) - MAX(IFNULL(Dp, 0)) SubTotal, MAX(JatuhTempo) JatuhTempo, 
									MAX(CreateDate) CreateDate, MAX(IdSo) IdSo 
								FROM tempSoTerinvoice
								GROUP BY Id
							) AS tst
							#SUDAH BAYAR
							LEFT JOIN (
								SELECT mpu.InvSo, SUM(CONVERT(IFNULL(Nominal,'0'), INT)) NominalBayar 
								FROM masterpiutang AS mpu
								WHERE IFNULL(mpu.CheckBy,'') != '' AND (IFNULL(mpu.IsBatal,'') = '' OR IFNULL(mpu.IsBatal,'0') = '0')
									AND IFNULL(mpu.Nominal,'0') != '0'
								GROUP BY mpu.InvSo
							) AS byr ON byr.InvSo = tst.Invoice
							LEFT JOIN masterso AS soo ON soo.Id = tst.IdSo
							WHERE CURDATE() > CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) * 2 DAY), DATE)
								AND CURDATE() <= CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) * 3 DAY), DATE)
						) t1 GROUP BY IdPelanggan
					) temp3 ON temp3.IdPelanggan = kred.IdPelanggan
					LEFT JOIN (
						/* JATUH TEMPO 4X */
						SELECT IdPelanggan, SUM(Subtotal) SubTotal, 
							SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar, SUM(LebihBayar) LebihBayar
						FROM (
							/* INVOICE BELUM JATUH TEMPO NominalBayar, KurangBayar, LebihBayar  */
							SELECT tst.*, CONVERT(DATE_ADD(tst.CreateDate, INTERVAL tst.JatuhTempo DAY), DATE) JatuhTempoDate,
								IFNULL(byr.NominalBayar, 0) NominalBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) > 0 THEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END KurangBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) < 0 THEN ABS((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END LebihBayar
							FROM 
							(
								SELECT Id Invoice, MAX(IdPelanggan) IdPelanggan, SUM(SubTotal) - MAX(IFNULL(Dp, 0)) SubTotal, MAX(JatuhTempo) JatuhTempo, 
									MAX(CreateDate) CreateDate, MAX(IdSo) IdSo 
								FROM tempSoTerinvoice
								GROUP BY Id
							) AS tst
							#SUDAH BAYAR
							LEFT JOIN (
								SELECT mpu.InvSo, SUM(CONVERT(IFNULL(Nominal,'0'), INT)) NominalBayar 
								FROM masterpiutang AS mpu
								WHERE IFNULL(mpu.CheckBy,'') != '' AND (IFNULL(mpu.IsBatal,'') = '' OR IFNULL(mpu.IsBatal,'0') = '0')
									AND IFNULL(mpu.Nominal,'0') != '0'
								GROUP BY mpu.InvSo
							) AS byr ON byr.InvSo = tst.Invoice
							LEFT JOIN masterso AS soo ON soo.Id = tst.IdSo
							WHERE CURDATE() > CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) * 3 DAY), DATE)
								AND CURDATE() <= CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) * 4 DAY), DATE)
						) t1 GROUP BY IdPelanggan
					) temp4 ON temp4.IdPelanggan = kred.IdPelanggan
					LEFT JOIN (  
						/* JATUH TEMPO >4X */ 
						
						SELECT IdPelanggan, SUM(Subtotal) SubTotal, 
							SUM(NominalBayar) NominalBayar, SUM(KurangBayar) KurangBayar, SUM(LebihBayar) LebihBayar
						FROM (
							/* INVOICE BELUM JATUH TEMPO NominalBayar, KurangBayar, LebihBayar  */
							SELECT tst.*, CONVERT(DATE_ADD(tst.CreateDate, INTERVAL tst.JatuhTempo DAY), DATE) JatuhTempoDate,
								IFNULL(byr.NominalBayar, 0) NominalBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) > 0 THEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END KurangBayar,
								CASE 
									WHEN ((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0)) < 0 THEN ABS((tst.SubTotal - IFNULL(soo.Dp,0)) - IFNULL(byr.NominalBayar, 0))
									ELSE 0
								END LebihBayar
							FROM 
							(
								SELECT Id Invoice, MAX(IdPelanggan) IdPelanggan, SUM(SubTotal) - MAX(IFNULL(Dp, 0)) SubTotal, MAX(JatuhTempo) JatuhTempo, 
									MAX(CreateDate) CreateDate, MAX(IdSo) IdSo 
								FROM tempSoTerinvoice
								GROUP BY Id
							) AS tst
							#SUDAH BAYAR
							LEFT JOIN (
								SELECT mpu.InvSo, SUM(CONVERT(IFNULL(Nominal,'0'), INT)) NominalBayar 
								FROM masterpiutang AS mpu
								WHERE IFNULL(mpu.CheckBy,'') != '' AND (IFNULL(mpu.IsBatal,'') = '' OR IFNULL(mpu.IsBatal,'0') = '0')
									AND IFNULL(mpu.Nominal,'0') != '0'
								GROUP BY mpu.InvSo
							) AS byr ON byr.InvSo = tst.Invoice
							LEFT JOIN masterso AS soo ON soo.Id = tst.IdSo
							WHERE CURDATE() > CONVERT(DATE_ADD(tst.CreateDate, INTERVAL IFNULL(tst.JatuhTempo, 0) * 4 DAY), DATE)
						) t1 GROUP BY IdPelanggan
					) temp5 ON temp5.IdPelanggan = kred.IdPelanggan
					LEFT JOIN masterpelanggan AS mp ON mp.Id = kred.IdPelanggan
					WHERE kred.IdPelanggan LIKE '%".$data["idPelanggan"]."%' AND mp.NamaPelanggan LIKE '%".$data["pelanggan"]."%'
					ORDER BY mp.NamaPelanggan";

		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM DetailReportToko
	public function DetailSoByInvoice($invoice)
	{
		$invoice = str_replace("-", "/", $invoice);
		$query = "SELECT inv.Id Invoice, raw.*, me.Username 
				FROM masterinvoice AS inv 
				LEFT JOIN (
					SELECT dsj.*, mp.NameProduct, mp.Satuan, ret.JmlEdit, dso.Harga, dso.Disc, (dsj.JmlTerkirim - IFNULL(ret.JmlEdit,0)) Jml,
						IFNULL(so.Pph,0) Pph, sj.Id IdSJ,
						((((dsj.JmlTerkirim - IFNULL(ret.JmlEdit,0)) * dso.Harga) - dso.Disc) * IFNULL(so.Pph,0))
						+ (((dsj.JmlTerkirim - IFNULL(ret.JmlEdit,0)) * dso.Harga) - dso.Disc) SubTotal
					FROM detailsjkolektor AS dsj 
					LEFT JOIN detailso AS dso ON dso.Id = dsj.IdDso
					LEFT JOIN mastersjkolektor AS sj ON sj.IdSo = dso.IdSo
					LEFT JOIN masterso AS so ON so.Id = dso.IdSo
					LEFT JOIN (
						SELECT rb.Id, rb.IdSo AS IdSJ, db.IdProduct, db.JmlEdit
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS db ON db.IdReturn = rb.Id
						WHERE rb.StatusReturn = 'Return' AND db.JmlEdit > 0
					) AS ret ON ret.IdSJ = sj.Id AND ret.IdProduct = dsj.IdProduct
					LEFT JOIN masterproduct AS mp ON mp.Id = dsj.IdProduct
					WHERE (dsj.JmlTerkirim - IFNULL(ret.JmlEdit,0)) > 0
				) raw ON raw.IdSJ = inv.IdSJ
				LEFT JOIN masteremploye AS me ON me.Id = inv.CreateBy
				WHERE IFNULL(inv.BatalBy,'') = '' AND inv.Id = '".$invoice."';";
		$resQuery = $this->db->query($query)->result(); 
		echo json_encode($resQuery);
	}
	//FROM RekapKomisi
	public function getKomisi()
	{
		$data = array(
			"startDate" => $this->input->post("tgl"),
			"range" => $this->input->post("hari")
		);
		$this->GetSoTerinvoice();
		$query = "SELECT *, DATEDIFF(TglCetak, DateClose) Hari FROM (
					SELECT so.Id, so.IdPelanggan, mp.NamaPelanggan, 
						inv.CreateDate TglCetak, 
						inv.Id Invoice, so.IdSales, me.Username NamaSales, so.Dp, IFNULL(u.SubTotal, 0) TotalUtang, 
						b.NominalBayar, 
						CASE 
							WHEN (b.NominalBayar - IFNULL(u.SubTotal, 0)) > 0 THEN (b.NominalBayar - IFNULL(u.SubTotal, 0))
							ELSE 0 
						END LebihBayar, 
						CASE 
							WHEN IFNULL(u.CloseBy,'') = '' THEN NULL
							ELSE u.CloseDate
						END DateClose
					FROM masterinvoice AS inv
					LEFT JOIN (
						SELECT Id Invoice, SUM(SubTotal) - MAX(IFNULL(Dp,0)) SubTotal, MAX(IdSo) IdSo, 
							MAX(CloseDate) CloseDate, MAX(CloseBy) CloseBy
						FROM tempSoTerinvoice
						GROUP BY Id
					) AS u ON u.Invoice = inv.Id
					LEFT JOIN masterso AS so ON so.Id = u.IdSo
					LEFT JOIN (
						SELECT InvSo, SUM(Nominal) NominalBayar, MAX(CreateDate) CreateDate
						FROM masterpiutang
						WHERE IFNULL(CheckBy, '') != '' AND (IFNULL(IsBatal,'') = '' OR IsBatal = '0')
						GROUP BY InvSo
					) AS b ON b.InvSo = u.Invoice
					LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
					LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
					WHERE IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.ApproveBy,'') != ''
						AND IFNULL(so.IsBlocked,0) = 0
						#AND b.NominalBayar >= IFNULL(u.SubTotal, 0)
						AND CONVERT(b.CreateDate, DATE) 
						BETWEEN DATE_SUB('".$data["startDate"]."', INTERVAL ".$data["range"]." DAY) AND '".$data["startDate"]."'
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
		$query = "SELECT rd.IdSo, so.IdPelanggan, mp.NamaPelanggan, so.IdSales IdStaff, so.CreateDate, 
						so.IdSales, rd.StatusReturn, rd.IdProduct, mpd.NameProduct, rd.CreateBy, me.Username NamaStaff, 
						rd.CreateDate CreateDateReturn, rd.JmlEdit, rd.JmlRusakEdit, rd.Harga, rd.TotalHarga
					FROM (
						SELECT rb.IdSo, rb.StatusReturn, rb.CreateBy, rb.CreateDate, drb.IdProduct,
							drb.JmlEdit, drb.JmlRusakEdit, dso.Harga, ((drb.JmlEdit + drb.JmlRusakEdit) * dso.Harga) TotalHarga
						FROM returnbarang AS rb
						LEFT JOIN detailreturnbarang AS drb ON drb.IdReturn = rb.Id
						LEFT JOIN detailso AS dso ON dso.IdSo = rb.IdSo AND dso.IdProduct = drb.IdProduct
						WHERE IFNULL(dso.StatusBatal, 0) = 0
					) rd
					LEFT JOIN (
						SELECT sj.Id, so.IdPelanggan, so.CreateDate, so.IdSales
						FROM mastersjkolektor AS sj
						LEFT JOIN masterso AS so ON so.Id = sj.IdSo 
					) so ON so.Id = rd.IdSo
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
		$this->GetSoTerinvoice();
		$queryHeader = "SELECT so.Id, so.IdSales IdStaff, me.Username CreateBy, so.CreateDate, 
							so.ApproveBy, mee.Username NameApprove, so.ApproveDate, tot.Invoice, 
							so.IdSales, ms.Username NameSales, IFNULL(tot.SubTotal, 0) Total, IFNULL(u.TotalBayar, 0) TotalBayar, 
							mp.NamaPelanggan, so.IdPelanggan
						FROM tempSoTerinvoice AS tot
						LEFT JOIN masterso AS so ON so.Id = IdSo
						LEFT JOIN (
							SELECT InvSo, SUM(Nominal) TotalBayar 
							FROM masterpiutang
							WHERE IFNULL(CheckBy,'') != '' AND IFNULL(IsBatal,'') != '1' 
							GROUP BY InvSo
						) AS u ON u.InvSo = tot.Invoice
						LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
						LEFT JOIN masteremploye AS mee ON mee.Id = so.ApproveBy
						LEFT JOIN masteremploye AS ms ON ms.Id = so.IdSales
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						WHERE IFNULL(so.StatusBatal, '') = '' AND IFNULL(so.IsBlocked,'0') = '0'
							AND IFNULL(so.IsBlocked,'0') = '0'
							AND so.IdPelanggan = '".$idPelanggan."' ";
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
						LEFT JOIN tempSoTerinvoice AS so ON so.Invoice = mpu.InvSo
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
			"KeteranganBatal" => $this->input->post("KeteranganBatal"),
			"BatalBy" => $this->input->post("BatalBy"),
		);
		$queryUpdateSo = "UPDATE masterso SET StatusBatal = 1, KeteranganBatal = '<BATAL>".$in["KeteranganBatal"]."',
							BatalBy = '".$in["BatalBy"]."', BatalDate = NOW()
							WHERE Id = '".$in["IdSo"]."'";
		$resSo = $this->db->query($queryUpdateSo);
		$queryUpdateDso = "UPDATE detailso SET StatusBatal = 1, BatalDate =  NOW(), BatalBy = '".$in["BatalBy"]."'
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
					CASE IFNULL(frm.Invoice,'') WHEN '' THEN 'Batal SO' ELSE 'Batal DO' END BatalDari,
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
				LEFT JOIN (
					SELECT inv.Id Invoice, inv.IdSJ, sj.IdSo
					FROM masterinvoice AS inv 
					LEFT JOIN mastersjkolektor AS sj ON sj.Id = inv.IdSJ
					WHERE IFNULL(inv.BatalBy,'') != ''
				) AS frm ON frm.IdSo = so.Id
				WHERE so.KeteranganBatal LIKE '%<BATAL>%' 
					AND ( so.BatalDate BETWEEN '".$data["StartDate"]."' AND '".$data["EndDate"]."'
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
	//FROM history outstanding so
	public function HistoryStandingSo()
	{
		$query = "SELECT so.IdPelanggan, mp.NamaPelanggan, so.IdSales, me.Username NamaSales,dso.IdSo, 
					dso.Id, dso.IdProduct, dso.Jml, dsj.JmlTerkirim, 
					IFNULL(ret.JmlEdit, 0) JmlEdit, IFNULL(ret.JmlRusakEdit, 0) JmlRusakEdit,
					CASE
						WHEN (IFNULL(dsj.JmlTerkirim,0) - IFNULL(ret.JmlRusakEdit, 0)) < dso.Jml 
							THEN (dso.Jml - (IFNULL(dsj.JmlTerkirim,0) - IFNULL(ret.JmlRusakEdit, 0)))
						ELSE 0
					END JmlOutStanding
				FROM detailso AS dso 
				LEFT JOIN 
				(
					SELECT IdDSo, IdProduct, SUM(IFNULL(JmlTerkirim, 0)) JmlTerkirim FROM detailsjkolektor
					GROUP BY IdDSo, IdProduct
				) AS dsj ON dsj.IdDso = dso.Id AND dsj.IdProduct = dso.IdProduct
				LEFT JOIN masterso AS so ON so.Id = dso.IdSo
				LEFT JOIN mastersjkolektor AS sj ON sj.IdSo = so.Id
				LEFT JOIN 
				(
					SELECT r.IdSo IdSJ, dr.IdProduct, dr.JmlEdit, dr.JmlRusakEdit
					FROM returnbarang AS r 
					LEFT JOIN detailreturnbarang AS dr ON r.Id = dr.IdReturn
					WHERE IFNULL(dr.IdProduct,'') != '' 
				) AS ret ON ret.IdSJ = sj.Id AND ret.IdProduct = dso.IdProduct
				LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
				LEFT JOIN masteremploye AS me ON me.Id = so.IdSales
				WHERE dso.StatusBatal = 0 AND IFNULL(dso.Jml, 0) > 0
					AND sj.Id IN ( SELECT IdSJ FROM masterinvoice WHERE IFNULL(BatalBy,'') = '' )
					AND (dso.Jml - (IFNULL(dsj.JmlTerkirim,0) - IFNULL(ret.JmlRusakEdit, 0))) > 0
					AND IFNULL(so.StatusBatal,0) = 0 AND IFNULL(so.IsBlocked,0) = 0
				ORDER BY so.CreateDate DESC";
		$resData = $this->db->query($query)->result();
		echo json_encode($resData);
	}
	
	//TagihanKolektor
	public function InsertBuktiPenagihan()
	{
		$data = array(
			"InvSo" => $this->input->post("InvSo"),
			"IdStaff" => $this->input->post("IdStaff"),
			"SisaBayar" => $this->input->post("SisaBayar"),
			"CreateBy" => $this->input->post("CreateBy")
		);
		$getId = $this->GenId("BuktiTagihanKolektor", "SJK");
		$query = "INSERT INTO BuktiTagihanKolektor(Id, Invoice, IdKolektor, JmlPenagihan,
					IsCetak, CetakBy, CetakDate, CreateDate, CreateBy)
				VALUES('".$getId."', '".$data["InvSo"]."', '".$data["IdStaff"]."', 
					'".$data["SisaBayar"]."', 1, '".$data["IdStaff"]."', 
					NOW(), NOW(), '".$data["IdStaff"]."')";
		$resQuery = $this->db->query($query);
		
		
		if($resQuery)
		{
			$newId = $this->GenId('masterpiutang', 'mpu');
			
			$query = "INSERT INTO masterpiutang(Id, InvSo, CreateDate, CreateBy, IdKolektor)
							VALUES('".$newId."', '".$data["InvSo"]."', NOW(), '".$data["CreateBy"]."', '".$data["IdStaff"]."')";
			$resQuery = $this->db->query($query);
			if($resQuery) { echo "Success"; }
			else { echo "Failed"; }
		}
		else { echo "Failed"; }
	}
	//TemplateTandaTagihanKolektor
	public function CreateTagihanKolektor()
	{
		$data = array(
			"invoice" => $this->input->post("invoice")
		);
		$this->GetSoTerinvoice();
		$query = "SELECT inv.*, IFNULL(sby.SudahBayar, 0) SudahBayar, sby.LastBayar, 
					(inv.SubTotal - IFNULL(sby.SudahBayar,0)) - inv.Dp KurangBayar,
					sby.IdKolektor, me.Username NamaKolektor, IFNULL(so.JatuhTempo, 0) JatuhTempo
					FROM (
						SELECT Id, MAX(NamaPelanggan) NamaPelanggan, SUM(SubTotal) SubTotal, MAX(Dp) Dp, MAX(IdSo) IdSo
						FROM tempSoTerinvoice
						GROUP BY Id
					) inv 
					LEFT JOIN 
					(
						SELECT InvSo, SUM(Nominal) SudahBayar, MAX(CreateDate) LastBayar, MAX(IdKolektor) IdKolektor
						FROM masterpiutang
						WHERE (IFNULL(IsBatal,'') = '' OR IFNULL(IsBatal,'0') = '0')
							AND IFNULL(CheckBy,'') != ''
						GROUP BY InvSo
					) sby ON sby.InvSo = inv.Id
					LEFT JOIN masteremploye AS me ON me.Id = sby.IdKolektor
					LEFT JOIN masterso AS so ON so.Id = inv.IdSo 
					WHERE inv.Id = '".$data["invoice"]."'";
		$resData["details"] = $this->db->query($query)->result();
		$owner = "SELECT me.Username, me.NamaBank, me.NoRek, mcv.Nama
					FROM masteremploye AS me
					LEFT JOIN mastercv AS mcv ON mcv.Id = me.IdCV
					WHERE me.Jabatan = (SELECT Id FROM masterjabatan WHERE Nama = 'OWNER' ORDER BY CreateDate LIMIT 1)
						AND IFNULL(me.Aktif,0) = 1
						AND mcv.Nama = 'PSP'";
		$resData["owners"] = $this->db->query($owner)->result();
		$header = "SELECT bk.*, rr.JenisBayar, CURDATE() curDate, rr.NamaPelanggan, me.Username NamaKolektor
					FROM BuktiTagihanKolektor AS bk  
					LEFT JOIN (
						SELECT '".$data["invoice"]."' inv, so.*, jb.JenisBayar, mp.NamaPelanggan
						FROM masterso AS so
						LEFT JOIN masterjenisbayar AS jb ON jb.id = so.MetodeBayar
						LEFT JOIN masterpelanggan AS mp ON mp.Id = so.IdPelanggan
						WHERE so.Id = (
							SELECT sj.IdSo FROM mastersjkolektor AS sj WHERE sj.Id = (SELECT iv.IdSJ FROM masterinvoice AS iv 
							WHERE iv.Id = '".$data["invoice"]."' LIMIT 1) LIMIT 1)
							AND IFNULL(so.StatusBatal, 0) = 0
					) rr ON rr.inv = bk.Invoice
					LEFT JOIN masteremploye AS me ON me.Id = bk.IdKolektor
					WHERE bk.Invoice = '".$data["invoice"]."'
					ORDER BY bk.CreateDate DESC
					LIMIT 1";
		$resData["header"] = $this->db->query($header)->result();
		echo json_encode($resData);
	}
	//FROM ORDERMASUK
	public function updatePembagianBarangSo()
	{
		$data = array(
			"jsonData" => $this->input->post("jsonData"),
			"createBy" => $this->input->post("createBy")
		);
		
		$jsonDecode = json_decode($data["jsonData"], true);
		
		$queryUpdate = "UPDATE detailsjkolektor mp JOIN ( ";
		$count = 1;
		foreach($jsonDecode as $json)
		{
			$queryUpdate .= " SELECT '".$json["Id"]."' IdDso, '".$json["Jml"]."' JmlTerkirim, '".$json["IdProduct"]."' IdProduct
							  UNION ALL ";
			
			$count++;
			
		}
		$queryUpdate = substr($queryUpdate, 0, -10);
		$queryUpdate .= ") newVals ON newVals.IdDso = mp.IdDso AND newVals.IdProduct = mp.IdProduct 
			SET mp.JmlTerkirim = newVals.JmlTerkirim";
		$resUpdate = $this->db->query($queryUpdate);
		
		
		if($resUpdate)
		{
			echo "Success";
		}
		else
		{
			echo "Failed";
		}
	}
	//FROM MasterPeriode
	public function getLastPeriode()
	{
		$query = "SELECT Id, Keterangan, TglMulai, TglAkhir 
					FROM masterperiode 
					WHERE IsAktif = 1 AND CURDATE() BETWEEN CONVERT(TglMulai, DATE) AND CONVERT(TglAkhir, DATE)
					ORDER BY CreateDate DESC LIMIT 1";
		$resQuery = $this->db->query($query)->num_rows();
		echo $resQuery;
	}
	//GET SETTING 
	public function getValueSetting()
	{
		$in = $this->input->post("Nama");
		$query = "SELECT CONVERT((CONCAT(LEFT(CURDATE(), 8), Nilai)), DATE) Nilai, 
						DATE_FORMAT(CURDATE(), '%Y %M') Keterangan,
						DATE_ADD((CONVERT((CONCAT(LEFT(CURDATE(), 8), Nilai)), DATE)), INTERVAL 1 MONTH) TglAkhir,
						LEFT(REPLACE(CURDATE(),'-',''), 6) Id
					FROM mastersetting 
					WHERE Nama = '".$in."' AND IsAktif = 1 
					ORDER BY CreateDate DESC LIMIT 1";
		$resQuery = $this->db->query($query)->row();
		echo json_encode($resQuery);
	}
	
	//GET SJtoSupplier 
	public function GetSJBelumLunas()
	{
		$query = "#Cari Sj ke supplier yang belum lunas 
					SELECT r.*, s.JmlAngsuran, s.SisaBayar, 
						CASE 
							WHEN (r.TotalHargaBeli - (IFNULL(s.JmlAngsuran, 0))) > 0 
								THEN (r.TotalHargaBeli - (IFNULL(s.JmlAngsuran, 0)))  
							ELSE 0
						END KurangBayar
					FROM (
						SELECT SUM(IFNULL(rp.TotalHargaBeli, 0)) TotalHargaBeli, SUM(IFNULL(rp.DpToSupplier, 0)) DpToSupplier, rp.SjSupplier
						FROM reportproduct AS rp
						WHERE rp.Keterangan = 'PRODUCT IN' AND IFNULL(rp.ApproveBy,'') != ''
							AND IFNULL(rp.SjSupplier,'') != ''   
						GROUP BY rp.SjSupplier 
					) AS r 
					LEFT JOIN (
						#Cari SJ yg sudah terbayar
						SELECT * FROM (
							SELECT KodeSJ, SUM(JmlAngsuran) JmlAngsuran, SisaBayar, ROW_NUMBER() OVER (Partition By KodeSJ ORDER BY CreateDate DESC) Urutan
							FROM masterpiutangtosupplier 
							WHERE IFNULL(BuktiBayar, '') != '' OR IFNULL(TtdPenerima, '') != ''
						) ss WHERE Urutan = 1
					) s ON s.KodeSJ = r.SjSupplier";
		$resData = $this->db->query($query)->result();
		echo json_encode($resData);
	}
	//FROM PembayaranKeSupplier ( insert into masterpiutangtosupplier )
	public function InsertPembayaranToSup()
	{
		$data = array(
			"KodeSJ" => $this->input->post("KodeSJ"),
			"JmlBayar" => $this->input->post("JmlBayar"),
			"BuktiBayar" => $this->input->post("BuktiBayar"),
			"TtdPenerima" => $this->input->post("TtdPenerima"),
			"CreateBy" => $this->input->post("CreateBy"),
		);
		$getId = $this->GenId("masterpiutangtosupplier", "MPS");
		$query = "INSERT INTO masterpiutangtosupplier (Id, KodeSj, JmlAngsuran, BuktiBayar, TtdPenerima, CreateBy)
					VALUES('".$getId."', '".$data["KodeSJ"]."', '".$data["JmlBayar"]."', '".$data["BuktiBayar"]."'
					, '".$data["TtdPenerima"]."', '".$data["CreateBy"]."')";
		$resData = $this->db->query($query);
		if($resData) { echo "Success"; }
		else { echo "Failed"; }
		
	}
	//FROM PembayaranKeSupplier
	public function GetSjToSup()
	{
		$query = "SELECT sb.KodeSj, ms.NamaSupplier, sb.JmlAngsuran SudahBayar, pi.TotalHarga, pi.SupplierId, pi.TotalHargaBeli
					FROM (
						SELECT SUM(TotalHarga) TotalHarga, MAX(SupplierId) SupplierId, SjSupplier, SUM(TotalHargaBeli) TotalHargaBeli 
						FROM reportproduct 
						WHERE Keterangan = 'PRODUCT IN' AND IFNULL(ApproveBy,'') != ''
							AND IFNULL(SjSupplier,'') != ''
						GROUP BY SjSupplier
					) pi
					LEFT JOIN (
						SELECT KodeSj, SUM(JmlAngsuran) JmlAngsuran 
						FROM masterpiutangtosupplier 
						GROUP BY KodeSj
					) sb ON pi.SjSupplier = sb.KodeSj
					LEFT JOIN mastersupplier AS ms ON ms.Id = pi.SupplierId";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	//FROM MasterPeriode
	public function InsertPeriode()
	{
		$data = array(
			"Id" => $this->input->post("Id"),
			"TglAwal" => $this->input->post("TglAwal"),
			"TglAkhir" => $this->input->post("TglAkhir"),
			"Keterangan" => $this->input->post("Keterangan"),
			"CreateBy" => $this->input->post("CreateBy"),
		);
		
		$this->db->query("SET @endPeriodeBefore = CURDATE();");
		$this->db->query("SET @startPeriodeBefore = 
							(
							SELECT IF( 
								(SELECT COUNT(Id) JmlId FROM masterperiode WHERE Id = LEFT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH), 7)) 
								, (SELECT TglMulai FROM masterperiode WHERE Id = LEFT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH), 7) LIMIT 1)  
								, (SELECT CONCAT(LEFT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH), 8), Nilai ) startPeriodeBefore 
									FROM mastersetting WHERE Nama = 'TglPeriode' AND IsAktif = 1 
									ORDER BY CreateDate DESC 
									LIMIT 1 )
							)
							);");
		$queryAll = "SELECT (IFNULL(PembayaranMasuk, 0) + IFNULL(DpMasuk,0)) Pemasukan, 
					( IFNULL(OngkosKuli,0) + IFNULL(OngkosAkomodasi,0) + IFNULL(OngkosSopir,0) + 
					IFNULL(TotalHargaBeli,0) + IFNULL(MaxDpToSupplier,0) ) Pengeluaran
						FROM (
						#Cari Pembayaran yg sudah di cek dari periode sebelumnya
						SELECT a.PembayaranMasuk, b.DpMasukBlmCek, c.DpMasuk, d.*, e.*
						FROM (
							SELECT SUM(Nominal) PembayaranMasuk
							FROM (
								SELECT InvSo, SUM(Nominal) Nominal 
								FROM masterpiutang 
								WHERE (IFNULL(IsBatal, '0') = '0' OR IFNULL(IsBatal,'') = '')
									AND IFNULL(CheckBy, '') != ''
									AND CONVERT(CheckDate, DATE) BETWEEN @startPeriodeBefore AND @endPeriodeBefore
								GROUP BY InvSo
							) s 
						) a,
						(
							#Cari pembayaran yg belum di cek dari periode sebelumnya
							SELECT SUM(Nominal) DpMasukBlmCek
							FROM (
								SELECT InvSo, SUM(IFNULL(Nominal, 0)) Nominal 
								FROM masterpiutang 
								WHERE (IFNULL(IsBatal, '0') = '0' OR IFNULL(IsBatal,'') = '')
									AND IFNULL(CheckBy, '') = ''
									AND CONVERT(KolektorDate, DATE) BETWEEN @startPeriodeBefore AND @endPeriodeBefore
								GROUP BY InvSo
							) s
						) b,
						(
							#Cari dp yg sudah masuk dan approve dari periode sebelumnya
							SELECT SUM(Nominal) DpMasuk
							FROM (
								SELECT Id, SUM(Dp) Nominal 
								FROM masterso 
								WHERE IFNULL(StatusBatal,0) = 0 AND IFNULL(ApproveBy,'') != ''
									AND IFNULL(IsBLocked,0) = 0
									AND CONVERT(ApproveDate, DATE) BETWEEN @startPeriodeBefore AND @endPeriodeBefore
								GROUP BY Id
							) s
						) c,
						(
							#Cari pengeluaran dari pengiriman
							SELECT SUM(OngKosKuli) OngkosKuli, SUM(OngkosAkomodasi) OngkosAkomodasi, SUM(OngkosSopir) OngkosSopir 
							FROM detailpengiriman
							WHERE CONVERT(CreateDate, DATE) BETWEEN @startPeriodeBefore AND @endPeriodeBefore
						) d,
						(
							#Cari Dp dan Beli Ke Supplier
							SELECT SUM(TotalHargaBeli) TotalHargaBeli, SUM(SumDpToSupplier)SumDpToSupplier,
								SUM(MaxDpToSupplier) MaxDpToSupplier
							FROM (
								SELECT SjSupplier, SUM(IFNULL(TotalHargaBeli, 0)) TotalHargaBeli, 
									SUM(IFNULL(DpToSupplier, 0)) SumDpToSupplier, 
									MAX(IFNULL(DpToSupplier, 0)) MaxDpToSupplier
								FROM reportproduct 
								WHERE Keterangan = 'PRODUCT IN' AND IFNULL(SjSupplier, '') != ''
									AND CONVERT(ApproveDate, DATE) BETWEEN @startPeriodeBefore AND @endPeriodeBefore
								GROUP BY SjSupplier
							) f
						) e
						) last";
		$resData = $this->db->query($queryAll)->row();
		$queryInsert = "INSERT INTO masterperiode (Id, Keterangan, TglMulai, TglAkhir, IsAktif, Penjualan, Pembelian, CreateBy)
						VALUES('".$data["Id"]."', '".$data["Keterangan"]."', '".$data["TglAwal"]."', '".$data["TglAkhir"]."',
						1, ".$resData->Pemasukan.", ".$resData->Pengeluaran.", '".$data["CreateBy"]."')";
		$resInsert = $this->db->query($queryInsert);
		if($resInsert) { echo "Success"; }
		else { echo "Failed"; }
	}
	//FROM DASHBOARD new
	public function GetContentDashboard()
	{
		$inp = array(
			"periode" => $this->input->post("periode"),
		);
		
		$query = "CREATE TEMPORARY TABLE tempSoTerinvoice
					SELECT so.Id, so.IdPelanggan, so.MetodeBayar, so.JatuhTempo, so.Dp,
						t.SubTotal, sb.SudahBayar, t.OngkosKuli,
						so.CreateDate, so.ApproveDate
					FROM masterso AS so
					LEFT JOIN (
						SELECT IdSo, SUM(SubTotal) SubTotal, SUM(OngkosKuli) OngkosKuli
						FROM 
						(
							SELECT a.Id, a.IdSo, a.IdProduct, b.JmlTerkirim, a.Harga, a.Disc, a.OngkosKuli,
								(IFNULL(b.JmlTerkirim, 0) * IFNULL(a.Harga, 0)) - IFNULL(a.Disc,0) Subtotal
							FROM (
								SELECT dso.Id, dso.IdSo, dso.IdProduct, dso.Jml, dso.Harga, 
									dso.Disc, dso.SubTotal, dso.OngkosKuli
								FROM detailso AS dso
								WHERE IFNULL(dso.StatusBatal,0) = 0
							) a
							JOIN 
							(
								SELECT dsj.Id, dsj.IdDso, dsj.IdProduct, dsj.JmlTerkirim
								FROM detailsjkolektor AS dsj
								WHERE dsj.JmlTerkirim > 0
							) b ON a.Id = b.IdDso AND a.IdProduct = b.IdProduct
						) r 
						GROUP BY IdSo
					) t ON t.IdSo = so.Id
					LEFT JOIN 
					(
						SELECT sj.Id, sj.IdSo, inv.Id Invoice, inv.PPH, sby.Nominal SudahBayar
						FROM mastersjkolektor AS sj
						LEFT JOIN masterinvoice AS inv ON inv.IdSJ = sj.Id
						LEFT JOIN 
						(
							SELECT Invso, SUM(IFNULL(Nominal, 0)) Nominal
							FROM masterpiutang
							WHERE IFNULL(CheckBy, '') != ''
							GROUP BY InvSo
						) sby ON sby.InvSo = inv.Id
						WHERE IFNULL(inv.BatalBy,'') = ''
					) sb ON sb.IdSo = so.Id
					WHERE IFNULL(so.ApproveBy, '') != '' 
						AND IFNULL(so.BatalBy, '') = '' AND IFNULL(so.IsBLocked,0) = 0
					ORDER BY CreateDate DESC;";
		$resTemp = $this->db->query($query);
		
		$getPeriode = "SELECT Id FROM masterperiode WHERE IsAktif = 1 ORDER BY CreateDate DESC";
		$resData["periodes"] = $this->db->query($getPeriode)->result();
		$perPeriode = "#per periode
						SELECT SUM(IFNULL(SubTotal, 0)) SubTotal, SUM(IFNULL(SudahBayar, 0)) SudahBayar, 
							SUM(IFNULL(OngkosKuli, 0)) OngkosKuli
						FROM tempSoTerinvoice 
						WHERE EXTRACT(YEAR_MONTH FROM Createdate) = '".$inp["periode"]."';";
		$resData["perPeriode"] = $this->db->query($perPeriode)->row();
		$perDay = "#per hari ini
					SELECT SUM(IFNULL(SubTotal, 0)) SubTotal, SUM(IFNULL(SudahBayar, 0)) SudahBayar, 
						SUM(IFNULL(OngkosKuli, 0)) OngkosKuli
					FROM tempSoTerinvoice 
					WHERE CONVERT(CreateDate, DATE) = CURDATE();";
		$resData["perDay"] = $this->db->query($perDay)->row();
		$pengeluaran = "#pengeluaran untuk belanja periode ini
						SELECT SUM(JmlAngsuran) JmlPembayaran FROM masterpiutangtosupplier
						WHERE IFNULL(BuktiBayar,'') != '' OR IFNULL(TtdPenerima,'') != ''
							AND EXTRACT(YEAR_MONTH FROM Createdate) = '".$inp["periode"]."';";
		$resData["pengeluaran"] = $this->db->query($pengeluaran)->row();
		$jurnal = "SELECT tm.*, mp.NamaPelanggan, mj.JenisBayar
					FROM tempSoTerinvoice tm
					LEFT JOIN masterpelanggan AS mp ON mp.Id = tm.IdPelanggan
					LEFT JOIN masterjenisbayar AS mj ON mj.Id = tm.MetodeBayar
					WHERE EXTRACT(YEAR_MONTH FROM tm.Createdate) = '".$inp["periode"]."';";
		$resData["jurnals"] = $this->db->query($jurnal)->result();
		echo json_encode($resData); 
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
