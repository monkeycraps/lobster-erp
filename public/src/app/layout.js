define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )
	var Mission = require( '/src/app/mission' );
	var mscroll = require( '/mcustomscrollbar/jquery.mCustomScrollbar.concat.min' );
	require( '/mcustomscrollbar/jquery.mCustomScrollbar.css' );

	function resize(){
		var wh = $(window).height()
		var board_height = (wh - $("header").height() ) + "px";
		$( '#front-menu' ).css( 'height', board_height );
		$( '#front-list' ).css( 'height', board_height );
		$( '#front-form' ).css( 'height', board_height );
	}

	var Layout = Backbone.View.extend({
		el: $( '#layout' ),
		menu: null,  
		board: null,  
		events: {

		}, 
		initialize: function(){
			window.onload = window.onresize = function(){
				resize();
			}
			resize();
		}
	});

	var Form = Backbone.View.extend({
		el: $( '#front-form' ), 
		events: {
		}, 
		initialize: function(){
		}, 
		add: function( cate_id, sub_cate_id ){
			this.$el.load( '/mission/add?cate='+ cate_id+'&subcate=' + sub_cate_id, null, function( data ){
				require.async( '/src/app/forminner/form-'+cate_id+'-'+ sub_cate_id, function(FormInner){
					var view = new FormInner.View()
					$( '#front-form' ).mCustomScrollbar({
						advanced:{
					        updateOnContentResize: true, 
					        autoScrollOnFocus: false
					    }, 
					    scrollInertia : 150
					});
				} );
			} );
		}, 
		load: function( id ){
			this.$el.load( '/mission/load?id=' + id, null, function( data ){
				var cate_id = this.$( '#cate_id' ).val();
				var sub_cate_id = this.$( '#sub_cate_id' ).val();
				require.async( '/src/app/forminner/form-'+cate_id+'-'+ sub_cate_id, function(FormInner){
					FormInner.model.set({id: id})
				} );
			} );
		}, 

	});

	var Menu = Backbone.View.extend({
		el: $( '#front-menu' ), 
		events: {
			'click .mission-add': 'mission_add', 
			'change .select-cate': 'change_cate', 
			'click .btn-mission-add': 'do_mission_add', 
			'click .btn-mission-add-cancel': 'do_mission_add_cancel', 
		}, 
		initialize: function(){
			var mission_type = app_data.user.role.mission_type;
			$( '.select-cate', this.$el ).empty()
			for( key in mission_type ){
				$( '.select-cate', this.$el ).append( '<option value="'+ key +'">'+ mission_type[key].data.name +'</option>' )
			}
		}, 
		mission_add: function(){
			$( '.select-cate', this.$el ).change();
			$( '.mission-add-popover', this.$el ).show();
		},
		do_mission_add: function(){
			$( '.mission-add-popover', this.$el ).hide();
			var cate_id = $( '.select-cate', this.$el ).val()
			var sub_cate_id = $( '.select-subcate', this.$el ).val()
			list.add( cate_id, sub_cate_id );
		}, 
		do_mission_add_cancel: function(){
			$( '.mission-add-popover', this.$el ).hide();
		}, 
		change_cate: function( event ){
			var mission_type = app_data.user.role.mission_type;
			var mtid = $( event.target ).val()
			if( typeof mission_type[mtid] == 'undefined' || typeof mission_type[mtid].children == 'undefined' ){
				$( '.select-subcate', this.$el ).hide()
				$( '.label-subcate', this.$el ).hide()
				return;
			}

			var children = mission_type[mtid].children
			$( '.select-subcate' ).empty()
			for( key in children ){
				$( '.select-subcate', this.$el ).show();
				$( '.label-subcate', this.$el ).show();
				$( '.select-subcate', this.$el ).append( '<option value="'+ key +'">'+ children[key].name +'</option>' )
			}
		}
	});
	
	var ListView = Backbone.View.extend({
		el: $('#front-list'), 
		initialize: function(){
			console.log( this.$() )
			this.$el.mCustomScrollbar({
				advanced:{
			        updateOnContentResize: true, 
			        autoScrollOnFocus: false
			    }, 
			    scrollInertia : 150
			});
		}
	});
	
	var List = Backbone.Collection.extend({
		add: function( cate_id, sub_cate_id ){
			form.add( cate_id, sub_cate_id);
		}, 
		addItem: function( model ){
			console.log( model )
		}, 
		renderItem: function( model ){
			console.log( 'list render item' )
		}
	});

	var menu = new Menu();
	var form = new Form();
	var layout = new Layout();
	var list = new List();
	var list_view = new ListView();

	var FormToolbar = Backbone.View.extend({
		el: $( '#form-tool-bar' ), 
		events: {
			'click .mission-type-change': 'change_mission_show'
		}, 
		change_mission_show: function(){
			Mission.mission_change_modal.show();
		}
	});
	
	module.exports = {
		layout: layout, 
		menu: menu, 
		list: list, 
		list_view: list_view,  
		form: form, 
		form_toolbar: new FormToolbar() 
	};
	
	list.add(2,12)
	
});