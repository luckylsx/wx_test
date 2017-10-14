<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 位置模型
 */
class Location_model extends CI_Model
{
    public function saveLocation($data,$oppenid)
    {
        $wuser = $this->db->select('wxname')->get_where("members",['wxname'=>$oppenid])->row_array();
        if (!$wuser){
            $data['wxname'] = $oppenid;
            $this->db->insert($data);
        }else{
            $this->db->update("members",$data,['wxname'=>$oppenid]);
        }
    }
}