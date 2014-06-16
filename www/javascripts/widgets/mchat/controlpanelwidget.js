'use strict';

define(['jquery', 'underscore', 'backbone', 'text!definition.json', 'tinycolor', 'colorpicker'], function ($, _, Backbone, defs, tinycolor, colorpicker) {

	var _template = '<ul class="nav mchat">\
			<li style="text-align: right"><div id="privatewith" style="display: none;padding: 0px;font-size: 13px;color: #a68485;font-weight: bold;">private with</div>\
			<span class="username mchat"></span></li>\
			</ul>\
			<ul class="nav-bottom mchat">\
				<li>\
				<div class="emoticons-panell-ui" style="display: none; bottom: 46px;">\
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
				<li>\
				<div class="colorSliderWrapper" style="display: none; bottom: 46px;"><div id="colorSlider"></div><div class="currentColor"></div><div class="defaulColor"></div></div>\
				<a id="colorButton" href="#"><span class="sprite sprite-chats_36"></span></a></li>\
				<li>\
					<div class="fontChageWrapper mchat" style="display: none">\
					<div class="fontDown" id="fontDown">-</div>\
					<div class="fontUp" id="fontUp">+</div>\
					<div class="cfont" id="cfont"></div>\
					</div>\
					<a href="#" id="fontButton"><span class="sprite sprite-chats_25"></span></a>\
				</li>\
				<li>\
				<ul class="mchat-navbar">\
					<li class="nav-item">\
						<a class="icon icon-credits"></a>\
					</li>\
					<li class="nav-item">\
						<a class="icon icon-add2f"></a>\
					</li>\
					<li class="nav-item">\
						<a class="icon icon-gifts"></a>\
					</li>\
					<li class="nav-item">\
						<a class="icon icon-info"></a>\
					</li>\
					<li class="nav-item">\
						<a class="icon icon-sound"></a>\
					</li>\
					<li class="nav-item">\
						<a class="icon icon-fullsize"></a>\
					</li>\
				</ul>\
				</li>\
			</ul>';

	var ControlPanelView = Backbone.View.extend({
		className: 'controlpanel-ui',
		id: 'controlpanel',
		template: _.template(_template),
		fontSize: 14,
		events: {
			'click #toolsPanel': 'switchTools',
			'click #memberAreaButton': 'memberAreaClick',
			'click #setTopicButton': 'setTopicButtonClick',
			'keypress #inputTopic': 'keyPressTopicHandler',
			'click #smileyPanel': 'smileyClick',
			'click .emoticon': 'emoticonClick',
			'click #colorButton': 'colorButtonClick',
			'click .defaulColor': 'defaultColorClick',
			'click #fontButton': 'fontButtonClick',
			'click #fontUp': "fontUpClick",
			'click #fontDown': "fontDownClick"
		},

		initialize: function () {
			this.defs = eval("(" + defs + ")");
			this.$el.addClass(this.className);
		},

		render: function () {
			this.$el.html(this.template());
			this.$inputTopic = this.$('#inputTopic');
			var self = this;
			$(function () {
				window.tinycolor = tinycolor;
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
		fontButtonClick: function () {
			if (this.$('.fontChageWrapper').css('display') === 'none') {
				this.$('.fontChageWrapper').show();
				this.$('.fontChageWrapper').find('#cfont').text(this.fontSize + 'px');
			} else {
				this.$('.fontChageWrapper').hide();
			}
		},
		fontUpClick: function () {
			//this.$('.fontChageWrapper').hide();
			this.fontSize = this.fontSize < 20 ? this.fontSize + 1 : this.fontSize;
			this.$('.fontChageWrapper').find('#cfont').text(this.fontSize + 'px');
		},
		fontDownClick: function () {
			//this.$('.fontChageWrapper').hide();
			this.fontSize = this.fontSize > 14 ? this.fontSize - 1 : this.fontSize;
			this.$('.fontChageWrapper').find('#cfont').text(this.fontSize + 'px');
		},
		smileyClick: function (e) {
			if (this.$('.emoticons-panell-ui').css('display') === 'none') {
				this.$('.emoticons-panell-ui').show();
			} else {
				this.$('.emoticons-panell-ui').hide();
			}
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
		emoticonClick: function (e) {
			this.$('.emoticons-panell-ui').hide();
			var code = this.defs[$(e.currentTarget).data('smile')].codes[0];
			App.widgets.ChatArea.insertText(code);
		},
		switchTools: function () {
			App.widgets.ChatSettings.switchTools();
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
		}
	});

	var ControlPanelWidget = function (options) {
		_.extend(this, options);
		this._view;
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
		this.showPrivateLabel = function () {
			this._view.$('#privatewith').show();
		};
		this.hidePrivateLabel = function () {
			this._view.$('#privatewith').hide();
		}
		this.init();
	};

	return ControlPanelWidget;
});
