define(function(require, exports, module) {

	require('/src/json2/json2')
	require('underscore')
	var Backbone = require('backbone')
	
	var Admin = {
		layout: null, 
		menu: null, 
		list: null, 
		form: null, 
		user: null, 
		header: null, 
		init: function(){
			require.async(['/src/admin/layout'], function(Layout){
				app.layout = new Layout.Layout();
				app.menu = new Layout.Menu();
			});
		}
	}
	
	$.ajaxSetup({
		'error': function(){
			alert( '系统错误' );
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

	module.exports = Admin
});