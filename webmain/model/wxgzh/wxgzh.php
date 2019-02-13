<?php
class wxgzhModel extends Model
{
	//定义远程连接的
	protected $URL_public		= 'https:&#47;&#47;api.weixin.qq.com/cgi-bin/';
	
	protected $URL_gettoken		= 'token';
	protected $URL_jsapiticket	= 'ticket/getticket';
	

	
	public $appid 		= '';
	public $backarr 	= array();
	private $secret 	= '';
	public function initWxgzh(){}
	
	public function initModel()
	{
		$this->backarr	= array('errcode'=>-1, 'msg'=>'sorry,error');
		$this->option	= m('option');
		$this->initWxgzh();
	}
	
	public function gettourl($can)
	{
		$url = $this->URL_public;
		if(substr($url,0,4)!='http'){
			$url=$this->rock->jm->uncrypt($url);
			$url.=$this->rock->jm->uncrypt($this->$can);
		}else{
			$url.=$this->$can;
		}
		return $url;
	}

	//读取配置
	public function readwxset()
	{
		if($this->appid!='')return;
		$this->appid 	= $this->option->getval('wxgzh_appid');
		$this->secret	= $this->option->getval('wxgzh_secret');
		$this->corpid	= $this->option->getval('weixin_corpid');
		return $this->appid;
	}
	
	//判断是否可以使用公众号定位的
	public function isusegzh()
	{
		if($this->rock->web!='wxbro')return 0;
		$this->readwxset();
		$is = 1;
		if($this->appid=='' || $this->secret=='' || !isempt($this->corpid))$is = 0;
		return $is;
	}
	
	//获取token
	public function gettoken()
	{
		$time 	= date('Y-m-d H:i:s', time()-2*3600);
		$num 	= 'wxgzh_token';
		$rs		= $this->option->getone("`num`='$num' and `optdt`>'$time'");
		$val 	= '';
		if($rs)$val = $rs['value'];
		if(isempt($val)){
			$this->readwxset();
			$secret = $this->secret;
			if($this->appid=='' || $this->secret=='')showreturn('','没有设置公众号',201);
			if(isempt($secret))return '';
			$url 	= ''.$this->gettourl('URL_gettoken').'?grant_type=client_credential&appid='.$this->appid.'&secret='.$secret.'';
			$result = c('curl')->getcurl($url);
			if($result != ''){
				$arr	= json_decode($result);
				if(!isset($arr->access_token)){
					showreturn('',$result,201);
				}else{
					$val 	= $arr->access_token;
					$this->option->setval($num, $val);
				}
			}	
		}
		return $val;
	}
	
	public function getticket()
	{
		$time 	= date('Y-m-d H:i:s', time()-2*3600);
		$num 	= 'wxgzh_ticket';
		$rs		= $this->option->getone("`num`='$num' and `optdt`>'$time'");
		$val 	= '';
		if($rs)$val = $rs['value'];
		if(isempt($val)){
			$token	= $this->gettoken();
			$url 	= ''.$this->gettourl('URL_jsapiticket').'?access_token='.$token.'&type=jsapi';
			$result = c('curl')->getcurl($url);
			if($result != ''){
				$arr	= json_decode($result);
				if(!isset($arr->ticket)){
					showreturn('', $result, 201);
				}else{
					$val 	= $arr->ticket;
					$this->option->setval($num, $val);
				}
			}
		}
		return $val;
	}

	
	public function setbackarr($code, $msg)
	{
		$this->backarr	= array('errcode'=>$code, 'msg'=>$msg);
	}
}