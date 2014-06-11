'use strict';

define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var _template = '<ul class="navbar-settings" id="settingsBar">\
		<li><a href="#" class="icon"><span class="icon-file"></span>view ignore list</a></li>\
		<li><a href="#" class="icon"><span class="checkbox"></span>Ignore guests</a></li>\
		<li><a href="#" class="icon"><span class="checkbox checked"></span>microphone</a></li>\
		<li><a href="#" class="icon"><span class="checkbox"></span>enable snapshot</a></li>\
		</ul><div class="translate-ui-bar" id="translateBar" style="display: none">\
		<div class="tfrom"><span>From:</span><div class="from-lang"></div></div>\
		<div class="tto"><span>To:</span><div class="to-lang"></div></div>\
		<div class="buttonsBar">\
		<div class="button-controls">\
			<a href="#" class="c-button">Translate</a>\
			<a href="#" class="c-button">Copy to chat</a>\
			<a href="#" class="icon"><span class="checkbox checked"></span>Copy to chat (auto)</a>\
			</div>\
		</div>\
		</div>';
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
		this.showDictionary = function(){
			_view.$el.find('#settingsBar').hide();
			_view.$el.find('#translateBar').show();
		};
		this.showSettings = function(){
			_view.$el.find('#settingsBar').show();
			_view.$el.find('#translateBar').hide();
		}

		this.init();
	};
	return ChatSettingsWidget;
});
