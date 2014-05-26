'use strict';

module.exports = function (app) {
	var io = require('socket.io')(app),
			userMgr = require('./users-manager'),
			roomMgr = require('./rooms-manager'),
			pubMgr = require('./publish-manager');
	io.use(require('./session-auth').auth);
	io.sockets.on('connection',
		function (socket) {
			var data = userMgr.get(socket);
			if (userMgr.isPerformer(data)) {
				roomMgr.create(data);
			}
			socket.on('sendMessage', function (msg) {
				//@todo permit message
				if (userMgr.isPerformer(data)) {
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
