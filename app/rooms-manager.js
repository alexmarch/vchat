var messegs = require('./messages'),
	debug = require('debug')('fancyflirt'),
	userMgr = require('./users-manager'),
	conf = require('./config'),
	_s = require('underscore.string'),
	escape = require('escape-html');

module.exports = {
	rooms: {},
	create: function (data) {
		var name = "room" + data.uid;
		data.socket.join(name);
		debug("New room name:" + name);
		this.rooms[name] = data;
		this.rooms[name].clients = {};
		data.socket.emit('welcome_to_chat_room', {
			room: name,
			username: data.username,
			nickname: data.nickname,
			msg: messegs.welcome_to_chat,
			host: conf.host
		});
	},
	exit: function (data) {
		var name = "room" + data.uid;
		data.socket.leave(name);
		if (this.rooms[name]) {
			data.socket.broadcast.to(name).emit('performer_exit_room');
			delete this.rooms[name];
		}
	},
	exitClient: function(data) {
		if(this.rooms[data.room]){
			delete this.rooms[data.room].clients[data.socket.id];
			this.rooms[data.room].socket.emit('reset_users_list',this.rooms[data.room].clients);
		}
	},
	join: function (data) {
		var name = data.room
		if (this.rooms[name]) {
			data.socket.join(name);
			var sdata = userMgr.get(this.rooms[name].socket);
			data.socket.emit('join_successful', {
				type: sdata.type,
				nickname: sdata.nickname,
				topic: sdata.topic,
				host: conf.host,
				sname: sdata.type === 'free' ? sdata.sname : undefined
			});
			//if(sdata.type === "free"){
			//data.socket.emit('play',{sname: sdata.sname});
			//}
			var client = {
				uid: data.uid,
				id: data.socket.id,
				username: data.username,
				nickname: data.nickname,
				count: data.count
			}
			this.rooms[name].clients[client.id] = client;
			this.rooms[name].socket.emit('reset_users_list',this.rooms[name].clients);
		} else {
			data.socket.emit('join_room_error');
		}
	},
	leave: function (data) {
		data.socket.leave(data.room);
		if (this.rooms[data.room]) {
			delete this.rooms[name].clients[data.uid];
			this.rooms[name].socket.emit('reset_users_list',this.rooms[name].clients);
		}
	},
	sendToAll: function (data, msg, color) {
		if(_s.trim(msg) === '') return;
		msg = escape(msg);
		var r = color.r;
		var g = color.g;
		var b = color.b;
		var color = color ? 'rgb('+r+','+g+','+b+')' : '';
		var msg = '<span class="msg"><span class="'+(data.utype === 2 ? 'user' : '')+' nickname">' + data.nickname + '</span>: <span id="msg">'+
			msg + '</span></span>';
		if (data.room) {
			data.socket.broadcast.to(data.room).emit('receiveNewMsg', msg, color);
		} else {
			var name = "room" + data.uid;
			data.socket.broadcast.to(name).emit('receiveNewMsg', msg, color);
		}
		data.socket.emit('receiveNewMsg', msg, color);
	},
	inRoom: function (data) {
		var name = "room" + data.uid;
		if (this.rooms[name]) {
			return true;
		}
		;
		return false;
	},
	getRoom: function (data) {
		return "room" + data.uid;
	}
}
