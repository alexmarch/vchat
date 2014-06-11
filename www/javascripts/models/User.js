define(['jquery', 'underscore', 'backbone'], function ($, _, Backbone) {
	var User = Backbone.Model.extend({
		default:{
			uid: null,
			username: '',
			nickname: '',
			count: 0
		}
	});
	return User;
});
