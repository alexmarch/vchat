define(['jquery', 'underscore', 'backbone', '../models/User'], function ($, _, Backbone, User) {
	var Users = Backbone.Collection.extend({
		model: User
	});
	return Users;
});
