<?php
class flow_kqdkjlClassModel extends flowModel
{
	public function initModel()
	{
		$this->dateobj = c('date');
		$this->typearr = explode(',','在线打卡,考勤机,手机定位,手动添加,异常添加,数据导入,接口导入');
	}
	
	/**
	*	显示条件过滤
	*/
	protected function flowbillwhere($uid, $lx)
	{
		$atype	= $lx;
		$dt1	= $this->rock->post('dt1');
		$dt2	= $this->rock->post('dt2');
		$key	= $this->rock->post('key');
		$s 		= '';
		$s		= ' and b.id='.$this->adminid.'';
		
		//全部下属打卡
		if($lx=='down' || $lx=='dwdown'){
			$s  = 'and '.m('admin')->getdownwheres('b.id', $uid, 0);
		}
		if($atype=='all')$s ='';
		if(!isempt($dt1))$s.=" and a.`dkdt`>='$dt1'";
		if(!isempt($dt2))$s.=" and a.`dkdt`<='$dt2 23:59:59'";
		if(!isempt($key))$s.=m('admin')->getkeywhere($key, 'b.');
		$fields = 'a.*,b.name,b.deptname';
		$tabls  = $this->mtable;
		
		$table  = '`[Q]'.$tabls.'` a left join `[Q]admin` b on a.uid=b.id';
		return array(
			'where' => $s,
			'table' => $table, 
			'order' => 'a.`id` desc',
			'fields'=> $fields
		);
	}
	
	//替换
	public function flowrsreplace($rs)
	{
		$week 		= $this->dateobj->cnweek($rs['dkdt']);
		$rs['week'] = $week;
		$rs['type'] = $this->typearr[$rs['type']];
		if($week=='六' || $week=='日')$rs['ishui']= 1;
		return $rs;
	}
}