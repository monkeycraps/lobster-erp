define(function(require, exports, module){

	var _ = require( 'underscore' );
	var header = require( '/src/app/header' );

	var Util = {
		buildStore: function( select, selected ){
			_.each( app_data.store,function( value, key ){
				select.append( '<option value="'+ value.id +'" '+ ( (selected != undefined && ( value.id == selected )) ? 'selected' : '' ) +' >'+ value.name +'</option>' )
			} )
		}, 
		buildFold: function( control ){
			var wrap = control.next( '.fold-content' )
			$( '.dofold', control ).click(function(){
				wrap.hide();
				control.removeClass( 'unfold' );
			})
			$( '.dounfold', control ).click(function(){
				wrap.show();
				control.addClass( 'unfold' );
			})
		}, 
		showFormSuccess: function( msg ){
			header.showFormSuccess( msg );
		}, 
		formJson: function( form ){  
            var serializeObj={};  
            var array=form.serializeArray();  
            $(array).each(function(){  
                if(serializeObj[this.name]){  
                    if($.isArray(serializeObj[this.name])){  
                        serializeObj[this.name].push(this.value);  
                    }else{  
                        serializeObj[this.name]=[serializeObj[this.name],this.value];  
                    }  
                }else{  
                    serializeObj[this.name]=this.value;   
                }  
            });  
            return serializeObj;  
        }
	}

	module.exports = Util
});