'use strict';

module.exports = function (app) {
	var io = require('socket.io')(app),
			userMgr = require('./users-manager'),
			roomMgr = require('./rooms-manager'),
			pubMgr = require('./publish-manager'),
			debug = require('debug')('fancyflirt');

	io.use(require('./session-auth').auth); //middleware authentication

	io.sockets.on('connection',
		function (socket) {
			var data = userMgr.get(socket);
			debug("Socket connection");
			if (userMgr.isPerformer(data)) {
				debug("Create new room");
				roomMgr.create(data);
			}else if(userMgr.isMember(data) || userMgr.isGuest(data)){
				socket.emit('set_room');
			}
			socket.on('join_room',function(room){
				debug("Client join the room "+ room.id);
				userMgr.set(socket,'room','room'+room.id);
				data.room = 'room'+room.id;
				roomMgr.join(data);
			});
			socket.on('sendMessage', function (msg) {
				//@todo permit message
				var data = userMgr.get(socket);
				if (userMgr.isPerformer(data) || userMgr.isMember(data)) {
					roomMgr.sendToAll(data, msg);
				}
			});
			socket.on("disconnect",
				function () {
					if (userMgr.isPerformer(data)) {
						roomMgr.exit(data);
					}
					userMgr.remove(socket);
				});
			socket.on("connectedSuccess",
				function(){
					if(userMgr.isPerformer(data)){
						//Publish new stream
						pubMgr.publish(data,{
							name: roomMgr.getRoom(data)+'_'+pubMgr.generete(),
							type: 'free'
						});
					}
				}
			);
			socket.on("memberarea",function(){
				pubMgr.publish(data,{
					name: roomMgr.getRoom(data)+'_'+pubMgr.generete(),
					type: 'memberarea'
				});
			});
			socket.on("closemember",function(){
				pubMgr.publish(data,{
					name: roomMgr.getRoom(data)+'_'+pubMgr.generete(),
					type: 'free'
				});
			});
			socket.on("settopic",function(topic){
				if(userMgr.isPerformer(data)){
					userMgr.set(socket,"topic",topic.title);
					var newTopic = {
						title: '<div class="topic"><span>'+data.nickname + ' Room tipic: </span>'+topic.title+'</div>',
						nickname: data.nickname
					};
					socket.emit("topic_change", newTopic);
					socket.broadcast.to(roomMgr.getRoom(data)).emit("topic_change", newTopic);
				}
			})
		});
}
