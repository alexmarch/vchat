'use strict';
var debug = require('debug')('auth:fancyflirt');
exports.auth = function(s, next){
	var sidkey = "PHPSESSID",
	cookie = require('cookie'),
	http = require('http'),
	querystring = require('querystring'),
	userMgr = require('./users-manager'),
	roomMgr = require('./rooms-manager'),
	memcache = require('memcache'),
	options = require('./config').options,
	cookies = s.request.headers.cookie,
	cookies = cookie.parse(cookies);

	if(cookies[sidkey]){
		/*///////////////////////////////////////
		 * Authentication user with session id
		//////////////////////////////////////*/
		var data = querystring.stringify({"function":"auth_client", sessionid:cookies[sidkey]});
		options["headers"] = {
			'Content-Type': 'application/x-www-form-urlencoded',
			'Content-Length': Buffer.byteLength(data)
		};
		/*//////////////////////////////////////////
		* Memcache client connection
		/////////////////////////////////////////*/
		// var c = new memcache.Client(11211, '127.0.0.1');
		// c.connect();
		// c.on('connect', function(){
		// 	debug("Memcache client connected !");
		// 	console.log("Sessions:", 'sessions/'+cookies[sidkey]);
		// 	c.get('memc.sess.key.'+cookies[sidkey], function(err,sess){
		// 		if(err === undefined){
		// 			console.log(sess);
		// 		}else{
		// 			console.log("Session error", err);
		// 		}
		// 	});
		// });
		// c.on("error",function(e){
		// 	console.log("Memcache client error", e);
		// });
		debug("Authentication...");
		var req = http.request(options,function(res){
			res.setEncoding('utf8');
			res.on('data',function(chunk){
				try{
					debug(chunk);
					var data = JSON.parse(chunk);
					debug("Authentication -->");
					if(data['utype'] !== undefined){
						if(userMgr.isPerformer(data) && roomMgr.inRoom(data) === false){
							debug("Performer authentication");
							userMgr.add(s,data);
							return next();
						}else if(userMgr.isMember(data) || userMgr.isGuest(data)){
							debug("Client authentication");
							userMgr.add(s,data);
							return next();
						}else{
							debug("Auth error!");
							return next(new Error('Authintification error'));
						}
					}else{
						return next(new Error('Authintification error'));
					}
				}catch(e){
					debug(e.message);
				}
			})
		});
		req.on("error",function(e){
			console.log("Request error:", e.message);
		});

		req.write(data);
		req.end();
	}
}

}
