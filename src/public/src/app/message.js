define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )
	require( '/localStorage/backbone.localStorage' );
	var Notify = require( '/src/app/notify' );

	var NotifiedList = Backbone.Collection.extend({
		localStorage: new Backbone.LocalStorage("notified-message"), 
		initialize: function(){
			this.fetch()
			var list_to_remove = []
			this.each( function( message ){
				if( message.get( 'timeout' ) < new Date().getTime() ){
					list_to_remove.push( message )
				}
			})
			_.each( list_to_remove, function( one ){ one.destroy(); } )
		}, 
		showList: function( list ){

			_.each( list, function(message){

				if( notified_message.get( message.id ) ){
					return;
				}

				Notify.show( message.content, message.title, message.mission_id, message.id );

				message.timeout = new Date().getTime() + 3600 * 24 * 1 * 1000
				notified_message.add( message );
				notified_message.get( message.id ).save()
			} )
		}

	})
	var notified_message = new NotifiedList();

	var View = Backbone.View.extend({
		el: $('#message-wrapper'), 
		new_message: 0, 
		list: [], 
		interval: null, 
		events: {
			'click tbody tr': 'itemToggle', 
			'click tbody tr .op-remove': 'itemRemove', 
			'click tbody tr .op-check-message': 'goto_message'
		}, 
		initialize: function(){

			if( !app_data.user.id ){
				return 
			}

			this.list = message_init.list
			this.new_message = message_init.cnt

			if( app.header != null ){
				app.header.initMessage( this.list, this.new_message )
			}

			notified_message.showList( this.list );

			this.startCheckMessageInterval();
		}, 
		startCheckMessageInterval: function(){
			var view = this;
			this.interval = setInterval(function(){
				$.ajax('/message/check', {
					success: function( data ){
						view.list = data.list
						view.new_message = data.cnt
						notified_message.showList( view.list );
						view.updateHeader();
					}
				});
			}, 10000);
		}, 
		show: function( id, callback ){
			this.$('#message-inner').modal();
			if( id ){
				this.go( id, callback )
			}
		}, 
		itemToggle: function( e ){
			if( $( e.currentTarget ).hasClass( 'active' ) ){
				$( e.currentTarget ).removeClass( 'active' );
				$( e.currentTarget ).find( '.content' ).hide();
			}else{
				this.open( $( e.currentTarget ).attr( 'data-id' ) )
			}
		}, 
		open: function( id, callback ){
			this.$( 'tbody tr' ).removeClass( 'active' )
			this.$( 'tbody tr .content' ).hide();
			this.$( 'tbody tr[data-id="'+ id +'"]' ).addClass( 'active' ).show();
			this.$( 'tbody tr[data-id="'+ id +'"] .content' ).fadeIn();
			var view = this;
			if( !this.$( 'tbody tr[data-id="'+ id +'"]' ).hasClass( 'readed' ) ){
				this.itemReaded( id, function( cnt ){
					if( typeof( callback ) == 'function' ){
						callback( cnt )
					}
				} );
			}else{
				if( typeof( callback ) == 'function' ){
					callback( this.new_message )
				}
			}
		}, 
		go: function( id, callback ){
			got = false
			this.$( 'tbody tr' ).each(function(){
				if( $(this).attr('data-id') ==  id){
					got = true;
				}
			})
			if( !got ){
				var view = this;
				this.$( 'tbody' ).load('/message/page?id='+ id, function(){
					view.open( id, callback );
				})
			}else{
				this.open( id, callback );
			}
		}, 
		itemRemove: function( e ){
			var tr = $( e.currentTarget ).parents('tr:first')
			var id = tr.attr( 'data-id' )
			var view = this;
			$.post( '/message/delete', {id: id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误: '+ data )
					return
				}
				view.new_message = data.new_message
				tr.remove();

				view.updateHeader();
			} )
			return false;
		}, 
		itemReaded: function( id, callback ){

			var view = this;
			$.post( '/message/readed', {id: id}, function( data ){
				if( !_.isObject( data ) ){
					alert( '系统错误: '+ data )
					return
				}
				view.new_message = data.new_message

				if( typeof( callback ) == 'function' ){
					callback( view.new_message )
				}

				view.$( 'tbody tr[data-id="'+ id +'"]' ).addClass( 'readed' )
				_.each( view.list, function( val, key ){
					if( val.id == id ){
						delete( view.list[key] )
						console.log( view.list )
					}
				} )

				view.updateHeader();

			} )
		}, 
		updateHeader: function(){

			if( app.header == null ){
				return;
			}
			app.header.updateMessage( this.list, this.new_message )
		}, 
		goto_message: function( e ){
			var id = $( e.currentTarget ).parents( 'tr:first' ).attr( 'data-mission-id' )
			this.$( '#message-inner' ).modal('hide')

			app.mission.list_view.go( id )
			return false;
		}
	})

	var Message = {
		view: new View
	};

	module.exports = Message
});