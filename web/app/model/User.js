_.namespace('Emp.model.User');

Emp.model.User = Backbone.Model.extend({
			id : null,
			username : null,
			fullName : null,
			email : null,
			phone : null,
			company : null,
			occupation : null,
			lastLogin : null,
			url : null,
            companyUrl : null,
            bidder : false,
            simpleUser : false,
            admin : false,
            superAdmin : false,
            enabled : false,
            accountLocked : false,
            accountExpired : false,
            credentialsExpired : false,
            noPermissions : false,
            isBidder : function() {
				return this.get('bidder');
			},
            isSimpleUser : function() {
				return this.get('simpleUser');
			},
            isAdmin : function() {
				return this.get('admin');
			},
            isSuperAdmin : function() {
				return this.get('superAdmin');
			},
            isEnabled : function() {
				return this.get('enabled');
			},
            isAccountLocked : function() {
				return this.get('accountLocked');
			},
            isAccountExpired : function() {
				return this.get('accountExpired');
			},
            isCredentialsExpired : function() {
				return this.get('credentialsExpired');
			},
            hasNoPermissions : function() {
				return this.get('noPermissions');
			},
			has : function(property) {
				return _.isEmpty(this.get(property));
			}
		});