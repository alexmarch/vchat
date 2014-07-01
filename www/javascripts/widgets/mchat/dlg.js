define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var _template = '<div class="dlg-wrapper">' +
		'<div class="dlg-top"><span class="close-dlg">&times;</span></div>' +
		'<div class="dlg-content">' +
			'<div class="dlg-caption"><%= caption%></div>' +
		'<div class="dlg-text"><%= text%></div>' +
		'<div class="dlg-footer"><a href="#" class="button-start">START</a></div>' +
		'</div>' +
		'</div>';
	var Dlg = Backbone.View.extend({

		template: _.template(_template),

		events:{
			'click .close-dlg': 'closeDlg',
			'click .button-start': 'startButton'
		},

		initialize: function(options){
			this.opt = options;
		},

		render: function(){
			this.$el.html(this.template({caption: this.opt.caption, text: this.opt.text}));
			return this;
		},

		closeDlg: function(){
			console.log("close");
			this.$el.css({"display":"none"});
		},

		startButton: function(){
			console.log('stratButton');
			if(this.opt.onstart && "function" === typeof this.opt.onstart){
				this.opt.onstart();
			}
		}
	});
	return Dlg;
});
