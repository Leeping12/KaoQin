<?php
class flow_gongClassModel extends flowModel
{
	protected function flowchangedata(){
		$cont 	= c('html')->replace($this->rs['content']);
		$fm 	= $this->rs['fengmian'];
		if(!isempt($fm) && file_exists($fm)){
			$cont='<div align="center"><img src="'.$fm.'"></div>'.$cont.'';
		}
		$this->rs['content'] = $cont;
	}
	
	protected function flowsubmit($na, $sm)
	{
		if($this->rs['status']==1)$this->tisongtodo();
	}
	
	//审核完成后发通知
	protected function flowcheckfinsh($zt)
	{
		if($zt==1)$this->tisongtodo();
	}
	
	//发送推送通知
	private function tisongtodo()
	{
		$h 	  = c('html');
		$cont = $h->htmlremove($this->rs['content']);
		$cont = $h->substrstr($cont,0, 50);
		$this->push($this->rs['receid'], '通知公告', $cont.'...', $this->rs['title'],1);
	}
	
	protected function flowgetoptmenu($opt)
	{
		$to = m('log')->isread($this->mtable, $this->id);
		return $to<=0;
	}
	
	protected function flowoptmenu($ors, $crs)
	{
		$table 	= $this->mtable;
		$mid	= $this->id;
		$uid	= $this->adminid;
		$lx 	= $ors['num'];
		$log 	= m('log');
		if($lx=='yd'){
			$log->addread($table, $mid, $uid);
		}
		if($lx=='allyd'){
			$ydid 	= $log->getread($table, $uid);
			$where	= "id not in($ydid)";
			$meswh	= m('admin')->getjoinstr('receid', $uid);
			$where .= $meswh;
			$rows 	= m($table)->getrows($where,'id');
			foreach($rows as $k=>$rs)$log->addread($table, $rs['id'], $uid);
		}
	}
	
	protected function flowdatalog($arr)
	{
		return array('title'=>'');
	}
	
	protected function flowbillwhere($uid, $lx)
	{
		$s 		= m('admin')->getjoinstr('receid', $uid);
		$key 	= $this->rock->post('key');
		if($lx=='wfb'){
			$s =' and `optid`='.$this->adminid.'';
		}
		if($lx=='wexx'){
			$ydid 	= m('log')->getread('infor', $uid);
			$s 		= 'and id not in('.$ydid.') '.$s.' ';
		}
		
		if(!isempt($key))$s.=" and (`title` like '%$key%' or `typename`='$key')";
		
		return array(
			'where' => 'and `status`=1 '.$s,
			'order' => 'optdt desc',
			'fields'=> 'id,typename,optdt,title,optname,zuozhe,indate,recename,fengmian'
		);
	}
}