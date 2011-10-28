_.namespace('Emp.model.Tender');

Emp.model.Tender = Backbone.Model.extend({
			defaults : {
				id : null,
				name : null,
				url : null,
				opened : null,
				category : null,
				beginAt : null,
				endAt : null,
				biddingInProgress : false,
				biddingEnded : false,
				biddingNotBegined : false,
				fullyApproved : false,
				fullyRefused : false,
				approving : false
			},
			initialize : function(attrs) {
				this.bind('change:biddingEnded', function() {
							this.stop();
						}, this);
						
				return this;
			},
			isOpened : function() {
				return this.get('opened');
			},
			isClosed : function() {
				return !this.isOpened();
			},
			isFullyApproved : function() {
				return this.get('fullyApproved');
			},
			isFullyRefused : function() {
				return this.get('fullyRefused');
			},
			isApproving : function() {
				return this.get('approving');
			},
			isBiddingInProgress : function() {
				return this.get('biddingInProgress');
			},
			isBiddingEnded : function() {
				return this.get('biddingEnded');
			},
			isBiddingNotBegined : function() {
				return this.get('biddingNotBegined');
			},
			getBiddingDate : function() {
				var beginAt, endAt, biddingDate, dateFormat, timeFormat;

				beginAt = new Date(this.get('beginAt'));
				endAt = new Date(this.get('endAt'));
				dateFormat = "yyyy/MM/dd";
				timeFormat = "HH:mm";
				format = dateFormat + ' ' + timeFormat;
				biddingDate = $.format.date(beginAt, format);

				if ($.format.date(beginAt, dateFormat) == $.format.date(endAt,
						dateFormat)) {
					return biddingDate + ' - '
							+ $.format.date(endAt, timeFormat);
				} else {
					return biddingDate + ' - ' + $.format.date(endAt, format);
				}
			},
			start : function() {
				/**
				 * TODO refactor name method
				 */
				var me = this;
				me._updateBiddingState();
				me.threadId = setInterval(function() {
							me._updateBiddingState.call(me);
						}, 1000);
			},
			stop : function() {
				clearInterval(this.threadId);
				this.threadId = null;
			},
			_updateBiddingState : function() {
				var me, currentDate;

				me = this;
				currentDate = Emp.getServerDate();

				if (me.get('endAt') < currentDate) {
					if (!me.isBiddingEnded()) {
						me.set({
									biddingNotBegined : false,
									biddingInProgress : false,
									biddingEnded : true
								});
					}
					me.trigger('biddingstate', 'end', me.get('endAt'));
				} else if (currentDate < me.get('beginAt')) {
					if (!me.isBiddingNotBegined()) {

						me.set({
									biddingNotBegined : true,
									biddingInProgress : false,
									biddingEnded : false
								});
					}
					me.trigger('biddingstate', 'before', me.get('beginAt')
									- currentDate);
				} else {
					if (!me.isBiddingInProgress()) {
						me.set({
									biddingNotBegined : false,
									biddingInProgress : true,
									biddingEnded : false
								});
					}
					me.trigger('biddingstate', 'bidding', me.get('endAt')
									- currentDate);
				}
			}
		});