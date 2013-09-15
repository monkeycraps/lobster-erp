define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );

	var FormInnerView = Backbone.View.extend({
		el: '.mission-form-inner', 
		initialize: function(){
		}
	});

	module.exports = FormInnerView
});