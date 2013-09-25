define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Util = require( '/src/app/util' );

	var mission = app.mission;
	var list = mission.list;

	require( '/bootstrap/datepicker/js/bootstrap-datepicker' )
	require( '/bootstrap/datepicker/js/locales/bootstrap-datepicker.zh-CN' )
	require( '/bootstrap/datepicker/css/datepicker.css' )

	var Model = Backbone.Model.extend({
		urlRoot: '/mission/mission/id/', 
		defaults_p: {
			mission_type_id: 0, 
			cate_id: 0, 
			user_state: 0,  
			create_uid: app_data.user.id,  
			kf_uid: app_data.user.id,  
			create_uname: app_data.user.username,  
			store_id: null, 
			store: null, 
			wanwan: null, 
			order_num_list: null, 
			remarks: null, 
			flag_list: [], 
			flag_remarks: '', 
			state: '',  

			send_to_product_input: '', 
			mail_person_name: '', 
	        mail_person_bank: '', 
	        mail_person_bank_address: '', 
	        mail_money: '', 
	        mail_time: '', 
	        mail_product_info: '', 
	        mail_address: '', 
			pay_money: '', 
			pay_time: '', 
			detail_description: '', 
			sub_mail_type: '', 
			receipt: '', 
	        receipt_subject: '',
	        receipt_num: '',
	        receipt_money: '',
	        receipt_other: '',
	        receipt_address: '',
	        receipt_mail_to_num: '',
			tuihuo_reason: null, 
			new_order_num: null, 
			new_client_info: null, 
			mobile: null, 
			person_id: null, 
			realname: null, 
			refundment_reason: null, 
			mail_to_address: null, 
			time_point: null, 
			earlier_send: null, 
			huanhuo_reason: null, 
			detail_reason: null, 
			mail_back_num: null, 
			mail_to_num: null, 
			mail_to_address: null, 
			profit: null, 
			profit_reason: null, 
			drawback: null, 
			drawback_zhifubao: null, 
			kf_state: null, 
			kf_state_name: null, 
			cg_state: null, 
			cg_state_name: null, 
			created: app_data.now, 
			drawback_reason: null, 
			drawback_state: null, 
			drawback_state_name: null, 
			refundment_state: null, 
			refundment_state_name: null
		}, 
		initialize_p: function(){
		}
	});
	
	var FormView = Backbone.View.extend({
		model: null, 
		events_p: {
			'submit form': 'save_block', 
			'click .add_send_back_product': 'add_send_back_product',
			'click .list_send_back_product .delete': 'delete_send_back_product', 
			'change select[name="send_back_category_id"]': 'change_send_back_category', 
			'click .list_send_back_product tbody .ruku': 'send_back_product_ruku', 
			'click .list_send_back_product tbody .ruku-cancel': 'send_back_product_ruku_cancel', 
			'click .add_send_to_product': 'add_send_to_product',
			'click .list_send_to_product .delete': 'delete_send_to_product', 
			'change select[name="send_to_category_id"]': 'change_send_to_category',
			'click .add_send_old_product': 'add_send_old_product',
			'click .list_send_old_product .delete': 'delete_send_old_product', 
			'change select[name="send_old_category_id"]': 'change_send_old_category', 
			'click .draft': 'save', 
			'click .save': 'save', 
			'click .publish': 'publish', 
			'click .to_dealing': 'to_dealing', 
			'click .to_done': 'to_done', 
			'click .to_close': 'to_close', 
			'click .btn-reopen-kf': 'reopenKF', 
			'click .btn-reopen-cg': 'reopenCG', 
			'click .reopen': 'reopen', 
			'click .drawback_controller .do-drawback': 'applyDrawback', 
			'click .drawback_controller .do-drawback-cancel': 'cancelDrawback', 
			'click .refundment_controller .do-refundment': 'applyRefundment', 
			'click .refundment_controller .do-refundment-cancel': 'cancelRefundment', 
			'input input[name="wanwan"]': 'checkWanwan'
			// 'change input[name="wanwan"]': 'checkWanwan'
		}, 
		initialize_p: function(opt){

			if( opt.model.id != undefined ){
				opt.model.fetch();
			}else{
				opt.model.trigger( 'change', opt.model );
			}
		}, 
		init_p: function(){

		}, 
		checkWanwan: function( e ){

			var val = e.target.value;
			var input = $(e.currentTarget)

			if( $.trim( val ) != '' ){
				$.get( '/mission/checkWanwan?wanwan='+ val+ '&id='+ this.model.id, function( data ){

					if( !_.isObject( data ) ) {
						alert( '系统错误: '+ data )
						return;
					}
					if( data.is_second ){
						if( input.next( 'span.is_second_span' ).length == 1 ){
							return;
						}
						input.after( '<span class="is_second_span" style="color: orange">该旺旺已有记录</span>' )
					}else{
						input.next( 'span.is_second_span' ).remove()
					}

				}, 'json' );
			}
			// return false;
		}, 
		reopenKF: function(){
			var view = this
			$.post( '/mission/reopen', {id: this.model.id, action: 'kf'}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误'+ data )
					return;
				}
				view.model.set( data );
			} )
		}, 
		reopenCG: function(){
			var view = this
			$.post( '/mission/reopen', {id: this.model.id, action: 'cg'}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误'+ data )
					return;
				}
				view.model.set( data );
			} )
		}, 
		send_back_product_ruku: function( e ){
			target = $( e.currentTarget ).parents( 'tr:first' )
			var param = {};
			var view = this
			param.id = $( 'input[data-name="id"]', target ).val()
			$.post( 'mission/ruku', param, function( data ){
				data.uid = data.id
				target.html( $(_.template($('#template-formview-add-product-back').html(), data )).html() )
			})
		}, 
		send_back_product_ruku_cancel: function( e ){
			target = $( e.currentTarget ).parents( 'tr:first' )
			var param = {};
			var view = this
			param.id = $( 'input[data-name="id"]', target ).val()
			$.post( 'mission/rukuCancel', param, function( data ){
				data.uid = data.id
				target.html( $(_.template($('#template-formview-add-product-back').html(), data )).html() )
			})
		}, 
		render: function(){

			var view = this;

			if( ( app_data.user.role_id == 3 || app_data.user.role_id == 4 ) || this.model.get( 'user_state' ) == 4 || this.model.get( 'state' ) == 2 || ( app_data.user.role_id == 1 && this.model.get( 'kf_uid' ) != app_data.user.id ) ){

				// 已关闭
				this.$('#forminner-view').html( this.template_close( _.extend( this.model.toJSON(), {} ) ));
				
			}else{

				this.$('#forminner-view').html(this.template( _.extend( this.model.toJSON(), {} ) ));

				Util.buildStore( this.$( 'select[name="store_id"]' ), this.model.get('store_id') )
				_.each( this.$( '.fold-controller' ), function( dom ){
					Util.buildFold( $(dom) )
				} )

				if( undefined != this.model.get('send_back_product_list') ){
					_.each( this.model.get('send_back_product_list'), function( one ){
						view.add_send_back_product_do( one.category_id, one.product_id, one.cnt, one.category, one.product, one.state, one.state_name, _.uniqueId(), one.id )
					} )
				}

				if( undefined != this.model.get('send_to_product_list') ){
					_.each( this.model.get('send_to_product_list'), function( one ){
						view.add_send_to_product_do( one.category_id, one.product_id, one.cnt, one.category, one.product, one.state, one.state_name, _.uniqueId(), one.id )
					} )
				}

				if( undefined != this.model.get('send_old_product_list') ){
					_.each( this.model.get('send_old_product_list'), function( one ){
						view.add_send_old_product_do( one.category_id, one.product_id, one.cnt, one.category, one.product, one.state, one.state_name, _.uniqueId(), one.id )
					} )
				}

				if(app_data.user.role_id == 1 && this.model.get('profit') > 0 ){
					this.$( '.fold-controller.profit .dounfold' ).click();
				}

				if( app_data.user.role_id == 1 && this.model.get('drawback') > 0 ){
					this.$( '.fold-controller.drawback .dounfold' ).click();
					this.resetDrawback()
				}

				this.resetRefundment()

			}

			if( parseInt( this.model.get( 'state' )) == 2 ){
				view.$( '.do-flag' ).show();
			}else{
				view.$( '.do-flag' ).hide();
			}

			var flag_list_tmp = this.model.get( 'flag_list' )
			flag_list = []
			_.each( flag_list_tmp, function(val){
				view.$( '.flag-list' ).addClass( 'flag-list-'+ val );
			} )
			
			if( null != mission.form_view.form_board ){
				mission.form_view.form_board.show();
			}

			if( null != mission.form_view.comment ){
				mission.form_view.comment.show();
			}
		},
		resetDrawback: function(){
			if( !this.model.get( 'drawback_state' ) > 0 || (app_data.user.role_id != 1) ){
				return;
			}

			this.$( '.drawback_controller' ).show();
			switch( parseInt( this.model.get( 'drawback_state' ) ) ){
				case 1 :
					this.$( '.drawback_controller .do-drawback' ).show();
					this.$( '.drawback_controller .do-drawback-cancel' ).hide();
					break;
				case 2 :
					this.$( '.drawback_controller .do-drawback' ).hide();
					this.$( '.drawback_controller .do-drawback-cancel' ).show();
					break;
				case 3 :
				default:
					this.$( '.drawback_controller .do-drawback' ).hide();
					this.$( '.drawback_controller .do-drawback-cancel' ).hide();
					break;
			}
		}, 
		resetRefundment: function(){
			if( !this.model.id > 0 ){
				return;
			}

			this.$( '.refundment_controller' ).show();
			switch( parseInt( this.model.get( 'refundment_state' ) ) ){
				case 1 :
					this.$( '.refundment_controller .do-refundment' ).show();
					this.$( '.refundment_controller .do-refundment-cancel' ).hide();
					break;
				case 2 :
					this.$( '.refundment_controller .do-refundment' ).hide();
					this.$( '.refundment_controller .do-refundment-cancel' ).show();
					break;
				case 3 :
				default:
					this.$( '.refundment_controller .do-refundment' ).hide();
					this.$( '.refundment_controller .do-refundment-cancel' ).hide();
					break;
			}
		}, 
		applyDrawback: function( e ){
			var view = this;
			$.post( 'drawback/apply', {id: this.model.id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误' + data );
					return;
				}
				view.model.set( data )
				view.resetDrawback()
			} );
			return false;
		}, 
		cancelDrawback: function(){
			var view = this;
			$.post( 'drawback/cancel', {id: this.model.id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误' + data );
					return;
				}
				view.model.set( data )
				view.resetDrawback()
			} );
			return false;
		}, 
		applyRefundment: function( e ){
			var view = this;
			$.post( 'refundment/apply', {id: this.model.id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误' + data );
					return;
				}
				view.model.set( data )
				view.resetRefundment()
			} );
			return false;
		}, 
		cancelRefundment: function(){
			var view = this;
			$.post( 'refundment/cancel', {id: this.model.id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误' + data );
					return;
				}
				view.model.set( data )
				view.resetRefundment()
			} );
			return false;
		}, 
		clearProdocut: function(){
			var view = this;
			_.each( view.model.toJSON(), function(val, key){ 
				if( /send_product_back\[/.test(key) ) {
					view.model.unset( key )
				}
			} );
			_.each( view.model.toJSON(), function(val, key){ 
				if( /send_product_to\[/.test(key) ) {
					view.model.unset( key )
				}
			} );
			_.each( view.model.toJSON(), function(val, key){ 
				if( /send_product_old\[/.test(key) ) {
					view.model.unset( key )
				}
			} );
		}, 
		change_send_back_category: function( event ){
			var id = event.currentTarget.value	;
			this.$( 'select[name="send_back_product_id"]' ).empty();
			_.each( product_list[id], function(val, key){
				this.$( 'select[name="send_back_product_id"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			} )
		}, 
		add_send_back_product: function(event){
			this.add_send_back_product_do( 
				$('select[name="send_back_category_id"]').val(), 
				$('select[name="send_back_product_id"]').val(), 
				$('input[name="send_back_cnt"]').val().trim(), 
				$('select[name="send_back_category_id"] option:selected').text().trim(), 
				$('select[name="send_back_product_id"] option:selected').text().trim(), 
				1, 
				'新建', 
				_.uniqueId(), 
				0
			);
			return false;
		}, 
		add_send_back_product_do: function( category_id, product_id, cnt, category, product, state, state_name, uid, id ){
			this.$( 'table.list_send_back_product tbody' ).append( _.template($('#template-formview-add-product-back').html(), {
				category_id: category_id, 
				product_id: product_id, 
				cnt: cnt, 
				category: category, 
				product: product, 
				state: state, 
				state_name: state_name, 
				uid: uid, 
				id: id
			} ) );
		}, 
		delete_send_back_product: function( event ){
			var view = this;
			$( event.currentTarget ).parents('tr').find( 'input[type="hidden"]' ).each(function(){
				view.model.unset( $(this).attr('name') );
			});
			$( event.currentTarget ).parents('tr').remove();
			return false;
		}, 
		change_send_to_category: function( event ){
			var id = event.currentTarget.value	;
			this.$( 'select[name="send_to_product_id"]' ).empty();
			_.each( product_list[id], function(val, key){
				this.$( 'select[name="send_to_product_id"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			} )
		}, 
		add_send_to_product: function(event){
			this.add_send_to_product_do(
				$('select[name="send_to_category_id"]').val(), 
				$('select[name="send_to_product_id"]').val(), 
				$('input[name="send_to_cnt"]').val().trim(), 
				$('select[name="send_to_category_id"] option:selected').text().trim(), 
				$('select[name="send_to_product_id"] option:selected').text().trim(), 
				1, 
				'新建', 
				_.uniqueId(), 
				0
			);
			return false;
		}, 
		add_send_to_product_do: function( category_id, product_id, cnt, category, product, state, state_name, uid, id ){
			this.$( 'table.list_send_to_product tbody' ).append( _.template($('#template-formview-add-product-to').html(), {
				category_id: category_id, 
				product_id: product_id, 
				cnt: cnt, 
				category: category, 
				product: product, 
				state: state, 
				state_name: state_name, 
				uid: uid, 
				id: id
			} ) );
		}, 
		delete_send_to_product: function( event ){
			var view = this;
			$( event.currentTarget ).parents('tr').find( 'input[type="hidden"]' ).each(function(){
				view.model.unset( $(this).attr('name') );
			});
			$( event.currentTarget ).parents('tr').remove();
			return false;
		}, 
		change_send_old_category: function( event ){
			var id = event.currentTarget.value	;
			this.$( 'select[name="send_old_product_id"]' ).empty();
			_.each( product_list[id], function(val, key){
				this.$( 'select[name="send_old_product_id"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			} )
		}, 
		add_send_old_product: function(event){
			this.add_send_old_product_do(
				$('select[name="send_old_category_id"]').val(), 
				$('select[name="send_old_product_id"]').val(), 
				$('input[name="send_old_cnt"]').val().trim(), 
				$('select[name="send_old_category_id"] option:selected').text().trim(), 
				$('select[name="send_old_product_id"] option:selected').text().trim(), 
				1, 
				'新建', 
				_.uniqueId(), 
				0
			);
			return false;
		}, 
		add_send_old_product_do: function( category_id, product_id, cnt, category, product, state, state_name, uid, id ){
			this.$( 'table.list_send_old_product tbody' ).append( _.template($('#template-formview-add-product-old').html(), {
				category_id: category_id, 
				product_id: product_id, 
				cnt: cnt, 
				category: category, 
				product: product, 
				state: state, 
				state_name: state_name, 
				uid: uid, 
				id: id
			} ) );
		}, 
		delete_send_old_product: function( event ){
			var view = this;
			$( event.currentTarget ).parents('tr').find( 'input[type="hidden"]' ).each(function(){
				view.model.unset( $(this).attr('name') );
			});
			$( event.currentTarget ).parents('tr').remove();
			return false;
		}, 
		save_block: function(){
			return false;
		}, 
		publish: function(){
			this.save( 'publish' )
		}, 
		to_dealing: function(){
			this.save( 'to_dealing' )
		}, 
		to_done: function(){
			this.save( 'to_done' )
		}, 
		to_close: function(){
			this.save( 'to_close' )
		}, 
		save: function( action ){

			if( !action )action = 'save';

			var view = this

			if( mission.form_view.issaving ){
				mission.form_toolbar_view.showMessage( '保存中，请稍候' );
				return ;
			}

			mission.form_toolbar_view.showMessage( '保存中，请稍候' );
			mission.form_view.issaving = true;

			var changed = {};
			
			if( this.model == undefined ){
				console.log( 'no model' )
				return;
			}
			
			var isnew = this.model.isNew()
			
			this.clearProdocut();
			changed = Util.formJson( this.$( 'form' ) );

			switch( action ){
				case 'to_dealing':
					changed.user_state = 2;
					changed.do_publish = 0;
					break;
				case 'to_done':
					changed.user_state = 3;
					changed.do_publish = 0;
					break;
				case 'publish':
					changed.user_state = 3;
					changed.do_publish = 1;
					break;
				case 'to_close': 
					changed.action = 'close';
					changed.do_publish = 0;
					break;
				default:
					changed.do_publish = 0;
					break;
			}

			var formview = this;
			this.model.save2(changed, {
				success: function(model, response){
					if( isnew ){
						mission.list_view.list_item_add( model );
					}
					mission.form_view.form_history = null;
					mission.form_toolbar_view.showSuccess()

					mission.form_view.issaving = false;

					view.model.trigger( 'change', view.model );
				}
			});
			return false;
		}
	});
	
	module.exports = {
		Model: Model, 
		View: FormView 
	}
});