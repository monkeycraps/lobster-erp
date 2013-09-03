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

	var MissionChangeModal = Backbone.View.extend({
		el: $( '#modal-mission-change' ), 
		events: {
			'change .select-cate': 'change_cate', 
		}, 
		initialize: function(){
			var mission_type = app_data.user.role.mission_type;
			$( '.select-cate', this.$el ).empty()
			for( key in mission_type ){
				$( '.select-cate', this.$el ).append( '<option value="'+ key +'">'+ mission_type[key].data.name +'</option>' )
			}
		}, 
		change_cate: function( event ){
			var mission_type = app_data.user.role.mission_type;
			var mtid = $( event.target ).val()
			if( typeof mission_type[mtid] == 'undefined' || typeof mission_type[mtid].children == 'undefined' ){
				$( '.select-subcate', this.$el ).hide()
				$( '.label-subcate', this.$el ).hide()
				return;
			}

			var children = mission_type[mtid].children
			$( '.select-subcate' ).empty()
			for( key in children ){
				$( '.select-subcate', this.$el ).show();
				$( '.label-subcate', this.$el ).show();
				$( '.select-subcate', this.$el ).append( '<option value="'+ key +'">'+ children[key].name +'</option>' )
			}
		}, 
		change_mission: function(){
			var mission_id = this.$( 'input[name="mission_id"]' ).val();
			if( !mission_id ){
				alert( '系统错误' );
				return;
			}

			request_mission_change();
		}, 
		request_mission_change: function(){

			$.ajax({
				url: '/mission/change', 
				type: 'PUT', 
				data: this.$( 'form' ).serializeArray(), 
				success: function( data, textStatus, jqXHR ){

				}
			});
		}, 
		setMission: function( id ){
			this.$( 'input[name="mission_id"]' ).val( id );
			return this;
		}, 
		show: function(){
			this.setMission( 1 )
			$( '#modal-mission-change' ).modal();
		}
	});

	var Mission = {
		mission_change_modal: new MissionChangeModal()
	};
	module.exports = Mission
});