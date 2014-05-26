module.exports = {
	permormers: {},
	members: {},
	guests: {},
	add: function (s, data) {
		if (data) {
			switch (data['utype']) {
				case 1:
					if (!this.permormers[s.id]) {
						this.permormers[s.id] = data;
						this.permormers[s.id].socket = s;
					};
					break;
				case 2:
					if (!this.members[s.id]) {
						this.members[s.id] = data;
						this.members[s.id].socket = s;
					};
					break;
				case 0:
					if (!this.guests[s.id]) {
						this.guests[s.id] = data;
						this.guests[s.id].socket = s;
					};
					break;
			}
		}
	},
	remove: function(s){
		if(this.permormers[s.id]){
			delete this.permormers[s.id];
			return;
		};
		if(this.members[s.id]){
			delete this.members[s.id];
			return;
		};
		if(this.guests[s.id]){
			delete this.guests[s.id];
			return;
		}
	},
	get: function(s){
		if(this.permormers[s.id]){
			return this.permormers[s.id];
		};
		if(this.members[s.id]){
			return this.members[s.id];
		};
		if(this.guests[s.id]){
			return this.guests[s.id];
		}
	},
	set: function(s,key,val){
		if(this.permormers[s.id]){
			this.permormers[s.id][key] = val;
		};
		if(this.members[s.id]){
			this.members[s.id][key] = val;
		};
		if(this.guests[s.id]){
			this.guests[s.id][key] = val;
		}
	},
	isPerformer: function(data){
		return data['utype'] === 1 ? true : false;
	},
	isMember: function(data){
		return data['utype'] === 2 ? true : false;
	},
	isGuest: function(data){
		return data['utype'] === 0 ? true : false;
	}
}
