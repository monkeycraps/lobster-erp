define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Util = require( '/src/app/util' );

	var mscroll = require( '/mcustomscrollbar/jquery.mCustomScrollbar.concat.min' );
	require( '/mcustomscrollbar/jquery.mCustomScrollbar.css' );

	var $ = require( '$' );
	require( '/bootstrap/bootstrap-wysihtml5-0.0.2/js/wysihtml5-0.3.0_rc2' );
	require( '/bootstrap/bootstrap-wysihtml5-0.0.2/bootstrap-wysihtml5-0.0.2' );
	require( '/bootstrap/bootstrap-wysihtml5-0.0.2/bootstrap-wysihtml5-0.0.2.css' );

	var SWFUpload = require( 'gallery2/swfupload/2.2.0/swfupload-debug' );
	var SWFUploadHandlerWrapper = require( 'gallery2/swfupload/2.2.0/handler' );

	function resize(){
		var wh = $(window).height()
		var board_height = (wh - $("header").height() ) + "px";
		$( '#index-list' ).css( 'height', board_height );
		$( '#index-content' ).css( 'height', board_height );
		$( '#index-form' ).css( 'height', board_height );
	}

	var IndexLayout = Backbone.View.extend({
		el: $('#index'), 
		initialize: function(){
			window.onload = window.onresize = function(){
				resize();
			}
			resize();
		}
	});

	var List = Backbone.View.extend({
		el: $( '#index-list' ), 
		events: {
			'click .announce-create': 'create', 
			'click tbody tr': 'list_item_select', 
			'mouseover tbody tr': 'list_item_mouseover', 
			'mouseleave tbody tr': 'list_item_mouseleave', 
			'click tr .delete': 'list_item_delete'
		}, 
		initialize: function(){

			this.$el.mCustomScrollbar({
				advanced:{
			        updateOnContentResize: true, 
			        autoScrollOnFocus: false
			    }, 
			    scrollInertia : 150
			});

		}, 
		create: function(){
			form.showModel();
		}, 
		add: function(model){
			this.$( 'table tbody' ).append( _.template( $('#template-announce-listitem').html(), model.toJSON() ) );
			this.listenTo( model, 'change', this.renderItem );
		}, 
		renderItem: function( model ){
			this.$( '#listitem-announce-' + model.id ).html( $(_.template( $('#template-announce-listitem').html(), model.toJSON() )).html() );
		},
		list_item_mouseover: function( event ){
		}, 
		list_item_mouseleave: function( event ){
		}, 
		list_item_select: function( event ){
			this.$('table .active').removeClass( 'active' );
			this.$( event.currentTarget ).addClass( 'active' );
			var arr = new RegExp( "listitem-([a-z]+)-([0-9]+)", 'ig' ).exec( event.currentTarget.id )
			var type = arr[1]
			var id = arr[2]
			display.show( type, id );
		}, 
		removeItem: function( model ){
			form.clear();
			this.$( '#listitem-announce-'+ model.id ).remove();
		}
	});

	var Display = Backbone.View.extend({
		el: $( '#index-content' ), 
		initialize: function(){

		}, 
		show: function( type, id ){
			this.$('.content').load( '/index/show?type='+type+ '&id='+ id );
		}
	})
	
	list = new List();
	var model = null;

	var index = new IndexLayout();
	var display = new Display();


	$( '#index-list tbody tr:first' ).click();

});
