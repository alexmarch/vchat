'use strict';

define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var _template = '<ul class="nav"><li><span class="username"></span></li>\
			<li><div class="topic" id="topic"><div class="inner-wrapper">\
			<input type="text" placeholder="Type topic" id="inputTopic">\
			<a href="#" id="setTopicButton" class="settopic-button">Set</a>\
			</div></div></li>\
			<li><a href="#"><span class="sprite sprite-chats_11"></span></a></li>\
			<li><a href="#" id="toolsPanel"><span class="sprite sprite-chats_03"></span></a></li>\
			<li><a href="#"><span class="sprite sprite-chats_14"></span></a></li>\
			<li><a href="#"><span class="sprite sprite-chats_16"></span></a></li>\
			<li><a href="#"><span class="sprite sprite-chats_06"></span></a></li>\
			<li><a href="#" id="memberAreaButton"><span class="sprite sprite-chats_08"></span></a></li>\
			</ul><ul class="nav-bottom">\
				<li><a href="#"><span class="sprite sprite-chats_33"></span></a></li>\
				<li><a href="#"><span class="sprite sprite-chats_36"></span></a></li>\
				<li><div class="button-controls">\
					<a href="#" class="c-button">Dictionary</a>\
					<a href="#" class="c-button">Record</a>\
					<a href="#" class="c-button">Settings</a>\
					<a href="#" class="c-button">Snapshot</a>\
					<a href="#" class="c-button">Statistic</a>\
				</div></li>\
			</ul>';
	var ControlPanelView = Backbone.View.extend({
		className: 'controlpanel-ui',
		id: 'controlpanel',
		template: _.template(_template),
		events:{
			'click #toolsPanel':'switchTools',
			'click #memberAreaButton':'memberAreaClick',
			'click #setTopicButton': 'setTopicButtonClick',
			'keypress #inputTopic': 'keyPressTopicHandler'
		},
		initialize: function(){
			this.$el.addClass(this.className);
		},
		render: function(){
			this.$el.html(this.template());
			this.$inputTopic = this.$('#inputTopic');

			return this;
		},
		switchTools: function(){
			App.widgets.ChatSettings.switchTools();
		},
		memberAreaClick: function(){
			App.memberarea();
		},
		setTopicButtonClick: function(){
			var title = this.$inputTopic.val();
			if($.trim(title)!=''){
				App.settopic(this.$inputTopic.val());
			}
		},
		keyPressTopicHandler: function(e){
			if(e.keyCode == 13){
				this.setTopicButtonClick();
			}
		}
	});
	var ControlPanelWidget = function(options){
		_.extend(this,options);
		var _view;
		this.init = function(){
			_view = new ControlPanelView({el:this.el || $('#controlpanelwidget')});
			_view.render();
		};
		this.view = function(){
			return _view.render().$el;
		};
		this.setUserName = function(username){
			_view.$('.username').text(username);
		};
		this.init();
	};
	return ControlPanelWidget;
});
