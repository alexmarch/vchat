'use strict';

define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var _template = '<ul class="navbar-settings">\
		<li><a href="#" class="icon"><span class="icon-file"></span>view ignore list</a></li>\
		<li><a href="#" class="icon"><span class="checkbox"></span>Ignore guests</a></li>\
		<li><a href="#" class="icon"><span class="checkbox checked"></span>microphone</a></li>\
		<li><a href="#" class="icon"><span class="checkbox"></span>enable snapshot</a></li>\
		</ul>';
	var ChatSettingsView = Backbone.View.extend({
		className: 'chatsettings-navbar-ui',
		id: 'chatsettings-navbar',
		template: _.template(_template),
		initialize: function(){
			this.$el.addClass(this.className);
		},
		render: function(){
			this.$el.html(this.template());
			return this;
		}
	});
	var ChatSettingsWidget = function(options){
		_.extend(this,options);
		var _view;
		this.switch = false;


		this.init = function(){
			_view = new ChatSettingsView({el:this.el || $('#chatsettingswidget')});
			_view.render();
		};
		this.view = function(){
			return _view.render().$el;
		};
		this.switchTools = function(){
			if(this.switch){
				$('.settingsarea-ui').css({'display':'none'});
			}else{
				$('.settingsarea-ui').css({'display':'block'});
			}
			this.switch = !this.switch;
		};

		this.init();
	};
	return ChatSettingsWidget;
});
