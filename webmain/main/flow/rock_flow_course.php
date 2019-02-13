<?php if(!defined('HOST'))die('not access');?>
<script >
$(document).ready(function(){
	{params};
	var num = params.num,mid,optlx=0,maxpid = 0;
	var at = $('#optionview_{rand}').bootstable({
		tablename:'flow_set',defaultorder:'`sort`',where:'and isflow=1 and status=1',
		modedir:'{mode}:{dir}',storeafteraction:'setcourselistafter',
		columns:[{
			text:'名称',dataIndex:'name'
		},{
			text:'编号',dataIndex:'num'
		},{
			text:'步骤数',dataIndex:'shu'
		},{
			text:'ID',dataIndex:'id'
		}],
		itemdblclick:function(ad,oi,e){
			$('#downshow_{rand}').html('设置<b>['+ad.id+'.'+ad.name+']</b>的步骤管理');
			mid=ad.id;
			a.search("and `setid`="+ad.id+"");
		}
	});
	
	function btn(bo){
		get('edit_{rand}').disabled = bo;
		get('del_{rand}').disabled = bo;
	}
	
	var a = $('#view_{rand}').bootstable({
		tablename:'flow_course',celleditor:true,
		autoLoad:false,where:'and setid=-1',tree:true,modedir:'{mode}:{dir}',storeafteraction:'flowcourselistafter',storebeforeaction:'flowcourselistbefore',
		columns:[{
			text:'名称',dataIndex:'name',align:'left'
		},{
			text:'适用于',dataIndex:'recename'
		},{
			text:'编号',dataIndex:'num'
		},{
			text:'审核人类型',dataIndex:'checktype'
		},{
			text:'审核人',dataIndex:'checktypename'
		},{
			text:'说明',dataIndex:'explain'
		},{
			text:'排序号',dataIndex:'sort',editor:true
		},{
			text:'状态',dataIndex:'status',type:'checkbox',editor:true
		},{
			text:'转办',dataIndex:'iszf',type:'checkbox'
		},{
			text:'ID',dataIndex:'id'
		},{
			text:'',dataIndex:'opt',renderer:function(s, d){
				var s='';
				if(d.level==1)s='<a onclick="addbuzuo{rand}('+d.pid+')" href="javascript:;">[加步骤]</a>';
				return s;
			}
		}],
		load:function(d){
			maxpid = d.maxpid;
			get('add_{rand}').disabled=false;
			get('addp_{rand}').disabled=false;
		},
		itemclick:function(d){
			var bo = false;
			if(d.level==1)bo=true;
			btn(bo);
		},
		beforeload:function(){
			btn(true);
		}
	});
	var c = {
		reload:function(){
			a.reload();
		},
		del:function(){
			a.del();
		},
		clickwin:function(o1,lx){
			var icon='plus',name='新增流程'+(maxpid+1)+'的步骤',id=0,pid=0;
			if(lx==1){
				id = a.changeid;
				icon='edit';
				name='编辑['+a.changedata.name+']步骤';
			}else{
				pid = maxpid;
			}
			guanflowcourselist = a;
			addtabs({num:'flowcourse'+id+'',url:'main,flow,courseedit,id='+id+',setid='+mid+',pid='+pid+'',icons:icon,name:name});
		},
		pipei:function(){
			js.ajax(js.getajaxurl('reloadpipei','{mode}','{dir}'),{mid:mid},function(s){
				js.msg('success', s);
			},'get',false,'匹配中...,匹配完成');
		},
		addbuzu:function(pid){
			var name = '新增流程'+(pid+1)+'的步骤';
			guanflowcourselist = a;
			addtabs({num:'flowcourse0',url:'main,flow,courseedit,id=0,setid='+mid+',pid='+pid+'',icons:'plus',name:name});
		}
	};
	js.initbtn(c);
	$('#optionview_{rand}').css('height',''+(viewheight-62)+'px');
	addbuzuo{rand}=function(s1){
		c.addbuzu(s1);
	}
});
</script>


<table width="100%">
<tr valign="top">
<td width="30%">
	<div class="panel panel-info" style="margin:0px">
	  <div class="panel-heading">
		<h3 class="panel-title">流程模块(双击显示步骤)</h3>
	  </div>
	  <div id="optionview_{rand}" style="height:400px;overflow:auto"></div>
	</div>
</td>
<td width="10"></td>
<td>
	<div>
	<ul class="floats">
		<li class="floats70">
			<button class="btn btn-primary" click="clickwin,0" disabled id="add_{rand}" type="button"><i class="icon-plus"></i> 新增流程</button>&nbsp;&nbsp;
			<button class="btn btn-default" click="pipei" id="addp_{rand}" disabled type="button">匹配流程</button>&nbsp;&nbsp;
			<span id="downshow_{rand}"></span>
		</li>
		<li class="floats30" style="text-align:right">
			<button class="btn btn-info" id="edit_{rand}" click="clickwin,1" disabled type="button"><i class="icon-edit"></i> 编辑 </button> &nbsp; 
			<button class="btn btn-danger" id="del_{rand}" disabled click="del" type="button"><i class="icon-trash"></i> 删除</button>
		</li>
	</ul>
	</div>
	<div class="blank10"></div>
	<div id="view_{rand}"></div>
	<div class="tishi">一个模块可建立多个流程，遇到设置问题可F2查看帮助。</div>
	<div class="blank10"></div>
	<div style="background:url(images/cropbg.gif);height:600px;border:1px #cccccc solid;display:none"></div>
	
</td>
</tr>
</table>