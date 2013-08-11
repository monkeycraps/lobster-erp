define(function(require, exports, module){
	
	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var $ = require( '$' );
	
	function Test(){
		Backbone.sync = function(method, model) {
		  alert(method + ": " + JSON.stringify(model));
		  model.id = 1;
		};

		var book = new Backbone.Model({
		  title: "The Rough Riders",
		  author: "Theodore Roosevelt"
		});
		
//		Backbone.emulateHTTP = true;

//		book.save();

//		book.save({author: "Teddy"});
		
	}
	
	_.extend( Test.prototype, {
		test: function(){
		}
	} )
	
	module.exports = Test
});