define(function(require, exports, modelue){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Util = require( '/src/app/util' );

	var Filter = Backbone.View.extend({
		el: $( '#front-list .tab-pane.active .mission-filter-form' ), 
		events: {
			'change select[name="category"]': 'changeCategory'
		}, 
		initialize: function(){
			this.$el = $( '#front-list .tab-pane.active .mission-filter-form' );
			this.changeCategory();
		}, 
 		defaults: {
			sort: 'id', 
			page: 1, 
			size: 20, 
			show_type: 1,
		}, 
		params: {

		}, 
		changeCategory: function(){
			this.$( 'select[name="sub_category"]' ).empty()
			this.$( 'select[name="sub_category"]' ).append( '<option value="0">全部</option>' )
			var view = this
			_.each( app_data.mission_type_list, function( val, key ){
				if( key == view.$( 'select[name="category"]' ).val() ){
					_.each( val.children, function( val1, key1 ){
						view.$( 'select[name="sub_category"]' ).append( "<option value='"+ val1.id+"'>"+ val1.name +"</option>" )
					} )
				}
			})
		}, 
		getParams: function(){
			var filter = this.$el.serialize();
			filter = (filter ? filter + '&' : '' ) + $.param(this.params)
			return filter;
		}
	});

	return Filter;
});