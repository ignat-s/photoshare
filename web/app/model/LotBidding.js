/**
 * 1.Устанавливать новую цену,комментарии. 2.Отправлять данные на сервер.
 * 3.Уведомлять коллекцию о изменениях. 4.Валидация введеных данных. 5.Обработка
 * ошибок. 6.Сравнивать текущие данные с пришедшими.
 */
_.namespace('Emp.model.LotBidding');

Emp.model.LotBidding = Emp.model.Base.extend({
			urlRoot : Emp.config.routes.tender_bidding_lots,
			defaults : {
				// static data
				'id' : null,
				'number' : null,
				'customer' : null, // Заказчик
				'productName' : null, // Продукция
				'unit' : null, // Ед. изм.
				'quantity' : null, // Количество

				'actualPrice' : null, // Лучшая цена торгов

				'biddingStep' : null, // Шаг торгов
				'biddingInProgress' : null, // Проходят ли торги в данный момент
				'biddingNotBegined' : null, // Торги ещё не начались
				'biddingEnded' : null, // Торги закончились
				'comment' : null, // Дополнительно

				'lastPurchasePrice' : null, // Цена последней закупки
				'lastPurchaseDate' : null, // Дата последней закупки

				'userIsBidder' : null, // Является ли пользователь участником
				'maxProfitableBidPrice' : null, // Макc возможная цена
				'minProfitableBidPrice' : null, // Мин возможная цена нового
				'newProfitableBidPossible' : null, // Данный лот мог бы иметь
				// более высокую цену
				'actualPriceProposedByUser' : null, // Лучшая цена принадлежит
				'userBidderPrice' : null, // Цена пользователя
				'userBidderComment' : null, // Комментарий участника торгов

				'actualBidderCompany' : null

			},
			initialize : function(data, options) {
				return this;
			},
			getActualPrice : function() {
				return this.get('actualPrice');
			},
			getUserBidderPrice : function() {
				return this.get('userBidderPrice');
			},
			getMaxProfitableBidPrice : function() {
				return this.get('maxProfitableBidPrice');
			},
			getMinProfitableBidPrice : function() {
				return this.get('minProfitableBidPrice');
			},
			isBiddingInProgress : function() {
				return this.get('biddingInProgress');
			},
			isBiddingNotBegined : function() {
				return this.get('biddingNotBegined');
			},
			isBiddingEnded : function() {
				return this.get('biddingEnded');
			},
			isNewProfitableBidPossible : function() {
				return this.get('newProfitableBidPossible');
			},
			isActualPriceProposedByUser : function() {
				return this.get('actualPriceProposedByUser');
			},
			isUserIsBidder : function() {
				return this.get('userIsBidder');
			},
			isUserProposedPrice : function() {
				return this.get('userBidderPrice') > 0;
			},
			update : function(data, options) {
				var me = this;
				options = options || {};

				if (options.type === 'userBidderPrice') {
					return me.updateUserBidderPrice(data);
				}
				if (options.type === 'userBidderComment') {
					return me.updateUserBidderComment(data);
				}

				if (!_.isEqual(me.attributes, data)) {
					me.set(data);
				}
			},
			updateUserBidderPrice : function(price) {
				var me, params;

				me = this;
				params = {
					dataType : 'json',
					type : 'POST',
					data : price,
					error : function() {
						/**
						 * TODO show server error
						 */
					},
					success : function(data, textStatus, request) {
						var message = {
							type : data.success ? 'success' : 'error',
							msg : data.message,
							bind : 'userBidderPrice'
						};
						if (data.success) {
							data = data.data[0];
							me.update(data);
						}

						me.trigger('message', me, message);
					},
					url : _.template('{base}/{id}/bid', {
								base : me.urlRoot,
								id : me.get('id')
							})
				};
				$.ajax(params);
			},
			updateUserBidderComment : function(comment) {
				var me, params;

				me = this;
				params = {
					dataType : 'json',
					type : 'POST',
					data : comment,
					error : function() {
						/**
						 * TODO show server error
						 */
					},
					success : function(data, textStatus, request) {
						var message = {
							type : data.success ? 'success' : 'error',
							msg : data.message,
							bind : 'userBidderPrice'
						};
						if (data.success) {
							me.set(comment);
						}
						me.trigger('message', me, message);
					},
					url : _.template('{base}/{id}/comment', {
								base : me.urlRoot,
								id : me.get('id')
							})
				};
				$.ajax(params);
			}
		});