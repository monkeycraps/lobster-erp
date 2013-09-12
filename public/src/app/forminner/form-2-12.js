define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Util = require( '/src/app/util' );

	var Layout = require( '/src/app/layout' );
	var list = Layout.list;
	
	var Model = Backbone.Model.extend({
		urlRoot: '/mission/mission/id/', 
		defaults: {
			mission_type_id: 12, 
			user_state: 0,  
			store_id: null, 
			store: null, 
			wanwan: null, 
			order_num_list: null, 
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
			drawback_reason: null
		}, 
		initialize: function(){
		}
	});
	
	var FormView = Backbone.View.extend({
		model: null, 
		el: '#forminner-mission', 
		template: _.template($('#template-formview-2-12').html()),
		events: {
			'submit form': 'save_block', 
			'click .add_send_back_product': 'add_send_back_product',
			'click .list_send_back_product .delete': 'delete_send_back_product', 
			'change select[name="send_back_category_id"]': 'change_send_back_category', 
			'click .add_send_to_product': 'add_send_to_product',
			'click .list_send_to_product .delete': 'delete_send_to_product', 
			'change select[name="send_to_category_id"]': 'change_send_to_category', 
			'click .draft': 'save', 
			'click .save': 'save', 
			'click .publish': 'publish', 
			'click .to_dealing': 'to_dealing', 
			'click .to_done': 'to_done', 
			'click .to_close': 'to_close', 
			'click .reopen': 'reopen'
		}, 
		initialize: function(opt){

			this.listenTo( opt.model, 'change', this.render )

			if( opt.model.id != undefined ){
				opt.model.fetch();
			}else{
				opt.model.trigger( 'change', opt.model );
			}
		}, 
		render: function(){

			var view = this;

			if( this.model.get( 'state' ) == 2 ){

				// 已关闭
				this.$('#forminner-view').html( _.template( $( '#template-formview-2-12-closed' ).html(), _.extend( this.model.toJSON(), {} ) ));				
				
			}else{

				this.$('#forminner-view').html(this.template( _.extend( this.model.toJSON(), {} ) ));

				Util.buildStore( this.$( 'select[name="store_id"]' ), this.model.get('store_id') )
				_.each( this.$( '.fold-controller' ), function( dom ){
					Util.buildFold( $(dom) )
				} )

				if( undefined != this.model.get('send_back_product_list') ){
					_.each( this.model.get('send_back_product_list'), function( one ){
						view.add_send_back_product_do( one.category_id, one.product_id, one.cnt, one.category, one.product, one.state, one.id )
					} )
				}

				if( undefined != this.model.get('send_to_product_list') ){
					_.each( this.model.get('send_to_product_list'), function( one ){
						view.add_send_to_product_do( one.category_id, one.product_id, one.cnt, one.category, one.product, one.state, one.id )
					} )
				}

				if( this.model.get('profit') > 0 ){
					this.$( '.fold-controller.profit .dounfold' ).click();
				}

				if( this.model.get('drawback') > 0 ){
					this.$( '.fold-controller.drawback .dounfold' ).click();
				}

			}

			if( null != Layout.form_view.form_board ){
				Layout.form_view.form_board.show();
			}

			if( null != Layout.form_view.comment ){
				Layout.form_view.comment.show();
			}
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
				_.uniqueId()
			);
			return false;
		}, 
		add_send_back_product_do: function( category_id, product_id, cnt, category, product, state, uid ){
			this.$( 'table.list_send_back_product tbody' ).append( _.template($('#template-formview-2-12-add-product-back').html(), {
				category_id: category_id, 
				product_id: product_id, 
				cnt: cnt, 
				category: category, 
				product: product, 
				state: state, 
				uid: uid
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
				_.uniqueId()
			);
			return false;
		}, 
		add_send_to_product_do: function( category_id, product_id, cnt, category, product, state, uid ){
			console.log( arguments )
			this.$( 'table.list_send_to_product tbody' ).append( _.template($('#template-formview-2-12-add-product-to').html(), {
				category_id: category_id, 
				product_id: product_id, 
				cnt: cnt, 
				category: category, 
				product: product, 
				state: state, 
				uid: uid
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

			if( Layout.form_view.issaving ){
				Layout.form_toolbar_view.showMessage( '保存中，请稍候' );
				return ;
			}

			Layout.form_toolbar_view.showMessage( '保存中，请稍候' );
			Layout.form_view.issaving = true;

			var changed = {};
			
			if( this.model == undefined ){
				console.log( 'no model' )
				return;
			}
			
			var isnew = this.model.isNew()
			
			this.clearProdocut();
			changed = Util.formJson( this.$( 'form' ) );
			console.log( changed );

			switch( action ){
				case 'save':
					break;
				case 'to_dealing':
					changed.user_state = 2;
					break;
				case 'to_done':
					changed.user_state = 3;
					break;
				case 'publish':
					changed.user_state = 3;
					changed.do_publish = 1;
					break;
				case 'to_close': 
					changed.action = 'close';
					break;
			}

			var formview = this;
			this.model.save2(changed, {
				success: function(model, response){
					if( isnew ){
						Layout.list_view.list_item_add( model );
					}
					Layout.form_view.form_history = null;
					Layout.form_toolbar_view.showSuccess()

					Layout.form_view.issaving = false;

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