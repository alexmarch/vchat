define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var _template = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="320" height="175" id="Publisher" align="middle">\
		<param name="movie" value="chat-app/www/assets/src/Publisher.swf" />\
		<param name="quality" value="high" />\
		<param name="bgcolor" value="#000000" />\
		<param name="play" value="true" />\
		<param name="loop" value="true" />\
		<param name="wmode" value="direct" />\
		<param name="scale" value="showall" />\
		<param name="menu" value="true" />\
		<param name="devicefont" value="false" />\
		<param name="salign" value="" />\
		<param name="allowScriptAccess" value="sameDomain" />\
		<!--[if !IE]>-->\
		<object id="Publisher" type="application/x-shockwave-flash" data="chat-app/www/assets/src/Publisher.swf" width="320" height="175">\
			<param name="movie" value="chat-app/www/assets/src/Publisher.swf" />\
			<param name="quality" value="high" />\
			<param name="bgcolor" value="#000000" />\
			<param name="play" value="true" />\
			<param name="loop" value="true" />\
			<param name="wmode" value="direct" />\
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
	var PublisherView = Backbone.View.extend({
		className: "publisherwidget",
		id: "publisher",
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
	var PublisherController = function (prop) {
		var options = {};
		_.extend(options, prop);
		var _view = new PublisherView(options);

		this.view = function () {
			return _view;
		};

		this.publisherInit = function () {
			this.publisherObj = _view.getSWFObj('Publisher');
			return this.publisherObj;
		};

		this.debug = function (s) {
			console.log("%c***Publisher***%c	%o", "color: #00b3ee;background: #343638;padding:4px", "background:#fff", s)
		};

	};


	PublisherController.prototype = {
		init: function () {
			this.obj = this.publisherInit();
			console.log(this.obj);
			App.connect();
		},
		connect: function(host){
			this.obj.connect(host);
		},
		publish: function(stream){
			this.obj.publish(stream.name);
		},
		unpublish: function(){
			this.obj.unpublish();
		},
		connected: function(){
			App.connectedHandler();
		},
		memberarea: function(){
			this._inMemberArea = true;
			this.obj.memberarea();
		},
		closemember: function(){
			if(this._inMemberArea){
				this._inMemberArea = false;
				this.obj.closemember();
			}
		},
		startRec: function(){
			//this.obj.startRec();
			App.sio.emit('startRec');
		},
		stopRec: function(){
			//this.obj.stopRec();
			App.sio.emit('stopRec');
		},
		inMemberArea: function(){
			return this._inMemberArea;
		}
	};
	return PublisherController;
});
