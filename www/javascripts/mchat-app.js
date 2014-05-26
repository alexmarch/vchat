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

		App.sio.on('receiveNewMsg', function (msg) {
			App.widgets.ChatArea.addToChat('<p>' + msg + '</p>');
		});

		App.sio.on('publish', function (data) {
			publisher.publish(data);
			if(data.type === "memberarea"){
				publisher.memberarea();
			}else{
				if(publisher.inMemberArea()){
					publisher.closemember();
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
		this.sio = io.connect('http://localhost:3000');
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

	App.settopic = function(title){
		App.sio.emit("settopic",{title: title});
	}

	App.views.ChatContent = new ChatContent();

	return App;
});
