<?php
/**
*	来自：测试服开发团队
*	作者：磐石(rainrock)
*	网址：http://sucaihuo.com/
*	系统的核心文件之一，处理工作流程模块的。
*/
class flowModel extends Model
{
	public $modenum;		//当前模块编号
	public $editcont= '';	//修改的记录
	public $id		= 0;	//当前单据ID
	public $moders;			//当前模块数组
	public $modeid;			//当前模块Id
	public $modename;		//当前模块名称
	public $sericnum;		//当前单据单号
	public $rs			= array();	//当前单据记录信息
	public $urs			= array();	//当前单据对应用户
	public $drs			= array();	//当前单据对应用户
	public $fieldsarr	= array();	//主表元素字段数组
	public $mwhere;				
	public $mtable;				//当前模块对应表
	public $uname;				//当前单据对应用户姓名
	public $uid		= 0;		//当前单据对应用户Id
	public $optid	= 0;		//当前当街对应操作用Id，如提交人Id
	public $isflow	= 0;		//当前模块是否有流程审核步骤
	public $ismobile= 0;		//是否移动的页面请求的
	
	
	//当初始化模块后调用
	protected function flowinit(){}
	
	//当初始化单据调用
	protected function flowchangedata(){}
	
	//删除单据时调用，$sm删除说明
	protected function flowdeletebill($sm){}
	
	//作废单据时调用，$sm作废说明
	protected function flowzuofeibill($sm){}
	
	//提交时调用
	protected function flowsubmit($na, $sm){}
	
	//添加日志记录调用$arr 添加数组
	protected function flowaddlog($arr){}
	
	protected function flowdatalog($arr){}
	
	//审核之前调用$zt 状态， $sm说明
	protected function flowcheckbefore(){}
	
	//审核完成后调用
	protected function flowcheckafter($zt, $sm){}
	
	//流程全部完成后调用
	protected function flowcheckfinsh($zt){}
	
	
	protected function flowgetfields($lx){}
	protected function flowgetoptmenu($opt){}
	
	//自定义审核人重新的方法$num 步骤单号
	protected function flowcheckname($num){}
	
	//审核步骤根据$num 编号判断是否需要审核
	protected function flowcoursejudge($num){}
	
	//操作单据
	protected function flowoptmenu($ors, $crs){}
	
	//自定义是否可查看本单据
	protected function flowisreadqx(){return false;}
	
	
	protected function flowprintrows($r){return $r;}
	
	//子表数据替换处理
	protected function flowsubdata($r){return $r;}
	
	//单据判断条件从写$lx类型，$uid用户Id
	protected function flowbillwhere($lx, $uid){return '';}
	
	protected $flowweixinarr	= array();
	protected $flowviewufieds	= 'uid';
	
	//初始化单据可替换其他属性
	public function flowrsreplace($rs){return $rs;}
	
	public function echomsg($msg)
	{
		if(!isajax())exit($msg);
		showreturn('', $msg, 201);
		exit();
	}
	
	public function initdata($num, $id=null)
	{
		$this->modenum	= $num;
		$this->moders 	= m('flow_set')->getone("`num`='$num'");
		if(!$this->moders)$this->echomsg('not found mode['.$num.']');
		$table 			= $this->moders['table'];
		$this->modeid	= $this->moders['id'];
		$this->modename	= $this->moders['name'];
		$this->isflow	= (int)$this->moders['isflow'];
		$this->settable($table);
		$this->mtable	= $table;
		$this->viewmodel= m('view');
		$this->billmodel= m('flow_bill');
		$this->todomodel= m('flow_todo');
		$this->flogmodel= m('flow_log');
		$this->checksmodel	= m('flow_checks');
		$this->wheremodel	= m('where');
		$this->fieldsarr	= m('flow_element')->getrows("`mid`='$this->modeid' and `islu`=1 and `iszb`=0",'`name`,`fields`,`isbt`,`fieldstype`,`savewhere`,`data`,`iszb`','`sort`');
		$this->flowinit();
		if($id==null)return;
		$this->loaddata($id, true);
	}
	
	public function loaddata($id, $ispd=true)
	{
		$this->id		= (int)$id;
		$this->mwhere	= "`table`='$this->mtable' and `mid`='$id'";
		$this->rs 		= $this->getone($id);
		$this->uname	= '';
		if(!$this->rs)$this->echomsg('not found record');
		$this->rs['base_name'] 		= '';
		$this->rs['base_deptname'] 	= '';
		if(isset($this->rs['uid']))$this->uid = $this->rs['uid'];
		if(!isset($this->rs['applydt']))$this->rs['applydt'] = '';
		if(!isset($this->rs['status']))$this->rs['status']	 = 1;
		$uisfield 		= property_exists($this, 'uidfields') ? $this->uidfields : 'optid';
		if($this->uid==0 && isset($this->rs[$uisfield]))$this->uid = $this->rs[$uisfield];
		$this->optid 	= isset($this->rs['optid']) ? $this->rs['optid'] : $this->uid;
		$this->urs 		= $this->db->getone('[Q]admin',$this->uid,'id,name,deptid,deptname,ranking,superid,superpath,superman,deptpath');
		if($this->isempt($this->rs['applydt'])&&isset($this->rs['optdt']))$this->rs['applydt']=substr($this->rs['optdt'],0,10);
		if($this->urs){
			$this->drs		= $this->db->getone('[Q]dept',"`id`='".$this->urs['deptid']."'");
			$this->uname	= $this->urs['name'];
			$this->rs['base_name']		= $this->uname;
			if($this->drs){
				$this->rs['base_deptname']	= $this->drs['name'];
			}
		}
		$this->sericnum	= '';
		$this->billrs 	= $this->billmodel->getone($this->mwhere);
		if($this->billrs){
			$this->sericnum = $this->billrs['sericnum'];
		}else{
			if($this->isflow==1)$this->savebill();
		}
		
		if($ispd)$this->isreadqx();

		$this->rssust	= $this->rs;
		$this->flowchangedata();
		
		$this->rs['base_modename']	= $this->modename;
		$this->rs['base_sericnum']	= $this->sericnum;
		$this->rs['base_summary']	= $this->rock->reparr($this->moders['summary'], $this->rs);
	}
	
	/**
	*	当前模块我待办数量
	*/
	public function getdaiban()
	{
		$s 	= $this->rock->dbinstr('nowcheckid', $this->adminid);
		$to = $this->billmodel->rows('`modeid`='.$this->modeid.' and `isdel`=0 and `status` not in(1,2) and '.$s.'');
		return $to;
	}
	
	public function isreadqx()
	{
		$bo = false;
		if($this->uid==$this->adminid && $this->adminid>0)$bo=true;
		if(!$bo && $this->isflow==1){
			if($this->billrs){
				$allcheckid = $this->billrs['allcheckid'];
				if(contain(','.$allcheckid.',',','.$this->adminid.','))$bo = true;
			}
		}
		if(!$bo){
			if($this->urs && contain($this->urs['superpath'],'['.$this->adminid.']'))$bo = true;
		}
		if(!$bo)$bo = $this->flowisreadqx();
		if(!$bo){
			$where 	= $this->viewmodel->viewwhere($this->moders, $this->adminid, $this->flowviewufieds);
			$tos 	= $this->rows("`id`='$this->id'  $where ");
			if($tos>0)$bo=true;
		}
		if(!$bo)$this->echomsg('无权限查看模块['.$this->modenum.'.'.$this->modename.']'.$this->uname.'的数据，'.c('xinhu')->helpstr('cxqx').'');
	}
	
	public function iseditqx()
	{
		$bo = 0;
		if($bo==0 && $this->isflow==1){
			if($this->billrs && $this->uid == $this->adminid){
				if($this->billrs['nstatus']==0 || $this->billrs['nstatus']==2){
					$bo = 1;
				}
			}
		}
		if($bo==0){
			$where 	= $this->viewmodel->editwhere($this->moders, $this->adminid);
			$tos 	= $this->rows("`id`='$this->id'  $where ");
			if($tos>0)$bo=1;
		}
		return $bo;
	}
	
	public function isdeleteqx()
	{
		$bo = 0;
		if($bo==0 && $this->isflow==1){
			if($this->billrs && $this->uid == $this->adminid){
				if($this->billrs['nstatus']==0 || $this->billrs['nstatus']==2){
					$bo = 1;
				}
			}
		}
		if($bo==0){
			$where 	= $this->viewmodel->deletewhere($this->moders, $this->adminid);
			$tos 	= $this->rows("`id`='$this->id'  $where ");
			if($tos>0)$bo=1;
		}
		return $bo;
	}
	
	
	public function getfields($lx=0)
	{
		$fields = array();
		$farr 	= $this->db->getrows('[Q]flow_element',"`mid`='$this->modeid' and `iszb`=0 and `iszs`=1",'`fields`,`name`','sort,id');
		foreach($farr as $k=>$rs)$fields[$rs['fields']] = $rs['name'];
		$fters	= $this->flowgetfields($lx);
		if(is_array($fters))$fields = array_merge($fields, $fters);
		return $fields;
	}
	
	/**
	*	获取录入页面地址
	*/
	public function getinputurl($num='',$mid=0,$can=array())
	{
		if($num=='')$num = $this->modenum;
		$xa  = 'lu';
		if($this->ismobile==1)$xa = 'lum';
		$url = 'index.php?a='.$xa.'&m=input&d=flow&num='.$num.'&mid='.$mid.'';
		if(is_array($can)){
			foreach($can as $k=>$v)$url.='&'.$k.'='.$v.'';
		}else{
			$url .= '&'.$can.'';
		}
		return $url;
	}
	
	/**
	*	读取展示数据
	*	$lx 0pc, 1移动
	*/
	public function getdatalog($lx=0)
	{
		m('log')->addread($this->mtable, $this->id);
		$fobj	 		 = m('file');
		$this->ismobile  = $lx;
		$arr['modename'] = $this->modename;
		$arr['title'] 	 = $this->modename;
		$arr['modeid']   = $this->modeid;
		$arr['modenum']  = $this->modenum;
		$arr['mid']  	 = $this->id;
		$arr['logarr']	 = $this->getlog();
		$contview 	 	 = '';
		$path 			 = ''.P.'/flow/page/view_'.$this->modenum.'_'.$lx.'.html';
		$fstr			 = $fobj->getstr($this->mtable, $this->id, 2);
		$issubtabs		 = 0;
		if($fstr != ''){
			$this->rs['file_content'] 	= $fstr;
		}
		if(isset($this->rs['explain']))$this->rs['explain'] = str_replace("\n",'<br>', $this->rs['explain']);
		if(isset($this->rs['content']))$this->rs['content'] = str_replace("\n",'<br>', $this->rs['content']);
		
		$data 			= $this->flowrsreplace($this->rs, 1);
		//读取多行子表
		$subdata 		= $this->getsuballdata(1);
		foreach($subdata as $zb=>$da){
			$sub 						= $da['fields'];
			$data[$sub] 				= $this->getsubdata($zb,$da['data']);
			$data[''.$sub.'_style'] 	= 'padding:0';
		}
		
		//文件字段替换上传和上传图片的
		foreach($this->fieldsarr as $k=>$rs){
			$fid 	= $rs['fields'];
			if($rs['fieldstype']=='uploadfile'){
				$fval 	= (int)$this->rock->arrvalue($data, $fid, '0');
				if($fval>0){
					$ftrs = $fobj->getone($fval);
					$data[$fid] = $fobj->getfilestr($ftrs, 2);
				}
			}
			if($rs['fieldstype']=='uploadimg'){
				$fval 	= $this->rock->arrvalue($data, $fid);
				if(!isempt($fval) && substr($fval,0,4)!='<img')$data[$fid] = '<img src="'.$fval.'" height="100">';	
			}
		}

		//使用了自定的展示模板
		if(file_exists($path)){
			$contview 	 = file_get_contents($path);
			$_logarr	 = array();
			foreach($arr['logarr'] as $k1=>$rs1)$_logarr[$rs1['id']] = $rs1;			
			//读取流程审核步骤信息
			$logrows 	 	= $this->flogmodel->getrows($this->mwhere.' and `courseid`>0 GROUP BY `courseid`','`courseid`,max(id)id');
			foreach($logrows as $k2=>$rs2){
				$rs3 		= $_logarr[$rs2['id']];
				$_coid 		= $rs2['courseid'];
				$data['course'.$_coid.'_name'] 	= $rs3['name'];
				$data['course'.$_coid.'_zt'] 	= '<font color="'.$rs3['color'].'">'.$rs3['statusname'].'</font>';
				$data['course'.$_coid.'_sm'] 	= $rs3['explain'];
				$data['course'.$_coid.'_dt'] 	= $rs3['checkdt'];
			}
			$contview 	 	= $this->rock->reparr($contview, $data);
		}
		
		if($this->isempt($contview)){
			$_fields		 = array();
			if($this->isflow==1){
				$_fields['base_sericnum'] 	= '单号';
				$_fields['base_name'] 		= '申请人';
				$_fields['base_deptname'] 	= '申请人部门';
			}
			$fields			 = array_merge($_fields, $this->getfields($lx));
			if($lx==0)foreach($fields as $k=>$rs){$data[''.$k.'_style'] = 'width:75%';break;}
			if($fstr!='')$fields['file_content'] 			= '相关文件';
			foreach($subdata as $zb=>$da){
				$fields[$da['fields']]	= $da['name'];
			}
			if(!isset($fields['optdt']))$fields['optdt']	= '操作时间';
			
			$contview 	= c('html')->createtable($fields, $data);
			$contview 	= '<div align="center">'.$contview.'</div>';
		}
		$arr['contview'] = $contview;
		$arr['readarr']	 = m('log')->getreadarr($this->mtable, $this->id);
		
		$arr['isedit'] 	 = $this->iseditqx();
		$arr['isdel'] 	 = $this->isdeleteqx();
		$arr['isflow'] 	 = $this->isflow;
		$arr['ischehui'] = $this->ischehui();
		
		$ztass 				= $this->getnowstatus();
		$arr['statustext']	= $ztass[0];
		$arr['statuscolor']	= $ztass[1];
		
		$arr['isgbjl']		= (int)$this->rock->arrvalue($this->moders,'isgbjl','0'); //是否关闭操作记录
		$arr['isgbcy']		= (int)$this->rock->arrvalue($this->moders,'isgbcy','0'); //是否不显示查阅记录
		
		$arr['flowinfor']= array();
		if($this->isflow==1)$arr['flowinfor']= $this->getflowinfor();
		if(isset($data['title']))$arr['title'] = $data['title'];
		$_oarr 			 = $this->flowdatalog($arr);
		if(is_array($_oarr))foreach($_oarr as $k=>$v)$arr[$k]=$v;
		return $arr;
	}
	private function getsubdata($xu, $rows)
	{
		$iscz			= 0;
		$iszb			= $xu+1;
		$fields			= 'subdata'.$xu.'';
		$subrows 		= $this->db->getrows('[Q]flow_element','`mid`='.$this->modeid.' and `iszb`='.$iszb.' and `iszs`=1','`fields`,`name`','`sort`');
		$cont 			= '';
		if($this->db->count > 0){
			$iscz		= 1;
			$headstr	= 'xuhaos,,center';
			foreach($subrows as $k=>$rs)$headstr.='@'.$rs['fields'].','.$rs['name'].'';
			foreach($rows as $k=>$rs)$rows[$k]['xuhaos'] = $k+1;
			$cont 	 	= c('html')->createrows($rows, $headstr, '#cccccc', 'noborder');
		}
		return $cont;
	}
	
	/**
	*	判断当前是否可以撤回
	*	撤回条件，审核未通过，最后一步是当前人审核的而为通过，2小时之内
	*/
	public function ischehui()
	{
		$is 	= 0;
		if($this->rs['status']==1)return $is;
		$where 	= "".$this->mwhere." and `courseid`>0 order by `id` desc";
		$rs 	= $this->flogmodel->getone($where);
		$time 	= time()-2*3600;
		if($rs && $rs['checkid']==$this->adminid && $rs['status']!=2 && strtotime($rs['optdt'])>$time )$is = $rs['id'];
		return $is;
	}
	
	/**
	*	撤回操作
	*/
	public function chehui($sm='')
	{
		$id = $this->ischehui();
		if($id==0)return '当前不允许撤回操作';
		$this->flogmodel->update('courseid=0', "`id`='$id'");
		$this->addlog(array(
			'explain' 	=> $sm,
			'name'		=> '撤回'
		));
		$barr = $this->getflow(false);
		$this->checksmodel->delete($this->mwhere.' and `optid`='.$this->adminid.'');//删除我指定的人
		//当前审核人空
		if(isempt($barr['nowcheckid'])){
			$courseid = $barr['nowcourseid'];
			$this->addcheckname($courseid, $this->adminid, $this->adminname, false,2);
			$barr = $this->getflow(false);
		}
		$this->getflowsave($barr);
		return 'ok';
	}
	
	/**
	*	读取编辑数据
	*/
	public function getdataedit()
	{
		$fobj 			= m('file');
		$arr['data'] 	= $this->rssust;
		$arr['table'] 	= $this->mtable;
		$arr['tables'] 	= $this->moders['tables'];
		$arr['modeid'] 	= $this->modeid;
		$arr['isedit'] 	= $this->iseditqx();
		$arr['isflow'] 	= $this->isflow;
		$arr['user'] 	= $this->urs;
		$arr['status'] 	= $this->rs['status'];
		$arr['filers'] 	= $fobj->getfile($this->mtable,$this->id);
		$arr['subdata'] = $this->getsuballdata();
		$uploadfile		= $this->rock->post('uploadfile');
		$filearr 		= array();
		foreach($this->fieldsarr as $k=>$rs){
			if($rs['fieldstype']=='uploadfile'){
				$fid 	= $rs['fields'];
				$fval 	= (int)$this->rock->arrvalue($this->rssust, $fid, '0');
				if($fval>0)$filearr['f'.$fval.''] = $fobj->getone($fval,'filename,id,filesizecn,fileext,optname');
			}
		}
		$arr['filearr'] = $filearr;
		$ztarr 			= $this->getnowstatus();
		$arr['statustext'] 	= $ztarr[0];
		$arr['statuscolor'] = $ztarr[1];
		return $arr;
	}
	
	/*
	*	读取流程信息
	*/
	public function getflowinfor()
	{
		$ischeck = 0;
		$ischange= 0;
		$str	 = '';
		$arr 	 = $this->getflow();
		$nstatus = $this->rs['status'];
		$nowcheckid = ','.$arr['nowcheckid'].',';
		if($nstatus !=1 && contain($nowcheckid, ','.$this->adminid.',') && $nstatus != 2){
			$ischeck = 1;
		}
		$logarr = $this->getlog();
		$nowcur = $this->nowcourse;
		if($this->rock->arrvalue($this->nextcourse,'checktype')=='change')$ischange = 1; //需要自己选择下一步处理人
		$sarr['ischeck'] 		= $ischeck;
		$sarr['ischange'] 		= $ischange;
		$sarr['nowcourse'] 		= $nowcur;
		$sarr['iszhuanban'] 	= $this->rock->arrvalue($nowcur,'iszf',0);
		$sarr['nextcourse'] 	= $this->nextcourse;
		$sarr['nstatustext'] 	= $arr['nstatustext'];
		
		//读取当前审核表单
		$_checkfields	= $this->rock->arrvalue($nowcur,'checkfields');
		$checkfields	= array();
		if($ischeck == 1 && !isempt($_checkfields)){
			$inputobj			= c('input');
			$inputobj->flow 	= $this;
			$inputobj->mid 		= $this->id;
			$inputobj->urs		= $this->urs;
			$elwswhere			= "`mid`='$this->modeid' and `iszb`=0 and instr(',$_checkfields,', concat(',',`fields`,','))>0";
			$infeidss  = $inputobj->initFields($elwswhere);
			foreach($infeidss as $_fs=>$fsva){
				$_sfes = $fsva['fields'];
				$_type = $fsva['fieldstype'];
				$checkfields[$_sfes] = array(
					'inputstr' 	=> $inputobj->getfieldcontval($_sfes, $this->rock->arrvalue($this->rssust, $_sfes)),
					'name' 		=> $fsva['name'],
					'fieldstype'=> $_type,
					'fieldsarr' => $fsva,
					'showinpus' => 1
				);
				if(substr($_type,0,6)=='change' && !isempt($fsva['data'])){
					$_sfes = $fsva['data'];
					$checkfields[$_sfes] = array(
						'inputstr' 	=> '',
						'name' 		=> $fsva['name'].'id',
						'fieldsarr' => false,
						'showinpus' => 2
					);
				}
			}
		}
		$sarr['checkfields']	= $checkfields;
		if($nstatus==2)$sarr['nstatustext'] ='<font color="#AB47F7">待提交人处理('.$this->urs['name'].')</font>';
		$loglen 				= count($logarr);
		foreach($logarr as $k=>$rs){
			$rs = $logarr[$loglen-$k-1];
			if($rs['courseid']>0){
				$sty = '';
				$col = $rs['color'];
				if($str!='')$str.=' → ';
				$str.='<span style="'.$sty.'">'.$rs['actname'].'('.$rs['name'].'<font color="'.$col.'">'.$rs['statusname'].'</font>)</span>';
			}
		}
		//未通过
		if($nstatus=='2'){
			if($str!='')$str.=' → ';
			$str.= $sarr['nstatustext'];
		}else{
			foreach($this->flowarr as $k=>$rs){
				if($rs['ischeck']==0){
					$sty = 'color:#888888';
					if($rs['isnow']==1)$sty='font-weight:bold;color:#800000';
					if($str!='')$str.=' <font color=#888888>→</font> ';
					$str.='<span style="'.$sty.'">'.$rs['name'].'';
					if(!isempt($rs['nowcheckname']))$str.='('.$rs['nowcheckname'].')';
					$str.='</span>';
				}
			}
		}	
		
		$sarr['flowcoursestr'] 	= $str;
		if($nstatus==1)$sarr['nstatustext'] = $this->getnowstatus(1); //完成后状态
		
		$actstr	= ',通过|green,不通过|red';
		if(isset($nowcur['courseact']) ){
			$actstrt = $nowcur['courseact'];
			if(!isempt($actstrt))$actstr = ','.$actstrt;
		}
		$act 	= c('array')->strtoarray($actstr);
		foreach($act as $k=>$as1)if($k>0 && $as1[0]==$as1[1])$act[$k][1]='';
		$sarr['courseact'] 		= $act;
		$nowstatus				= $this->rs['status'];
		if($this->isflow==1 && $this->rs['isturn']==0)$nowstatus=3;
		$sarr['nowstatus']		= $nowstatus;
		
		//不通过退回可选择人员
		$step = $this->rock->arrvalue($nowcur, 'step','0');
		$tuicourse = $this->flogmodel->getall($this->mwhere.' and `courseid`>0 and `valid`=1 and `status`=1 and `step`<'.$step.'','`id`,`checkname`,`name`','`step` desc');
		$sarr['tuicourse']		= $tuicourse;
		
		return $sarr;
	}
	
	/**
	*	更新单据状态
	*/
	public function updatestatus($zt)
	{
		$this->update('`status`='.$zt.'', $this->id);
		$this->billmodel->update('`status`='.$zt.'', $this->mwhere);
	}
	
	/**
	*	获取某单据当前状态
	*/
	public function getstatus($rs, $statusstr='',$other='', $glx=0)
	{
		$statustext	= $statuscolor = '';
		if($statusstr=='')$statusstr=$this->rock->arrvalue($this->moders,'statusstr');
		$statusara 	= array();
		$colorsa   	= array('blue','green','red','#ff6600','#526D08','#888888','','','','','','','','','','','','','');
		if(isempt($statusstr))$statusstr =  '待?处理|blue,已审核|green,未通过|red';
		$statusstr 	= str_replace('?',$other,$statusstr);
		$statusar  	= c('array')->strtoarray($statusstr);
		foreach($statusar as $k=>$v){
			if($v[0]==$v[1])$v[1]= $colorsa[$k];
			$statusara[$k] = $v;
		}
		$statusara[5] 	= array('已作废','#888888');
		$statusara[23] 	= array('退回','#17B2B7');
		if($glx==2)return $statusara;
		
		$isturn 	= -1;
		if(isset($rs['isturn']))$isturn = (int)$rs['isturn'];
		$zt 		= $this->rock->arrvalue($rs, 'status');
		if($isturn==0){
			$statustext	= '待提交';
			$statuscolor= '#ff6600';
		}elseif(!isempt($zt)){
			if(isset($statusara[$zt])){
				$statustext = $statusara[$zt][0];
				$statuscolor = $statusara[$zt][1];
			}
		}
		if($glx==1)return '<font color="'.$statuscolor.'">'.$statustext.'</font>';
		return array($statustext, $statuscolor, $zt);
	}
	
	/**
	*	当前单击状态
	*/
	public function getnowstatus($glx=0)
	{
		return $this->getstatus($this->rs, '','', $glx);
	}
	
	private $getlogrows = array();
	public function getlog()
	{
		if($this->getlogrows)return $this->getlogrows;
		$rows = $this->flogmodel->getrows($this->mwhere, '`checkname` as `name`,`checkid`,`name` as actname,`optdt`,`explain`,`statusname`,`courseid`,`color`,`id`','`id` desc');
		$uids = $idss = '';
		$dts  = c('date');
		$fo   = m('file');
		foreach($rows as $k=>$rs){
			$uids.=','.$rs['checkid'].'';
			$idss.=','.$rs['id'].'';
			$col = $rs['color'];
			if(isempt($col))$col='green';
			if(contain($rs['statusname'],'不'))$col='red';
			$rows[$k]['color'] 		= $col;
			$rows[$k]['checkdt'] 	= $rs['optdt'];
			$rows[$k]['optdt'] 	= $dts->stringdt($rs['optdt'], 'G(周w) H:i:s');	
		}
		//读取相关文件
		if($idss!=''){
			$farr = $fo->getfile('flow_log', substr($idss, 1));
			if($farr)foreach($rows as $k=>$rs){
				$fstr 		= $fo->getallstr($farr, $rs['id'],2);
				$rows[$k]['explain']= $this->strappend($rs['explain'], $fstr, '<br>');
			}
		}
		//读取对应人员头像
		if($uids!=''){
			$rows = m('admin')->getadmininfor($rows, substr($uids, 1), 'checkid');
		}
		$this->getlogrows = $rows;
		return $rows;
	}
	
	public function addlog($arr=array())
	{
		$addarr	= array(
			'table'		=> $this->mtable,
			'mid'		=> $this->id,
			'checkname'	=> $this->adminname, 
			'checkid'	=> $this->adminid, 
			'optdt'		=> $this->rock->now,
			'courseid'	=> '0',
			'status'	=> '1',
			'ip'		=> $this->rock->ip,
			'web'		=> $this->rock->web,
			'modeid'	=> $this->modeid
		);
		foreach($arr as $k=>$v)$addarr[$k]=$v;
		$this->flogmodel->insert($addarr);
		$ssid = $this->db->insert_id();
		$fileid			= $this->rock->post('fileid');
		if($fileid!='')m('file')->addfile($fileid, 'flow_log', $ssid);
		$logfileid		= $this->rock->post('logfileid');
		if($logfileid!='')m('file')->addfile($logfileid, $this->mtable, $this->id);
		$addarr['id'] 	= $ssid;
		$this->flowaddlog($addarr);
		return $ssid;
	}
	
	public function submit($na='', $sm='')
	{
		if($na=='')$na='提交';
		$isturn	 = 1;
		if($na=='保存')$isturn	= 0;
		$this->addlog(array(
			'name' 		=> $na,
			'explain' 	=> $sm
		));
		if($this->isflow == 1){
			$marr['isturn'] = $isturn;
			$marr['status'] = 0;
			$this->rs['status'] = 0;
			$this->update($marr, $this->id);
			$farr = $this->getflow();
			//第一步自定义审核人
			if($farr['nowcourseid']>0){
				$sysnextoptid 	= $this->rock->post('sysnextoptid'); $sysnextopt 	= $this->rock->post('sysnextopt'); $sysnextcustidid = (int)$this->rock->post('sysnextcustidid');
				if($sysnextcustidid == $farr['nowcourseid'] && !isempt($sysnextoptid) && !isempt($sysnextopt)){
					$this->addcheckname($sysnextcustidid, $sysnextoptid, $sysnextopt, true, 1);
					$farr = $this->getflow(); //重新在读取匹配流程
				}
			}
			$farr['status'] = 0;
			$this->savebill($farr);
			if($isturn == 1){
				$this->nexttodo($farr['nowcheckid'],'submit');
			}
		}
		$this->flowsubmit($na, $sm);
		if($na=='编辑'){
			$this->gettodosend('boedit');
		}else{
			$this->gettodosend('boturn');//提交
		}
	}
	
	/**
	* 追加说明
	*/
	public function zhuijiaexplain($sm='')
	{
		$this->addlog(array(
			'explain' 	=> $sm,
			'name'		=> '追加说明',
			'status'	=> 1,
		));
		$zt = $this->rs['status'];
		if($zt==2 && $this->isflow==1){
			$marr['status'] 	= 0;
			$this->rs['status'] = 0;
			$this->update($marr, $this->id);
			$farr = $this->getflow();
			$farr['status'] 	= 0;
			$this->savebill($farr);
			$this->nexttodo($farr['nowcheckid'],'zhui', $sm);
		}
		$this->gettodosend('bozhui','', $sm);
	}
	
	/**
	*	匹配流程读取
	*/
	public function getflowpipei($uid=0)
	{
		$rows  	= $this->db->getrows('[Q]flow_course', "`setid`='$this->modeid' and `status`=1" ,'*', '`pid`,`sort`,`id` asc');
		$gpid 	= 0; $psid 	= -1; $oj = 0;
		if($uid == 0)$uid 	= $this->adminid;
		$garr	= $lcarr  	= array();
		foreach($rows as $k=>$rs){
			if(isempt($rs['pid']))$rs['pid'] = 0;
			if($rs['pid'] != $psid)$garr[] = $rs;
			$psid	= $rs['pid'];
			$rows[$k]['pid'] = $psid;
		}
		$urs 	= $this->urs;
		if(!$urs)$urs = $uid;
		if(!is_array($urs))$urs 	= $this->db->getone('[Q]admin', "`id`='$urs'", '`deptid`,`deptpath`,`id`');
		$gpid 		= m('kaoqin')->getpipeimid($urs, $garr, 'pid', 0); //匹配到哪个流程
		$uid 		= $urs['id'];
		$upath 		= '['.$uid.'],[u'.$uid.']';
		if(!isempt($urs['deptpath'])){
			$depta		= explode(',', str_replace(array('[',']'), array('',''), $urs['deptpath']));
			foreach($depta as $depta1)$upath.=',[d'.$depta1.']';
		}
		foreach($rows as $k=>$rs){
			if($rs['pid'] == $gpid){
				$oj++;
				$receid 	 = $rs['receid'];
				if(!isempt($receid) && $oj>1){
					$receida = explode(',', $receid);
					foreach($receida as $receidas){
						if(contain($upath, '['.$receidas.']')){
							$lcarr[] 	= $rs;
							break;
						}
					}
				}else{
					$lcarr[] 	= $rs;
				}
			}
		}
		return $lcarr;
	}
	/*
	*	获取流程
	*/
	public function getflow($sbo=false)
	{
		$this->flowarr 	= array();
		$allcheckid 	= $nowcheckid 	=  $nowcheckname  = $nstatustext = '';
		$allcheckids	= array();
		$nowcourseid	= 0;
		$nstatus 		= $this->rs['status'];
		$this->nowcourse	= array();
		$this->nextcourse	= array();
		$this->flowisend	= 0;
		
		$curs 	= $this->flogmodel->getrows("$this->mwhere and `courseid`>0",'checkid,checkname,courseid,`valid`,`status`,`statusname`,`name`','id desc');
		$cufss  =  $ztnas  = $chesarr	= array();
		foreach($curs as $k=>$rs){
			$_su  = ''.$rs['courseid'].'';
			$_su1 = ''.$rs['courseid'].'_'.$rs['checkid'].'';
			if($rs['valid']==1 && $rs['status']==1){
				if(!isset($cufss[$_su]))$cufss[$_su]=0;
				$cufss[$_su]++;
				$chesarr[$_su1] = 1;
			}
			if(!in_array($rs['checkid'], $allcheckids))$allcheckids[] = $rs['checkid'];
			if($nstatustext=='' && $rs['courseid']>0){
				$nstatustext = ''.$rs['checkname'].'处理'.$rs['statusname'].'';
				$nstatus	 = $rs['status'];
			}
			$ztnas[$rs['courseid']] = ''.$rs['checkname'].''.$rs['statusname'].'';
		}
		$nowstep = $zongsetp  = -1;
		$isend 	= 0;
		
		$rows  	= ($this->rs['status']==1)? array() : $this->getflowpipei($this->uid);
		if($rows){
			//读取flow_checks是否比审核状态(退回的)
			$checksa = $this->checksmodel->getrows($this->mwhere.' and `addlx`=3');
			$coursea = array();
			foreach($checksa as $k=>$rs)$coursea[$rs['courseid']]='1';
			foreach($rows as $k=>$rs){
				$whereid 	= (int)$rs['whereid'];
				$checkshu 	= $rs['checkshu'];
				
				//不在审核人子表中
				if(!isset($coursea[$rs['id']])){
					if($whereid > 0){
						$bo = $this->wheremanzhu($whereid);
						if(!$bo)continue;
					}
					
					if(!isempt($rs['num'])){
						$bo = $this->flowcoursejudge($rs['num']);
						if(is_bool($bo) && !$bo)continue;
					}
				}
				
				$zongsetp++;
				$uarr 		= $this->getcheckname($rs);
				$checkid	= $uarr[0];
				$checkname	= $uarr[1];
				$ischeck 	= 0;
				$checkids	= $checknames = '';
				
				$_su  		= ''.$rs['id'].'';
				$nowshu		= 0;
				if(isset($cufss[$_su]))$nowshu = $cufss[$_su];
				
				if(!$this->isempt($checkid)){
					$checkida 	= explode(',', $checkid);
					$checkidna 	= explode(',', $checkname);
					$_chid		= $_chna	= '';
					
					foreach($checkida as $k1=>$chkid){
						$_su1 = ''.$rs['id'].'_'.$chkid.'';
						if(!in_array($chkid, $allcheckids))$allcheckids[] = $chkid;
						if(!isset($chesarr[$_su1])){
							$_chid.=','.$chkid.'';
							$_chna.=','.$checkidna[$k1].'';
						}
					}
					if($_chid!='')$_chid = substr($_chid, 1);
					if($_chna!='')$_chna = substr($_chna, 1);
					
					if($_chid==''){
						$ischeck	= 1;
					}else{
						if($checkshu>0&&$nowshu>=$checkshu)$ischeck	= 1;
					}
					$checkids 	= $_chid;
					$checknames = $_chna;
				}else{
					if($checkshu>0&&$nowshu>=$checkshu)$ischeck	= 1;				
					//需要全部审核时 同时已有审核过了 也没有审核人了
					if($checkshu == 0 && $nowshu>0)$ischeck = 1;
				}
				
				$rs['ischeck'] 		= $ischeck;
				$rs['islast'] 		= 0;
				$rs['checkid'] 		= $checkid;
				$rs['checkname'] 	= $checkname;
				$rs['nowcheckid'] 	= $checkids;
				$rs['nowcheckname'] = $checknames;
				$rs['isnow'] 		= 0;
				$rs['nowstep']	 	= $zongsetp;
				$rs['step']	 		= $k+1;
				
				if($ischeck==0 && $nowstep==-1){
					$rs['isnow']= 1;
					$nowstep = $zongsetp;
					$this->nowcourse = $rs;	//当前审核步骤信息
					$nowcourseid	 = $rs['id'];
					$nowcheckid		 = $checkids;
					$nowcheckname	 = $checknames;
				}
				
				if($nowstep>-1 && $zongsetp==$nowstep+1)$this->nextcourse = $rs; //下一步信息
				$this->flowarr[]= $rs;
			}
		}
		if($zongsetp>-1)$this->flowarr[$zongsetp]['islast']=1;
		if($nowstep == -1){
			$isend = 1;
		}else{
			$nstatustext 	= '待'.$nowcheckname.'处理';
		}
		$this->flowisend 	= $isend;
		$allcheckid			= join(',', $allcheckids);
		$arrbill['allcheckid'] 		= $allcheckid;
		$arrbill['nowcourseid'] 	= $nowcourseid;
		$arrbill['nowcheckid'] 		= $nowcheckid;
		$arrbill['nowcheckname']	= $nowcheckname;
		$arrbill['nstatustext']		= $nstatustext;
		$arrbill['nstatus']			= $nstatus;
		$arrbill['status']			= $this->rs['status'];
		if($sbo)$this->getflowsave($arrbill);
		return $arrbill;
	}
	private function wheremanzhu($id)
	{
		$ser = $this->wheremodel->getflowwhere($id, $this->adminid);
		if(!$ser)return true;
		$str = $ser['ntr'];
		if(!isempt($str)){
			$to = $this->db->rows('[Q]admin',"`id`='$this->uid' and ($str)");
			if($to>0)return false;
		}
		$str = $ser['str'];
		if(!isempt($str)){
			$to = $this->rows("`id`='$this->id' and $str");
			if($to==0)return false;
		}
		$str = $ser['utr'];
		if(!isempt($str)){
			$to = $this->db->rows('[Q]admin',"`id`='$this->uid' and $str");
			if($to==0)return false;
		}
		return true;
	}
	
	public function getflowsave($sarr, $suvu=false)
	{
		if(!$sarr)return;
		if($suvu)$sarr['updt'] = $this->rock->now;
		$this->billmodel->update($sarr, $this->mwhere);
	}
	
	//获取审核人
	private function getcheckname($crs)
	{
		$type	= $crs['checktype'];
		$cuid 	= $name = '';
		$courseid = $crs['id'];
		if(!$this->isempt($crs['num'])){
			$uarr	= $this->flowcheckname($crs['num']);
			if(is_array($uarr)){
				if(!$this->isempt($uarr[0]))return $uarr;
			}
		}
		
		$cheorws= $this->checksmodel->getall($this->mwhere.' and courseid='.$courseid.' and `status`=0','checkid,checkname');
		if($cheorws){
			foreach($cheorws as $k=>$rs){
				$cuid.=','.$rs['checkid'].'';
				$name.=','.$rs['checkname'].'';
			}
			if($cuid != ''){
				$cuid = substr($cuid, 1);
				$name = substr($name, 1);
				return array($cuid, $name);
			}
		}
		
		if($type=='super'){
			$cuid = $this->urs['superid'];
			$name = $this->urs['superman'];
		}
		if($type=='dept' || $type=='super'){
			if($this->isempt($cuid) && $this->drs){
				$cuid = $this->drs['headid'];
				$name = $this->drs['headman'];
			}
		}
		if($type=='apply'){
			$cuid = $this->urs['id'];
			$name = $this->urs['name'];
		}
		if($type=='opt'){
			$cuid = $this->rs['optid'];
			$name = $this->rs['optname'];
		}
		if($type=='user'){
			$cuid = $crs['checktypeid'];
			$name = $crs['checktypename'];
		}
		if($type=='rank'){
			$rank = $crs['checktypename'];
			if(!$this->isempt($rank)){
				$rnurs	= $this->db->getrows('[Q]admin',"`status`=1 and `ranking`='$rank'",'id,name','sort');
				foreach($rnurs as $k=>$rns){
					$cuid.=','.$rns['id'].'';
					$name.=','.$rns['name'].'';
				}
				if($cuid != ''){
					$cuid = substr($cuid, 1);
					$name = substr($name, 1);
				}
			}
		}
		$cuid	= $this->rock->repempt($cuid);
		$name	= $this->rock->repempt($name);
		return array($cuid, $name);
	}
	
	/**
	*	创建编号
	*/
	public function createbianhao($num, $fid)
	{
		if(isempt($num))$num=''.$this->modenum.'-';
		@$appdt = $this->rs['applydt'];
		if(isempt($appdt))$appdt = $this->rock->date;
		$apdt 	= str_replace('-','', $appdt);
		$num	= str_replace('Ymd',$apdt,$num);
		return $this->db->sericnum($num,'[Q]'.$this->mtable.'', $fid,3);
	}
	
	
	/**
	*	创建流程单号
	*/
	public function createnum()
	{
		$num = $this->moders['sericnum'];
		if($num=='无'||$this->isempt($num))$num='TM-Ymd-';
		@$appdt = $this->rs['applydt'];
		if(isempt($appdt))$appdt = $this->rock->date;
		$apdt 	= str_replace('-','', $appdt);
		$num	= str_replace('Ymd',$apdt,$num);
		return $this->db->sericnum($num,'[Q]flow_bill');
	}
	public function savebill($oarr=array())
	{
		$dbs = $this->billmodel;
		$whes= $this->mwhere;
		$biid= (int)$dbs->getmou('id', $whes);
		$arr = array(
			'table' => $this->mtable,
			'mid' 	=> $this->id,
			'optdt' => isset($this->rs['optdt']) ? $this->rs['optdt'] : $this->rock->now,
			'optname' 	=> $this->adminname,
			'optid' 	=> $this->adminid,
			'modeid'  	=> $this->modeid,
			'updt'  	=> $this->rock->now,
			'isdel'		=> '0',
			'nstatus'	=> $this->rs['status'],
			'applydt'	=> $this->rs['applydt'],
			'modename'  => $this->modename
		);
		foreach($oarr as $k=>$v)$arr[$k]=$v;
		if($biid==0){
			$arr['uid'] 	= $this->uid;
			$arr['status'] 	= $arr['nstatus'];
			$arr['createdt']= $arr['optdt'];
			$arr['sericnum']= $this->createnum();
			$whes			= '';
			$this->sericnum	= $arr['sericnum'];
		}
		$dbs->record($arr, $whes);
		return $arr;
	}
	
	public function nexttodo($nuid, $type, $sm='', $act='')
	{
		$cont	= '';
		$gname	= '流程待办';
		if($type=='submit' || $type=='next'){
			$cont = '你有['.$this->adminname.']的['.$this->modename.',单号:'.$this->sericnum.']需要处理';
			if($sm!='')$cont.='，说明:'.$sm.'';
		}
		//审核不通过
		if($type == 'nothrough'){
			$cont = '你提交['.$this->modename.',单号:'.$this->sericnum.']'.$this->adminname.'处理['.$act.']，原因:['.$sm.']';
			$gname= '流程申请';
		}
		if($type == 'finish'){
			$cont = '你提交的['.$this->modename.',单号:'.$this->sericnum.']已全部处理完成';
		}
		if($type == 'zhui'){
			$cont = '你有['.$this->adminname.']的['.$this->modename.',单号:'.$this->sericnum.']需要处理，追加说明:['.$sm.']';
		}
		//退回
		if($type == 'tui'){
			$cont = '['.$this->adminname.']退回单据['.$this->modename.',单号:'.$this->sericnum.']到你这请及时处理，说明:'.$sm.'';
		}
		if($cont!='')$this->push($nuid, $gname, $cont);
	}
	
	private function addcheckname($courseid, $uid, $uname, $onbo=false, $addlx=0)
	{
		$uida 	= explode(',', ''.$uid.'');
		$uidan 	= explode(',', $uname);
		if($onbo)$this->checksmodel->delete($this->mwhere.' and `courseid`='.$courseid.'');
		foreach($uida as $k=>$uid){
			$uname	= $this->rock->arrvalue($uidan, $k);
			$zyarr 	= array(
				'table' 	=> $this->mtable,
				'mid' 		=> $this->id,
				'modeid' 	=> $this->modeid,
				'courseid' 	=> $courseid,
				'optid' 	=> $this->adminid,
				'optname' 	=> $this->adminname,
				'addlx' 	=> $addlx, //添加类型:1自定义,2撤回添加,3退回添加,4转移添加
				'optdt' 	=> $this->rock->now,
				'status' 	=> 0
			);
			$this->checksmodel->delete($this->mwhere.' and `checkid`='.$uid.' and `courseid`='.$courseid.'');
			$zyarr['checkid'] 	= $uid;
			$zyarr['checkname'] = $uname;
			$this->checksmodel->insert($zyarr);
		}
	}
	
		
	/**
	*	判断保存的数据是否
	*/
	public function savedatastr($fval, $farr, $data=array())
	{
		$str 		= '';
		if(!$farr)return $str;
		$savewhere 	= $farr['savewhere'];
		$name 		= $farr['name'];
		$types 		= $farr['fieldstype'];
		if(isempt($savewhere) || isempt($fval))return $str;
		$savewhere	= str_replace(array('{0}','{date}','{now}'), array($name, $this->rock->date,$this->rock->now), $savewhere);
		$savewhere	= $this->rock->reparr($savewhere, $data);
		$saees		= explode(',', $savewhere);
		if($types=='date' || $types=='datetime')$fval = strtotime($fval);
		if($types=='number')$fval = floatval($fval);
		foreach($saees as $saeess){
			$fsaed 	= explode('|', $saeess);
			$msg 	= isset($fsaed[2]) ? $fsaed[2] : ''.$name.'数据不符号';
			$val 	= isset($fsaed[1]) ? $fsaed[1] : '';
			$lfs 	= $fsaed[0];
			if($val != ''){
				if($types=='date' || $types=='datetime')$val = strtotime($val);
				if($types=='number')$val = floatval($val);
				if($lfs=='gt'){$bo = $fval>$val;if(!$bo)return $msg;}
				if($lfs=='egt'){$bo = $fval>=$val;if(!$bo)return $msg;}
				if($lfs=='lt'){$bo = $fval<$val;if(!$bo)return $msg;}
				if($lfs=='elt'){$bo = $fval<=$val;if(!$bo)return $msg;}
				if($lfs=='eg'){$bo = $fval==$val;if(!$bo)return $msg;}
				if($lfs=='neg'){$bo = $fval!=$val;if(!$bo)return $msg;}
			}
		}
		return $str;
	}
	
	/**
	*	重写更新一下方法
	*/
	public function update($arr, $where)
	{
		if(is_array($arr)){
			foreach($arr as $k=>$v)$this->rs[$k]=$v;
		}
		return parent::update($arr,$where);
	}
	
	/**
	*	将操作记录标识无效状态
	*/
	public function updatelogvalid($whe)
	{
		$this->flogmodel->update('valid=0', $this->mwhere.' '.$whe);
	}
	
	/**
	*	说明追加
	*/
	public function strappend($sm, $str, $fh=',')
	{
		if(isempt($str))return $sm;
		if(!isempt($sm))$sm.=$fh;
		$sm.=$str;
		return $sm;
	}
	
	
	/**
	*	处理
	*/
	public function check($zt, $sm='')
	{
		if($this->rs['status']==1)$this->echomsg('流程已处理完成,无需操作');
		$arr 	 	= $this->getflow();
		$flowinfor 	= $this->getflowinfor();
		if($flowinfor['ischeck']==0){
			$this->echomsg('当前是['.$arr['nowcheckname'].']处理');
		}
		$nowcourse	= $this->nowcourse;
		$nextcourse	= $this->nextcourse;
		$zynameid	= $this->rock->post('zynameid');
		$zyname		= $this->rock->post('zyname');
		$nextname	= $this->rock->post('nextname');
		$nextnameid	= $this->rock->post('nextnameid');
		$tuiid		= (int)$this->rock->post('tuiid'); //退回到哪个flowlog.id上
		$iszhuanyi	= $ischangenext = 0;
		if($zt==1 && $this->rock->arrvalue($nextcourse,'checktype')=='change'){
			if($nextnameid=='')$this->echomsg('请选择下一步处理人');
			$ischangenext = 1;
		}
		if($zt!=2)$tuiid = 0;//只有2的状态才能退回
		if($zynameid!='' && $zt==1){
			if($zynameid==$this->adminid)$this->echomsg('不能转给自己');
			$sm 	= $this->strappend($sm, '转给：'.$zyname.'');
			$iszhuanyi 		 = 1;
			$this->rs['zb_name'] 	= $zyname;
			$this->rs['zb_nameid'] 	= $zynameid;
		}
		$ufied 	= array();
		if($iszhuanyi == 0 && $zt==1){
			foreach($flowinfor['checkfields'] as $chef=>$chefv){
				$ufied[$chef] = $this->rock->post('cfields_'.$chef.'');
				if(isempt($ufied[$chef]))$this->echomsg(''.$chefv['name'].'不能为空');
				$_str = $this->savedatastr($ufied[$chef], $chefv['fieldsarr'], $this->rs);
				if($_str!='')$this->echomsg($_str);
			}
		}
		
		$this->checkiszhuanyi = $iszhuanyi;//是否为转移的
		
		$barr 		= $this->flowcheckbefore($zt, $ufied, $sm);
		$msg 		= '';
		if(is_array($barr) && isset($barr['msg']))$msg = $barr['msg'];
		
		//更新字段
		if(is_array($barr) && isset($barr['update'])){
			foreach($barr['update'] as $_k=>$_v)$ufied[$_k] = $_v;
		}
		if(is_string($barr))$msg = $barr;
		if(!isempt($msg))$this->echomsg($msg);
		
		if($ufied){
			$bo = $this->update($ufied, $this->id);
			if(!$bo)$this->echomsg('dberr:'.$this->db->error());
		}
		
		$courseact 	= $flowinfor['courseact'];
		$act 		= $courseact[$zt];
		$statusname	= $act[0];//状态名称
		$statuscolor= $act[1];//状态颜色
		$nzt		= $act[2];//处理后对应状态
		$courseid	= $nowcourse['id'];
		
		$this->checksmodel->delete($this->mwhere.' and `checkid`='.$this->adminid.' and `courseid`='.$courseid.'');
		if($iszhuanyi == 1){
			$this->addcheckname($courseid, $zynameid, $zyname, false, 4);
			$nowcourse['id'] = 0;
		}
		if($ischangenext==1){
			$_nesta = explode(',', $nextnameid);
			$_nestb = explode(',', $nextname);
			foreach($_nesta as $_i=>$_nes)$this->addcheckname($nextcourse['id'], $_nesta[$_i], $_nestb[$_i]);
		}
		
		//读取退回记录
		$tuirs 	= array();
		if($tuiid > 0)$tuirs = $this->flogmodel->getone($tuiid);
		if(!$tuirs)$tuiid = 0;
		if($tuiid>0){
			$sm = $this->strappend($sm, '退回到['.$tuirs['name'].'('.$tuirs['checkname'].')]');
			$statusname 	= '退回';
			$statuscolor	= '#17B2B7';
		}
		$this->checkistui 	= $tuiid;//是否为退回的
		$this->addlog(array(
			'courseid' 	=> $nowcourse['id'],
			'name' 		=> $nowcourse['name'],
			'step' 		=> $nowcourse['step'],
			'status'	=> $zt,
			'statusname'=> $statusname,
			'color'		=> $statuscolor,
			'explain'	=> $sm
		));
		
		
		//退回处理
		if($tuiid > 0){
			$this->addcheckname($tuirs['courseid'], $tuirs['checkid'], $tuirs['checkname'], true, 3);
			$this->updatelogvalid('and `courseid`>0 and `status`=1 and `step`>='.$tuirs['step'].'');
		}
		
		$lzt 		= $this->rock->repempt($nzt, $zt);//最后状态
		
		$uparr		= array();
		$bsarr 	 	= $this->getflow();
		$bsarr['tuiid'] = $tuiid;
		$nextcheckid = $bsarr['nowcheckid']; //下一步审核人
		
		if($zt==1){//通过
			if($iszhuanyi==0){
				$uparr['status'] 	= $this->rock->repempt($nzt,'0');
			}
			$this->nexttodo($nextcheckid, 'next', $sm, $statusname); //通知下一步处理人
		}else{
			if($tuiid>0){
				$lzt = 23; //退回固定状态23
				$this->nexttodo($nextcheckid, 'tui', $sm, $statusname); //通知到退回的人员
			}elseif($zt==2){//2固定不通过
				$this->nexttodo($this->optid, 'nothrough', $sm, $statusname);
			}
			$uparr['status'] 	= $lzt;
		}
		$this->flowcheckafter($zt, $sm);
		
		$bsarr['nstatus'] = $lzt;
		$bsarr['checksm'] = $sm;
		
		//没有当前步骤就是结束完成了
		if(!$this->nowcourse){
			$uparr['status'] = $lzt;
			$this->nexttodo($this->optid, 'finish', $sm);
		}
		
		if($uparr){
			$this->update($uparr, $this->id);
			foreach($uparr as $k=>$v)$this->rs[$k]=$v;
		}
		
		//审核完成了调用对应函数接口
		if(!$this->nowcourse){
			$this->flowcheckfinsh($zt);
			if($zt==1){
				$this->checksmodel->delete($this->mwhere);//完成了删除设置审核人
			}
		}
		$bsarr['status'] = $this->rs['status'];//状态
		$this->getflowsave($bsarr, true);
		
		//通知发送的
		if($iszhuanyi == 1){
			$this->gettodosend('bozhuan','', $sm, 0, ''.$this->adminname.'将['.$nowcourse['name'].']转给:'.$zyname.'');
		}else{
			if($zt==1)$this->gettodosend('botong', $statusname, $sm, $nowcourse['id']);
			if($zt==2)$this->gettodosend('bobutong',$statusname, $sm, $nowcourse['id']);
			if(!$this->nowcourse && $zt==1)$this->gettodosend('bofinish', '', $sm);	//全部完成了
		}
		return '处理成功';
	}
	
	public function push($receid, $gname='', $cont, $title='', $wkal=0)
	{
		if($this->isempt($receid) && $wkal==1)$receid='all';
		if($this->isempt($receid))return false;
		if($gname=='')$gname = $this->modename;
		$reim	= m('reim');
		$url 	= ''.URL.'task.php?a=p&num='.$this->modenum.'&mid='.$this->id.'';
		$wxurl 	= ''.URL.'task.php?a=x&num='.$this->modenum.'&mid='.$this->id.'';
		$emurl 	= ''.URL.'task.php?a=a&num='.$this->modenum.'&mid='.$this->id.'';
		if($this->id==0){
			$url = '';$wxurl = '';$emurl='';
		}
		$slx	= 0;
		$pctx	= $this->moders['pctx'];
		$mctx	= $this->moders['mctx'];
		$wxtx	= $this->moders['wxtx'];
		$emtx	= $this->moders['emtx'];
		if($pctx==0 && $mctx==1)$slx=2;
		if($pctx==1 && $mctx==0)$slx=1;
		if($pctx==0 && $mctx==0)$slx=3;
		$this->rs['now_adminname'] 	= $this->adminname;
		$this->rs['now_modename'] 	= $this->modename;
		$cont	= $this->rock->reparr($cont, $this->rs);
		if(contain($receid,'u') || contain($receid, 'd'))$receid = m('admin')->gjoin($receid);
		m('todo')->addtodo($receid, $this->modename, $cont, $this->modenum, $this->id);
		$reim->pushagent($receid, $gname, $cont, $title, $url, $wxurl, $slx);
		
		
		if($title=='')$title = $this->modename;
		//邮件提醒发送不发送全体人员的，太多了
		if($emtx == 1 && $receid != 'all'){
			$emcont = '您好：<br>'.$cont.'(邮件由系统自动发送)';
			if($emurl!=''){
				$emcont.='<br><a href="'.$emurl.'" target="_blank" style="color:blue"><u>详情&gt;&gt;</u></a>';
			}
			m('email')->sendmail($title, $emcont, $receid);
		}
		
		//微信提醒发送
		if($wxtx==1){
			$wxarra  = $this->flowweixinarr;
			$wxarr	 = array(
				'title' 		=> $title,
				'description' 	=> $cont,
				'url' 			=> $wxurl
			);
			$picurl  = $this->rock->arrvalue($this->rs, 'fengmian');
			if($picurl != ''){
				if(substr($picurl,0,4) != 'http')$picurl = URL.$picurl;
				$wxarr['picurl'] = $picurl;
			}
			foreach($wxarra as $k=>$v)$wxarr[$k]=$v;
			//微信企业号
			if($reim->installwx(0)){
				m('weixin:index')->sendnews($receid, ''.$gname.',0', $wxarr);
			}
			//企业微信提醒
			if($reim->installwx(1)){
				m('weixinqy:index')->sendxiao($receid, ''.$gname.',办公助手', $wxarr);
			}
			$this->flowweixinarr=array();
		}
	}
	
	public function getwxurl($num='')
	{
		if($num=='')$num = $this->modenum;
		$str = ''.URL.'?m=ying&d=we&num='.$num.'';
		return $str;
	}
	
	public function deletebill($sm='', $qxpd=true)
	{
		if($qxpd){
			$is = $this->isdeleteqx();
			if($is==0)return '无权删除';
		}
		$this->flogmodel->delete($this->mwhere);
		m('reads')->delete($this->mwhere);
		m('file')->delfiles($this->mtable, $this->id);
		$tables 	= $this->moders['tables'];
		if(!isempt($tables)){
			$arrse = explode(',', $tables);
			foreach($arrse as $arrses)m($arrses)->delete('mid='.$this->id.'');
		}
		$this->billmodel->delete($this->mwhere);
		$this->delete($this->id);
		$this->flowdeletebill($sm);
		$this->flowzuofeibill($sm);
		$this->gettodosend('bodel','', $sm);
		return 'ok';
	}
	
	/**
	*	单据作废处理
	*/
	public function zuofeibill($sm='')
	{
		$this->update('`status`=5', $this->id);
		$zfarr = array(
			'status' 		=> 5,
			'nstatus' 		=> 5,
			'checksm' 		=> '作废：'.$sm.'',
			'nowcheckid' 	=> '',
			'nowcheckname' 	=> '',
			'nstatustext' 	=> '作废',
			'updt' 			=> $this->rock->now,
		);
		$this->billmodel->update($zfarr, $this->mwhere);
		$tables 	= $this->moders['tables'];
		if(!isempt($tables)){
			$arrse = explode(',', $tables);
			foreach($arrse as $arrses)m($arrses)->delete('mid='.$this->id.'');
		}
		$this->flowzuofeibill($sm);
		$this->gettodosend('bozuofei','', $sm);
		return 'ok';
	}
	
	
	/*
	*	获取操作菜单
	*/
	public function getoptmenu($flx=0)
	{
		$rows 	= $this->db->getrows('[Q]flow_menu',"`setid`='$this->modeid' and `status`=1",'id,wherestr,name,statuscolor,statusvalue,num,islog,issm,type','`sort`');
		$arr 	= array();
		foreach($rows as $k=>$rs){
			$wherestr 	= $rs['wherestr'];
			$bo 		= false;
			if(isempt($wherestr)){
				$bo = true;
			}else{
				$ewet	= m('where')->getstrwhere($this->rock->jm->base64decode($wherestr));
				$tos 	= $this->rows("`id`='$this->id' and $ewet");
				if($tos>0)$bo = true;
			}
			$rs['lx']	  = $rs['type'];
			$rs['optnum'] = $rs['num'];
			if(!isempt($rs['num'])){
				$glx = $this->flowgetoptmenu($rs['num']);
				if(is_bool($glx))$bo = $glx;
			}
			$rs['optmenuid'] = $rs['id'];
			if(!isempt($rs['statuscolor']))$rs['color']  = $rs['statuscolor'];
			unset($rs['id']);unset($rs['num']);unset($rs['wherestr']);unset($rs['type']);unset($rs['statuscolor']);
			if($bo)$arr[] = $rs;
		}
		
		if($this->isflow==1){
			if(($this->rs['status'] == 0 || $this->rs['status'] == 1) && $this->uid == $this->adminid){
				$arr[] = array('name'=>'追加说明...','lx'=>1,'optmenuid'=>-12);
			}
			$chearr = $this->getflowinfor();
			if($chearr['ischeck']==1){
				$arr[] = array('name'=>'<b>去处理单据...</b>','color'=>'#1389D3','lx'=>996);
				if(1==2)foreach($chearr['courseact'] as $zv=>$dz){
					if($zv>0){
						$assar =  array('name'=>$dz[0],'color'=>$dz[1],'optnum'=>'check','issm'=>1,'islog'=>0,'statusvalue'=>$zv,'lx'=>'10','optmenuid'=>-10);
						if($zv==1)$assar['issm'] = 0;
						$arr[] = $assar;
					}
				}
			}
		}
		
		if($this->iseditqx()==1){
			$arr[] = array('name'=>'编辑','optnum'=>'edit','lx'=>'11','optmenuid'=>-11);
		}
		
		if($this->isdeleteqx()==1){
			$arr[] = array('name'=>'删除','color'=>'red','optnum'=>'del','issm'=>0,'islog'=>0,'statusvalue'=>9,'lx'=>'9','optmenuid'=>-9);
		}
		
		return $arr;
	}
	
	/**
	*	操作菜单操作
	*/
	public function optmenu($czid, $zt, $sm='')
	{
		$msg 	 = '';
		$cname 	 = $this->rock->post('changename');
		$cnameid = $this->rock->post('changenameid');
		$cdate   = $this->rock->post('changedate');
		if($czid==-9){
			$msg = $this->deletebill($sm);
		}else if($czid==-10){
			$msg 	 = $this->check($zt, $sm);
			if(contain($msg,'成功'))$msg = 'ok';
		}else if($czid==-12){
			$this->zhuijiaexplain($sm);
		}else{
			$ors 	 = m('flow_menu')->getone("`id`='$czid' and `setid`='$this->modeid' and `status`=1");
			if(!$ors)return '菜单不存在';
			$name	 = str_replace('.', '', $ors['name']);
			$actname = $ors['actname'];if(isempt($actname))$actname=$name;
			if($ors['islog']==1){
				if(!isempt($cname)){
					if(!isempt($sm))$sm.=',';
					$sm.=''.$name.':'.$cname.'';
				}
				$this->addlog(array(
					'explain' 	=> $sm,
					'name'		=> $actname,
					'statusname'=> $ors['statusname'],
					'status'	=> $ors['statusvalue'],
					'color'		=> $ors['statuscolor']
				));
			}
			$barrs = array(
				'cname' 	=> $cname,
				'sm'    	=> $sm,
				'cnameid' 	=> $cnameid,
				'cdate' 	=> $cdate
			);
			if($ors['type']==4 && !isempt($ors['fields'])){
				$fielsa = explode(',', $ors['fields']);
				$uarrs  = array();
				foreach($fielsa as $fielsas){
					$fsdiwe = 'fields_'.$fielsas.'';
					if(isset($_REQUEST[$fsdiwe])){
						$uarrs[$fielsas]=$this->rock->post($fsdiwe);
						$barrs[$fsdiwe] = $uarrs[$fielsas];
					}
				}
				if($uarrs)$this->update($uarrs, $this->id);
			}
			$upgcont		= $ors['upgcont'];
			if(!isempt($upgcont)){
				$upgcont	= $this->rock->jm->base64decode($upgcont);
				$upgcont 	= str_replace(array('{now}','{date}','{adminid}','{admin}','{sm}','{cname}','{cnameid}'),array($this->rock->now,$this->rock->date, $this->adminid, $this->adminname, $sm, $cname, $cnameid), $upgcont);
				$this->update($upgcont, $this->id);
			}
			$this->flowoptmenu($ors, $barrs);
		}
		if($msg=='')$msg='ok';
		return $msg;
	}
	
	/**
	*	单据展示条件搜索
	*/
	public function billwhere($uid, $lx)
	{
		$arr['table'] 	= $this->mtable;
		$arr['fields'] 	= '';
		$arr['order'] 	= '';
		$nas 			= $this->flowbillwhere($uid, $lx);
		$inwhere		= '';
		if(substr($lx,0,5)=='grant'){
			$inwhere	= $this->viewmodel->viewwhere($this->moders, $this->adminid, $this->flowviewufieds);
		}
		$_wehs			= '';
		if(is_array($nas)){
			if(isset($nas['where']))$_wehs = $nas['where'];
			if(isset($nas['order']))$arr['order']  = $nas['order'];
			if(isset($nas['fields']))$arr['fields']= $nas['fields'];
			if(isset($nas['table']))$arr['table']  = $nas['table'];
		}else{
			$_wehs	= $nas;
		}
		$arr['where'] 	= $inwhere.' '.$_wehs;
		return $arr;
	}
	
	public function getflowrows($uid, $lx, $limit=5)
	{
		$nas 	= $this->billwhere($uid, $lx);
		$table 	= $nas['table'];
		if(!contain($table,' '))$table='[Q]'.$table.'';
		$rows 	= $this->db->getrows($table, '1=1 '.$nas['where'].'', $nas['fields'], $nas['order'], $limit);
		return $rows;
	}
	
	
	
	
	/**
	*	打印导出
	*/
	public function printexecl($event)
	{
		$arr['moders'] = $this->moders;
		$arr['fields'] = $this->getfields();
		$cell = 1;
		foreach($arr['fields'] as $k=>$v)$cell++;
		$arr['cell']	= $cell;
		
		$where 			= '1=1';
		$str1		 	= $this->moders['where'];
		if(!isempt($str1)){
			$str1 = $this->rock->covexec($str1);
			$where = $str1;
		}
		
		$vwhere 		= $this->viewmodel->viewwhere($this->moders, $this->adminid);
		$rows 			= $this->getrows(''.$where.' '.$vwhere.'', '*', 'id desc', 100);
		$arr['rows']	= $this->flowprintrows($rows);
		$arr['count']	= $this->db->count;
		return $arr;
	}
	
	/**
	*	获取所有多行子表数据
	*	$lx=0编辑时读取，1展示时读取
	*/
	public function getsuballdata($lx=0)
	{
		$tabless	= $this->moders['tables'];
		$subdata	= array();
		if(!isempt($tabless)){
			$tablessa = explode(',', $tabless);
			$namessa  = explode(',', $this->moders['names']);
			$tabless1 = '['.str_replace(',','],[', $tabless).']';
			foreach($tablessa as $zbx=>$tables){
				$cis 	= substr_count($tabless1, '['.$tables.']');
				$whes	= '';
				if($cis>1)$whes=' and `sslx`='.$zbx.'';
				$data 	= m($tables)->getall('mid='.$this->id.''.$whes.'','*','`sort`');
				if($lx == 0){
					$subdata['subdata'.$zbx.''] 	 = $data;
				}else{
					$subdata[$zbx] = array(
						'data' 	=> $this->flowsubdata($data, $lx),
						'fields'=> 'subdata'.$zbx.'',
						'name'	=> $this->rock->arrvalue($namessa, $zbx)
					);
				}
			}
		}
		return $subdata;
	}
	
	
	/**
	*	读取通知
	*	$act 当前动作有(boturn,boedit,bochang,bodel)
	*/
	private $gettodolistarr = null;
	public function gettodolist($act)
	{
		if($this->gettodolistarr === null){
			$rows = $this->todomodel->getrows("`setid`='".$this->modeid."' and `status`=1");
			$barr = array();
			foreach($rows as $k=>$rs){
				$whereid = (int)$rs['whereid'];
				if($whereid > 0){
					$bo = $this->wheremanzhu($whereid);
					if(!$bo)continue;
				}
				$barr[] = $rs;
			}
			$this->gettodolistarr = $barr;
		}else{
			$barr = $this->gettodolistarr;
		}
		$garr = array();
		if($barr)foreach($barr as $k=>$rs){
			if($this->rock->arrvalue($rs,$act)=='1')$garr[] = $rs;
		}
		return $garr;
	}
	
	/**
	*	发送设置的通知
	*/
	public function gettodosend($act, $actname='',$sm='', $courseid=0, $conts='')
	{
		$barr = $this->gettodolist($act);
		if(!$barr)return;
		$changearr	= array('boturn'=>'提交','boedit'=>'编辑','bozhuan'=>'转办','bochang'=>'修改字段','bodel'=>'删除','bozuofei'=>'作废','botong'=>'处理通过','bobutong'=>'处理不通过','bofinish'=>'全部处理完成','bozhui'=>'追加说明');
		if($actname=='')$actname = $this->rock->arrvalue($changearr, $act);
		if(isempt($actname))return; //没有动作名就不
		foreach($barr as $k=>$rs){
			$receid = $rs['receid'];
			
			if($act=='botong' || $act=='bobutong'){
				$changewe = $rs['changecourse'];
				if(!isempt($changewe) && !contain(','.$changewe.',',','.$courseid.','))continue;
			}
			
			if($rs['toturn']==1)$receid.=','.$this->uid.''; //通知给提交人
			if($rs['tocourse']==1 && $this->billrs){
				$allcheckid = $this->billrs['allcheckid'];
				if(!isempt($allcheckid))$receid.=','.$allcheckid.''; //通知流程所有参与人
			}
			//其他字段
			$todofields = $rs['todofields'];
			if(!isempt($todofields)){
				$toad = explode(',', $todofields);
				foreach($toad as $toads){
					$ttv = $this->rock->arrvalue($this->rs, $toads);
					if(!isempt($ttv))$receid.=','.$ttv.'';
				}
			}
			if(isempt($receid))continue;
			$cont = $rs['summary'];
			if(isempt($cont))$cont = $conts;
			if(isempt($cont)){
				$cont = ''.$this->adminname.''.$actname.'['.$this->modename.',单号:'.$this->sericnum.']';
				if($sm!='')$cont.=',说明:'.$sm.'';
			}
			$this->push($receid, '', $cont, '');
		}
	}
}