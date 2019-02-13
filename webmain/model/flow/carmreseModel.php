<?php
class flow_carmreseClassModel extends flowModel
{


	protected function flowbillwhere($uid, $lx)
	{
		$where  = '';
		$key 	= $this->rock->post('key');
		$dt 	= $this->rock->post('dt');
		if($key != '')$where.=" and (`carnum`='$key' or `usename` like '%$key%' or `optname` like '%$key%')";
		if($dt != '')$where.=" and (`applydt`='$dt' or `startdt` like '$dt%')";
		
		return array(
			'where' => $where,
			'order' => 'optdt desc'
		);
	}
	
	//自定义审核人读取
	protected function flowcheckname($num){
		$sid = '';
		$sna = '';
		//驾驶员审核读取
		if($num=='jias'){
			$sid = $this->rs['jiaid'];
			$sna = $this->rs['jianame'];
		}
		return array($sid, $sna);
	}
}