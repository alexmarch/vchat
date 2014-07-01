var http = require('http'),
	querystring = require('querystring'),
	options = require('./config').options,
	debug = require('debug')('fancyflirt');

exports.api = {
	post: function (data, cb) {
		if(data && data.path){
			options.path = data.path;
		}
		var data = querystring.stringify(data);
		options["headers"] = {
			'Content-Type': 'application/x-www-form-urlencoded',
			'Content-Length': Buffer.byteLength(data)
		};
		var req = http.request(options, function (res) {
			res.setEncoding('utf8');
			res.on('data',function(chunk){
				try{
					debug(chunk);
					var data = JSON.parse(chunk);
					cb(null,data);
				}catch(ex){
					debug(ex.message);
				}
			});
		});
		req.on("error",function(e){
			cb(e,null);
		});

		req.write(data);
		req.end();
	}
}
