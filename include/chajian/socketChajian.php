<?php
class socketChajian extends Chajian
{
	//UDP服务器主机，不需要修改
	private $serverhost = '127.0.0.1';
	
	//UDP服务端口，数字类型
	private $serverport = 780;			
	
	/**
	*	UDP发送文本
	*/
	public function udpsend($str)
	{
		$sock 	= socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		$len 	= strlen($str);
		$bo 	= socket_sendto($sock, $str, $len, 0, $this->serverhost, $this->serverport);
		socket_close($sock);
		return $bo;
	}
	
	/**
	*	转pdf发送命令
	*/
	public function topdf($path, $fid, $type)
	{
		if($this->serverport==0)return false;
		$flx	= 'doc';
		if($type=='xls' || $type=='xlsx')$flx='xls';
		$url  	= m('base')->getasynurl('asynrun', 'topdfok', array('id'=>$fid));;
		$path 	= ''.ROOT_PATH.'/mode/pdfjs/topdf/start.bat "'.ROOT_PATH.'/mode/pdfjs/topdf/'.$flx.'.js" "'.ROOT_PATH.'/'.$path.'" "'.$url.'"';
		$bo 	= $this->udpsend($path);
		return $bo;
	}
}