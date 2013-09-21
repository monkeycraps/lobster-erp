define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );

	var MissionChangeModal = Backbone.View.extend({
		el: $( '#modal-mission-change' ), 
		events: {
		}, 
		model: null, 
		initialize: function(){
			this.initMoal();
		}, 
		doChangeMissionType: function(){
			var view = this;

			var mission_type = this.$( '.tab-pane .btn-info' ).attr( 'data-id' )
			if( !mission_type ){
				alert( '请选择要更改的类型' )
				return;
			}
			if( mission_type == this.model.get( 'mission_type_id' ) ){
				alert( '更改的类型和原类型一致' )
				return;
			}
			$.post( '/mission/changeType', {id: this.model.id, mission_type: mission_type}, function( data, textStatus, jqXHR ){

				if( !_.isObject( data ) ){
					alert( '系统错误'+ data )
					return;
				}

				app.mission.form_view.remove();
				app.mission.list_view.go( data.id )
				app.mission.form_toolbar_view.showSuccess( '更改类型成功' );
				view.close();
			});
		}, 
		doChangeMissionKefu: function(){
			var view = this;

			$.post( '/mission/changeKefu', { id: this.model.id, uid: this.$( '.kefu-uid' ).val() }, function( data, textStatus, jqXHR ){

				app.mission.form_view.remove();
				app.mission.form_toolbar_view.showSuccess( '交接成功' );
				view.close();

			});
		}, 
		show: function( model ){
			this.model = model;
			this.$el.modal('show');

			var view = this
			this.$( '.tab-pane .btn' ).each(function(){
				if( $(this).attr( 'data-id' ) == view.model.get( 'mission_type_id' ) ){
					$(this).addClass( 'disabled' )
				}else{
					$(this).removeClass( 'disabled' )
				}
				$(this).removeClass( 'btn-info' )
			})
		}, 
		initMoal: function(){

			var view = this;

			view.$( '.tab-pane .btn' ).click(function(){
				view.$( '.tab-pane .btn' ).removeClass( 'btn-info' );
				$( this ).addClass( 'btn-info' )
				return false;
			})

			view.$('.nav a').click(function (e) {
				e.preventDefault()
				$(this).tab('show')
				view.$( '.tab-pane:visible' ).hide();
				view.$( '.tab-pane[id="'+ ($( this ).attr( 'href' ).substr( 1 ) ) +'"]' ).show();
			})

			view.$( '.btn-change-type' ).click(function(event){
				view.doChangeMissionType()
			});
			view.$( '.btn-change-kefu' ).click(function(event){
				view.doChangeMissionKefu()
			});
		}, 
		close: function(){
			this.$el.modal('hide')
		}
	});

	var MissionFlagModal = Backbone.View.extend({
		el: $( '#modal-mission-flag' ), 
		events: {
		}, 
		model: null, 
		initialize: function(){
			this.initMoal();
		}, 
		doFlag: function(){
			var view = this;

			var flag_list = []
			this.$( '.checkbox input[type="checkbox"]:checked' ).each(function(){
				flag_list.push( this.value );
			})

			$.post( '/mission/flag', { id: this.model.id, flag: flag_list, remarks: this.$( 'textarea' ).val() }, function( data, textStatus, jqXHR ){

				if( !_.isObject( data ) ){
					alert( '系统错误'+ data )
					return;
				}

				view.model.set( data );
				app.mission.form_toolbar_view.showSuccess( '记录成功' );
				view.close();

			});
		}, 
		show: function( model ){
			this.model = model;
			this.$el.modal('show');

			var view = this
			var flag_list_tmp = this.model.get( 'flag_list' )
			flag_list = []
			_.each( flag_list_tmp, function(val){
				flag_list.push( val+ '' )
			} )
			this.$( '.checkbox input[type="checkbox"]' ).each(function(){
				if( $.inArray( this.value, flag_list ) >= 0 ){
					this.checked = true;
				}else{
					this.checked = false;
				}
			})

			this.$( 'textarea' ).val( this.model.get( 'flag_remarks' ) )
		}, 
		initMoal: function(){

			var view = this;

			view.$( '.btn-primary' ).click(function(event){
				view.doFlag()
			});
		}, 
		close: function(){
			this.$el.modal('hide')
		}
	});

	var Mission = {
		mission_change_modal: new MissionChangeModal(), 
		mission_flag_modal: new MissionFlagModal()
	};

	module.exports = Mission

});