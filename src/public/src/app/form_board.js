define(function(require, exports, module){

	var _ = require( 'underscore' );
	var $ = require( '$' );
	var Backbone = require( 'backbone' );
	var Mission = require( '/src/app/mission' );
	var SWFUpload = require( 'gallery2/swfupload/2.2.0/swfupload-debug' );
	var SWFUploadHandlerWrapper = require( 'gallery2/swfupload/2.2.0/handler' );

	var FormBoard = Backbone.View.extend({
		el: $( '.form_board' ), 
		events: {
			'click .comment': 'do_comment', 
			'keydown input[name="comment"]': 'check_enter'
		}, 
		form_view: null, 
		initialize: function(opt){
			this.form_view = opt.form_view
			this.$el = $('#front-form .form_board');
			// var view = this;
			// this.$( '.comment' ).click(function(){view.do_comment()});
			this.init_comment_img();
		}, 
		check_enter: function( event ){
			var key = event.which; 
			var view = this;
			if (key == 13) { 
				event.preventDefault();
				if( $.trim( view.$( 'input[name="comment"]' ).val() ) != '' ){
					view.do_comment();
				}
			}
		}, 
		do_comment: function(){
			var view = this;
			$.post( '/comment/post', {
				id: 0, 
				mission_id: this.form_view.form_model.id, 
				content: this.$( 'input[name="comment"]' ).val(), 
				replyto: this.$( 'input[name="replyto"]' ).val()
 			}, function( data ){
				if( !_.isObject( data ) ){
					alert( '评论失败！'+ data )
					return;
				}
				view.form_view.comment.add(data);
				view.form_view.$( 'input[name="comment"]' ).val( '' );
			}, 'json' );
		},
		init_comment_img: function(){

			if( !this.form_view.form_model.id ){
				return;
			}

			var handler = SWFUploadHandlerWrapper.SWFUploadHandler;
			console.log( handler )

			var post_params = {
				id: 0
			};
			if( this.form_view.form_model.id ){
				post_params.mission_id = this.form_view.form_model.id
			}

			var swfu = new SWFUpload({
				upload_url : '/comment/postImg',
				flash_url : '/sea-modules/gallery2/swfupload/2.2.0/swfupload.swf',

				file_post_name : "Filedata",
				post_params: post_params,
				assume_success_timeout : 0,
				file_types : "*.jpg;*.png;*.gif",
				file_types_description : 'JPG,PNG,GIF文件',
				file_size_limit : "3 MB",
				file_queue_limit : 1,
				file_upload_limit : 9999,
				debug: false, 
				prevent_swf_caching : false,
				preserve_relative_urls : false,
				button_placeholder_id : 'do-comment-img',
				button_image_url : '/img/do_comment_img.jpg',
				button_width : 20,
				button_height : 20,
				button_text_left_padding : 0,
				button_text_top_padding : 0,
				button_action : SWFUpload.BUTTON_ACTION.SELECT_FILES,
				button_cursor : SWFUpload.CURSOR.HAND,
				button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,

				// swfupload_loaded_handler : function(){ alert( 'swfupload_loaded_handler ' ) }, 
				// file_dialog_start_handler : function(){ alert( 'file_dialog_start_handler ' ) }, 
				// file_queued_handler: function(){ alert( 'file_queued_handler' ) }, 
				file_queue_error_handler: handler.fileQueueError, 
				file_dialog_complete_handler: handler.fileDialogComplete,
				// upload_start_handler: function(){ alert( 'start' ) }, 
				// upload_success_handler: function(){ alert( 'upload_success_handler' ) }, 
				upload_success_handler : this.do_comment_img_success,
				upload_error_handler: handler.uploadError, 
				upload_complete_handler: handler.uploadComplete, 
				upload_progress_handler: handler.uploadProgress, 

				custom_settings : {
					form_board: this
				}
			});
		}, 
		do_comment_img_success: function(file, serverData, responseReceived) {


			var d = jQuery.parseJSON(serverData);
			if( !d ){
				alert( '上传图片错误' );
				return;
			}

			this.customSettings.form_board.form_view.comment.add(d);
			this.customSettings.form_board.form_view.$( 'input[name="comment"]' ).val( '' );
		}, 
		show: function(){
			var view = this;

			this.$el.show();
			var icon_selector = this.$( '.icon-selector' ).on('shown.bs.popover', function () {
				$( 'li', $( '.icon-wrapper:visible' )).hover(function(){
		      			$(this).css( 'background', 'rgb(218, 239, 255)' );
		      		}, function(){
		      			$(this).css( 'background', 'transparent' )
		      		}
		      	).click(function(){
		      		var data = $( this ).attr( 'data' )
	      			icon_selector.popover( 'hide' );
	      			view.$( 'input[name="comment"]' ).val( view.$( 'input[name="comment"]' ).val() + '['+ data +']' )
	      		});
			})

			switch( parseInt(view.form_view.form_model.get('user_state')) ){
				case 0:
					this.$( '.save' ).hide();
					this.$( '.to_dealing' ).hide();
					this.$( '.to_done' ).hide();
					this.$( '.to_close' ).show();
					this.$( '.draft' ).show();
					this.$( '.publish' ).show();
					this.$( '.reopen' ).hide();
					break;
				case 1:
					this.$( '.save' ).show();
					this.$( '.to_dealing' ).show();
					this.$( '.to_done' ).show();
					this.$( '.to_close' ).show();
					this.$( '.draft' ).hide();
					this.$( '.publish' ).hide();
					this.$( '.reopen' ).hide();
					break;
				case 2:
					this.$( '.save' ).show();
					this.$( '.to_dealing' ).hide();
					this.$( '.to_done' ).show();
					this.$( '.to_close' ).show();
					this.$( '.draft' ).hide();
					this.$( '.publish' ).hide();
					this.$( '.reopen' ).hide();
					break;
				case 3:
					this.$( '.save' ).show();
					this.$( '.to_dealing' ).show();
					this.$( '.to_done' ).hide();
					this.$( '.to_close' ).show();
					this.$( '.draft' ).hide();
					this.$( '.publish' ).hide();
					this.$( '.reopen' ).hide();
					break;
				case 4:
					this.$( '.save' ).hide();
					this.$( '.to_dealing' ).hide();
					this.$( '.to_done' ).hide();
					this.$( '.to_close' ).hide();
					this.$( '.draft' ).hide();
					this.$( '.publish' ).hide();
					if( app_data.user.role_id == 4 ){
						// 店长能重新打开关闭的任务
						this.$( '.reopen' ).show();
					}
					break;
				default:
					this.$( '.save' ).hide();
					this.$( '.to_dealing' ).hide();
					this.$( '.to_done' ).hide();
					this.$( '.to_close' ).hide();
					this.$( '.draft' ).hide();
					this.$( '.publish' ).hide();
					break;
			}
		}
	});

	module.exports = FormBoard
});