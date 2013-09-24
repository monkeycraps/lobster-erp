define(function(require, exports, module) {

	var wysi = window.wysihtml5
	var $ = require( '$' );
	var SWFUpload = require( 'gallery2/swfupload/2.2.0/swfupload-debug' );
	var SWFUploadHandlerWrapper = require( 'gallery2/swfupload/2.2.0/handler' );
	var _ = require( 'underscore' );

	"use strict"

	var templates = {
		"font-styles": "<li class='dropdown'>" +
							"<a class='btn dropdown-toggle' data-toggle='dropdown' href='#'>" +
									"<i class='glyphicon glyphicon-font'></i>&nbsp;<span class='current-font'>正文</span>&nbsp;<b class='caret'></b>" +
							"</a>" +
						    "<ul class='dropdown-menu'>" +
						      	"<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='div'>正文</a></li>" +
					            "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h1'>标题1</a></li>" +
					            "<li><a data-wysihtml5-command='formatBlock' data-wysihtml5-command-value='h2'>标题2</a></li>" +
						    "</ul>" +
						"</li>",
		"emphasis":     "<li>" +
							"<div class='btn-group'>" 
							    + "<a class='btn' data-wysihtml5-command='bold' title='CTRL+B'>黑体</a>" 
							    + "<a class='btn' data-wysihtml5-command='italic' title='CTRL+I'>斜体</a>" 
							    //,+ "<a class='btn' data-wysihtml5-command='underline' title='CTRL+U'>Underline</a>" 
							+ "</div>" 
						+ "</li>",
		"lists": 	"<li>" 
						+ "<div class='btn-group'>" 
					    	+ "<a class='btn' data-wysihtml5-command='insertUnorderedList' title='Unordered List'><i class='glyphicon glyphicon-list'></i></a>" 
						    + "<a class='btn' data-wysihtml5-command='insertOrderedList' title='Ordered List'><i class='glyphicon glyphicon-th-list'></i></a>" 
						    + "<a class='btn' data-wysihtml5-command='Outdent' title='Outdent'><i class='glyphicon glyphicon-indent-right'></i></a>"  							    
						    + "<a class='btn' data-wysihtml5-command='Indent' title='Indent'><i class='glyphicon glyphicon-indent-left'></i></a>" 
						+ "</div>" 
					+ "</li>",

		"link": 	"<li>" 
						
						+ "<div class='bootstrap-wysihtml5-insert-link-modal modal hide fade'>"
							+ "<div class='modal-header'>"
							+ "<a class='close' data-dismiss='modal'>×</a>"
							  + "<h3>Insert Link</h3>"
							+ "</div>"
							+ "<div class='modal-body'>"
							  + "<input value='http://' class='bootstrap-wysihtml5-insert-link-url input-xlarge'>"
							+ "</div>"
							+ "<div class='modal-footer'>"
							  + "<a href='#' class='btn' data-dismiss='modal'>Cancel</a>"
							  + "<a href='#' class='btn btn-primary' data-dismiss='modal'>Insert link</a>"
							+ "</div>"
						+ "</div>"

				    	// + "<a class='btn' data-wysihtml5-command='createLink' title='Link'><i class='glyphicon glyphicon-share'></i></a>" 

					+ "</li>",

			"image": "<li>" 
						
						+ "<div class='bootstrap-wysihtml5-insert-image-modal modal hide fade'>"
							+ "<div class='modal-header'>"
							+ "<a class='close' data-dismiss='modal'>×</a>"
							  + "<h3>Insert Image</h3>"
							+ "</div>"
							+ "<div class='modal-body'>"
							  + "<input value='http://' class='bootstrap-wysihtml5-insert-image-url input-xlarge'>"
							+ "</div>"
							+ "<div class='modal-footer'>"
							  + "<a href='#' class='btn' data-dismiss='modal'>Cancel</a>"
							  + "<a href='#' class='btn btn-primary' data-dismiss='modal'>Insert image</a>"
							+ "</div>"
						+ "</div>"

						+ "<a href='#' style='position: relative; top: 5px;' ><span id='do-comment-img'>building...</span></a>" 

					+ "</li>",

		"html": 
						"<li>"
							+ "<div class='btn-group'>"
								+ "<a class='btn' data-wysihtml5-action='change_view' title='Edit HTML'><i class='glyphicon glyphicon-pencil'></i></a>" 
							+ "</div>"
						+ "</li>"
	};
	
	var defaultOptions = {
		"font-styles": true,
		"emphasis": true,
		"lists": true,
		"html": false,
		"link": true,
		"image": true,
		events: {},
		parserRules: {
			tags: {
				"b":  {},
				"i":  {},
				"br": {},
				"ol": {},
				"ul": {},
				"li": {},
				"h1": {},
				"h2": {},
				"u": 1,
				'span': {},
				"img": {
					check_attributes: {
			            width: "numbers",
			            alt: "alt",
			            src: "url",
			            height: "numbers"
			        }
				},
				"a":  {
					set_attributes: {
						target: "_blank",
						rel:    "nofollow"
					},
					check_attributes: {
						href:   "url" // important to avoid XSS
					}
				}
			}
		}
	};

	var Wysihtml5 = function(el, options) {
		this.el = el;
		this.toolbar = this.createToolbar(el, options || defaultOptions);
		this.editor =  this.createEditor(options);
		this.initContent( el, options );
		
		window.editor = this.editor;

  		$('iframe.wysihtml5-sandbox').each(function(i, el){
			$(el.contentWindow).off('focus.wysihtml5').on({
			  'focus.wysihtml5' : function(){
			     $('li.dropdown').removeClass('open');
			   }
			});
		});
	};

	Wysihtml5.prototype = {
		constructor: Wysihtml5,

		initContent: function(el, option){


		}, 

		createEditor: function(options) {
			var parserRules = defaultOptions.parserRules; 

			if(options && options.parserRules) {
				parserRules = options.parserRules;
			}
				
			var editor = new wysi.Editor(this.el.attr('id'), {
	    		toolbar: this.toolbar.attr('id'),
				parserRules: parserRules
	  		});

	  		if(options && options.events) {
				for(var eventName in options.events) {
					editor.on(eventName, options.events[eventName]);
				}
			}	

	  		return editor;
		},
		
		createToolbar: function(el, options) {
			var self = this;
			var toolbar = $("<ul/>", {
				'id' : el.attr('id') + "-wysihtml5-toolbar",
				'class' : "wysihtml5-toolbar",
				'style': "display:none"
			});

			for(var key in defaultOptions) {
				var value = false;
				
				if(options[key] != undefined) {
					if(options[key] == true) {
						value = true;
					}
				} else {
					value = defaultOptions[key];
				}
				
				if(value == true) {
					toolbar.append(templates[key]);

					if(key == "html") {
						this.initHtml(toolbar);
					}

					if(key == "link") {
						this.initInsertLink(toolbar);
					}

					if(key == "image") {
						this.initInsertImage(toolbar);
					}
				}
			}
			
			var self = this;
			
			toolbar.find("a[data-wysihtml5-command='formatBlock']").click(function(e) {
				var el = $(e.srcElement);
				self.toolbar.find('.current-font').text(el.html())
			});
			
			this.el.before(toolbar);

			this.initInsertImageAfter(toolbar);
			
			return toolbar;
		},

		initHtml: function(toolbar) {
			var changeViewSelector = "a[data-wysihtml5-action='change_view']";
			toolbar.find(changeViewSelector).click(function(e) {
				toolbar.find('a.btn').not(changeViewSelector).toggleClass('disabled');
			});
		},

		showed: false, 

		initInsertImage: function(toolbar) {

			return false;
			
			var self = this;
			var insertImageModal = toolbar.find('.bootstrap-wysihtml5-insert-image-modal');
			var urlInput = insertImageModal.find('.bootstrap-wysihtml5-insert-image-url');
			var insertButton = insertImageModal.find('a.btn-primary');
			var initialValue = urlInput.val();

			var insertImage = function() { 
				var url = urlInput.val();
				urlInput.val(initialValue);
				self.editor.composer.commands.exec("insertImage", url);
			};
			
			urlInput.keypress(function(e) {
				if(e.which == 13) {
					insertImage();
					insertImageModal.modal('hide');
				}
			});

			insertButton.click(insertImage);

			insertImageModal.on('shown', function() {
				urlInput.focus();
			});

			insertImageModal.on('hide', function() { 
				self.editor.currentView.element.focus();
			});

			toolbar.find('a[data-wysihtml5-command=insertImage]').click(function() {
				insertImageModal.modal('show');
			});
		},

		initInsertImageAfter: function( toolbar ){
			this.init_comment_img( toolbar );
		}, 

		init_comment_img: function( wysi ){

			if( this.showed ){
				return false;
			}
			this.showed = true;

			var handler = SWFUploadHandlerWrapper.SWFUploadHandler;

			var post_params = {
				session_id: app_data.user.session_id
			};

			var swfu = new SWFUpload({
				upload_url : '/admin/attach/postImg',
				flash_url : '/sea-modules/gallery2/swfupload/2.2.0/swfupload.swf',

				file_post_name : "Filedata",
				post_params: post_params,
				assume_success_timeout : 0,
				file_types : "*.jpg;*.png;*.gif",
				file_types_description : 'JPG,PNG,GIF文件',
				file_size_limit : "5 MB",
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
					whsi: wysi
				}
			});
		}, 
		do_comment_img_success: function(file, serverData, responseReceived) {

			try{

				var d = $.parseJSON(serverData);
				if( !d ){
					alert( '上传图片错误' );
					return;
				}
			}catch( ex ){
				alert( '服务异常: '+ serverData );
			}

			editor.composer.commands.exec("insertImage", d.url);
		}, 


		initInsertLink: function(toolbar) {

			return false;
			var self = this;
			var insertLinkModal = toolbar.find('.bootstrap-wysihtml5-insert-link-modal');
			var urlInput = insertLinkModal.find('.bootstrap-wysihtml5-insert-link-url');
			var insertButton = insertLinkModal.find('a.btn-primary');
			var initialValue = urlInput.val();

			var insertLink = function() { 
				var url = urlInput.val();
				urlInput.val(initialValue);
				self.editor.composer.commands.exec("createLink", { 
					href: url, 
					target: "_blank", 
					rel: "nofollow" 
				});
			};
			var pressedEnter = false;

			urlInput.keypress(function(e) {
				if(e.which == 13) {
					insertLink();
					insertLinkModal.modal('hide');
				}
			});

			insertButton.click(insertLink);

			insertLinkModal.on('shown', function() {
				urlInput.focus();
			});

			insertLinkModal.on('hide', function() { 
				self.editor.currentView.element.focus();
			});

			toolbar.find('a[data-wysihtml5-command=createLink]').click(function() {
				insertLinkModal.modal('show');
			});
		}
	};

	$.fn.wysihtml5 = function (options) {
		return this.each(function () {
			var $this = $(this);
	      	$this.data('wysihtml5', new Wysihtml5($this, options));
	    })
  	};

  	$.fn.wysihtml5.Constructor = Wysihtml5;

});
