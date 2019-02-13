<?php
class flow_scheduleClassModel extends flowModel
{

	protected function flowinit(){
		$this->ratearr		 = array('d'=>'天','w'=>'周','m'=>'月');
	}
	
	//是否有读权限
	public function flowisreadqx()
	{
		if(!isempt($this->rs['receid'])){
			$where 		= m('admin')->gjoin($this->rs['receid'], 'ud', $blx='where');
			$where 		= 'id='.$this->adminid.' and ('.$where.')';
			$bo 		= true;
			if(m('admin')->rows($where)==0)$bo=false;
			return $bo;
		}else{
			return false;
		}
	}
	


	public function flowrsreplace($rs)
	{
		$txsj = (int)$rs['txsj'];
		$str  = '不提醒';
		if($txsj==1)$str  = '提醒';
		$rs['txsj'] = $str;
		$rate = $rs['rate'];
		if(isset($this->ratearr[$rate])){
			if($rate=='w')$rate='每周'.$rs['rateval'].'';
			if($rate=='d')$rate='每天';
			if($rate=='m')$rate='每月'.$rs['rateval'].'号';
			$rs['rate'] = $rate;
		}
		return $rs;
	}
	
	protected function flowprintrows($rows)
	{
		foreach($rows as $k=>$rs){
			$rows[$k] = $this->flowrsreplace($rs);
		}
		return $rows;
	}
	
	protected function flowbillwhere($uid, $lx)
	{
		$where	= 'and uid='.$uid.'';
		$dt 	= $this->rock->post('dt');
		$key 	= $this->rock->post('key');
		//有提醒给我的
		if($lx=='rece'){
			$where = m('admin')->getjoinstr('receid', $uid, 0 , 1);
		}
		if($dt!='')$where.=" and `startdt` like '$dt%'";
		if($key!='')$where.=" and `title` like '%$key%'";
		return array(
			'where' => $where,
			'order' => 'optdt desc'
		);
	}
}