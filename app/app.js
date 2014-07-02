'use strict';

module.exports = function (app) {
	var io = require('socket.io')(app),
		userMgr = require('./users-manager'),
		roomMgr = require('./rooms-manager'),
		pubMgr = require('./publish-manager'),
		PrivateManager = require('./private-manager').PrivateManager,
		debug = require('debug')('fancyflirt'),
		api = require('./http-api').api,
	  pvtMgr = new PrivateManager();

	io.use(require('./session-auth').auth); //middleware authentication
	io.sockets.on('connection', function (socket) {
		/**
		 * Get client data
		 * @type {*}
		 */
		var data = userMgr.get(socket);

		if (userMgr.isPerformer(data)) {
			roomMgr.create(data);
			pvtMgr.create(data);
		} else if (userMgr.isMember(data) || userMgr.isGuest(data)) {
			socket.emit('set_room');
		}

		socket.on('join_room', function (room) {
			debug("Client join the room " + room.id);
			userMgr.set(socket, 'room', 'room' + room.id);
			data.room = 'room' + room.id;
			roomMgr.join(data);
		});

		socket.on('sendMessage', function (msg, color, fontSize) {
			var data = userMgr.get(socket);
			if (userMgr.isPerformer(data) || userMgr.isMember(data)) {
				roomMgr.sendToAll(data, msg, color, fontSize);
			}
		});

		socket.on("disconnect", function () {
			var data = userMgr.get(socket);
			if (userMgr.isPerformer(data)) {
				api.post({
					function: 'update_performer_state',
					sessionid: data.sid,
					state: 'offline'
				},function(err,data){
					if(!err){
						console.log("Data:", data);
					}else{
						console.log("Error", err);
					}
				});
				roomMgr.exit(data);
			} else {
				roomMgr.exitClient(data);
			}
			userMgr.remove(socket);
		});

		socket.on("connectedSuccess", function () {
			if (userMgr.isPerformer(data)) {
				//Publish new stream
				pubMgr.publish(data, {
					name: roomMgr.getRoom(data) + '_' + pubMgr.generete(),
					type: 'free'
				});
			}
		});

		socket.on("memberarea", function () {
			pubMgr.publish(data, {
				name: roomMgr.getRoom(data) + '_' + pubMgr.generete(),
				type: 'memberarea'
			});
		});

		socket.on("closemember", function () {
			pubMgr.publish(data, {
				name: roomMgr.getRoom(data) + '_' + pubMgr.generete(),
				type: 'free'
			});
		});

		socket.on("settopic", function (topic) {
			if (userMgr.isPerformer(data)) {
				var newTopic = {
					title: '<div class="topic"><span>' + data.nickname + ' Room tipic: </span>' + topic.title + '</div>',
					nickname: data.nickname
				};
				userMgr.set(socket, "topic", newTopic.title);
				socket.emit("topic_change", newTopic);
				socket.broadcast.to(roomMgr.getRoom(data)).emit("topic_change", newTopic);
			}
		});
		/** ////////////////////////////////
		*	Start/Stop recording
		* ///////////////////////////////*/
		socket.on('startRec',function(){
			var userData = userMgr.get(socket);
			if(userMgr.isPerformer(userData)){
				var data = 'control/record/start?app=videochat&name='+userData.sname+'&rec=rec1';
				api.get(data,function(res){
					console.log("Record start responce", res ,userData.sname);
				});
			}
		});
		socket.on('stopRec',function(){
			var userData = userMgr.get(socket);
			if(userMgr.isPerformer(userData)){
				var data = 'control/record/stop?app=videochat&name='+userData.sname+'&rec=rec1';
				api.get(data,function(res){
					console.log("Record stop responce", res, userData.sname);
				});
			}
		});
		/** ////////////////////////////////
		 * 	Private chat callbacks
		 * 	///////////////////////////////
		 */
		socket.on('start_private', function () {
			var userData = userMgr.get(socket);
			var roomData = roomMgr.getRoomData(userData.room);
			var modelData = userMgr.get(roomData.socket);
			if (userMgr.isMember(userData) && !modelData['premium']) {
				api.post({path: '/chat_api.php', 'function':'get_user_credit', sessionid: userData.sid}, function (err, data) {
					if (!err) {
						if(parseFloat(roomData.private_cost) <= parseFloat(data.credits)){
							if(pvtMgr.notInPrivate(userData.room,userData.uid)){
								pvtMgr.addToPrivateRoom(userData);
								if(pvtMgr.getCountInPrivate(userData) == 1){
									roomData.socket.emit('start_private');
									userMgr.set(roomData.socket,'private',true);
									roomData.socket.to(userData.room).emit('performer_in_private');
								}
								pubMgr.publish(modelData, {
									name: roomMgr.getRoom(modelData) + '_' + pubMgr.generete(),
									type: 'private',
									socket: userData.socket
								});
							}
						}else{
							socket.emit('buy_credits'); //Show member dialog for buy credits
						}
					} else {
						socket.emit('start_private_error');
					}
				});
			}else{
				console.log("not member");
			}
		});
	});
}
