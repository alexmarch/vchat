var messegs = require('./messages'),
	debug = require('debug')('fancyflirt'),
	userMgr = require('./users-manager'),
	conf = require('./config'),
	_s = require('underscore.string');
module.exports = {
	rooms: {},
	create: function (data) {
		var name = "room" + data.uid;
		data.socket.join(name);
		debug("New room name:" + name);
		this.rooms[name] = data;
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
			this.rooms[name].socket.emit(
				'new_user_joined',
				{
					username: data.username,
					nickname: data.nickname,
					uid: data.uid
				}
			);
		} else {
			data.socket.emit('join_room_error');
		}
	},
	leave: function (data) {
		data.socket.leave(data.room);
		if (this.rooms[data.room]) {
			this.rooms[data.room].socket.emit(
				'user_leave_room',
				{
					username: data.username,
					nickname: data.nickname,
					uid: data.uid
				}
			);
		}
	},
	sendToAll: function (data, msg) {
		if(_s.trim(msg) === '') return;
		var msg = '<span class="msg"><span class="'+(data.utype === 2 ? 'user' : '')+' nickname">' + data.nickname + '</span>: ' + msg + '</span>';
		if (data.room) {
			data.socket.broadcast.to(data.room).emit('receiveNewMsg', msg);
		} else {
			var name = "room" + data.uid;
			data.socket.broadcast.to(name).emit('receiveNewMsg', msg);
		}
		data.socket.emit('receiveNewMsg', msg);
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
