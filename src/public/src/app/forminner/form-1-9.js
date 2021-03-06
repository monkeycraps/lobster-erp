define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Util = require( '/src/app/util' );

	var Layout = app.mission
	var list = Layout.list;
	var FormInner = require( '/src/app/forminner' );

	var Model = FormInner.Model.extend({
		urlRoot: '/mission/mission/id/', 
		defaults: {
			mission_type_id: 9
		}, 
		initialize: function(){
			this.defaults = _.extend( this.defaults_p, this.defaults )
			this.set( this.defaults )
			this.initialize_p();
		}
	});
	
	var FormView = FormInner.View.extend({
		el: '#forminner-mission-1-9', 
		template: _.template($('#template-formview-1-9').html()),
		template_close: _.template($('#template-formview-closed-1-9').html()),
		events: {
		}, 
		initialize: function(opt){

			this.events = _.extend( this.events_p, this.events );

			this.listenTo( opt.model, 'change', this.render )

			console.log()
			this.initialize_p( opt )
		}
	});
	
	module.exports = {
		Model: Model, 
		View: FormView 
	}
});