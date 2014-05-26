'use strict';

define(['jquery', 'underscore', 'backbone', 'widgets/mchat/controlpanelwidget'], function ($, _, Backbone,  ControlPanelWidget) {
	var _template = '<div class="chatarea-content mchat">\
			<div id="controlpanelwidget"></div>\
			<div class="sprite sprite-chats_29 bg-image"></div>\
			<div class="content mchat"></div>\
		</div>\
		<div class="chatarea-input mchat"><input type="text" placeholder="Enter here" name="inputText" id="inputText" class="mchat" /></div>';
	var ChatArea = Backbone.View.extend({
		className: 'chatarea mchat',
		id: 'chatarea',
		template: _.template(_template),
		events:{
			'keypress #inputText':'inputKeyPress'
		},
		initialize: function(){
			this.render();
		},
		render: function(){
			this.$el.html(this.template);
			this.$input = this.$('#inputText');
			App.widgets.ctrlPanelWidget = new ControlPanelWidget({el:this.$el.find('#controlpanelwidget')});
			return this;
		},
		inputKeyPress:function(event){
			if(event.charCode === 13){
				App.sio.emit('sendMessage', this.$input.val());
				this.$input.val('');
			}
		}
	});
	var ChatAreaController = function(){
		this.ChatArea = new ChatArea();
		this.addToChat = function(msg){
			this.ChatArea.$('.content').append(msg);
		},
		this.clearChat = function(){
			this.ChatArea.$('.content').empty();
		},
		this.view = function(){
			return this.ChatArea;
		},
		this.focus = function(){
			this.ChatArea.$input.focus();
		}
	};
	return ChatAreaController;
});
