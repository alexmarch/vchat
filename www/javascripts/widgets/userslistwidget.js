'use strict';
define(['jquery', 'underscore', 'backbone',
	'mousewheel', 'jscrollpane'], function ($, _, Backbone, mousewheel, jscrollpane) {
	var _template = '<div class="userlistwidget"><div id="userslist-scrollbar" class="scroll-pane">\
	<ul class="userlist">\
	</ul>\
	</div></div>';
	var UsersListView = Backbone.View.extend({
		className: 'userslist-ui',
		id: 'userslist',
		template: _.template(_template),
		initialize: function () {
			this.$el.addClass(this.className);
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
					self.$scrollbar.jScrollPane({
						verticalDragMinHeight: 64,
						verticalDragMaxHeight: 64,
						horizontalDragMinWidth: 64,
						horizontalDragMaxWidth: 64
					});
				})
			});
		}
	});
	var UsersListWidget = function (options) {
		_.extend(this, options);
		var _view;
		this.init = function () {
			_view = new UsersListView({el: this.el || $('.userslist-widget')});
			_view.afterRender();
		};
		this.view = function () {
			return _view.render().$el;
		};
		this.init();
	};
	return UsersListWidget;
})
;
