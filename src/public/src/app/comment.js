define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Mission = require( '/src/app/mission' );
	var jQuery = $ = require( '$' );
	require( '/colorbox/v1.4.15/jquery.colorbox.js' )
	require( '/colorbox/v1.4.15/i18n/jquery.colorbox-zh-CN.js' )
	require( '/colorbox/v1.4.15/colorbox.css' )

	var CommissionListView = Backbone.View.extend({
		el: $( '#comment_list' ), 
		events: {
			'mouseenter ul li': 'mouseenter_item', 
			'mouseleave ul li': 'mouseleave_item', 
			'click ul li .delete': 'delte_item'
		}, 
		form_view: null, 
		initialize: function(opt){

			this.form_view = opt.form_view;
			this.$el = $( '#front-form #comment_list' );

			if( !this.form_view.form_model.id ){
				return
			}

			var view = this;
			this.$('ul').load( '/comment/index/id/' + this.form_view.form_model.id, function(data){
				view.$( '.content a' ).colorbox({rel: view.form_view.form_model.id, photo: true});
			} );
		}, 
		show: function(){
			this.$el.show();
		}, 
		add: function( model ){
			this.$( 'ul' ).append( _.template( this.$('#comment_list_item').html(), model ) )
			this.$( '.content a' ).colorbox({rel: this.form_view.form_model.id, photo: true});
			this.form_view.gotoLast();
		}, 
		mouseenter_item: function( event ){
		}, 
		mouseleave_item: function( event ){
		}, 
		delte_item: function( event ){
			var view = this;
			var id = $( event.currentTarget ).parents('li').attr( 'data-id' )
			$.post( '/comment/delete', { id: id }, function(data){
				$( event.currentTarget ).parents('li').remove();
			} );
			return false;
		}, 
		render: function(){

		}
	});

	module.exports = CommissionListView
});
