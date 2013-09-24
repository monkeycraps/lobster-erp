define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var mscroll = require( '/mcustomscrollbar/jquery.mCustomScrollbar.concat.min' );
	require( '/mcustomscrollbar/jquery.mCustomScrollbar.css' );

	var ModalManager = require( '/src/app/modal' )
	var MenuView = require( '/src/app/menu' );
	var Mission = require( '/src/app/mission_ext' );
	var FormBoard = require( '/src/app/form_board' );
	var Comment = require( '/src/app/comment' );
	var Filter = require( '/src/app/filter' );

	function resize(){
		var wh = $(window).height()
		var board_height = (wh - $("header").height() ) + "px";
		$( '#front-menu' ).css( 'height', board_height );
		$( '#front-list' ).css( 'height', board_height );
		$( '#front-form' ).css( 'height', board_height );
		// $( '#front-history' ).css( 'height', board_height );
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
			// this.showSuccess();
		}, 
		showSuccess: function( msg ){
			if( !msg ){
				msg = '保存成功'
			}
			this.$( '.layout-message, .layout-success' ).hide();
			this.$( '.layout-success' ).html( msg ).show();
			// this.$( '.layout-message-outter' ).show().fadeOut( 10000 );
			this.$( '.layout-message-inner' ).show().fadeOut( 4000 );
		}, 
		showMessage: function( msg ){
			if( !msg ){
				msg = '没设置消息'
			}
			this.$( '.layout-message, .layout-success' ).hide();
			this.$( '.layout-message' ).html( msg ).show();
			// this.$( '.layout-message-outter' ).show().fadeOut( 10000 );
			this.$( '.layout-message-inner' ).show().fadeOut( 4000 );
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

			if( list_view.loading )return;
			list_view.loading = true;

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
					app.mission.form_toolbar_view = new FormToolbarView()
					form_view.reset();
					if( typeof( callback ) == 'function' ){
						callback( form_view.form_model )
					}
					list_view.loading = false;
				} );
			} );
		}, 
		load: function( id, cate_id, sub_cate_id, callback ){

			if( list_view.loading )return;
			list_view.loading = true;

			this.clear();
			this.$el.load( '/mission/form?cate='+ cate_id+'&subcate=' + sub_cate_id, null, function( data ){
				require.async( '/src/app/forminner/form-'+cate_id+'-'+ sub_cate_id, function(FormInner){
					form_view.FormInner = FormInner;
					form_view.form_model = new FormInner.Model({id: id})
					form_view.form_model.set({cate_id: cate_id})
					form_view.view = new FormInner.View({model: form_view.form_model})
					form_view.form_board = new FormBoard({form_view: form_view});
					form_view.form_board.show();
					app.mission.form_toolbar_view = new FormToolbarView()
					form_view.comment = new Comment({form_view: form_view});
					form_view.reset();
					if( typeof( callback ) == 'function' ){
						callback( form_view.form_model )
					}
					list_view.loading = false;
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
			app.mission.form_toolbar_view.initForm();
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
			this.$( '.form_board' ).css( 'width', this.$( '#form-tool-bar' ).outerWidth() );
		},
		unfloatBoard: function(){
			this.$( '.form_board' ).css( 'position', 'static' );
			this.$el.css( 'padding-bottom', '10px' );
		}, 
		showHistory: function(){
			this.hide();
			if( this.form_history == null && this.form_model.id ){
				this.form_history = new FormHistory()
				this.form_history.show();
			}else{
				this.form_history.show();
			}
		}, 
		showForm: function(){
			this.show();
			if( this.form_history ){
				this.form_history.hide();
			}
		}, 
		show: function(){
			$('#form-tab-info').show();
		}, 
		hide: function(){
			$('#form-tab-info').hide();
		}, 
		gotoLast: function(){
			form_view.$el.mCustomScrollbar("scrollTo",'bottom');
		}
	});

	var ListView = Backbone.View.extend({
		el: $('#front-list'), 
		loading: false, 
		events: {
			'change thead .checkall': 'checkALlChange', 
			'click tbody tr': 'list_item_select', 
			'mouseover tbody tr': 'list_item_mouseover', 
			'mouseleave tbody tr': 'list_item_mouseleave', 
			'mouseleave tbody tr': 'list_item_mouseleave', 
			'click .batch-submit': 'batchSubmit', 
			'click .do-filter': 'btnDoFilter', 
			'click .pager a': 'btnGoPage', 
			'click .list-refresh': 'listReload'
		}, 
		filter: null, 
		initialize: function(){
			this.$el.mCustomScrollbar({
				advanced:{
			        updateOnContentResize: true, 
			        autoScrollOnFocus: false
			    }, 
			    scrollInertia : 150
			});
			if( app_data.user.id ){
				this.reloadCnt();
			}

			var view = this
			$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				filter = new Filter();
				var tabpane = view.$( $(e.target).attr( 'href' ) )
				var attrs = {
					show_type: tabpane.attr( 'data-type' ),
					page: tabpane.attr( 'data-page' ),
					size: tabpane.attr( 'data-size' ),
					sort: tabpane.attr( 'data-sort' )
				};
				_.each( attrs, function( val, key ){
					if( null == val || val == '' ){
						delete( attrs[key] )
					}
				})
				filter.params = _.extend( filter.defaults, attrs );
				list_view.filter = filter;
			})

			var filter = new Filter();
			var tabpane = view.$( '.tab-pane.active' )
			var attrs = {
				show_type: tabpane.attr( 'data-type' ),
				page: tabpane.attr( 'data-page' ),
				size: tabpane.attr( 'data-size' ),
				sort: tabpane.attr( 'data-sort' )
			};
			_.each( attrs, function( val, key ){
				if( null == val || val == '' ){
					delete( attrs[key] )
				}
			})
			filter.params = _.extend( filter.defaults, attrs );
			this.filter = filter;
		}, 
		btnDoFilter: function(){
			this.filter.params.page = 1
			this.doFilter();
		},
		btnGoPage: function( e ){
			var dom = $( e.currentTarget );
			if( dom.hasClass( 'current' ) ){
				return false;
			}
			this.filter.params.page = dom.attr( 'data-page' )
			this.doFilter();
			return false;
		}, 
		doFilter: function( callback ){

			var params = this.filter.getParams();

			this.$( '.tab-pane.active .wrapper' ).load( 'mission/filter?'+ params+ '&t='+ (new Date().getTime()), function(){
			} )
		},
		listReload: function(){
			this.reload();
		}, 
		showListSuccess: function( msg ){
			if( !msg ){
				msg = '保存成功'
			}
			layout.showSuccess( msg )
		}, 
		showListMessage: function( msg ){
			if( !msg ){
				msg = '没设置消息'
			}
			layout.showMessage( msg )
		}, 
		batchSubmit: function(){
			list_view.batchSubmit();
		}, 
		reloadCnt: function(){
			var view = this;
			$.get( '/mission/reloadCnt', function(data){

				_.each( data, function( val, key ){
					view.$( '.nav a[href="#listitem-'+ key+ '"] .cnt' ).text( parseInt( val ) == 0 ? '' : '('+ val +')' )
				} )
			}, 'json' )
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
				this.showListMessage( '没有选中' );
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
				view.showListSuccess( data.msg );
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
		search: function( id, order_num, wanwan ){
			var view = this;
			this.$el.load( '/mission/search?id='+ id +'&order_num='+ order_num+ '&wanwan='+ wanwan, function(){
				view.$el.mCustomScrollbar({
					advanced:{
				        updateOnContentResize: true, 
				        autoScrollOnFocus: false
				    }, 
				    scrollInertia : 150

				});
				list_view.reloadCnt()
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
			this.reloadCnt();
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

			this.reloadCnt();

			list_view.listenTo( model, 'change', list_view.renderItem )
		}, 
		list_item_select: function( event ){

			if( this.loading ){
				this.showListMessage( '您操作太频繁了，请稍候' );
				return false;
			}

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

				view.reloadCnt();

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
			'click .form-refresh': 'form_reload'
		}, 
		initialize: function(){
			this.$el = $( '#form-tool-bar' )
		}, 
		form_reload: function(){
			form_view.reload();
		}, 
		flagMissionShow: function(){
			if( form_view.form_model && form_view.form_model.id && form_view.form_model.get( 'state' ) == 2 ){
				Mission.mission_flag_modal.show( form_view.form_model );
			}else if( form_view.form_model.get( 'state' ) == 2 ){
				alert( '任务还没关闭' );
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
			this.$( 'a[href="#form-tab-info"]' ).unbind( 'click' ).click(function(){
				form_view.showForm();
			})

			this.$( 'a[href="#form-tab-history"]' ).unbind( 'click' ).click(function(){
				form_view.showHistory();
			})
		}, 
		showSuccess: function( msg ){
			if( !msg ){
				msg = '保存成功'
			}
			layout.showSuccess( msg )
		}, 
		showMessage: function( msg ){
			if( !msg ){
				msg = '没设置消息'
			}
			layout.showMessage( msg )
		}
	});

	var FormHistory = Backbone.View.extend({
		el: $('#front-history'), 
		initialize: function(opt){

			this.$el = $('#front-history')
			// var form_history = this;
			var id = form_view.form_model.id

			this.$el.empty();
			this.$el.load( '/mission/history/id/'+ id, function(){
				// opt.callback();
				// form_history.$el.mCustomScrollbar({
				// 	advanced:{
				//         updateOnContentResize: true, 
				//         autoScrollOnFocus: false
				//     }, 
				//     scrollInertia : 150
				// });
			});
		}, 
		show: function(){
			this.$el.show();
			$('#form-tab-history').show();
		}, 
		hide: function(){
			this.$el.hide();
			$('#form-tab-history').hide();
		}
	})

	var layout = new Layout();
	var menu_view = new MenuView();
	var form_view = new FormView();
	var list_view = new ListView();
	var form_toolbar_view = null; 
	var form_board = null;

	module.exports = {
		layout: layout, 
		menu_view: menu_view, 
		list_view: list_view,  
		form_view: form_view, 
		form_toolbar_view: form_toolbar_view, 
	};

	// form_view.load( 315, 2, 14 )

	list_view.gotoList( 9 )

});