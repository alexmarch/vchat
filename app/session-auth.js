'use strict';

exports.auth = function(s, next){
	var sidkey = "PHPSESSID";
	var cookies = s.request.headers.cookie;
	var cookie = require('cookie');
	var http = require('http');
	var querystring = require('querystring');
	var userMgr = require('./users-manager');
	var roomMgr = require('./rooms-manager');
	var options = {
		host:'localhost',
		port: 8081,
		path: '/api/auth.php',
		method: 'POST'
	};
	var req = null;
	cookies = cookie.parse(cookies);
	if(cookies[sidkey]){
		var data = querystring.stringify({sid:cookies[sidkey]});
		options["headers"] = {
			'Content-Type': 'application/x-www-form-urlencoded',
			'Content-Length': Buffer.byteLength(data)
		}
		req = http.request(options,function(res){
			res.setEncoding('utf8');
			res.on('data',function(chunk){
				try{
					var data = JSON.parse(chunk);
					if(data && data['auth'] === true && data['utype']){
						console.log(roomMgr.inRoom(data));
						if(userMgr.isPerformer(data) && roomMgr.inRoom(data) === false){
							userMgr.add(s,data);
							return next();
						}else{
							return next(new Error('Authintification error'));
						}
					}else{
						return next(new Error('Authintification error'));
					}
				}catch(e){
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
