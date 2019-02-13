<?php
/**
	下载文件类插件
*/

class downChajian extends Chajian{
	
	private $upobj;
	
	protected function initChajian()
	{
		$this->upobj = c('upfile');
	}
	
	/**
	*	获取随机文件名
	*/
	public function getallfilename($ext)
	{
		$mkdir 	= ''.UPDIR.'/'.date('Y-m').'';
		if(!is_dir($mkdir))mkdir($mkdir);
		$allfilename			= ''.$mkdir.'/'.date('d_His').''.rand(10,99).'.'.$ext.'';
		return $allfilename;
	}
	
	/**
	*	根据扩展名保存文件(一般邮件附件下载)
	*/
	public function savefilecont($ext, $cont)
	{
		$bo  = $this->upobj->issavefile($ext);
		if(isempt($cont))return;
		$file= '';
		if(!$bo){
			$file	= $this->getallfilename('uptemp');
			$bo 	= @file_put_contents($file, base64_encode($cont));
		}else{
			$file 	= $this->getallfilename($ext);
			$bo 	= @file_put_contents($file, $cont);
		}
		if(!$bo){
			$file = '';
		}else{
			if($this->upobj->isimg($ext)){
				$bo = $this->upobj->isimgsave($ext, $file);
				if(!$bo)$file = '';
			}
		}
		return $file;
	}
	
	public function createimage($cont, $ext, $filename, $thumbnail='')
	{
		if(isempt($cont))return false;
		$allfilename			= $this->getallfilename($ext);
		$upses['oldfilename'] 	= $filename.'.'.$ext;
		$upses['fileext'] 	  	= $ext;
		@file_put_contents($allfilename, $cont);
		if(!file_exists($allfilename))return false;
		
		$fileobj				= getimagesize($allfilename);
		$mime					= strtolower($fileobj['mime']);
		$next 					= 'jpg';
		if(contain($mime,'bmp'))$next = 'bmp';
		if($mime=='image/gif')$next = 'gif';
		if($mime=='image/png')$next = 'png';
		if($ext != $next){
			@unlink($allfilename);
			$ext = $next;
			$allfilename			= $this->getallfilename($ext);
			$upses['oldfilename'] 	= $filename.'.'.$ext;
			$upses['fileext'] 	  	= $ext;
			@file_put_contents($allfilename, $cont);
			if(!file_exists($allfilename))return false;	
		}
		
		$filesize 			  	= filesize($allfilename);
		$filesizecn 		  	= $this->upobj->formatsize($filesize);
		$picw					= $fileobj[0];				
		$pich					= $fileobj[1];
		if($picw==0||$pich==0){
			@unlink($allfilename);
			return false;
		}
		$upses['filesize']	 	= $filesize;
		$upses['filesizecn']	= $filesizecn;
		$upses['allfilename']	= $allfilename;
		$upses['picw']	 		= $picw;
		$upses['pich']	 		= $pich;
		$arr 					= $this->uploadback($upses, $thumbnail);
		return $arr;
	}
	
	public function uploadback($upses, $thumbnail='')
	{
		if($thumbnail=='')$thumbnail='150x150';
		$msg 		= '';
		$data 		= array();
		if(is_array($upses)){
			$arrs	= array(
				'adddt'	=> $this->rock->now,
				'valid'	=> 1,
				'filename'	=> $this->replacefile($upses['oldfilename']),
				'web'		=> $this->rock->web,
				'ip'		=> $this->rock->ip,
				'fileext'	=> substr($upses['fileext'],0,10),
				'filesize'	=> $upses['filesize'],
				'filesizecn'=> $upses['filesizecn'],
				'filepath'	=> str_replace('../','',$upses['allfilename']),
				'optid'		=> $this->adminid,
				'optname'	=> $this->adminname
			);
			$arrs['filetype'] = m('file')->getmime($arrs['fileext']);
			$thumbpath	= $arrs['filepath'];
			$sttua		= explode('x', $thumbnail);
			$lw 		= (int)$sttua[0];
			$lh 		= (int)$sttua[1];
			if($upses['picw']>$lw || $upses['pich']>$lh){
				$imgaa	= c('image', true);
				$imgaa->createimg($thumbpath);
				$thumbpath 	= $imgaa->thumbnail($lw, $lh, 1);
			}
			if($upses['picw'] == 0 && $upses['pich']==0)$thumbpath = '';
			$arrs['thumbpath'] = $thumbpath;
			
			
			$this->db->record('[Q]file',$arrs);
			$id	= $this->db->insert_id();
			$arrs['id'] = $id;
			$arrs['picw'] = $upses['picw'];
			$arrs['pich'] = $upses['pich'];
			$data= $arrs;
		}else{
			$data['msg'] = $upses;
		}
		return $data;
	}
	
	//过滤特殊文件名
	private function replacefile($str)
	{
		$s 			= strtolower($str);
		$lvlaraa  	= explode(',','user(),found_rows,(),select*from,select*,%20');
		$lvlarab	= array();
		foreach($lvlaraa as $_i)$lvlarab[]='';
		$s = str_replace($lvlaraa, $lvlarab, $s);
		if($s!=$str)$str = $s;
		return $str;
	}
	
	//获取扩展名
	public function getext($file)
	{
		return strtolower(substr($file,strrpos($file,'.')+1));
	}
}
