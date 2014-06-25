﻿package  {		import flash.display.MovieClip;	import flash.net.NetConnection;	import flash.net.NetStream;	import flash.events.NetStatusEvent;	import flash.system.Security;	import flash.display.StageScaleMode;	import flash.display.StageAlign;	import flash.external.ExternalInterface;	import flash.events.SecurityErrorEvent;	import flash.events.StatusEvent;	import flash.display.Stage;	import flash.display.StageDisplayState;	import flash.events.FullScreenEvent;	import flash.events.MouseEvent;			public class VPlay extends MovieClip {				private var nc:NetConnection;		private var ns:NetStream;		private var connectionData:Object;				public function VPlay() {			// constructor code			setup();		}		public function setup():void{			//Security settings			Security.allowDomain("*");			Security.allowDomain("fancyflirt.com");			//Stage settings			stage.scaleMode = StageScaleMode.NO_SCALE;			//stage.align = StageAlign.LEFT;			stage.addEventListener(FullScreenEvent.FULL_SCREEN,onFullScreen);						view.addEventListener(MouseEvent.MOUSE_OVER,stageMouseOver);			view.addEventListener(MouseEvent.MOUSE_OUT,stageMouseOut);						loader_mc.visible = true;			member_mc.visible = false;			fullsize_controlls.visible = false;			//fullsize_mc.visible = false;			fullsize_mc.addEventListener(MouseEvent.CLICK,fullSizeMouseClick);						initJS();		}		public function stageMouseOver(event:MouseEvent):void{			//if(stage.displayState === StageDisplayState.NORMAL)			jsdebug("stage mouse over");			fullsize_mc.visible = true;		}		public function stageMouseOut(event:MouseEvent):void{			//fullsize_mc.visible = false;		}		public function fullSizeMouseClick(event:MouseEvent):void{			fullScreenHandler();		}		public function playStream(name:String):void{			ns = new NetStream(nc);			ns.play(name);			ns.addEventListener(NetStatusEvent.NET_STATUS,playStreamnetStatusHandler);			ns.addEventListener(StatusEvent.STATUS,nsStatus);			view.attachNetStream(ns);			if(view.scaleX < view.scaleY){				view.scaleY = view.scaleX;			}else{				view.scaleX = view.scaleY;			}						view.x = stage.stageWidth / 2 - view.width / 2; 			jsdebug("Play stream "+ name);		}		private function nsStatus(event:StatusEvent):void{			//jsdebug(event.code);		}		private function playStreamnetStatusHandler(event:NetStatusEvent):void{			jsdebug(event.info.code);			switch(event.info.code){				case "NetStream.Video.DimensionChange":					loader_mc.visible = false;					break;				default: break;			}		}		private function onNetStatusHandler(event:NetStatusEvent):void{			trace(event.info.code);			switch(event.info.code){				case "NetConnection.Connect.Success":					ExternalInterface.call('vplayer.onsuccess');					if(connectionData.type && connectionData.type == "memberarea"){						this.memberArea();						break;					}					if(connectionData && connectionData.sname != undefined){						this.playStream(connectionData.sname);					}					break;				default: break;			}		}		private function initJS():void{			if(ExternalInterface.available){				jsdebug("call player.init()");				ExternalInterface.call("vplayer.init");				//ExternalInterface.addCallback("publish",_publish);				//ExternalInterface.addCallback("unpublish",_unpublish);				ExternalInterface.addCallback("connect",initConnection);				ExternalInterface.addCallback("play",playStream);				ExternalInterface.addCallback("memberarea",memberArea);				ExternalInterface.addCallback("closememberarea",memberAreaClose);				ExternalInterface.addCallback("fullscreen",fullScreenHandler);								//ExternalInterface.addCallback("memberarea",memberarea);				//ExternalInterface.addCallback("closemember",closemember);			}		}		public function fullScreenHandler():void{			jsdebug("Enter fullscreen");			stage.fullScreenSourceRect = null;			stage.displayState = StageDisplayState.FULL_SCREEN;			view.x =0 ;				view.y =0;									}		public function onFullScreen(event:FullScreenEvent):void{						if(event.fullScreen){				fullsize_mc.visible = false;				view.width = stage.fullScreenWidth;				//view.width = stage.stageWidth;				//view.height = stage.stageHeight;				view.x = stage.stageWidth / 2 - view.width / 2;				fullsize_controlls.visible = true;				//fullsize_controlls.y = view.height - 70;			}else{				fullsize_controlls.visible = false;				fullsize_mc.visible = true;				view.width = stage.stageWidth;				view.height = stage.stageHeight;				if(view.scaleX < view.scaleY){					view.scaleY = view.scaleX;				}else{					view.scaleX = view.scaleY;				}				view.x = stage.stageWidth / 2 - view.width / 2;			}		}		public function memberArea():void{			member_mc.visible = true;			loader_mc.visible = false;		}		public function memberAreaClose():void{			loader_mc.visible = true;			member_mc.visible = false;		}		public function initConnection(data:Object):void{			nc = new NetConnection();			nc.addEventListener(NetStatusEvent.NET_STATUS,onNetStatusHandler);			nc.addEventListener(SecurityErrorEvent.SECURITY_ERROR,securityError);			nc.connect(data.host);			this.connectionData = data;			nc.client = this;			jsdebug("Init net connection");		}		private function securityError(event:SecurityErrorEvent):void{			jsdebug(event.text);		}		private function jsdebug(params:*):void{			if(ExternalInterface.available){				ExternalInterface.call("console.log",params);			}		}	}	}