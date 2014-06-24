require.config({
	paths: {
		"backbone": "/chat-app/www/bower_components/backbone/backbone",
		"jquery": "/chat-app/www/bower_components/jquery/dist/jquery.min",
		"underscore": "/chat-app/www/bower_components/underscore/underscore",
		"jscrollpane": "/chat-app/www/javascripts/libs/jscrollpane/jquery.jscrollpane",
		"mousewheel": "/chat-app/www/javascripts/libs/jscrollpane/jquery.mousewheel",
		"emoticons": "/chat-app/www/bower_components/emoticons/lib/emoticons",
		"tinycolor": "/chat-app/www/javascripts/libs/tinycolor",
		"colorpicker": "/chat-app/www/javascripts/libs/jquery-colorpickersliders/jquery.colorpickersliders",
	},
	shim: {
		"backbone": {
			deps: ["underscore", "jquery"],
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
});
require(['mchat-app'], function (App) {
	//$('#chatapp').html(App.views.ChatContent.render().$el);
});
