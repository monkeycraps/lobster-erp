define(function(require, exports, module){
	
	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var $ = require( '$' )
	var Test = {}
	require( 'jquery-plugin/form/3.36.0/form' )

	module.exports = Test

	$( '#abc' ).click(function(){
		console.log( $('form').formToArray() );
		console.log( $('form').formSerialize() );
		return false;
	});

});