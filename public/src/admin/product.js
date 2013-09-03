define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/admin/modal' )
	
	var List = Backbone.View.extend({
		el: $( '#admin-list' ), 
		events: {
			'click .cate-create': 'cate_create', 
			'click .product-create': 'product_create', 
			'click tbody tr': 'list_item_select', 
			'mouseover tbody tr': 'list_item_mouseover', 
			'mouseleave tbody tr': 'list_item_mouseleave', 
			'click tr .delete': 'list_item_delete'
		}, 
		initialize: function(){
//			this.$( 'tr' ).hover(this.list_item_over, this.list_item_leave);
		}, 
		cate_create: function(){
			this.$( 'a[href="#cate"]' ).click();
			form.showCate();
		}, 
		product_create: function(){
			this.$( 'a[href="#product"]' ).click();
			form.showProduct();
		}, 
		add: function(model){
			var clone = this.$( '.tab-pane.active .clone' );
			this.$( '.tab-pane.active table tbody' ).append( _.template( $('#template-'+ model.type +'-listitem').html(), model.toJSON() ) );
			this.listenTo( model, 'change', this.renderItem );
		}, 
		renderItem: function( model ){
			this.tab( model.type );
			if( model.type == 'cate' ){
				// 处理到category_list 中
			}
			this.$( '#listitem-'+ model.type + '-' + model.id ).html( $(_.template( $('#template-'+ model.type +'-listitem').html(), model.toJSON() )).html() );
		},
		list_item_mouseover: function( event ){
		}, 
		list_item_mouseleave: function( event ){
		}, 
		list_item_select: function( event ){
//			$( '.edit', $(event.currentTarget) ).show();
//			$( '.edit', $(event.currentTarget) ).hide();
			this.$('table .active').removeClass( 'active' );
			this.$( event.currentTarget ).addClass( 'active' );
			var arr = new RegExp( "listitem-([a-z]+)-([0-9]+)", 'ig' ).exec( event.currentTarget.id )
			var type = arr[1]
			var id = arr[2]
			if( type == 'cate' ){
				model = form.showCate( id );
			}else{
				model = form.showProduct( id );
			}
			this.listenTo( model, 'change', this.renderItem );
		}, 
		tab: function( type ){
			this.$( 'a[href="#'+ type +'"]' ).click();
		}, 
		list_item_delete: function( event ){
			var tr = $(event.currentTarget).parents('tr')
			var arr = new RegExp( "listitem-([a-z]+)-([0-9]+)", 'ig' ).exec( tr.attr('id') )
			var type = arr[1]
			var id = arr[2]
			
			$msg = '确定删除'+ ( type == 'cate' ? '类目' : '产品' ) + ': '+  $('.name', tr).html();
			if( !confirm( $msg ) ){
				return;
			}
				
			if( type == 'cate' ){
				var model = new Cate({id: id});
			}else{
				var model = new Product({id: id});
			}
			model.destroy2({success: function(model, response) {
				alert( '删除成功' );
				model.type = type
				list.removeItem(model);
			}});
			return false;
		}, 
		removeItem: function( model ){
			console.log( model )
			if( model.type == 'cate' ){
				form.deleteCategory( model.id );
			}else{
			}
			form.clear();
			this.$( '#listitem-'+ model.type + '-'+ model.id ).remove();
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
		showCate: function( id ){
			
			this.stopListening( product );
			
			if( cate == undefined || cate.id != id ){
				cate = new Cate({id: id});
			}
			this.listenTo( cate, 'change', this.render );
			
			if( id != undefined ){
				cate.fetch();
			}else{
				cate.trigger( 'change', cate );
			}
			
			$( '.product', this.$el ).hide();
			$( '.cate', this.$el ).show();
			return cate;
		}, 
		showProduct: function( id ){
			
			this.stopListening( cate );
			
			if( product == undefined || product.id != id ){
				product = new Product({id: id});
			}
			this.listenTo( product, 'change', this.render );
			
			if( id != undefined ){
				product.fetch();
			}else{
				product.trigger( 'change', product );
			}
			
			$( '.cate', this.$el ).hide();
			$( '.product', this.$el ).show();
			return product;
		}, 
		render: function(model, a, b, c){
			
			console.log( 'render' )
			var html;
			if( model.type == 'cate' ){
				if( model.isNew() ){
					console.log( 'isnew' )
					html = _.template( $( '#cate-template' ).html(), _.extend( model.toJSON(), {'title': '新建类目'} ) );
				}else{
					console.log( 'edit' )
					html = _.template( $( '#cate-template' ).html(), _.extend( model.toJSON(), {'title': '编辑类目'} ) );
				}
			}else{
				if( model.isNew() ){
					console.log( 'isnew' )
					html = _.template( $( '#product-template' ).html(), _.extend( model.toJSON(), {'title': '新建产品', category_list: category_list} ) );
				}else{
					console.log( 'edit' )
					html = _.template( $( '#product-template' ).html(), _.extend( model.toJSON(), {'title': '编辑产品', category_list: category_list} ) );
				}
			}
			this.model = model;
			this.$('#form-inner').html( html );
		}, 
		save: function(){
			try{
				var changed = {};
				
				if( this.model == undefined ){
					console.log( 'no model' )
					return;
				}
				
				list.tab(this.model.type);
				
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
						if( model.type == 'cate' ){
							form.updateCategoryList(model);
						}
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
		updateCategoryList: function( cate ){
			var tmp = {};
			tmp[cate.id] = cate.toJSON();
			category_list = _.extend( category_list, tmp );
			console.log( tmp );
			console.log( category_list );
		}, 
		deleteCategory: function( id ){
			if( typeof( category_list[id] ) == 'undefined' ) return;
			delete( category_list[id] )
		},
		clear: function(){
			this.$('#form-inner').empty();
		}
	});
	
	var Cate = Backbone.Model.extend({
		type: 'cate', 
		urlRoot: '/admin/product/category/id/'
	});
	
	var Product = Backbone.Model.extend({
		type: 'product', 
		urlRoot: '/admin/product/product/id/'
	});
	
	list = new List();
	form = new Form();
	var cate;
	var product;
//	console.log( 'form created' );
//	$('.product-create').click();
});
