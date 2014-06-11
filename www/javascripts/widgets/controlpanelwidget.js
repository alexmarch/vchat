'use strict';

define(['jquery', 'underscore', 'backbone', 'text!definition.json', 'tinycolor', 'colorpicker'], function ($, _, Backbone, defs, tinycolor, colorpicker) {
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
				<li style="position: relative">\
					<div class="emoticons-panell-ui" style="display: none">\
					<div class="emoticon-ui-wrapper">\
						<span class="emoticon emoticon-smile" data-smile="smile"></span>\
						<span class="emoticon emoticon-sad-smile" data-smile="sad-smile"></span>\
						<span class="emoticon emoticon-big-smile" data-smile="big-smile"></span>\
						<span class="emoticon emoticon-cool" data-smile="cool"></span>\
						<span class="emoticon emoticon-wink" data-smile="wink"></span>\
						<span class="emoticon emoticon-crying" data-smile="crying"></span>\
						<span class="emoticon emoticon-heart" data-smile="heart"></span>\
						<span class="emoticon emoticon-angry" data-smile="angry"></span>\
						<span class="emoticon emoticon-star" data-smile="star"></span>\
						<span class="emoticon emoticon-smile2" data-smile="smile2"></span>\
						<span class="emoticon emoticon-smile3" data-smile="smile3"></span>\
						<span class="emoticon emoticon-smile4" data-smile="smile4"></span>\
						<span class="emoticon emoticon-smile5" data-smile="smile5"></span>\
					</div>\
					</div>\
					<a href="#" id="smileyPanel"><span class="sprite sprite-chats_33"></span></a>\
				</li>\
				<li style="position: relative">\
				<div class="colorSliderWrapper" style="display: none;"><div id="colorSlider"></div><div class="currentColor"></div><div class="defaulColor"></div></div>\
				<a href="#" id="colorButton">\
					<span class="sprite sprite-chats_36"></span>\
				</a>\
				</li>\
				<li>\
				<a href="#" id="fontButton">\
					<span class="sprite sprite-chats_25"></span>\
				</a>\
				</li>\
				<li><div class="button-controls">\
					<a href="#" class="c-button" id="dictionaryButton">Dictionary</a>\
					<a href="#" class="c-button">Record</a>\
					<a href="#" class="c-button" id="settingsButton">Settings</a>\
					<a href="#" class="c-button">Snapshot</a>\
					<a href="statistic.php" class="c-button" target="_blank">Statistic</a>\
				</div></li>\
			</ul>';
	var ControlPanelView = Backbone.View.extend({
		className: 'controlpanel-ui',
		id: 'controlpanel',
		template: _.template(_template),
		events: {
			'click #toolsPanel': 'switchTools',
			'click #memberAreaButton': 'memberAreaClick',
			'click #setTopicButton': 'setTopicButtonClick',
			'keypress #inputTopic': 'keyPressTopicHandler',
			'click #smileyPanel': 'smileyClick',
			'click .emoticon': 'emoticonClick',
			'click #colorButton': 'colorButtonClick',
			'click .defaulColor': 'defaultColorClick',
			'click #dictionaryButton': 'showDictionaryClick',
			'click #settingsButton': 'showSettingsClick'
		},
		initialize: function () {
			this.$el.addClass(this.className);
			this.defs = eval("(" + defs + ")");
		},
		render: function () {
			this.$el.html(this.template());
			this.$inputTopic = this.$('#inputTopic');
			this.changed = false;
			var self = this;
			window.tinycolor = tinycolor;
			$(function () {
				self.$("#colorSlider").ColorPickerSliders({
					color: "rgb(36, 170, 242)",
					size: 'large',
					customswatches: false,
					flat: true,
					swatches: false,
					order: {
						hsl: 1
					},
					onchange: function (container, color) {
						if (self.changed) {
							self.rgba = color.rgba;
							self.$('.currentColor').css({'background': '#' + tinycolor(color.rgba).toHex()});
						}
						self.changed = true;
					}
				});
				self.$('.cp-hslhue>span').text('');
			});
			this.rgba = tinycolor(this.$('.defaultColor').css('color')).toRgb();
			this.$('.currentColor').css({'background': '#' + tinycolor(this.$('.defaultColor').css('color')).toHex()});
			return this;
		},
		defaultColorClick: function () {
			this.rgba = tinycolor(this.$('.defaultColor').css('color')).toRgb();
			this.$('.currentColor').css({'background': '#' + tinycolor(this.$('.defaultColor').css('color')).toHex()});
		},
		colorButtonClick: function () {
			if (this.$('.colorSliderWrapper').css('display') === 'none') {
				this.$('.colorSliderWrapper').show();
			} else {
				this.$('.colorSliderWrapper').hide();
			}
		},
		smileyClick: function (e) {
			if (this.$('.emoticons-panell-ui').css('display') === 'none') {
				this.$('.emoticons-panell-ui').show();
			} else {
				this.$('.emoticons-panell-ui').hide();
			}
		},
		emoticonClick: function (e) {
			this.$('.emoticons-panell-ui').hide();
			var code = this.defs[$(e.currentTarget).data('smile')].codes[0];
			App.widgets.ChatArea.insertText(code);
		},
		switchTools: function () {
			//App.widgets.ChatSettings.switchTools();
			if (this.$('.button-controls').css('display') === 'none') {
				this.$('.button-controls').show();
			} else {
				this.$('.button-controls').hide();
			}
		},
		memberAreaClick: function () {
			App.memberarea();
		},
		setTopicButtonClick: function () {
			var title = this.$inputTopic.val();
			if ($.trim(title) != '') {
				App.settopic(this.$inputTopic.val());
			}
		},
		keyPressTopicHandler: function (e) {
			if (e.keyCode == 13) {
				this.setTopicButtonClick();
			}
		},
		showDictionaryClick: function(){
			App.widgets.ChatSettings.showDictionary();
		},
		showSettingsClick: function(){
			App.widgets.ChatSettings.showSettings();
		}
	});
	var ControlPanelWidget = function (options) {
		_.extend(this, options);
		this.init = function () {
			this._view = new ControlPanelView({el: this.el || $('#controlpanelwidget')});
			this._view.render();
		};
		this.view = function () {
			return this._view.render().$el;
		};
		this.setUserName = function (username) {
			this._view.$('.username').text(username);
		};
		this.init();
	};
	return ControlPanelWidget;
});
