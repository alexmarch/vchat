'use strict';

define(['jquery', 'underscore', 'backbone', 'views/mchat/chatarea',
 'widgets/mchat/playerwidget'],
	function ($, _, Backbone, ChatArea, VPlayerWidget) {
	var _template = '<div class="chatarea-ui mchat">\
	<div class="texture-wrapper mchat">\
		<div class="chatarea-right-controls mchat">\
			<div class="video-ui-widget mchat"></div>\
			<div class="private-buttons-group">\
				<a href="#" class="pvt-btn premium-pvt-button">Premium private</a>\
				<a href="#" class="pvt-btn pvt-button">Private</a>\
			</div>\
		</div>\
		<div id="content"></div>\
	</div>\
	</div>\
			<div class="settingsarea-ui" style="display: none">\
				<div class="texture-wrapper">\
					<div class="settingsarea-wrapper-ui">\
					<div id="chatsettingswidget"></div>\
					</div>\
				</div>\
			</div>';

	var ChatContent = Backbone.View.extend({
		id: 'chatcontent',
		className: 'chatcontent-ui mchat',
		template: _.template(_template),
		render: function(){
			//Render chat area
			App.widgets.ChatArea = new ChatArea();
			this.$el.html(this.template())
				.find('.chatarea-ui #content')
				.html(App.widgets.ChatArea.view().$el);

//			App.widgets.ChatSettings = new ChatSettingsWidget({el: this.$el.find('#chatsettingswidget')});
//			App.widgets.UsersList = new UserListWidget({el: this.$('.userslist-widget')});
			App.widgets.ChatArea.focus();

			//Render video publisher
			window.vplayer = new VPlayerWidget();
			this.$('.video-ui-widget').html(vplayer.elem());
			return this;
		}
	});

	return ChatContent;
});
