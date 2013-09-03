define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Util = require( '/src/app/util' );

	var Layout = require( '/src/app/layout' );
	var list = Layout.list;
	
	var Form = Backbone.Model.extend({
		urlRoot: '/mission/mission?id=', 
		defaults: {
			mission_type_id: null, 
			store: null, 
			wanwan: null, 
			order_num: null, 
			time_point: null, 
			earlier_send: null, 
			huanhuo_reason: null, 
			detail_reason: null, 
			mail_back_num: null, 
			mail_back_product_list: null, 
			mail_to_num: null, 
			mail_to_address: null, 
			mail_to_product_list: null, 
			profit: null, 
			profit_reason: null, 
			drawback: null, 
			drawback_zhifubao: null, 
			drawback_reason: null
		}, 
		initialize: function(){
			
		}
	});
	
	var FormView = Backbone.View.extend({
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
			'click .publish': 'publish'
		}, 
		initialize: function(){
			this.model = new Form({mission_type_id: 12});

			this.$('#forminner-view').html(this.template( _.extend( this.model.toJSON(), {} ) ));

			Util.buildStore( this.$( 'select[name="store"]', this.model.store_id ) )
			_.each( this.$( '.fold-controller' ), function( dom ){
				Util.buildFold( $(dom) )
			} )
		}, 
		change_send_back_category: function( event ){
			var id = event.currentTarget.value	;
			this.$( 'select[name="send_back_product_id"]' ).empty();
			_.each( product_list[id], function(val, key){
				this.$( 'select[name="send_back_product_id"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			} )
		}, 
		add_send_back_product: function(event){
			this.$( 'table.list_send_back_product tbody' ).append( _.template($('#template-formview-2-12-add-product-back').html(), {
				category_id: $('select[name="send_back_category_id"]').val(), 
				product_id: $('select[name="send_back_product_id"]').val(), 
				cnt: $('input[name="send_back_cnt"]').val().trim(), 
				category: $('select[name="send_back_category_id"]').text().trim(), 
				product: $('select[name="send_back_product_id"]').text().trim(), 
				uid: _.uniqueId()
			} ) );
			return false;
		}, 
		delete_send_back_product: function( event ){
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
			this.$( 'table.list_send_to_product tbody' ).append( _.template($('#template-formview-2-12-add-product-out').html(), {
				category_id: $('select[name="send_to_category_id"]').val(), 
				product_id: $('select[name="send_to_product_id"]').val(), 
				cnt: $('input[name="send_to_cnt"]').val().trim(), 
				category: $('select[name="send_to_category_id"]').text().trim(), 
				product: $('select[name="send_to_product_id"]').text().trim(), 
				uid: _.uniqueId()
			} ) );
			return false;
		}, 
		delete_send_to_product: function( event ){
			$( event.currentTarget ).parents('tr').remove();
			return false;
		}, 
		save_block: function(){
			return false;
		}, 
		save: function(){
			var changed = {};
			
			if( this.model == undefined ){
				console.log( 'no model' )
				return;
			}
			
			var isnew = this.model.isNew()
			
			changed = Util.formJson( this.$( 'form' ) );
			console.log( changed );

			var formview = this;
			this.model.save2(changed, {
				success: function(model, response){
					if( isnew ){
						// list.add( model );
					}
					formview.showSuccess();
				}
			});
		}, 
		publish: function(){
			try{
				var changed = {};
				
				if( this.model == undefined ){
					console.log( 'no model' )
					return;
				}
				
				var isnew = this.model.isNew()
				
				this.$( '#form-inner input, #form-inner select' ).each(function(itr, one){
					one = $(one)
					changed[one.attr( 'name' )] = one.val();
				});

				var formview = this;
				this.model.save2(changed, {
					success: function(model, response){
						if( isnew ){
							// list.add( model );
						}
						formview.showSuccess();
					}
				});
			}catch(ex){
				console.log(ex.name)
				console.log(ex.message)
				console.log(ex.description)
				console.log(ex.number)
			}
			return false;
		}, 
		showSuccess: function(){
			Util.showFormSuccess( '保存成功' );
		}
	});
	
	module.exports = {
		Model: Form, 
		View: FormView 
	}
});