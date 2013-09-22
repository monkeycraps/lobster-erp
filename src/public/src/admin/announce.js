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

	var List = Backbone.View.extend({
		el: $( '#admin-list' ), 
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
			this.$( 'table tbody' ).prepend( _.template( $('#template-announce-listitem').html(), model.toJSON() ) );
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
			var id = arr[2]
			model = form.showModel( id );
			this.listenTo( model, 'change', this.renderItem );
		}, 
		list_item_delete: function( event ){
			var tr = $(event.currentTarget).parents('tr')
			var arr = new RegExp( "listitem-([a-z]+)-([0-9]+)", 'ig' ).exec( tr.attr('id') )
			var id = arr[2]
			
			$msg = '确定删除公告' + ': '+  $('.name', tr).html();
			if( !confirm( $msg ) ){
				return;
			}
				
			var model = new Announce({id: id});
			
			model.destroy2({success: function(model, response) {
				alert( '删除成功' );
				list.removeItem(model);
			}});
			return false;
		}, 
		removeItem: function( model ){
			form.clear();
			this.$( '#listitem-announce-'+ model.id ).remove();
		}
	});

	var Form = Backbone.View.extend({
		model: null, 
		el: $( '#admin-form' ),
		events: {
			'submit form': 'save'
		}, 
		initialize: function(){
			
		}, 
		showModel: function( id ){
			
			if( model == undefined || model.id != id ){
				model = new Announce({id: id});
			}
			this.listenTo( model, 'change', this.render );
			
			if( id != undefined ){
				model.fetch();	
			}else{
				model.trigger( 'change', model );
			}
			
			return model;
		}, 
		render: function(model){
			
			var html;
			if( model.isNew() ){
				html = _.template( $( '#announce-template' ).html(), _.extend( model.toJSON(), {'title': '新建公告'} ) );
			}else{
				html = _.template( $( '#announce-template' ).html(), _.extend( model.toJSON(), {'title': '编辑公告 - '+ model.get('subject')} ) );
			}
			this.model = model;
			this.$('#form-inner').html( html );

			$('.textarea').wysihtml5();

		}, 
		save: function( event ){

			console.log( this.$( 'form textarea[name="content"]' ).val() )

			try{
				var changed = {};
				
				if( this.model == undefined ){
					return;
				}
				
				var isnew = this.model.isNew()

				changed = Util.formJson( this.$( 'form' ) );
				// changed.content = this.$( 'form textarea[name="content"]' ).val()
				// console.log( changed.content )
				// console.log( $( 'form textarea[name="content"]' ).val() )

				this.model.save2(changed, {
					success: function(model, response){
						if( isnew ){
							list.add( model );
						}
						form.showSuccess();
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
		}, 
		clear: function(){
			this.$('#form-inner').empty();
		}
	});
	
	var Announce = Backbone.Model.extend({
		defaults: {
		    "subject":  "",
		    "create_uid": app_data.admin_id,
		    "create_uname": app_data.admin_name,
		    "subject": '', 
		    "created": '', 
		    "deleted": '', 
		    "updated": '', 
		    "content": ''
		}, 
		urlRoot: '/admin/announce/announce/id/'
	});
	
	list = new List();
	form = new Form();
	var model = null;

	$('.announce-create').click();

});
