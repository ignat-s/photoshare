_.namespace('Emp.model.Company');

Emp.model.Company = Backbone.Model.extend({
			id : null,
			name : null,
			url : null,
			code : null,
			director : null,
			phone : null,
			fax : null,
			site : null,
			email : null,
            bidder: false,
            trader : false,
            manufactorer : false,
            isBidder : function() {
				return this.get('bidder');
			},
            isTrader : function() {
				return this.get('trader');
			},
            isManufactorer : function() {
				return this.get('manufactorer');
			},
			isEmptyProperty : function(property) {
				return _.isEmpty(this.get(property));
			},
			hasPhone : function() {
				return !this.isEmptyProperty('phone');
			},
			hasFax : function() {
				return !this.isEmptyProperty('fax');
			},
			hasFaxAndPhone : function() {
				return this.hasFax() && this.hasPhone();
			},
			hasEmail : function() {
				return !this.isEmptyProperty('email');
			},
			hasSite : function() {
				return !this.isEmptyProperty('site');
			},
			hasEmailAndSite : function() {
				return this.hasEmail() && this.hasSite();
			}
		});