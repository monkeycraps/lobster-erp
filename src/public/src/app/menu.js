define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );

	var MenuView = Backbone.View.extend({
		el: $( '#front-menu' ), 
		events: {
			'submit #menu-form-search': 'searchMissoin'
		}, 
		initialize: function(){

			var view = this
			this.$( '.mission-add' ).on('shown.bs.popover', function () {

				var popover = $( '.popover:visible' );
				$( 'form', popover ).show();

				$( '.tab-pane .btn', popover ).unbind( 'click' ).click(function(){
					$( '.tab-pane .btn', popover ).removeClass( 'btn-info' );
					$( this ).addClass( 'btn-info' )
					return false;
				})

				$('.nav a', popover).unbind( 'click' ).click(function (e) {
					e.preventDefault()
					$(this).tab('show')
					$( '.tab-pane:visible', popover ).hide();
					$( '.tab-pane[id="'+ ($( this ).attr( 'href' ).substr( 1 ) ) +'"]', popover ).show();
				})

				$( '.btn-mission-add', popover ).unbind( 'click' ).click(function(event){
					view.do_mission_add( event )
				});
				$( '.btn-mission-add-cancel', popover ).unbind( 'click' ).click(function(event){
					view.do_mission_add_cancel( event )
				});

				$('.nav a:first', popover).click();
			})
		}, 
		do_mission_add: function( event ){

			var wrapper = $( event.currentTarget ).parents( '.popover:first' );

			var cate_id = $( 'li.active a', wrapper ).attr( 'data-id' )
			var sub_cate_id = $( '.tab-pane:visible .btn-info', wrapper ).attr( 'data-id' )

			if( !sub_cate_id ){
				alert( '还没选择具体任务' )
				return ;
			}
			app.Layout.form_view.add( cate_id, sub_cate_id );

			this.do_mission_add_cancel();
		}, 
		do_mission_add_cancel: function(){

			this.$( '.mission-add' ).popover( 'hide' );
		}, 
		searchMissoin: function(){

			try{
				app.Layout.list_view.search( 
					$.trim( this.$( 'input[name="id"]' ).val() ), 
					$.trim( this.$( 'input[name="order_num"]' ).val() ), 
					$.trim( this.$( 'input[name="wanwan"]' ).val() ) );
			}catch(ex){
				console.log(ex.name)
				console.log(ex.message)
				console.log(ex.description)
				console.log(ex.number)
			}
			return false;
		}
	});
	
	return MenuView;
});