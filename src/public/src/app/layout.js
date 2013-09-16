define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var ModalManager = require( '/src/app/modal' )
	var Mission = require( '/src/app/mission' );
	var mscroll = require( '/mcustomscrollbar/jquery.mCustomScrollbar.concat.min' );
	var FormBoard = require( '/src/app/form_board' );
	var Comment = require( '/src/app/comment' );
	require( '/mcustomscrollbar/jquery.mCustomScrollbar.css' );

	function resize(){
		var wh = $(window).height()
		var board_height = (wh - $("header").height() ) + "px";
		$( '#front-menu' ).css( 'height', board_height );
		$( '#front-list' ).css( 'height', board_height );
		$( '#front-form' ).css( 'height', board_height );
		$( '#front-history' ).css( 'height', board_height );
	}

	var Layout = Backbone.View.extend({
		el: $( '#layout' ),
		menu: null,  
		board: null,  
		events: {

		}, 
		initialize: function(){
			window.onload = window.onresize = function(){
				resize();
			}
			resize();
		}
	});

	var FormView = Backbone.View.extend({
		el: $( '#front-form' ), 
		FormInner: null,
		form_model: null, 
		form_board: null, 
		view: null, 
		form_history: null, 
		comment: null, 
		issaving: false, 
		events: {
		}, 
		initialize: function(){
		}, 
		clear: function(){
			this.FormInner = null;
			this.form_model = null;
			this.view = null;
			this.form_history = null;
			this.$el.empty();
		}, 
		add: function( cate_id, sub_cate_id, callback ){
			this.clear();
			this.$el.load( '/mission/form?cate='+ cate_id+'&subcate=' + sub_cate_id, null, function( data ){
				require.async( '/src/app/forminner/form-'+cate_id+'-'+ sub_cate_id, function(FormInner){
					form_view.FormInner = FormInner;
					form_view.form_model = new FormInner.Model()
					form_view.form_model.set({cate_id: cate_id})
					form_view.view = new FormInner.View({model: form_view.form_model})
					form_view.form_board = new FormBoard({form_view: form_view});
					form_view.form_board.show();
					form_view.comment = new Comment({form_view: form_view});
					form_view.reset();
					if( typeof( callback ) == 'function' ){
						callback( form_view.form_model )
					}
				} );
			} );
		}, 
		load: function( id, cate_id, sub_cate_id, callback ){
			this.clear();
			this.$el.load( '/mission/form?cate='+ cate_id+'&subcate=' + sub_cate_id, null, function( data ){
				require.async( '/src/app/forminner/form-'+cate_id+'-'+ sub_cate_id, function(FormInner){
					form_view.FormInner = FormInner;
					form_view.form_model = new FormInner.Model({id: id})
					form_view.form_model.set({cate_id: cate_id})
					form_view.view = new FormInner.View({model: form_view.form_model})
					form_view.form_board = new FormBoard({form_view: form_view});
					form_view.form_board.show();
					form_view.comment = new Comment({form_view: form_view});
					form_view.reset();
					if( typeof( callback ) == 'function' ){
						callback( form_view.form_model )
					}
				} );
			} );
		}, 
		remove: function(){
			list_view.list_item_remove()
			this.clear();
		}, 
		reload: function(){
			this.load( this.form_model.id, this.form_model.get( 'cate_id' ), this.form_model.get( 'mission_type_id' ) )
		}, 
		reset: function(){
			form_toolbar_view.initForm();
			form_view.showForm();
			this.$el.mCustomScrollbar({
				advanced:{
			        updateOnContentResize: true, 
			        autoScrollOnFocus: false
			    }, 
			    scrollInertia : 150, 
			    callbacks:{
        			whileScrolling:function( mcs ){
            			form_view.checkFloatBoard( mcs );
        			}
    			}
			})
			setTimeout( function(){ form_view.$el.mCustomScrollbar("scrollTo",'top'); }, 1000 );
		}, 
		checkFloatBoard: function( mcs ){
			if( mcs.topPct > 95 ){
				form_view.unfloatBoard();
			}else if( mcs.topPct < 70 ){
				form_view.floatBoard();
			}
		}, 
		floatBoard: function(){
			this.$( '.form_board' ).css( 'position', 'fixed' ).css( 'bottom', '0px' );
			this.$el.css( 'padding-bottom', this.$( '.form_board' ).outerHeight() );
			this.$( '.form_board' ).css( 'width', this.$( '#forminner-view' ).outerWidth() );
		},
		unfloatBoard: function(){
			this.$( '.form_board' ).css( 'position', 'static' );
			this.$el.css( 'padding-bottom', '10px' );
		}, 
		showHistory: function(){
			if( this.form_history == null ){
				this.form_history = new FormHistory({id: this.form_model.id, callback: function(){
					form_view.hide();
					form_view.form_history.show();
				}});
			}else{
				form_view.hide();
				form_view.form_history.show();
			}
		}, 
		showForm: function(){
			form_view.show();
			if( null != form_view.form_history ){
				form_view.form_history.hide();
			}
		}, 
		show: function(){
			this.$el.show();
		}, 
		hide: function(){
			this.$el.hide();
		}, 
		gotoLast: function(){
			form_view.$el.mCustomScrollbar("scrollTo",'bottom');
		}
	});

	var MenuView = Backbone.View.extend({
		el: $( '#front-menu' ), 
		events: {
		}, 
		initialize: function(){

			this.$( '.mission-add' ).on('shown.bs.popover', function () {

				var popover = $( '.popover:visible' );
				$( 'form', popover ).show();

				$( '.tab-pane .btn', popover ).click(function(){
					$( '.tab-pane .btn', popover ).removeClass( 'btn-info' );
					$( this ).addClass( 'btn-info' )
					return false;
				})

				$('.nav a', popover).click(function (e) {
					e.preventDefault()
					$(this).tab('show')
					$( '.tab-pane:visible', popover ).hide();
					$( '.tab-pane[id="'+ ($( this ).attr( 'href' ).substr( 1 ) ) +'"]', popover ).show();
				})

				$( '.btn-mission-add', popover ).click(function(event){
					menu_view.do_mission_add( event )
				});
				$( '.btn-mission-add-cancel', popover ).click(function(event){
					menu_view.do_mission_add_cancel( event )
				});
			})
		}, 
		do_mission_add: function( event ){

			var wrapper = $( event.currentTarget ).parents( '.popover:first' );

			var cate_id = $( 'li.active a', wrapper ).attr( 'data-id' )
			var sub_cate_id = $( '.tab-pane .btn-info', wrapper ).attr( 'data-id' )

			if( !sub_cate_id ){
				alert( '还没选择具体任务' )
				return ;
			}
			form_view.add( cate_id, sub_cate_id );

			this.do_mission_add_cancel();
		}, 
		do_mission_add_cancel: function(){

			this.$( '.mission-add' ).popover( 'hide' );
		}, 
		change_cate: function( event ){

			var wrapper = $( event.target ).parents( '.popover.in' )

			var mission_type = app_data.user.role.mission_type;
			var mtid = $( event.target ).val()
			if( typeof mission_type[mtid] == 'undefined' || typeof mission_type[mtid].children == 'undefined' ){
				$( '.select-subcate', wrapper ).hide()
				$( '.label-subcate', wrapper ).hide()
				return;
			}

			var children = mission_type[mtid].children
			$( '.select-subcate' ).empty()
			for( key in children ){
				$( '.select-subcate', wrapper ).show();
				$( '.label-subcate', wrapper ).show();
				$( '.select-subcate', wrapper ).append( '<option value="'+ key +'">'+ children[key].name +'</option>' )
			}
		}
	});
	
	var ListView = Backbone.View.extend({
		el: $('#front-list'), 
		events: {
			'change thead .checkall': 'checkALlChange', 
			'click tbody tr': 'list_item_select', 
			'mouseover tbody tr': 'list_item_mouseover', 
			'mouseleave tbody tr': 'list_item_mouseleave', 
			'mouseleave tbody tr': 'list_item_mouseleave'
		}, 
		initialize: function(){
			this.$el.mCustomScrollbar({
				advanced:{
			        updateOnContentResize: true, 
			        autoScrollOnFocus: false
			    }, 
			    scrollInertia : 150
			});
		},
		batchSubmit: function(){
			var action = this.$( '#myTab .active a' ).attr( 'href' ).substr( 10 )
			switch( parseInt(action) ){
				case 6:
					this.batchSubmitDo( '/drawback/batch' )
					break;
				case 7:
					this.batchSubmitDo( '/refundment/batch' )
					break;
				default: 
					break;
			}
		}, 
		batchSubmitDo: function( url ){
			var view = this
			var batch = []
			$( 'tbody tr .checkbox:checked' ).each(function(){batch.push( this.value )});
			if( batch.length == 0 ){
				form_toolbar_view.showListMessage( '没有选中' );
				return;
			}
			$.post( url, {batch: batch}, function(data){
				if( !_.isObject( data ) ){
					alert( '系统错误'+ data )
					return;
				}
				if( 0 != data.error ) {
					alert( data.msg )
					return;
				}
				form_toolbar_view.showListSuccess( data.msg );
				list_view.reload()
			} )
		}, 
		checkClick: function( e ){
			e.stopPropagation()
		}, 
		checkALlChange: function( e ){
			var checked = $( e.currentTarget ).parent().find( '.checkall:checked' ).length > 0 ? true : false;
			if( checked ){
				this.$( 'tbody:visible tr .checkbox' ).each(function(){this.checked=true;}); 
			}else{
				this.$( 'tbody:visible tr .checkbox' ).each(function(){this.checked=false;}); 
			}
		}, 
		search: function( key ){
			var view = this;
			this.$el.load( '/mission/search/key/' + key, {}, function(){
				view.$el.mCustomScrollbar({
					advanced:{
				        updateOnContentResize: true, 
				        autoScrollOnFocus: false
				    }, 
				    scrollInertia : 150
				});

			} );
		}, 
		renderItem: function( model ){

			var user_state = model.get( 'user_state' )
			// 草稿 和 待处理放一起
			if( user_state == 0 )user_state = 1;

			var show_type = this.$( '#listitem-' + model.id ).attr( 'data-type' );

			var now_list_data_type = this.$( '#listitem-' + model.id ).parents( '.tab-pane:first' ).attr( 'data-type' );
			switch( parseInt( now_list_data_type ) ){
				case 8:
					if( model.state == 2 ){
						this.$( '#listitem-' + model.id ).remove();
						this.list_item_add( model, 8 );
						return;
					}
					break;
				case 9:
					if( model.state == 1 ){
						this.$( '#listitem-' + model.id ).remove();
						this.list_item_add( model, 9 );
						return;
					}	
					break;
				default:
					if( this.$( '#listitem-' + model.id ).attr( 'data-type' ) != user_state ){
						this.$( '#listitem-' + model.id ).remove();

						this.list_item_add( model );
						return;
					}
					break;
			}
			this.$( '#listitem-' + model.id ).html( $(_.template( $('#template-'+ show_type +'-listitem').html(), model.toJSON() )).html() );

		},
		gotoList: function( show_type ){
			this.$( '.list-toggller-'+ show_type ).find( 'a' ).click();
		}, 
		list_item_remove: function(){
			var id = form_view.form_model.id
			if( this.$( '#listitem-'+ id ).length ){
				this.$( '#listitem-'+ id ).remove();
			}
		}, 
		list_item_mouseover: function( event ){
		}, 
		list_item_mouseleave: function( event ){
		}, 
		list_item_stopListening: function(){
			this.stopListening();
		}, 
		list_item_add: function( model, show_type ){

			var user_state = model.get( 'user_state' );
			if( user_state == 0 )user_state = 1;

			if( !show_type )show_type = user_state;	
			
			this.$( '#listitem-'+ show_type + ' tbody' ).prepend( $(_.template( $('#template-'+ show_type +'-listitem').html(), model.toJSON() )) );

			this.$( 'table .active' ).removeClass( 'active' );
			this.$( '#listitem-' + model.id ).addClass( 'active' );
			this.gotoList( show_type )
			
		}, 
		list_item_select: function( event ){

			this.list_item_stopListening();

			this.$('table .active').removeClass( 'active' );
			var list_item = this.$( event.currentTarget )
			list_item.addClass( 'active' );
			var arr = new RegExp( "listitem-([0-9]+)", 'ig' ).exec( event.currentTarget.id )
			var id = arr[1]
			var cate_id = list_item.attr( 'cate-id' );
			var sub_cate_id = list_item.attr( 'sub-cate-id' );;

			form_view.load( id, cate_id, sub_cate_id, function( model ){
				list_view.listenTo( model, 'change', list_view.renderItem )
			} )
		}, 
		go: function( id ){
			if( this.$( '#listitem-'+ id ).length ){
				this.gotoList( this.$( '#listitem-'+ id ).attr( 'data-type' ) );
				this.$( '#listitem-'+ id ).click();
			}else{
				this.reload( id )
			}
		}, 
		reload: function( id ){
			var view  = this
			this.$el.load( 'mission/reload/search/'+ id, function(){

				view.$el.mCustomScrollbar({
					advanced:{
				        updateOnContentResize: true, 
				        autoScrollOnFocus: false
				    }, 
				    scrollInertia : 150
				});

				if( id ){
					if( view.$( '#listitem-'+ id ).length ){
						view.gotoList( view.$( '#listitem-'+ id ).attr( 'data-type' ) );
						view.$( '#listitem-'+ id ).click();
					}
				}
			} )
		}
	});

	var FormToolbarView = Backbone.View.extend({
		el: $( '#form-tool-bar' ), 
		events: {
			'click .mission-type-flag': 'flagMissionShow', 
			'click .mission-type-change': 'change_mission_show', 
			'click .form-refresh': 'form_reload', 
			'click .list-refresh': 'list_reload'
		}, 
		list_reload: function(){
			list_view.reload();
		}, 
		form_reload: function(){
			form_view.reload();
		}, 
		flagMissionShow: function(){
			if( form_view.form_model && form_view.form_model.id && form_view.form_model.get( 'state' ) == 2 ){
				Mission.mission_flag_modal.show( form_view.form_model );
			}
			return false;
		}, 
		change_mission_show: function(){
			if( form_view.form_model && form_view.form_model.id ){
				Mission.mission_change_modal.show( form_view.form_model );
			}
			return false;
		}, 
		initForm: function(){
			this.$( '.glyphicon-edit' ).unbind( 'click' )
			this.$( '.glyphicon-edit' ).click(function(){
				form_view.showForm();
			})

			this.$( '.glyphicon-briefcase' ).click(function(){
				form_view.showHistory();
			})
		}, 
		showSuccess: function( msg ){
			if( !msg ){
				msg = '保存成功'
			}
			this.$( '.bs-callout' ).hide();
			this.$( '.form-success' ).html( msg ).show().fadeOut( 10000 );
		}, 
		showMessage: function( msg ){
			if( !msg ){
				msg = '没设置消息'
			}
			this.$( '.bs-callout' ).hide();
			this.$( '.form-message' ).html( msg ).show().fadeOut( 10000 );
		}, 
		showListSuccess: function( msg ){
			if( !msg ){
				msg = '保存成功'
			}
			this.$( '.bs-callout' ).hide();
			this.$( '.list-success' ).html( msg ).show().fadeOut( 10000 );
		}, 
		showListMessage: function( msg ){
			if( !msg ){
				msg = '没设置消息'
			}
			this.$( '.bs-callout' ).hide();
			this.$( '.list-message' ).html( msg ).show().fadeOut( 10000 );
		}
	});

	var FormHistory = Backbone.View.extend({
		el: $('#front-history'), 
		initialize: function(opt){

			var form_history = this;
			var id = form_view.form_model.id
			this.$el.empty();
			this.$el.load( '/mission/history/id/'+ id, function(){
				opt.callback();
				form_history.$el.mCustomScrollbar({
					advanced:{
				        updateOnContentResize: true, 
				        autoScrollOnFocus: false
				    }, 
				    scrollInertia : 150
				});
			});
		}, 
		show: function(){
			this.$el.show();
		}, 
		hide: function(){
			this.$el.hide();
		}
	})

	var menu_view = new MenuView();
	var form_view = new FormView();
	var layout = new Layout();
	var list_view = new ListView();
	var form_toolbar_view = new FormToolbarView() 
	var form_board = null;

	module.exports = {
		layout: layout, 
		menu_view: menu_view, 
		list_view: list_view,  
		form_view: form_view, 
		form_toolbar_view: form_toolbar_view, 
	};

});