<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Soal_model extends CI_Model {
    
    public function getDataSoal($id, $guru)
    {
        $this->datatables->select('a.id_soal, a.soal, FROM_UNIXTIME(a.created_on) as created_on, FROM_UNIXTIME(a.updated_on) as updated_on, b.nama_pelajaran, c.nama_guru');
        $this->datatables->from('tb_soal a');
        $this->datatables->join('pelajaran b', 'b.id_pelajaran=a.pelajaran_id');
        $this->datatables->join('guru c', 'c.id_guru=a.guru_id');
        if ($id!==null && $guru===null) {
            $this->datatables->where('a.pelajaran_id', $id);            
        }else if($id!==null && $guru!==null){
            $this->datatables->where('a.guru_id', $guru);
        }
        return $this->datatables->generate();
    }

    public function getSoalById($id)
    {
        return $this->db->get_where('tb_soal', ['id_soal' => $id])->row();
    }

    public function getPelajaranGuru($nip)
    {
        $this->db->select('pelajaran_id, nama_pelajaran, id_guru, nama_guru');
        $this->db->join('pelajaran', 'pelajaran_id=id_pelajaran');
        $this->db->from('guru')->where('nip', $nip);
        return $this->db->get()->row();
    }

    public function getAllGuru()
    {
        $this->db->select('*');
        $this->db->from('guru a');
        $this->db->join('pelajaran b', 'a.pelajaran_id=b.id_pelajaran');
        return $this->db->get()->result();
    }
}