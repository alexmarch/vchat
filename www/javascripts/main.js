requirejs.config({
	paths: {
		"backbone": "/bower_components/backbone/backbone",
		"jquery": "/bower_components/jquery/dist/jquery.min",
		"underscore": "/bower_components/underscore/underscore",
		"jscrollpane": "/javascripts/libs/jscrollpane/jquery.jscrollpane",
		"mousewheel": "/javascripts/libs/jscrollpane/jquery.mousewheel"
	},
	shim: {
		"backbone": {
			deps: ["underscore","jquery"],
			exports: "Backbone"
		},
		"underscore": {
			exports: "_"
		},
		"jscrollpane": {
			deps: ["jquery"]
		},
		"mousewheel": {
			deps: ["jquery"]
		}
	}
});
define(['app'], function (App) {
	$('#chatapp').html(App.views.ChatContent.render().$el);
});
