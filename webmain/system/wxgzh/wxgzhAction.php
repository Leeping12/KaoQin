<?php
class wxgzhClassAction extends Action
{

	public function setsaveAjax()
	{
		$this->option->update('`value`=null',"`num` like 'wxgzh_%'");
		$this->option->setval('wxgzh_appid@-4', $this->post('appid'));
		$this->option->setval('wxgzh_secret@-4', $this->post('secret'));
		$this->backmsg();
	}
	
	public function getsetAjax()
	{
		$arr= array();
		$arr['appid']		= $this->option->getval('wxgzh_appid');
		$arr['secret']		= $this->option->getval('wxgzh_secret');
		echo json_encode($arr);
	}
	
	public function testsendAjax()
	{
		$lx  = (int)$this->get('lx');
		if($lx==0){
			$val = m('wxgzh:wxgzh')->getticket();
		}else{
			$val = 'ok';
		}
		if($val==''){
			showreturn('','测试失败');
		}else{
			showreturn('','测试成功');
		}
	}
}