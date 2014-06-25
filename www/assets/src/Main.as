﻿package  {		import flash.display.MovieClip;	import flash.system.Security;	import flash.display.StageScaleMode;	import flash.display.StageAlign;	import flash.media.Camera;	import flash.external.ExternalInterface;	import flash.media.Microphone;	import flash.net.NetStream;	import flash.net.NetConnection;	import flash.media.H264VideoStreamSettings;	import flash.media.H264Profile;	import flash.media.H264Level;	import flash.events.NetStatusEvent;	import flash.media.SoundTransform;	import flash.events.SecurityErrorEvent;	import flash.media.MicrophoneEnhancedOptions;	import flash.media.SoundCodec;			public class Main extends MovieClip {		private var cam:Camera;		private var mic:Microphone;		private var publish:NetStream;		private var conn:NetConnection;		private var h264Settings:H264VideoStreamSettings = new H264VideoStreamSettings();				public function Main() {			setup();		}		private function setup():void{			//Security settings			Security.allowDomain("*");			//Stage settings			stage.scaleMode = StageScaleMode.NO_SCALE;			stage.align = StageAlign.LEFT;			//Init ui			memberLabel.visible = false;						//Init camera/mic			initMicAndCam();			initJS();			jsdebug("Setup flash");		}		private function initMicAndCam():Boolean{			if(Camera.names.length === 0){				jsevent('CamDisable');				return false;			}			if(Microphone.names.length === 0){			   jsevent('MicDisable');			}			//Setup camera			cam = Camera.getCamera();			cam.setQuality(0,70);			cam.setMode(640,480,30,true);			cam.setKeyFrameInterval(15);						//Setup mic			mic = Microphone.getMicrophone();			mic.setUseEchoSuppression(true);			mic.codec = SoundCodec.SPEEX;			//mic.gain = 11;			//mic.rate = 22;						//Setup H264 codecs			h264Settings.setProfileLevel(H264Profile.MAIN, H264Level.LEVEL_3_1);			h264Settings.setQuality(0,70);						if(Preview.scaleX < Preview.scaleY){				Preview.scaleY = Preview.scaleX;			}else{				Preview.scaleX = Preview.scaleY;			}						Preview.x = stage.stageWidth / 2 - Preview.width / 2; 			Preview.attachCamera(cam);						return true;					}		private function initJS():void{			if(ExternalInterface.available){				jsdebug("call publisher.init()");				ExternalInterface.call("publisher.init");				ExternalInterface.addCallback("publish",_publish);				ExternalInterface.addCallback("unpublish",_unpublish);				ExternalInterface.addCallback("connect",initConnection);				ExternalInterface.addCallback("memberarea",memberarea);				ExternalInterface.addCallback("closemember",closemember);			}		}		public function _publish(name:String):void{			if(conn){				publish = new NetStream(conn);				publish.addEventListener(NetStatusEvent.NET_STATUS,netStatusHandler);				publish.attachCamera(cam);				publish.attachAudio(mic);				publish.videoStreamSettings = h264Settings;				publish.publish(name,"live");			}else{				jsdebug("Publish connection error");			}		}		public function memberarea():void{			memberLabel.visible = true;		}		public function closemember():void{			jsdebug('visible false');			memberLabel.visible = false;		}		private function netStatusHandler(event:NetStatusEvent):void{			jsdebug(event.info.code);		}		public function _unpublish():void{			publish.publish(null);			publish.soundTransform = new SoundTransform(0);		}		public function initConnection(host:String):void{			conn = new NetConnection();			conn.addEventListener(NetStatusEvent.NET_STATUS,onNetStatus);			conn.addEventListener(SecurityErrorEvent.SECURITY_ERROR,securityError);			conn.connect(host);			conn.client = this;			jsdebug("Init net connection");		}		private function securityError(event:SecurityErrorEvent):void{			jsdebug(event.text);		}		private function onNetStatus(event:NetStatusEvent):void{			jsdebug(event.info.code);			switch(event.info.code){				case "NetConnection.Connect.Success":						ExternalInterface.call("publisher.connected");						jsdebug("Connection success !");					break;				default: break;			}		}				private function jsevent(event:String):void{			if(ExternalInterface.available){				ExternalInterface.call("publisher.onevent",event);			}		}		private function jsdebug(params:*):void{			if(ExternalInterface.available){				ExternalInterface.call("publisher.debug",params);			}		}	}	}