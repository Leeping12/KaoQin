<?php
//加班
class flow_jiabanClassModel extends flowModel
{
	
	public function flowrsreplace($rs)
	{
		$rs['modenum'] = $this->modenum;
		return $rs;
	}

	protected function flowbillwhere($uid, $lx)
	{
		$key 	= $this->rock->post('key');
		$where 	= "`uid`='$uid' and `kind`='加班'";
		//if(!isempt($key))$where.=" and (`explain`='$key')";
		
		return array(
			'where' => 'and '.$where,
			'order' => '`optdt` desc'
		);
	}
}