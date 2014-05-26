var messegs = require('./messages'),
		debug = require('debug')('fancyflirt');
module.exports = {
	rooms: {},
	create: function (data) {
		var name = "room" + data.uid;
		data.socket.join(name);
		this.rooms[name] = data;
		data.socket.emit('welcome_to_chat_room',{
			room: name,
			username: data.username,
			nickname: data.nickname,
			msg: messegs.welcome_to_chat,
			host:'rtmp://127.0.0.1/videochat'
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
		if (this.rooms[data.room]) {
			data.socket.join(data.room);
			this.rooms[data.room].socket.emit(
				'new_user_joined',
				{
					username: data.username,
					nickname: data.nickname,
					uid: data.uid
				}
			);
		}
	},
	leave: function (data) {
		data.socket.leave(data.room);
		if(this.rooms[data.room]){
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
	sendToAll: function(data,msg){
		var msg = '<span class="msg"><span class="nickname">' + data.nickname + '</span>: ' + msg + '</span>';
		if(data.room){
			data.socket.broadcast.to(data.room).emit('receiveNewMsg',msg);
		}else{
			var name = "room" + data.uid;
			data.socket.broadcast.to(name).emit('receiveNewMsg',msg);
		}
		data.socket.emit('receiveNewMsg',msg);
	},
	inRoom: function(data){
		var name = "room" + data.uid;
		if(this.rooms[name]){
			return true;
		};
		return false;
	},
	getRoom: function(data){
		return "room" + data.uid;
	}
}
