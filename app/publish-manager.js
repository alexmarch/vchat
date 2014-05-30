'use strict';

var debug = require('debug')('fancyflirt'),
	crypto = require('crypto'),
	userMgr = require('./users-manager'),
	roomMgr = require('./rooms-manager');

module.exports = {
	generete: function () {
		return crypto.randomBytes(32).toString('base64');
	},
	publish: function (o, data) {

		userMgr.set(o.socket, 'type', data.type);
		userMgr.set(o.socket, 'sname', data.name);

		var room = roomMgr.getRoom(o);
		o.socket.emit('publish', {name: data.name, type: data.type});
		if (data.type === "free") {
			o.socket.broadcast.to(room).emit('play', {name: data.name});
		}
		if (data.type === "memberarea") {
			o.socket.broadcast.to(room).emit('memberarea');
		}
		debug("Publish new stream:" + data.name);
	},
	unpublish: function (o) {
		o.socket.emit('unpublish');
	}
}
