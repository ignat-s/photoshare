_.namespace('Emp.model.Registration');

Emp.model.Registration = Backbone.Model.extend({
			id : null,
			fullName : null,
			email : null,
			phone : null,
            occupation : null,
			companyName : null,
			companyCode : null,
			createdAt : null,
			statusName : null,
            fresh : false,
            accepted : false,
            declined : false,
            url : false,
            isAccepted : function() {
				return this.get('accepted');
			},
            isDeclined : function() {
				return this.get('declined');
			},
            isFresh : function() {
				return this.get('fresh');
			},
			has : function(property) {
				return _.isEmpty(this.get(property));
			}
		});