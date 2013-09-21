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
			}
		}
		options.wait = true
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