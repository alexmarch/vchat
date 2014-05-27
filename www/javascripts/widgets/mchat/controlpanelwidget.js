'use strict';

define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {

	var _template = '<ul class="nav">\
			<li><span class="username mchat"></span></li>\
			</ul>\
			<ul class="nav-bottom">\
				<li><a href="#"><span class="sprite sprite-chats_33"></span></a></li>\
				<li><a href="#"><span class="sprite sprite-chats_36"></span></a></li>\
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
