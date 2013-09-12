define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/admin/modal' )
	
	var List = Backbone.View.extend({
		el: $( '#admin-list' ), 
		events: {
			'click .user-create': 'create', 
			'click tbody tr': 'list_item_select', 
			'mouseover tbody tr': 'list_item_mouseover', 
			'mouseleave tbody tr': 'list_item_mouseleave', 
			'click tr .delete': 'list_item_delete', 
			'click tr .restore': 'list_item_restore', 
			'click tr .ban': 'list_item_ban'
		}, 
		initialize: function(){
		}, 
		create: function(){
			form.showModel();
		}, 
		add: function(model){
			this.$( 'table tbody' ).append( _.template( $('#template-user-listitem').html(), model.toJSON() ) );
			this.listenTo( model, 'change', this.renderItem );
		}, 
		renderItem: function( model ){
			this.$( '#listitem-user-' + model.id ).html( $(_.template( $('#template-user-listitem').html(), model.toJSON() )).html() );
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
			
			$msg = '确定删除用户' + ': '+  $('.name', tr).html();
			if( !confirm( $msg ) ){
				return;
			}
				
			var model = new User({id: id});
			
			model.destroy2({success: function(model, response) {
				alert( '删除成功' );
				list.removeItem(model);
			}});
			return false;
		}, 
		removeItem: function( model ){
			form.clear();
			this.$( '#listitem-user-'+ model.id ).remove();
		}, 
		list_item_ban: function( event ){
			var tr = $(event.currentTarget).parents('tr')
			var arr = new RegExp( "listitem-([a-z]+)-([0-9]+)", 'ig' ).exec( tr.attr('id') )
			var id = arr[2]
			
			$msg = '确定禁止用户' + ': '+  $('.name', tr).html();
			if( !confirm( $msg ) ){
				return;
			}
				
			var model = new User({id: id});
			this.listenTo( model, 'change', this.renderItem );
			
			$.post( model.url(), _.extend( model.toJSON(), {action:'ban'} ), function(data){
				alert( '禁止成功' );
				model.set(data);
				tr.addClass( 'baned' )
			});
			return false;
		}, 
		list_item_restore: function( event ){
			var tr = $(event.currentTarget).parents('tr')
			var arr = new RegExp( "listitem-([a-z]+)-([0-9]+)", 'ig' ).exec( tr.attr('id') )
			var id = arr[2]
			
			$msg = '确定恢复用户' + ': '+  $('.name', tr).html();
			if( !confirm( $msg ) ){
				return;
			}
				
			var model = new User({id: id});
			this.listenTo( model, 'change', this.renderItem );
			
			$.post( model.url(), _.extend( model.toJSON(), {action:'restore'} ), function(data){
				alert( '恢复成功' );
				model.set(data);
				tr.removeClass( 'baned' )
			});
			return false;
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
			
			if( user == undefined || user.id != id ){
				user = new User({id: id});
			}
			this.listenTo( user, 'change', this.render );
			
			if( id != undefined ){
				user.fetch();
			}else{
				user.trigger( 'change', user );
			}
			
			return user;
		}, 
		render: function(model){
			
			var html;
			if( model.isNew() ){
				html = _.template( $( '#user-template' ).html(), _.extend( model.toJSON(), {'title': '新建用户'} ) );
			}else{
				html = _.template( $( '#user-template' ).html(), _.extend( model.toJSON(), {'title': '编辑用户 - '+ model.get('username')} ) );
			}
			this.model = model;
			this.$('#form-inner').html( html );
		}, 
		save: function( event ){
			try{
				var changed = {};
				
				if( this.model == undefined ){
					return;
				}
				
				var isnew = this.model.isNew()
				
				this.$( '#form-inner input, #form-inner select' ).each(function(itr, one){
					one = $(one)
					changed[one.attr( 'name' )] = one.val();
				});
				
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
			event.stopPropagation()
		}, 
		showSuccess: function(){
			this.$( '.success' ).show().fadeOut(2000);
		}, 
		clear: function(){
			this.$('#form-inner').empty();
		}
	});
	
	var User = Backbone.Model.extend({
		defaults: {
		    "username":  "",
		    "role_id":     1,
		    "role_name":  '客服',
		    "name":    ''
		}, 
		urlRoot: '/admin/user/user/id/'
	});
	
	list = new List();
	form = new Form();
	var user;
	$('.user-create').click();
});
