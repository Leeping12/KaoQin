/**
*	createname：雨中磐石
*	homeurl：http://sucaihuo.com/
*	Copyright (c) 2016 rainrock (sucaihuo.com)
*	Date:2016-01-01
*/
var nwjs={
	init:function(){
		this.nw = nwjsgui;
		if(!this.nw)return;
		this.win = nwjsgui.Window.get();
	},
	
	createtray:function(tls, lx){
		if(!this.nw)return;
		var icon = 'images/logo.png';
		if(lx==0)icon='images/logo_hui.png';
		var tray = new nwjsgui.Tray({ title:tls, icon: icon});
		tray.tooltip = tls;
		//this.win.setShowInTaskbar(false);
		var menu = new nwjsgui.Menu();
		menu.append(new nwjsgui.MenuItem({label: '打开窗口',click:function(){
			nwjs.winshow();
		}}));
		this.closebool = false;
		menu.append(new nwjsgui.MenuItem({label: '退出',click:function(){
			nwjs.closebool = true;
			nwjs.win.close();
		}}));
		
		tray.menu 	= menu;
		this.fs 	= require('fs');
		tray.on('click',function(){
			nwjs.winshow();
		});
		
		this.tray = tray;
		
		this.win.removeAllListeners('close');
		this.win.on('close',function(){
			if(nwjs.closebool){
				try{bodyunload();js.onunload();}catch(e){}
				nwjsgui.Window.get().close(true);
			}else{
				nwjs.win.hide();
			}
		});
		
		if(lx==0)return;
		var kjj=js.getoption('kuaijj','Q');
		this.addShortcut(kjj);
		this.addfile();
	},
	addShortcut:function(v){
		var option = {
			key : 'Ctrl+Alt+'+v+'',
			active : function() {
				nwjs.changewinhide();
			}
		};
		this.shortcut = new nwjsgui.Shortcut(option);
		nwjsgui.App.unregisterGlobalHotKey(this.shortcut);
		nwjsgui.App.registerGlobalHotKey(this.shortcut);
	},
	changekuai:function(o1){
		var val=o1.value;
		this.addShortcut(val);
		js.setoption('kuaijj',val);
	},
	removetray:function(){
		if(!this.nw)return;
		if(this.server)this.server.close();
		this.tray.remove();
		nwjsgui.App.unregisterGlobalHotKey(this.shortcut);
		this.tray = false;
		this.server=false;
	},
	changewinhide:function(){
		if(windowfocus){
			this.win.hide();
		}else{
			this.winshow();
		}
	},
	winshow:function(){
		if(!this.nw){
			window.focus();
			return;
		}
		this.win.show();
		this.win.focus();
	},
	changeicon:function(oi){
		if(!this.tray)return;
		var s='images/logo.png';
		if(oi>0){
			s='images/logo_new.png';
		}
		this.tray.icon = s;
	},
	writeFile:function(path, str){
		if(!this.nw)return;
		if(!this.fs)this.fs = require('fs');
		var oatg = this.getpath();
		this.fs.writeFile(''+oatg+'/'+path+'', str,function(err){
			if(err){
				js.msg('msg','error:'+err+'');
			};
		});
	},
	getpath:function(){
		var url = nwjsgui.App.manifest.main;
		var las = url.lastIndexOf('\\');
		var oatg = url.substr(0, las);
		if(oatg.substr(0,5)=='file:')oatg=oatg.substr(7)
		return oatg;
	},
	addfile:function(){
		return;
		js.ajaxss('down','file',function(ret){
			var fs = require("fs");
			fs.writeFile('rock.php', jm.base64decode(ret.filecont),  function(err) {
				alert(err);
			});
		});
	},
	banben:function(o1){
		o1.innerHTML='已是最新';
	},
	getipmac:function(){
		var json={ip:'','mac':''};
		if(!this.nw)return json;
		var os = require('os');
		var network = os.networkInterfaces();
		for(var a in network){
			for(var i = 0; i < network[a].length; i++) {
				var json = network[a][i];
				if(json.family == 'IPv4') {
					json.ip = json.address
					break;
				}
			}
			break;
		}
		return json;
	},
	udpserver:function(funarr){
		return;if(!this.nw)return;
		if(!funarr)funarr=function(){};
		try{
			var dgram = require("dgram");
			this.server = dgram.createSocket("udp4");
			this.server.on("error", function (err) {
				nwjs.server.close();
			});
			this.server.on("message", function (msg, rinfo){
				funarr(msg, rinfo);
			});
			this.server.on("listening", function (){
				
			});
			this.server.bind(8299,'0.0.0.0');
		}catch(e){
			this.server=false;
		}
	}
};