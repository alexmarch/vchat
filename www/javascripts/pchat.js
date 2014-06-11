requirejs.config({
	paths: {
		"backbone": "/bower_components/backbone/backbone",
		"jquery": "/bower_components/jquery/dist/jquery.min",
		"underscore": "/bower_components/underscore/underscore",
		"jscrollpane": "/javascripts/libs/jscrollpane/jquery.jscrollpane",
		"mousewheel": "/javascripts/libs/jscrollpane/jquery.mousewheel",
		"emoticons": "/bower_components/emoticons/lib/emoticons",
		"tinycolor": "/javascripts/libs/tinycolor",
		"colorpicker": "/javascripts/libs/jquery-colorpickersliders/jquery.colorpickersliders",
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
		},
		"emoticons": {
			deps: ["jquery"]
		},
		"tinycolor" :{
			deps:["jquery"]
		},
		"colorpicker": {
			deps: ["tinycolor","jquery"]
		}
	}
	}
});
define(['pchat-app'], function (App) {
	$('#chatapp').html(App.views.ChatContent.render().$el);
});
