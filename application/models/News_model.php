<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 获取新闻模型方法
 */
class News_model extends CI_Model
{
    /**
     * 查询新闻列表
     */
    public function getNewslist()
    {
        $data = $this->db->select('title,description,picUrl,url')->limit(10)
            ->order_by('id','asc')->get("newsImages")->result_array();
        return $data;
    }
}