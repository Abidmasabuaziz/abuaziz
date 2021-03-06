<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pendapatan extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
		$this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
		$this->output->set_header('Pragma: no-cache');
		
		if(!$this->session->userdata('no_user'))
        {
        	redirect('auth');
        }
        $this->load->model('model_pendapatan');
	}

	public function index()
	{
		$data = [
			'judul_form'=>'Data Pemasukan Lain - Lain',
			'judul'=>'Pemasukan Lain - Lain',
			'main_view'=>'view_pendapatan'
		];
		$this->load->view('template/index', $data);
	}
	
	public function ajax_list()
	{
		$list = $this->model_pendapatan->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $pendapatan) {
			$no++;
			$row = array();
			$row[] = "<span class='size'>".$no."</span>";
			$row[] = "<span class='size'>".$pendapatan->no_transaksi."</span>";
			$row[] = "<span class='size'>".date('d F Y', strtotime($pendapatan->tanggal_transaksi))."</span>";
			$row[] = "<span class='size'>".$pendapatan->nama_pendapatan."</span>";
			$row[] = "<span class='size'>".$pendapatan->sumber_pendapatan."</span>";
			$row[] = "<span class='size pull-right'>Rp. ".number_format($pendapatan->jumlah_pendapatan,2,',','.')."</span>";
			$row[] = "<span class='size'>".$pendapatan->keterangan."</span>";
			
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->model_pendapatan->count_all(),
						"recordsFiltered" => $this->model_pendapatan->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function tambah(){
		$this->form_validation->set_rules('jumlah_pendapatan', 'Jumlah Pendapatan', 'required|numeric|greater_than[0]');
	
		if($this->form_validation->run() == TRUE){
			$no_transaksi = $this->model_pendapatan->kode();
			$data_transaksi = array(
				'no_transaksi' => $no_transaksi,
				'tanggal_transaksi' => date('Y-m-d'),
				'nama_transaksi' => "pendapatan",
				'no_user' => $this->session->userdata('no_user')
			);
			
			$transaksi = $this->model_pendapatan->tambah('transaksi', $data_transaksi);
			
			$data_pendapatan = array(
				'no_transaksi' => $no_transaksi,
				'nama_pendapatan' => $this->input->post("nama_pendapatan"),
				'sumber_pendapatan' => $this->input->post("sumber_pendapatan"),
				'jumlah_pendapatan' => $this->input->post("jumlah_pendapatan"),
				'keterangan' => $this->input->post("keterangan")
			);
			
			$pendapatan = $this->model_pendapatan->tambah('pendapatan', $data_pendapatan);
			if($pendapatan){
				$jurnal1 = array(
					'no_transaksi' => $no_transaksi,
					'no_akun' => "111",
					'posisi' => "debit",
					'nominal' => $this->input->post("jumlah_pendapatan")
				);
				$query_jurnal1 = $this->model_pendapatan->tambah("jurnal", $jurnal1);
				
				$jurnal2 = array(
					'no_transaksi' => $no_transaksi,
					'no_akun' => "423",
					'posisi' => "kredit",
					'nominal' => $this->input->post("jumlah_pendapatan")
				);
				$query_jurnal2 = $this->model_pendapatan->tambah("jurnal", $jurnal2);
				$res = [
					'status'  => true,
					'message' => 'Data berhasil dimasukkan'
				];
			}else{
				$res = [
					'status'  => false,
					'message' => 'Data gagal dimasukkan'
				];
			}

		}else{
			$res = [
				'status'  => false,
				'message' => 'Jumlah Pendapatan harus diatas Rp. 0'
			];
		}
		
		echo json_encode($res);
	}
}
