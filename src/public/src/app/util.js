define(function(require, exports, module){

	var _ = require( 'underscore' );
	require( 'ikj/serialize-object/1.0.0/serialize-object-debug' )

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
		formJson: function( form ){
            // var serializeObj = {};
            // var array = form.serializeArray();
            // $(array).each(function(){  
            //     if(serializeObj[this.name]){  
            //         if($.isArray(serializeObj[this.name])){  
            //             serializeObj[this.name].push(this.value);  
            //         }else{  
            //             serializeObj[this.name]=[serializeObj[this.name],this.value];  
            //         }  
            //     }else{  
            //         serializeObj[this.name]=this.value;   
            //     }  
            // });
            // return serializeObj;
            // console.log( form.serializeArray() )
            // this.$( 'form' ) 会缓存，需要处理掉
            return form.serializeObject();
        }
	}

	module.exports = Util
});