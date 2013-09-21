define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/admin/modal' )
	require( '/bootstrap/datepicker/js/bootstrap-datepicker' )
	require( '/bootstrap/datepicker/js/locales/bootstrap-datepicker.zh-CN' )
	require( '/bootstrap/datepicker/css/datepicker.css' )

	var Analyse = Backbone.View.extend({
		model: null, 
		el: $( '#analyse-wrapper' ),
		events: {
			'click .btn-time': 'chooseTime'
		}, 
		initialize: function(){

			this.initProduct();
			this.initMissionType();
			this.initDatePicker();

		}, 
		chooseTime: function( e ){
			this.$( '.btn-time' ).removeClass( 'active' );
			$( e.currentTarget ).addClass( 'active' );
			this.$( '.ipt_time' ).val( $( e.currentTarget ).attr( 'data' ) )
			this.$('.datepicker[name="from"]').val( '' )
			this.$('.datepicker[name="to"]').val( '' )
			this.$( 'form' ).submit();
		}, 
		initDatePicker: function(){

			var period = analyse_filter.time;

			if( period ){
				this.$( '.period button[data="'+ period +'"]' ).addClass( 'active' );
				this.$( '.ipt_time' ).val( period )
			}

			this.$('.datepicker[name="from"]').val( analyse_filter.from ).datepicker({
				language: 'zh-CN', 
				format: 'yyyy-mm-dd'
			})
			this.$('.datepicker[name="to"]').val( analyse_filter.to ).datepicker({
				language: 'zh-CN', 
				format: 'yyyy-mm-dd'
			})

		}, 
		initProduct: function(){

			var cate_id = analyse_filter.category;
			var product_id = analyse_filter.product;
			_.each( category_list, function( val, key ){
				this.$( 'select[name="category"]' ).append( '<option value="'+ val.id +'" '+ ( val.id == cate_id ? 'selected' : '' ) +'>'+ val.name +'</option>' );
			})

			_.each( product_list[cate_id], function( val, key ){
				this.$( 'select[name="product"]' ).append( '<option value="'+ val.id +'" '+ ( val.id == product_id ? 'selected' : '' ) +' >'+ val.name +'</option>' );
			})

			this.$( 'select[name="category"]' ).change(function(){
				var product_select = $(this).next('select[name="product"]');
				product_select.empty();
				product_select.append( '<option value="0">全部</option>' )
				_.each( product_list[this.value], function( val, key ){
					product_select.append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
				})
			});

		}, 
		initMissionType: function(){

			var mission_category_id = analyse_filter.mission_category;
			var mission_sub_category_id = analyse_filter.mission_sub_category;

			_.each( mission_type_list, function( val, key ){
				this.$( 'select[name="mission_category"]' ).append( '<option value="'+ val.data.id + 
					'" '+ ( val.data.id == mission_category_id ? 'selected': '' ) +'>'+ val.data.name +'</option>' );

				if( _.isObject( val.children ) && val.data.id == mission_category_id ){
					_.each( val.children, function( val, key ){
						this.$( 'select[name="mission_sub_category"]' ).append( '<option value="'+ val.id + 
							'" '+ ( val.id == mission_sub_category_id ? 'selected': '' ) +'>'+ val.name +'</option>' );
					})
				}
			})

			this.$( 'select[name="mission_category"]' ).change(function(){
				var list_select = $(this).next('select[name="mission_sub_category"]');
				list_select.empty();
				list_select.append( '<option value="0">全部</option>' )
				_.each( mission_type_list[this.value].children, function( val, key ){
					list_select.append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
				})
			});

		}
	});
	
	new Analyse();
});
