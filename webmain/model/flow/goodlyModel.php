<?php
class flow_goodlyClassModel extends flowModel
{
	
	//审核完成处理
	protected function flowcheckfinsh($zt){
		m('goodss')->update('status='.$zt.'',"`mid`='$this->id'");
		$aid  = '0';
		$rows = m('goodss')->getall("`mid`='$this->id'",'aid');
		foreach($rows as $k=>$rs)$aid.=','.$rs['aid'].'';
		m('goods')->setstock($aid);
	}

	
	
	//子表数据替换处理
	protected function flowsubdata($rows){
		$db = m('goods');
		foreach($rows as $k=>$rs){
			$one = $db->getone($rs['aid']);
			if($one){
				$rows[$k]['aid'] 	= $one['name'];
				$rows[$k]['unit'] 	= $one['unit'];
			}
		}
		return $rows;
	}
}