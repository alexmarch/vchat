'use strict';

define(['jquery', 'underscore', 'backbone', 'views/mchat/chatcontent'], function ($, _, Backbone, ChatContent) {
	window.App = window.App || {};
	window.App = {
		views: {},
		models: {},
		widgets: {},
		sio: {}
	};
	App.bindevents = function () {
		App.sio.on('welcome_to_chat_room', function (data) {
			App.widgets.ChatArea.clearChat();
			App.widgets.ChatArea.addToChat(data.msg);
			App.widgets.ChatArea.addToChat('<span style="color:#757575">Now your room id(' + data.room + ')</span>');
			App.widgets.ctrlPanelWidget.setUserName(data.username);
			publisher.connect(data.host);
		});
		App.sio.on('set_room',function(){
			if(roomid){
				App.sio.emit('join_room',{id: roomid});
			};
		});
		App.sio.on('join_room_error', function(){
			console.log("join room error");
			//@todo redirect to all models page
		});
		App.sio.on('join_successful',function(data){
			vplayer.connect(data);
			console.log("Data:",data);
			App.widgets.ctrlPanelWidget.setUserName(data.nickname);
			if(data.topic){
				App.widgets.ChatArea.addToChat(data.topic);
			}
		});
		App.sio.on('play',function(data){
			vplayer.play(data.name);
		});
		App.sio.on('receiveNewMsg', function (msg,color) {
			var $msg = $(msg);
			$msg.find('#msg').css({'color':color});
			App.widgets.ChatArea.addToChat('<p>'+$msg.html()+'</p>');
		});
		App.sio.on('memberarea', function(data){
			vplayer.memberarea();
		});
		App.sio.on('closememberarea', function(data){
			vplayer.closememberarea();
		});
		App.sio.on('start_private', function(data){
			/**
			 * @todo - Start private with performer
			 */
			console.log("START PRIVATE FROM SERVER");
			App.inPrivate = true;
			App.widgets.ctrlPanelWidget.showPrivateLabel();
		});
		App.sio.on('performer_in_private', function(data){
			/**
			 * @todo - Performer in private chat
			 */
			if(!App.inPrivate){
				console.log("SHOW PRIVATE WINDOW");
			}
		});
		App.sio.on('end_private', function(data){
			/**
			 * @todo - Start private with performer
			 */
			App.inPrivate = false;
			App.widgets.ctrlPanelWidget.hidePrivateLabel();
		});
		App.sio.on('publish', function (data) {
			publisher.publish(data);
			if(data.type === "memberarea"){
				vplayer.memberarea();
			}else if(data.type === "free"){
				vplayer.closememberarea();
			}else{
				if(vplayer.inMemberArea()){
					vplayer.closemember();
				}
			}
		});

		App.sio.on('unpublish', function (data) {
			publisher.publish(data);
		});

		App.sio.on('topic_change', function(data){
			App.widgets.ChatArea.addToChat(data.title);
		});
	};

	App.connect = function () {
		this.sio = io.connect('http://fancyflirt.com:3000');
		this.bindevents();
	};

	App.connectedHandler = function(){
		App.sio.emit('connectedSuccess');
	};

	App.memberarea = function(){
		if(!publisher.inMemberArea()){
			App.sio.emit('memberarea');
		}else{
			App.sio.emit('closemember');
		}
	};

	//App.settopic = function(title){
		//App.sio.emit("settopic",{title: title});
//	}

	App.views.ChatContent = new ChatContent();

	return App;
});
