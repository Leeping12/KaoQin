<?php
class runtAction extends ActionNot
{
	public $runid = 0;
	public $runrs;
	public $splitlast = 0; //距离上次提醒秒数0上次没有运行
	
	public function initAction()
	{
		$this->runid	= (int)$this->get('runid','0');
		$this->runrs	= m('task')->getone($this->runid);
		$this->display 	= false;
		if($this->runrs && !isempt($this->runrs['lastdt'])){
			$this->splitlast = time() - strtotime($this->runrs['lastdt']);
		}
	}
	
	/**
	*	运行完成后判断运行状态
	*/
	public function afterAction()
	{
		if($this->runid > 0){
			$state	= 2;
			$cont  	= ob_get_contents();	
			if($cont=='success')$state=1;
			m('task')->update(array(
				'lastdt'	=> $this->rock->now,
				'lastcont' 	=> $cont,
				'state' 	=> $state
			), $this->runid);
		}
	}
}
class runtClassAction extends runtAction
{
	public function runAction()
	{
		$mid	= (int)$this->get('mid','0');
		m('task')->baserun($mid);
		echo 'success';
	}
	public function getlistAction()
	{
		$dt 	= $this->get('dt', $this->date);
		$barr 	= m('task')->getlistrun($dt);
		$this->option->setval('systaskrun', $this->now);
		$this->returnjson($barr);
	}
}