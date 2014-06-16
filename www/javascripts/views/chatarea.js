'use strict';

define(['jquery', 'underscore', 'backbone', '../widgets/controlpanelwidget', 'emoticons', 'jscrollpane','text!definition.json'],
	function ($, _, Backbone, ControlPanelWidget, emoticons, jscrollpane, defs) {
	var _template = '<div class="chatarea-content">\
			<div id="controlpanelwidget"></div>\
			<div class="sprite sprite-chats_29 bg-image"></div>\
			<div id="chatarea-scrollbar">\
				<div class="content"></div>\
			</div>\
		</div>\
		<div class="chatarea-input"><input type="text" placeholder="Enter here" name="inputText" id="inputText" /></div>';
	var ChatArea = Backbone.View.extend({
		className: 'chatarea',
		id: 'chatarea',
		template: _.template(_template),
		events: {
			'keypress #inputText': 'inputKeyPress'
		},
		initialize: function () {

			this.render();
		},
		render: function () {
			this.$el.html(this.template);
			this.$input = this.$('#inputText');
			this.$scrollbar = this.$('#chatarea-scrollbar');
			App.widgets.ctrlPanelWidget = new ControlPanelWidget({el: this.$el.find('#controlpanelwidget')});
			return this;
		},
		inputKeyPress: function (event) {
			if (event.charCode === 13) {
				var color = {r:0,g:0,b:0};
				var fontSize = App.widgets.ctrlPanelWidget._view.fontSize;
				if(App.widgets.ctrlPanelWidget._view.rgba){
					color = App.widgets.ctrlPanelWidget._view.rgba;
					console.log("Color:",color);
				}
				App.sio.emit('sendMessage', this.$input.val(),color,fontSize);
				this.$input.val('');
			}
		}
	});
	$(function () {
		var definition = eval('('+defs+')');
		$.emoticons.define(definition);
	});
	var ChatAreaController = function () {
		this.ChatArea = new ChatArea();
		this.addToChat = function (msg) {
			msg = $.emoticons.replace(msg);
			this.ChatArea.$('.content').append(msg);
			var self = this;
			$(function () {
				var scroll = self.ChatArea.$scrollbar.jScrollPane({
					verticalDragMinHeight: 71,
					verticalDragMaxHeight: 71,
					horizontalDragMinWidth: 71,
					horizontalDragMaxWidth: 71
				});
				var api = scroll.data('jsp');
				api.scrollToBottom();
			});
		},
			this.insertText = function(text){
				this.ChatArea.$input.val(this.ChatArea.$input.val() + text);
				this.ChatArea.$input.focus();
			},
			this.clearChat = function () {
				this.ChatArea.$('.content').empty();
			},
			this.view = function () {
				return this.ChatArea;
			},
			this.focus = function () {
				this.ChatArea.$input.focus();
			}
	};
	return ChatAreaController;
});
