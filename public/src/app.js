define(function(require, exports, module) {

	require('/src/json2/json2')
	require('underscore')
	require('backbone')
	// require('/src/app/user')
	// require('/src/app/header')
	// require('/src/app/name')
	
	var User = require('/src/app/user');
	var Header = require('/src/app/header');

	// var Test = require( '/src/test' )
	// Test.test();
	var App = {
		attrs: {}, 
		init: function( app ){
			App.attrs = app;

			User.init( app.user );

			var header = new Header();

		}
	}

	module.exports = App
});