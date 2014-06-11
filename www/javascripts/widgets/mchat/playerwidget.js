define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var _template = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="650" height="352" id="VPlay" align="middle">\
		<param name="movie" value="assets/src/VPlay.swf" />\
		<param name="quality" value="high" />\
		<param name="bgcolor" value="#000000" />\
		<param name="play" value="true" />\
		<param name="loop" value="true" />\
		<param name="wmode" value="window" />\
		<param name="scale" value="showall" />\
		<param name="menu" value="true" />\
		<param name="devicefont" value="false" />\
		<param name="salign" value="" />\
		<param name="allowScriptAccess" value="sameDomain" />\
		<!--[if !IE]>-->\
		<object type="application/x-shockwave-flash" id="VPlay" data="assets/src/VPlay.swf" width="650" height="352">\
			<param name="movie" value="assets/src/VPlay.swf" />\
			<param name="quality" value="high" />\
			<param name="bgcolor" value="#000000" />\
			<param name="play" value="true" />\
			<param name="loop" value="true" />\
			<param name="wmode" value="window" />\
			<param name="scale" value="showall" />\
			<param name="menu" value="true" />\
			<param name="devicefont" value="false" />\
			<param name="salign" value="" />\
			<param name="allowScriptAccess" value="sameDomain" />\
			<!--<![endif]-->\
			<a href="http://www.adobe.com/go/getflash">\
				<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />\
			</a>\
			<!--[if !IE]>-->\
		</object>\
		<!--<![endif]-->\
		</object>';
	var PlayerView = Backbone.View.extend({
		className: "playerwidget",
		id: "playerwidget",
		template: _.template(_template),

		initialize: function () {
			this.render();
		},

		render: function () {
			this.$el.html(this.template());
			return this;
		},

		getSWFObj: function (elementNmae) {
			var isIE = navigator.appName.indexOf("Microsoft") != -1;
			return (isIE) ? window[elementNmae] : document[elementNmae];
		}
	});

	var VPlayerController = function (prop) {
		if ("object" === typeof prop)
			_.extend(this, prop);

		this.PlayerView = new PlayerView();

		this.elem = function () {
			return this.PlayerView.$el;
		};

		this.connect = function (host) {
			try {
				if (this.swf) {
					this.swf.connect(host);
				}
			} catch (e) {
				//catch exception
			}
		};

		this.onsuccess = function(){
			App.sio.emit('get_stream');
		};

		this.memberarea = function(){
			this.swf.memberarea();
		};

		this.closememberarea = function(){
			this.swf.closememberarea();
		};

		this.play = function(name){
			try {
				if (this.swf) {
					console.log("Stream name:",name);
					this.swf.play(name);
				}
			} catch (e) {
				//catch exception
			}
		}

		this.init = function () {
			this.swf = this.PlayerView.getSWFObj('VPlay');
			if (this.swf) {
				console.log("Init object successfuly");
				App.connect();
			};

		}
	}

	return VPlayerController;
});
