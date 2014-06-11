'use strict';
define(['jquery', 'underscore', 'backbone',
	'mousewheel', 'jscrollpane', '../collections/Users'], function ($, _, Backbone, mousewheel, jscrollpane, Users) {
	var _template = '<div class="userlistwidget" style="padding-top: 5px;"><div id="userslist-scrollbar" class="scroll-pane">\
	<ul class="userlist">\
	</ul>\
	</div></div>';
	var UsersListView = Backbone.View.extend({
		className: 'userslist-ui',
		id: 'userslist',
		template: _.template(_template),
		initialize: function () {
			this.$el.addClass(this.className);
			this.listenTo(this.collection,'reset',this.resetUsersList);
			this.render();
		},
		render: function () {
			this.$el.append(this.template());
			this.$scrollbar = this.$el.find('#userslist-scrollbar');
			return this;
		},
		afterRender: function () {
			var self = this;
			setTimeout(function () {
				$(function () {
					var scroll = self.$scrollbar.jScrollPane({
						verticalDragMinHeight: 64,
						verticalDragMaxHeight: 64,
						horizontalDragMinWidth: 64,
						horizontalDragMaxWidth: 64
					});
					//var api = scroll.data('jsp');
					//api.scrollToBottom();
				})
			});
		},
		resetUsersList:function(){
			var clients = this.collection.toJSON();
			var $list = this.$el.find('.userlist');
			var $item = $('<li>');
			var $link = $('<a href="#"></a>');
			var self = this;
			$list.empty();
			_.each(clients,function(client){
					var user = _.values(client)[0];
					$item.append($link.text(user.username)).appendTo($list);
			});
			this.afterRender();
		}
	});
	var UsersListWidget = function (options) {
		_.extend(this, options);
		var _view, users;

		this.init = function () {
			users = new Users();
			_view = new UsersListView({el: this.el || $('.userslist-widget'), collection: users});
			_view.afterRender();
		};
		this.view = function () {
			return _view.render().$el;
		};
		this.reset = function(clients){
			users.reset(clients);
		}
		this.init();
	};
	return UsersListWidget;
})
;
