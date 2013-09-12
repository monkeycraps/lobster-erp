define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/admin/modal' )
	
	var Analyse = Backbone.View.extend({
		model: null, 
		el: $( '#analyse-wrapper' ),
		events: {
			// 'submit form': 'save'
		}, 
		initialize: function(){

			this.initProduct();
			this.initMissionType();
			this.initDatePicker();

		}, 
		initDatePicker: function(){

			var period = 'today';

			if( period ){
				this.$( '.period .'+ period ).addClass( 'active' );
			}

			this.$('.datepicker[name="from"]').datepicker()
			this.$('.datepicker[name="to"]').datepicker()

		}, 
		initProduct: function(){

			var cate_id = 0;
			var product_id = 0;
			_.each( category_list, function( val, key ){
				if( !cate_id )cate_id = key;
				this.$( 'select[name="category"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			})

			_.each( product_list[cate_id], function( val, key ){
				if( !product_id )product_id = key;
				this.$( 'select[name="product"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			})

			this.$( 'select[name="category"]' ).change(function(){
				var product_select = $(this).next('select[name="product"]');
				product_select.empty();
				_.each( product_list[this.value], function( val, key ){
					product_select.append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
				})
			});

		}, 
		initMissionType: function(){

			var mission_category_id = 0;
			var mission_sub_category_id = 0;

			_.each( mission_type_list, function( val, key ){
				if( !mission_category_id )mission_category_id = key;
				this.$( 'select[name="mission_category"]' ).append( '<option value="'+ val.data.id + 
					'" '+ ( val.data.id == mission_category_id ? 'selected': '' ) +'>'+ val.data.name +'</option>' );

				if( _.isObject( val.children ) && val.data.id == mission_category_id ){
					if( !mission_sub_category_id )mission_sub_category_id = key;

					_.each( val.children, function( val, key ){
						this.$( 'select[name="mission_sub_category"]' ).append( '<option value="'+ val.id + 
							'" '+ ( val.id == mission_sub_category_id ? 'selected': '' ) +'>'+ val.name +'</option>' );
					})
				}
			})

			this.$( 'select[name="mission_category"]' ).change(function(){
				var list_select = $(this).next('select[name="mission_sub_category"]');
				list_select.empty();
				_.each( mission_type_list[this.value].children, function( val, key ){
					list_select.append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
				})
			});



		}
	});
	
	new Analyse();
});
