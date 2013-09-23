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
		$( '#front-menu' ).css( 'height', board_height );
		$( '#front-list' ).css( 'height', board_height );
		$( '#front-form' ).css( 'height', board_height );
		// $( '#front-history' ).css( 'height', board_height );
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
			// this.showSuccess();
		}, 
		showSuccess: function( msg ){
			if( !msg ){
				msg = '保存成功'
			}
			this.$( '.layout-message, .layout-success' ).hide();
			this.$( '.layout-success' ).html( msg ).show();
			// this.$( '.layout-message-outter' ).show().fadeOut( 10000 );
			this.$( '.layout-message-inner' ).show().fadeOut( 4000 );
		}, 
		showMessage: function( msg ){
			if( !msg ){
				msg = '没设置消息'
			}
			this.$( '.layout-message, .layout-success' ).hide();
			this.$( '.layout-message' ).html( msg ).show();
			// this.$( '.layout-message-outter' ).show().fadeOut( 10000 );
			this.$( '.layout-message-inner' ).show().fadeOut( 4000 );
		}
	});

	var List = Backbone.View.extend({
		el: $( '#front-list' ), 
		events: {
			'click .comeback-create': 'create', 
			'click tbody tr': 'list_item_select', 
			'mouseover tbody tr': 'list_item_mouseover', 
			'mouseleave tbody tr': 'list_item_mouseleave', 
			'click tr .delete': 'list_item_delete', 
			'submit form': 'search'
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
		search: function(){
			var key = this.$( 'form input[name="mail_num"]' ).val()
			this.$( 'tbody' ).load( '/comeback/search?key='+ key, function(){
			} )
		}, 
		create: function(){
			form.showModel();
		}, 
		add: function(model){
			this.$( 'table tbody' ).prepend( _.template( $('#template-comeback-listitem').html(), model.toJSON() ) );
			this.listenTo( model, 'change', this.renderItem );
		}, 
		renderItem: function( model ){
			this.$( '#listitem-comeback-' + model.id ).html( $(_.template( $('#template-comeback-listitem').html(), model.toJSON() )).html() );
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
			
			$msg = '确定删除退货登记' + ': '+  $('.name', tr).html();
			if( !confirm( $msg ) ){
				return;
			}
				
			var model = new Comeback({id: id});
			
			model.destroy2({success: function(model, response) {
				alert( '删除成功' );
				list.removeItem(model);
			}});
			return false;
		}, 
		removeItem: function( model ){
			form.clear();
			this.$( '#listitem-comeback-'+ model.id ).remove();
		}
	});

	var Form = Backbone.View.extend({
		model: null, 
		el: $( '#front-form' ),
		events: {
			'submit form': 'save', 
			'click .add_comeback_product': 'add_comeback_product',
			'click .list_comeback_product .delete': 'delete_comeback_product', 
			'change select[name="comeback_category_id"]': 'change_comeback_category'
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
		change_comeback_category: function( event ){
			var id = event.currentTarget.value	;
			this.$( 'select[name="comeback_product_id"]' ).empty();
			_.each( product_list[id], function(val, key){
				this.$( 'select[name="comeback_product_id"]' ).append( '<option value="'+ val.id +'">'+ val.name +'</option>' );
			} )
		}, 
		add_comeback_product: function(event){
			this.add_comeback_product_do(
				$('select[name="comeback_category_id"]').val(), 
				$('select[name="comeback_product_id"]').val(), 
				$('input[name="comeback_product_cnt"]').val().trim(), 
				$('select[name="comeback_category_id"] option:selected').text().trim(), 
				$('select[name="comeback_product_id"] option:selected').text().trim(), 
				1, 
				'新建', 
				_.uniqueId(), 
				0
			);
			return false;
		}, 
		add_comeback_product_do: function( category_id, product_id, cnt, category, product, state, state_name, uid, id ){
			this.$( 'table.list_comeback_product tbody' ).append( _.template($('#template-formview-comeback-product').html(), {
				category_id: category_id, 
				product_id: product_id, 
				cnt: cnt, 
				category: category, 
				product: product, 
				state: state, 
				state_name: state_name, 
				uid: uid, 
				id: id
			} ) );
		}, 
		delete_comeback_product: function( event ){
			var view = this;
			$( event.currentTarget ).parents('tr').find( 'input[type="hidden"]' ).each(function(){
				view.model.unset( $(this).attr('name') );
			});
			$( event.currentTarget ).parents('tr').remove();
			return false;
		}, 
		showModel: function( id ){
			
			if( model == undefined || model.id != id ){
				model = new Comeback({id: id});
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
				html = _.template( $( '#comeback-template' ).html(), _.extend( model.toJSON(), {'title': '新建退货登记'} ) );
			}else{
				html = _.template( $( '#comeback-template' ).html(), _.extend( model.toJSON(), {'title': '编辑退货登记 - '+ model.id} ) );
			}
			this.model = model;

			this.$('#form-inner').html( html );

			var view = this
			if( undefined != this.model.get('comeback_product_list') ){
				_.each( this.model.get('comeback_product_list'), function( one ){
					view.add_comeback_product_do( one.category_id, one.product_id, one.cnt, one.category, one.product, one.state, one.state_name, _.uniqueId(), one.id )
				} )
			}


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
			layout.showSuccess();
			// this.$( '.success' ).show().fadeOut(2000);
		}, 
		clear: function(){
			this.$('#form-inner').empty();
		}
	});
	
	var Comeback = Backbone.Model.extend({
		defaults: {
		    "create_uid": app_data.admin_id,
		    "create_uname": app_data.admin_name,
		    "mail_company": '', 
		    "mail_num": '', 
		    "created": '', 
		    "deleted": '', 
		    "updated": '', 
		    "comment": '', 
		    "result": '', 
		    'comeback_product_list': null
		}, 
		urlRoot: '/comeback/comeback/id/'
	});
	
	var layout = new Layout();

	list = new List();
	form = new Form();
	var model = null;

	$('.comeback-create').click();


});
