define(function(require, exports, module) {

	var _ = require('underscore')
	var Backbone = require('backbone')

	var App = {
		init: function(){
			require.async(['/src/app/user', '/src/app/header', '/src/app/layout', '/src/app/notify'], function(User, header, Layout, Notify){
				app.User = User;
				app.Layout = Layout;
				app.header = header;

				Notify.checkDesktopNotification();

				// Notify.show( '今晚上山打老虎' );
				
				$('body').tooltip({
			      selector: "[data-toggle=tooltip]",
			      container: "body"
			    })

			    $('body').popover({
			      selector: "[data-toggle=popover]",
			      container: "body", 
			      html: true, 
			      placement: function( wrapper, trigger ){
			      	return $(trigger).attr('data-placement');
			      }, 
			      content: function(){
			      	return $(this).next('.popover').html();
			      }
			    })
			});


		    $( '#message-wrapper' ).load('/message/index', function(){
		    	require.async('/src/app/message', function( Message ){
		    		app.Message = Message;
		    	})
		    });
		}
	}
	
	$.ajaxSetup({
		'error': function( jqXHR, textStatus, errorThrown ){
			try{
				alert( '保存出错：'+ jqXHR.status + ':' + eval( "\'" + jqXHR.responseText + "\'") )
			}catch(ex){
				alert( '保存出错：'+ jqXHR.status + ':' + jqXHR.responseText )
			}
			console.log( jqXHR )
			console.log( textStatus )
			console.log( errorThrown )
		}
	});
	
	Backbone.Model.prototype.save2 = function(attr, options){
		if( options.error == undefined ){
			options.error = function( model, response, options ){
				try{
					alert( '保存出错：'+ response.status + ':' + eval( "\'" + response.responseText + "\'") )
				}catch(ex){
					alert( '保存出错：'+ response.status + ':' + response.responseText )
				}
				app.Layout.form_view.issaving = false;
			}
		}
		options.wait = true
		return Backbone.Model.prototype.save.apply( this, arguments );
	}
	
	Backbone.Model.prototype.destroy2 = function(options){
		if( options.error == undefined ){
			options.error = function( model, response, options ){
				try{
					alert( '删除出错：'+ response.status + ':' + eval( "\'" + response.responseText + "\'") )
				}catch(ex){
					alert( '删除出错：'+ response.status + ':' + response.responseText )
				}
				app.Layout.form_view.issaving = false;
			}
		}
		return Backbone.Model.prototype.destroy.apply( this, arguments );
	}

	module.exports = App
});