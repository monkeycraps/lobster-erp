define(function(require, exports, module){
	
	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	// var Layout = require( '/src/app/layout' );
	
	var TestView = Backbone.View.extend({
		el: '#test-view', 
		template: _.template($('#test').html()), 
		events: {
			'click .btn': 'check'
		}, 
		initialize: function(){
			this.listenTo( book, 'change', this.render );
		}, 
		render: function( model ){
			this.$el.html(this.template(this.model.toJSON()));
			return this;
		}, 
		check: function(){
			alert(1)
		}
	});
	var Book = Backbone.Model.extend({
		urlRoot : '/admin/product/product'
	});
//	var solaris = new Book({id: 3});
////	alert(solaris.url());
////	solaris.save();
////	solaris.id = 3;
////	solaris.set( 'a', '3' )
////	solaris.save();
//	solaris.fetch();
	
	var book = new Book({id: 3});
//	alert( book.url() );
	console.log( book.sync )
//	console.log( Backbone.sync )
	Backbone.Model.prototype.save2 = function(attr, options){
		if( options.error == undefined ){
			options.error = function( model, response, options ){
				try{
					alert( '保存出错：'+ response.status + ':' + eval( "\'" + response.responseText + "\'") )
				}catch(ex){
					alert( '保存出错：'+ response.status + ':' + response.responseText )
				}
			}
		}
		return Backbone.Model.prototype.save.apply( this, arguments );
	}
	console.log( Backbone.Model.prototype.save2 )
//	book.sync = function(method, model) {
//	  alert(method + ": " + JSON.stringify(model));
//	  model.id = 1;
//	};
	book.fetch();
	var view = new TestView({model: book});
	book.set('name', 'abc');
	book.save2({}, {
//		success: function(model){
//			alert( 'saved' )
//			console.log( model.toJSON() )
//		}, 
//		error: function(model, response, options){
//			console.log( model )
//			console.log( response )
//			console.log( options )
//			alert('error')
//		}
	});
//	book.set('id', 'abc');
	
	var Test = Backbone.Model.extend({
		initialize: function(){
			// var modal = new Modal()
			// alert(1)
			
//			Backbone.sync = function(method, model) {
//				  alert(method + ": " + JSON.stringify(model));
//				  model.id = 1;
//			};

			
		
			
		}, 
		
	})
	
	
	
	console.log( 'test loaded' )
	new Test();

	module.exports = Test
});