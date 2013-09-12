define(function(require, exports, module){

	var _ = require( 'underscore' );
	var ModalManager = require( '/src/app/modal' )
	var Layout = require( '/src/app/layout' );

	require( '/html5-notify/desktop-notify-min' )
	require( '/html5-notify/notifications.css' )

	var Notify = {

		show: function( content, title ){
			if( !title ) title = '合道电器'
			notify.createNotification( title, {body: content, icon: "/html5-notify/alert.ico"})
		}, 
		checkDesktopNotification: function(){

			var permission_messages = {
		      '0': '通知允许.',
		      '1': '请点击允许通知. ',
		      '2': '禁止桌面通知. (请在系统=》高级=》内容设置=》通知中删除本网站禁止项)'}
		    var permissionLevels = {};
		    permissionLevels[notify.PERMISSION_GRANTED] = 0;
		    permissionLevels[notify.PERMISSION_DEFAULT] = 1;
		    permissionLevels[notify.PERMISSION_DENIED] = 2;

		    isSupported = notify.isSupported;

		    if( !isSupported ){
		    	$( '#desktop_notification_warningMsg' ).show();
		    	return;
		    }

		    if( notify.PERMISSION_GRANTED == notify.permissionLevel() ){
		    	return;
		    }

		    $( '.notificationLevel' ).css( 'display', 'block' );

		    permissionLevel = permissionLevels[notify.permissionLevel()];

		    getClassName = function( permissionLevel ) {
		        if (permissionLevel === 0) {
		            return "allowed"
		        } else if (permissionLevel === 1) {
		            return "default"
		        } else {
		            return "denied"
		        }
		    }

		    $( '.notificationLevel' ).addClass( getClassName( permissionLevel ) );
		    $( '.notificationLevel' ).text( permission_messages[permissionLevel] )
		    $( '.notificationLevel' ).click(function(){
		        var before = getClassName( permissionLevels[notify.permissionLevel()] );
		        notify.requestPermission(function() {
		          $( '.notificationLevel' ).removeClass( before );
		          permissionLevel = permissionLevels[notify.permissionLevel()]
		          $( '.notificationLevel' ).addClass( getClassName(permissionLevel) );
		          $( '.notificationLevel' ).text( permission_messages[permissionLevel] );

		          if( permissionLevel == 0 ){
		          	$( '.notificationLevel' ).fadeOut( 5000 );
		          }
		        })
		    });
		}
	}

	module.exports = Notify
});