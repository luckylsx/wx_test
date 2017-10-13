<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Menu_model extends CI_Model
{
    public $table_name;
    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->table_name = "data_menu";
    }
    public function query($sql){
        return $this->db->query($sql);
    }
    public function getone($id){
        $get_sql  = "select * from {$this->table_name} where id = {$id}";
        return $this->query($get_sql)->row();
    }
    public function addone($data){
        if(($data['pid'] == 0)&&($this->checksum()>=3)){
            //一级菜单不超过3个
            return "toomany1";
        }elseif(($data['pid']!=0)&&($this->checksum($data['pid']))>=7){
            //二级菜单不超过7个
            return "toomany2";
        }
        if(is_array($data)&&!empty($data)){
            $keys = "`".implode("`,`",array_keys($data))."`";
            $vals = "'".implode("','",array_values($data))."'";
            $insert_sql = "insert into {$this->table_name} ($keys) values ($vals)";
            return $this->query($insert_sql);
        }else{
            return false;
        }
    }
    public function del($id){
        $infos = $this->getone($id);
        $del_sql = "delete from {$this->table_name} where id = {$id} and pid = {$id}";
        return $this->query($del_sql);
    }
    private function checksum($id = ''){
        if($id == ''){
            $get_sql = "select count(1) as total from {$this->table_name} where pid =0";
        }else{
            $id = intval($id);
            $get_sql = "select count(1) as total from {$this->table_name} where pid ={$id}";
        }
        $r = $this->db->query($get_sql)->row();
        return $r->total;
    }
    public function getplist(){
        //获取一级菜单
        $get_sql = "select * from {$this->table_name} where pid=0 order by menuorder asc";
        return $this->db->query($get_sql)->result_array();
    }
    public function isplist(){
        $get_sql = "select pid from {$this->table_name} where pid <> 0 group by pid";
        return $this->db->query($get_sql)->result_array();
    }
    public function getlist(){
        $get_sql = "select * from {$this->table_name} where 1 order by pid asc, menuorder asc";
        return $this->db->query($get_sql)->result_array();
    }
    public function maketree($data){
        $pids = array();
        foreach($data as $k=>$v){
            if($v['pid'] == 0){
                $pids[$v['id']][] = $v;
            }else{
                $pids[$v['pid']][] = $v;
            }
        }
        list($t1,$t2,$t3) = array_values($pids);
        $r = array_merge_recursive(is_array($t1)?$t1:array(),is_array($t2)?$t2:array(),is_array($t3)?$t3:array());
        return $r;
    }
    public function update($data){
        if(is_array($data)&&!empty($data)){
            $id = $data['id'];
            unset($data['id']);
            foreach($data as $k=>$v){
                $update_arr[] = "`".$k."` = '".$v."'";
            }
            $update_fs = implode(",",$update_arr);
            $update_sql = "update {$this->table_name} set {$update_fs} where id = {$id}";
            return $this->query($update_sql);
        }else{
            return false;
        }
    }
}