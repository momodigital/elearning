<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_model extends CI_Model {

    public function create($table, $data, $batch = false)
    {
        if($batch === false){
            $insert = $this->db->insert($table, $data);
        }else{
            $insert = $this->db->insert_batch($table, $data);
        }
        return $insert;
    }

    public function update($table, $data, $pk, $id = null, $batch = false)
    {
        if($batch === false){
            $insert = $this->db->update($table, $data, array($pk => $id));
        }else{
            $insert = $this->db->update_batch($table, $data, $pk);
        }
        return $insert;
    }

    public function delete($table, $data, $pk)
    {
        $this->db->where_in($pk, $data);
        return $this->db->delete($table);
    }

    /**
     * Data Kelas
     */

    public function getDataKelas()
    {
        $this->datatables->select('id_kelas, nama_kelas, id_jurusan, nama_jurusan');
        $this->datatables->from('kelas');
        $this->datatables->join('jurusan', 'jurusan_id=id_jurusan');
        $this->datatables->add_column('bulk_select', '<div class="text-center"><input type="checkbox" class="check" name="checked[]" value="$1"/></div>', 'id_kelas, nama_kelas, id_jurusan, nama_jurusan');        
        return $this->datatables->generate();
    }

    public function getKelasById($id)
    {
        $this->db->where_in('id_kelas', $id);
        $this->db->order_by('nama_kelas');
        $query = $this->db->get('kelas')->result();
        return $query;
    }

    /**
     * Data Jurusan
     */

    public function getDataJurusan()
    {
        $this->datatables->select('id_jurusan, nama_jurusan');
        $this->datatables->from('jurusan');
        $this->datatables->add_column('bulk_select', '<div class="text-center"><input type="checkbox" class="check" name="checked[]" value="$1"/></div>', 'id_jurusan, nama_jurusan');
        return $this->datatables->generate();
    }

    public function getJurusanById($id)
    {
        $this->db->where_in('id_jurusan', $id);
        $this->db->order_by('nama_jurusan');
        $query = $this->db->get('jurusan')->result();
        return $query;
    }

    /**
     * Data Siswa
     */

    public function getDataSiswa()
    {
        $this->datatables->select('a.id_siswa, a.nama, a.nim, a.email, b.nama_kelas, c.nama_jurusan');
        $this->datatables->select('(SELECT COUNT(id) FROM users WHERE username = a.nim) AS ada');
        $this->datatables->from('siswa a');
        $this->datatables->join('kelas b', 'a.kelas_id=b.id_kelas');
        $this->datatables->join('jurusan c', 'b.jurusan_id=c.id_jurusan');
        return $this->datatables->generate();
    }

    public function getSiswaById($id)
    {
        $this->db->select('*');
        $this->db->from('siswa');
        $this->db->join('kelas', 'kelas_id=id_kelas');
        $this->db->join('jurusan', 'jurusan_id=id_jurusan');
        $this->db->where(['id_siswa' => $id]);
        return $this->db->get()->row();
    }

    public function getJurusan()
    {
        $this->db->select('id_jurusan, nama_jurusan');
        $this->db->from('kelas');
        $this->db->join('jurusan', 'jurusan_id=id_jurusan');
        $this->db->order_by('nama_jurusan', 'ASC');
        $this->db->group_by('id_jurusan');
        $query = $this->db->get();
        return $query->result();
    }

    public function getAllJurusan($id = null)
    {
        if($id === null){
            $this->db->order_by('nama_jurusan', 'ASC');
            return $this->db->get('jurusan')->result();    
        }else{
            $this->db->select('jurusan_id');
            $this->db->from('jurusan_pelajaran');
            $this->db->where('pelajaran_id', $id);
            $jurusan = $this->db->get()->result();
            $id_jurusan = [];
            foreach ($jurusan as $j) {
                $id_jurusan[] = $j->jurusan_id;
            }
            if($id_jurusan === []){
                $id_jurusan = null;
            }
            
            $this->db->select('*');
            $this->db->from('jurusan');
            $this->db->where_not_in('id_jurusan', $id_jurusan);
            $pelajaran = $this->db->get()->result();
            return $pelajaran;
        }
    }

    public function getKelasByJurusan($id)
    {
        $query = $this->db->get_where('kelas', array('jurusan_id'=>$id));
        return $query->result();
    }

    /**
     * Data Guru
     */

    public function getDataGuru()
    {
        $this->datatables->select('a.id_guru,a.nip, a.nama_guru, a.email, a.pelajaran_id, b.nama_pelajaran, (SELECT COUNT(id) FROM users WHERE username = a.nip OR email = a.email) AS ada');
        $this->datatables->from('guru a');
        $this->datatables->join('pelajaran b', 'a.pelajaran_id=b.id_pelajaran');
        return $this->datatables->generate();
    }

    public function getGuruById($id)
    {
        $query = $this->db->get_where('guru', array('id_guru'=>$id));
        return $query->row();
    }

    /**
     * Data Pelajaran
     */

    public function getDataPelajaran()
    {
        $this->datatables->select('id_pelajaran, nama_pelajaran');
        $this->datatables->from('pelajaran');
        return $this->datatables->generate();
    }

    public function getAllPelajaran()
    {
        return $this->db->get('pelajaran')->result();
    }

    public function getPelajaranById($id, $single = false)
    {
        if($single === false){
            $this->db->where_in('id_pelajaran', $id);
            $this->db->order_by('nama_pelajaran');
            $query = $this->db->get('pelajaran')->result();
        }else{
            $query = $this->db->get_where('pelajaran', array('id_pelajaran'=>$id))->row();
        }
        return $query;
    }

    /**
     * Data Kelas Guru
     */

    public function kelas()
    {
        $this->datatables->select('kelas_guru.id, guru.id_guru, guru.nip, guru.nama_guru, GROUP_CONCAT(kelas.nama_kelas) as kelas');
        $this->datatables->from('kelas_guru');
        $this->datatables->join('kelas', 'kelas_id=id_kelas');
        $this->datatables->join('guru', 'guru_id=id_guru');
        $this->datatables->group_by('guru.nama_guru');
        return $this->datatables->generate();
    }

    public function getAllGuru($id = null)
    {
        $this->db->select('guru_id');
        $this->db->from('kelas_guru');
        if($id !== null){
            $this->db->where_not_in('guru_id', [$id]);
        }
        $guru = $this->db->get()->result();
        $id_guru = [];
        foreach ($guru as $d) {
            $id_guru[] = $d->guru_id;
        }
        if($id_guru === []){
            $id_guru = null;
        }

        $this->db->select('id_guru, nip, nama_guru');
        $this->db->from('guru');
        $this->db->where_not_in('id_guru', $id_guru);
        return $this->db->get()->result();
    }

    
    public function getAllKelas()
    {
        $this->db->select('id_kelas, nama_kelas, nama_jurusan');
        $this->db->from('kelas');
        $this->db->join('jurusan', 'jurusan_id=id_jurusan');
        $this->db->order_by('nama_kelas');
        return $this->db->get()->result();
    }
    
    public function getKelasByGuru($id)
    {
        $this->db->select('kelas.id_kelas');
        $this->db->from('kelas_guru');
        $this->db->join('kelas', 'kelas_guru.kelas_id=kelas.id_kelas');
        $this->db->where('guru_id', $id);
        $query = $this->db->get()->result();
        return $query;
    }
    /**
     * Data Jurusan Pelajaran
     */

    public function getJurusanPelajaran()
    {
        $this->datatables->select('jurusan_pelajaran.id, pelajaran.id_pelajaran, pelajaran.nama_pelajaran, jurusan.id_jurusan, GROUP_CONCAT(jurusan.nama_jurusan) as nama_jurusan');
        $this->datatables->from('jurusan_pelajaran');
        $this->datatables->join('pelajaran', 'pelajaran_id=id_pelajaran');
        $this->datatables->join('jurusan', 'jurusan_id=id_jurusan');
        $this->datatables->group_by('pelajaran.nama_pelajaran');
        return $this->datatables->generate();
    }

    public function getPelajaran($id = null)
    {
        $this->db->select('pelajaran_id');
        $this->db->from('jurusan_pelajaran');
        if($id !== null){
            $this->db->where_not_in('pelajaran_id', [$id]);
        }
        $pelajaran = $this->db->get()->result();
        $id_pelajaran = [];
        foreach ($pelajaran as $d) {
            $id_pelajaran[] = $d->pelajaran_id;
        }
        if($id_pelajaran === []){
            $id_pelajaran = null;
        }

        $this->db->select('id_pelajaran, nama_pelajaran');
        $this->db->from('pelajaran');
        $this->db->where_not_in('id_pelajaran', $id_pelajaran);
        return $this->db->get()->result();
    }

    public function getJurusanByIdPelajaran($id)
    {
        $this->db->select('jurusan.id_jurusan');
        $this->db->from('jurusan_pelajaran');
        $this->db->join('jurusan', 'jurusan_pelajaran.jurusan_id=jurusan.id_jurusan');
        $this->db->where('pelajaran_id', $id);
        $query = $this->db->get()->result();
        return $query;
    }
}