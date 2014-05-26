define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var PlayerView = Backbone.View.extend({
		className: "playerwidget",
		id: "playerwidget",
//		template: _.template(_template),

		initialize: function () {
			this.render();
		},

		render: function () {
//			this.$el.html(this.template());
			return this;
		},

		getSWFObj: function (elementNmae) {
			var isIE = navigator.appName.indexOf("Microsoft") != -1;
			return (isIE) ? window[elementNmae] : document[elementNmae];
		}
	});
	return PlayerView
});
