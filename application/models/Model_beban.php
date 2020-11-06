<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Model_beban extends CI_Model
{
    var $column_order = array(null, 'transaksi.no_transaksi','tanggal_transaksi','total_pengeluaran','keterangan','nama_beban'); //set column field database for datatable orderable
	var $column_search = array('transaksi.no_transaksi','tanggal_transaksi','total_pengeluaran','keterangan','nama_beban'); //set column field database for datatable searchable 
	var $order = array('transaksi.no_transaksi' => 'asc'); // default order 
	
	private function _get_datatables_query()
	{
		
		$this->db->select('*');
		$this->db->from('transaksi');
		$this->db->join('pembayaran_beban', 'transaksi.no_transaksi = pembayaran_beban.no_transaksi', 'left');
		$this->db->join('beban_beban', 'pembayaran_beban.no_beban = beban_beban.no_beban', 'left');
		$this->db->where('transaksi.nama_transaksi', 'beban');
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	public function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	public function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->select('*');
		$this->db->from('transaksi');
		$this->db->join('pembayaran_beban', 'transaksi.no_transaksi = pembayaran_beban.no_transaksi', 'left');
		$this->db->join('beban_beban', 'pembayaran_beban.no_beban = beban_beban.no_beban', 'left');
		$this->db->where('transaksi.nama_transaksi', 'beban');
		return $this->db->count_all_results();
	}

    public function get_where($table, $field, $condition)
    {
        $this->db->select($field);
        $this->db->where($condition);
        $sql = $this->db->get($table);
        if($sql){
            return $sql->result();   
        }
        else{
            return false;
        }
    }
	
	public function get_row($table, $field, $condition)
    {
		$sql = $this->db->select($field)->where($condition)->get($table);        
		if($sql){
			return $sql->row();   
		}
		else{
			return false;
		}
    }
	
	public function kode() 
    {
        $get_no_transaksi = $this->db->query("select max(right(no_transaksi,6)) as max_id from pembayaran_beban");
		$kd = "";
		if($get_no_transaksi->num_fields()>0){
			foreach($get_no_transaksi->result() as $k){
				$tmp = ((int)$k->max_id)+1;
				$kd = sprintf("%06s", $tmp);
			}
		}else{
			$kd = "000001";
		}
		$no = "BB_".$kd;
		return $no;
    }
	
	public function tambah($table, $data) 
    {
        $q = $this->db->insert($table, $data);
        return $q;
    }
    
    public function ubah($table, $data, $condition) {
        $this->db->where($condition);
        return $this->db->update($table, $data);
		
    }
    
    public function hapus($table, $condition){
        $this->db->where($condition);
        return $this->db->delete($table);
		
    }
}