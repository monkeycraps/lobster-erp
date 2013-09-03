define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )

	var MissionListView = Backbone.View.extend({
		initialize: function(){
		}
	});

	var MissionList = Backbone.Model.extend({
		
	});
	var mission_list_view = new MissionListView();

	var MissionFormView = Backbone.View.extend({
		
	});

	var MissionForm = Backbone.Model.extend({
		
	});

	var Mission = {};
	module.exports = Mission
});