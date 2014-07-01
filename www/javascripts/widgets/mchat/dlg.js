define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var template = '<div class="dlg-wrapper"><div class="dlg-top"><span class="close-dlg">&times;</span></div><div class="dlg-content">' +
		'<div class="dlg-caption"><%= caption%></div><div class="dlg-text"><%= text%></div>' +
		'<div class="dlg-footer"><button class="button-start">START</button></div>' +
		'</div></div>';
	var Dlg = Backbone.View.extend({
		events:{
			'click .closeDlg': 'closeDlg',
			'click .button-start': 'startButton'
		},
		template: _.template(template),
		initialize: function(options){
			this.options = {};
			_.extend(this.options,options);
			this.render();
			return this;
		},
		render: function(){
			this.$el.html(this.template({caption: this.options.caption, text: this.options.text}));
		},
		closeDlg: function(){
			this.$el.css({"display":"none"});
		},
		startButton: function(){
			this.closeDlg();
			console.log('stratButton');
			if(this.options.onstart && "function" === typeof this.options.onstart){
				this.options.onstart();
			}
		}
	});
	return Dlg;
});
