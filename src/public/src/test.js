define(function(require, exports, module){
	
	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var $ = require( '$' )
	var Test = {}
	// require( 'jquery-plugin/form/3.36.0/form' )
	module.exports = Test

	// var Parent = Backbone.Model.extend({
	// 	defaults: {
	// 		name: 1
	// 	}, 
	// 	initialize: function(){
	// 	}, 
	// 	show: function(){
	// 		alert(this.get( 'name' ))
	// 	}
	// });

	// var Son = Parent.extend({
	// 	defaults_new: {
	// 		name: 2
	// 	}, 
	// 	initialize: function(){
	// 		this.set( this.defaults_new );
	// 	}
	// })

	var Parent = Backbone.View.extend({
		defaults: {
			name: 1
		}, 
		el: $( '' ), 
		events: {
			'click input[type="text"]': 'show', 
			'click input[type="button"]': 'show1'
		}, 
		initialize: function(){
		}, 
		show: function(){
			alert(1)
			return false;
		}, 
		show1: function(){
			this.listento(  )
			return false;
		}
	});

	var Son = Parent.extend({
		el: $( '#form2' ), 
		events_new: {
			// 'click input[type="button"]': 'show1'
		}, 
		initialize: function(){
			this.events = _.extend( this.events, this.events_new )
		}, 
		show2: function(){
			alert(2)
			return false;
		}
	})

	var son = new Son();
	// son.show();




});