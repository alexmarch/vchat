var userMgr = require('./users-manager'),
	roomMgr = require('./rooms-manager'),
	api = require('./http-api').api;

exports.PrivateManager = function () {
	this.privateRooms = {};
	this.premiumRooms = {};

	this.loop = function() {
		for(var key in this.privateRooms){
			///console.log(key);
		}
	};

	this.create = function(data){
		var name = "room" + data.uid;
		this.privateRooms[name] = {
			clients: {}
		};
		this.premiumRooms[name] = {
			clients: {}
		};
	};

	this.notInPrivate = function(room, uid){
		if(this.privateRooms[room] == undefined || this.privateRooms[room].clients[uid] === undefined){
			return true;
		}
		return false;
	};

	this.addToPrivateRoom = function(userData){
		this.privateRooms[userData.room].clients[userData.uid] = userData;
		userData.socket.emit('start_private');
	};

	this.getCountInPrivate = function(userData){
		return this.privateRooms[userData.room].clients[userData.uid].length;
	};

	var self = this;
	setInterval(function(){
		self.loop();
	},1000 * 60);
}
