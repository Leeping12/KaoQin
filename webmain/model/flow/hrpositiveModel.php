<?php
/**
*	人事模块.转正的
*/
class flow_hrpositiveClassModel extends flowModel
{
	//审核完成处理
	protected function flowcheckfinsh($zt){
		m('hr')->hrrun();
	}

	protected function flowbillwhere($uid, $lx)
	{
		$key  	= $this->rock->post('key');
		$where 	= '';
		if($key!='')$where.=" and (b.deptallname like '%$key%' or b.name like '%$key%' or b.ranking like '%$key%' )";
		$table 	= '`[Q]hrpositive` a left join `[Q]admin` b on a.uid=b.id';
		return array(
			'where' => $where,
			'table'	=> $table,
			'fields'=> 'a.*,b.deptname,b.name',
			'order' => 'a.`optdt` desc'
		);
	}
}