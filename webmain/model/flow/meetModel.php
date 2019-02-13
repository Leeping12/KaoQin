<?php

class flow_meetClassModel extends flowModel
{
	public function initModel()
	{
		$this->hyarra 	= array('正常','会议中','结束','取消');
		$this->hyarrb 	= array('green','blue','#ff6600','#888888');
		$this->dbobj	= c('date');
	}
	
	public function flowrsreplace($rs, $lx=0)
	{
		$rs['week']  = $this->dbobj->cnweek($rs['startdt']);
		$rs['ishui'] = ($rs['state']>=2) ? 1 : 0;
		if($lx==1){
			$content 	 = '';
			$inpurl 	 = $this->getinputurl('meetjy',0,'def_mid='.$this->id.'');
			$rows 		 = $this->getrows('`mid`='.$this->id.' and `type`=2','id,content,optname,optdt,optid','id');
			//是否可以加会议纪要
			$dtss   = c('date')->adddate($this->rock->date,'d',-10).' 00:00:00';
			$addbo 	= $rs['startdt']>$dtss && $rs['state']>0;
			$fobj   = m('file');
			foreach($rows as $k=>$rs1){
				$content.= '<div style="border-bottom:1px #cccccc solid;padding:5px">['.$rs1['optname'].']纪要';
				$inpurl1 = $this->getinputurl('meetjy',$rs1['id']);
				if($addbo && $rs1['optid']==$this->adminid)$content.= '&nbsp;<a href="'.$inpurl1.'" class="blue">[编辑]</a>';
				$content.= '：<br>'.$rs1['content'].'';
				$fstr 	 = $fobj->getstr('meet', $rs1['id'],1);
				if($fstr!='')$content.= '<br>'.$fstr.'';
				$content.= '</div>';
			}
			
			if($addbo){
				 $content.='&nbsp;<a href="'.$inpurl.'" class="blue">＋新增纪要</a>';
			}
			$rs['content']= $content;
			$rs['content_style'] = 'padding:0px';
		}
		$rs['state'] = $this->getstatezt($rs['state']);
		return $rs;
	}
	
	public function getstatezt($zt)
	{
		return '<font color="'.$this->hyarrb[$zt].'">'.$this->hyarra[$zt].'</font>';
	}
	
	protected function flowsubmit($na, $sm)
	{
		$cont  = '{optname}发起会议预定从{startdt}→{enddt},在{hyname},主题:{title}';
		$this->push($this->rs['joinid'], '会议', $cont);
	}
	
	protected function flowaddlog($a)
	{
		$actname = $a['name'];
		if($actname == '取消会议'){
			$this->push($this->rs['joinid'], '会议', ''.$this->adminname.'取消会议【{title}】{startdt}→{enddt}');
			$this->update('`state`=3', $this->id);
		}
		if($actname == '结束会议'){
			$this->update('`state`=2', $this->id);
		}
	}
	
	
	protected function flowbillwhere($uid, $lx)
	{
		$dt 	= $this->rock->post('dt');
		$key 	= $this->rock->post('key');
		
		$where	= 'and 1=2';
		if($lx=='my' || $lx=='mybz' || $lx=='myall'){
			$where	= m('admin')->getjoinstr('joinid', $uid);
		}
		$where	= 'and 1=1';
		if($lx=='my'){
			$where.=" and startdt like '{$this->rock->date}%'";
		}
		
		if($lx=='mybz'){
			$listdt	= c('date')->getweekfirst($this->rock->date);
			$where.=" and startdt >='$listdt'";
		}
		
		if($lx=='myfq'){
			$where =" and optid='$uid'";
		}
		
		m($this->mtable)->update('state=2',"`state`=0 and `enddt`<'{$this->rock->now}'");

		if($dt!='')$where.=" and startdt like '$dt%'";
		if(!isempt($key))$where.=" and (`joinname` like '%$key%' or `title` like '%$key%')";
		
		
		return array(
			'where' => "and type=0 and `status`=1 $where",
			'fields'=> 'id,startdt,enddt,optname,state,title,hyname,joinname,`explain`,jyname',
			'order' => 'startdt desc'
		);
	}
}