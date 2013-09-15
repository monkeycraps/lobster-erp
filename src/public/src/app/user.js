define(function(require, exports, module){


	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );

	var user_model = Backbone.Model.extend({
		initialize: function(){
		}
	});

	var User = {
		um: null, 
		init: function( user ){
			User.um = new user_model();
			User.um.set(user);
		}, 
		getInstance: function(){
			if( null == um ){
				throw new Error("system error, no user");  
			}
			return um
		}
	}

	module.exports = User
});