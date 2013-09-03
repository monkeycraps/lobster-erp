define(function(require, exports, module) {

	var _ = require('underscore')
	var Backbone = require('backbone')
	// require('/src/app/user')
	// require('/src/app/header')
	// require('/src/app/name')
	
	var App = {
		layout: null, 
		user: null, 
		init: function(){
			console.log( 'app init' )
			require.async(['/src/app/user', '/src/app/header', '/src/app/layout'], function(User, header, Layout){
				App.user = User;
			});
		}
	}
	
	$.ajaxSetup({
		'error': function( jqXHR, textStatus, errorThrown ){
			alert( '系统错误' );
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
			}
		}
		return Backbone.Model.prototype.save.apply( this, arguments );
	}
	
	Backbone.Model.prototype.destroy2 = function(options){
		if( options.error == undefined ){
			options.error = function( model, response, options ){
				try{
					alert( '保存出错：'+ response.status + ':' + eval( "\'" + response.responseText + "\'") )
				}catch(ex){
					alert( '保存出错：'+ response.status + ':' + response.responseText )
				}
			}
		}
		return Backbone.Model.prototype.destroy.apply( this, arguments );
	}

	module.exports = App
});