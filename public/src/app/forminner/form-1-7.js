define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );

	var Layout = require( '/src/app/layout' );
	var list = Layout.list;
	
	var Form = Backbone.Model.extend({
		urlRoot: '/mission/mission?id=', 
		defaults: {
			id: null, 
			store: null, 
			wanwan: null, 
			order_num: null, 
			category_id: null, 
			product_id: null, 
			address: null, 
			comment: null, 
		}, 
		initialize: function(){
			
		}
	});
	
	var FormView = Backbone.View.extend({
		el: '#forminner-mission', 
		template: _.template($('#template-formview-1-7').html()),
		events: {
			'submit form': 'save', 
			'click .add-product': 'add_product',
			'click .list-product .delete': 'delete_product', 
			'change select[name="send_category_id"]': 'change_category'
		}, 
		initialize: function(){
			this.model = new Form({mission_type_id: 7});
			this.$('#forminner-view').html(this.template( _.extend( this.model.toJSON(), {} ) ));
		}, 
		change_category: function( event ){
			var id = event.currentTarget.value	;
			this.$( 'select[name="send_product_id"]' ).empty();
			_.each( product_list[id], function(val, key){
				this.$( 'select[name="send_product_id"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			} )
		}, 
		add_product: function(event){
			this.$( 'table.list-product tbody' ).append( _.template($('#template-formview-1-17-add-product').html(), {
				category_id: $('select[name="send_category_id"]').val(), 
				product_id: $('select[name="send_product_id"]').val(), 
				cnt: $('select[name="send_cnt"]').val(), 
				category: $('select[name="send_category_id"]').text().trim(), 
				product: $('select[name="send_product_id"]').text().trim(), 
				cnt: $('input[name="send_cnt"]').val().trim(), 
				uid: _.uniqueId()
			} ) );
			return false;
		}, 
		delete_product: function( event ){
			$( event.currentTarget ).parents('tr').remove();
		}, 
		save: function(){
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
			this.$( '.success' ).show().fadeOut(2000);
		}
	});
	
	module.exports = {
		Model: Form, 
		View: FormView 
	}
});