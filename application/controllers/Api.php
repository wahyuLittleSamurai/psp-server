<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct(){
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
	public function GenId($tbl, $code)
	{
		$query = "SELECT Id FROM ".$tbl." ORDER BY CreateDate DESC LIMIT 1";
		$resCheck = $this->db->query($query)->row();
		if(empty($resCheck))
		{
			return $code . "-0001";
		}
		else
		{
			$getInt = str_replace($code."-", "", $resCheck->Id);
			$toInt = (int)$getInt + 1;
			return $code . "-". sprintf('%04d', $toInt);
		}
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
		if(!empty($tbl) && !empty($selects))
		{
			$query = "SELECT ". $selects . " FROM " . $tbl . " " . $wheres;
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
	public function updateDb()
	{
		$tbl = $this->input->post("table");
		$updates = json_decode($this->input->post("updates"), true);
		$wheres = $this->input->post("wheres");
		
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
	public function getProduct()
	{
		$query = "SELECT s.Id, s.NamaSupplier, mp.NameProduct, mp.Harga, mp.Id AS ProdId, mp.Stok, mp.Satuan
					FROM mastersupplier AS s 
					LEFT JOIN masterproduct AS mp ON mp.Supplier = s.Id
					WHERE s.Aktif = '1'";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
	public function getReportProduct()
	{
		$query = "SELECT rp.Id, rp.Jml, rp.Harga, rp.CreateBy, rp.CreateDate, rp.ApproveBy, 
						rp.Keterangan, rp.ApproveDate, mp.NameProduct, ms.NamaSupplier
					FROM reportproduct AS rp 
					LEFT JOIN masterproduct AS mp ON mp.Id = rp.ProductId
					LEFT JOIN mastersupplier AS ms ON ms.Id = rp.SupplierId";
		$resQuery = $this->db->query($query)->result();
		echo json_encode($resQuery);
	}
}
