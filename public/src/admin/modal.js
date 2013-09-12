define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );

	var ModalMissionAdd = Backbone.View.extend({
		el: $('#modal-mission-add'), 
		events: {
			'change .select-cate': 'change_cate'
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
		show_modal: function(){
			$( '.select-cate', this.$el ).change();
			this.$el.modal()
		}
	})

	var ModalManager = {
		init: function(){
			
		}, 
		modal: function( id ){
			if( $( '#modal-'+ id ).length < 1 ){
				return
			}
			eval( 'modal_'+ id.replace( new RegExp('-',"gm"), '_' ) + '.show_modal()' )
			return;
		}
	}

	module.exports = ModalManager

});