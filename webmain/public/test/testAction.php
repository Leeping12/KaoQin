<?php 
class testClassAction extends ActionNot{
	
	//测试地址http://127.0.0.1/app/xinhu/?m=test&d=public
	public function defaultAction()
	{
		$this->display = false;
		
		//$a = m('weixin:media')->upload('upload/2017-02/08_10092129.doc');
		
		//$a = c('xinhu')->getdata('mode');
		
		
		$a = m('weixin:kefu')->send('','','','','','');
		print_r($a);
	
		
		//echo '<br>'.$this->now.'<br>';
	}
	
}