<?php
/**
*	流程通知
*/
class flowtodoClassModel extends Model
{

	public function initModel()
	{
		$this->settable('flow_todo');
	}
	
	//判断当前单据
	public function gettodolist($setid)
	{
		$rows = $this->getrows("`setid`='' and `status`=1");
		
	}
}