var server = require('http').Server(),
	debug = require('debug')('fancyflirt'),
	spawn = require('child_process').spawn,
	//web = spawn('php', ['-S','127.0.0.1:'+(process.env.WEB || 8081),'-t','www/']),
	app = require('../app/app')(server);

// web.stdout.on('data', function (data) {
// 	debug(data);
// });

// web.stderr.on('data', function (data) {
// 	debug(data);
// });

server.listen(process.env.PORT || 3000, function () {
	debug("Server listening on port [%d]", process.env.PORT || 3000);
});


