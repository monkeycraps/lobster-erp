define(function(require, exports, module){

	var SWFUploadHandler = {

		fileQueueError: function(file, errorCode, message) {
			try {
				// swfupload 自带错误类型 
				switch (errorCode) {
					case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
						message = ('文件大小为 0KB， 请检查后重新上传');
						break;
					case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
						message = ('文件上传限制：'+ this.settings.file_types);
						break;
					case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
						message = ('同一时间最多上传：' + this.settings.file_queue_limit+ '个');
						break;
					case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
						message = ('文件大小超过限制，平台支持应用大小为'+ this.settings.file_size_limit +'，请联系管理员！');
						break;
					default:
						alert( "unknow errro: errorCode: ". errorCode+ "; message: "+ message );
						break;
				}
			} catch (ex) {
				this.debug("debug: "+ ex);
			}
			
			alert( message )
		}, 
		fileDialogComplete: function(numFilesSelected, numFilesQueued) {
			try {
				if (numFilesQueued > 0) {
					this.startUpload();
				}
			} catch (ex) {
				alert( ex );
				this.debug(ex);
			}
		}, 
		uploadProgress: function(file, bytesLoaded, bytesTotal) {
			try {
				var percent = Math.ceil((bytesLoaded / file.size) * 100);

				var progress = new FileProgress(file, this.customSettings.upload_target);
				progress.setProgress(percent);
				if (percent === 100) {
					var size = file.size / 1024;
					var unit = 'KB';
					if (size > 1024) {
						size = size / 1024;
						unit = 'MB';
					}
					progress.setStatus("上传完成:" + file.name + "(" + size.toFixed(2)
							+ " " + unit + ")，文件处理中");
					progress.toggleCancel(false, this);
				} else {
					progress.setStatus("上传中：" + file.name + "...");
					progress.toggleCancel(true, this);
				}
			} catch (ex) {
				this.debug(ex);
			}
		}, 
		uploadSuccess: function(file, serverData) {
			try {
				var progress = new FileProgress(file, this.customSettings.upload_target);

				if (serverData.substring(0, 7) === "FILEID:") {
					addImage("thumbnail.php?id=" + serverData.substring(7));

					progress.setStatus("Thumbnail Created.");
					progress.toggleCancel(false);
				} else {
					addImage("images/error.gif");
					progress.setStatus("Error.");
					progress.toggleCancel(false);
					alert(serverData);

				}

			} catch (ex) {
				this.debug(ex);
			}
		}, 
		uploadComplete: function(file) {
			try {
				/*
				 * I want the next upload to continue automatically so I'll call
				 * startUpload here
				 */
				if (this.getStats().files_queued > 0) {
					this.startUpload();
				} else {
					// var progress = new FileProgress(file,
					// this.customSettings.upload_target);
					// progress.setComplete();
					// progress.setStatus("All images received.");
					// progress.toggleCancel(false);
				}
			} catch (ex) {
				this.debug(ex);
			}
		}, 
		uploadError: function(file, errorCode, message) {

			try {
				// 自定义错误类型
				// message 记录的是非200 的请求返回的http code 
				switch (message) {
					case 200:
						// 正确
						break;
				}

				// swfupload 自带错误类型 
				switch (errorCode) {
					case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
					case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
					case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
					case SWFUpload.UPLOAD_ERROR.IO_ERROR:
					case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
					case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
						message = ('上传中断，请检查“上传的文件”和“网络”！code: '+ errorCode + "; message: "+ message);
						break
					case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
						message = ('上传文件大小超过限制，请联系管理员');
						break
					case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
						message = ('安全限制，请关闭您电脑的杀毒软件，或者使用别的浏览器重试');
						break
					case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
					case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
						message = ('文件上传被取消');
						break
					default:
						message = ('未知错误，请刷新页面重试');
						alert( "unknow errro: errorCode: ". errorCode+ "; message: "+ message );
						break;
				}

			} catch (ex) {
				this.debug("debug: "+ ex);
			}
			alert( message );
		}
	}

	function addImage(src) {
		var newImg = document.createElement("img");
		newImg.style.margin = "5px";

		document.getElementById("thumbnails").appendChild(newImg);
		if (newImg.filters) {
			try {
				newImg.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 0;
			} catch (e) {
				// If it is not set initially, the browser will throw an error. This
				// will set it if it is not set yet.
				newImg.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity='
						+ 0 + ')';
			}
		} else {
			newImg.style.opacity = 0;
		}

		newImg.onload = function() {
			fadeIn(newImg, 0);
		};
		newImg.src = src;
	}

	function fadeIn(element, opacity) {
		var reduceOpacityBy = 5;
		var rate = 30; // 15 fps

		if (opacity < 100) {
			opacity += reduceOpacityBy;
			if (opacity > 100) {
				opacity = 100;
			}

			if (element.filters) {
				try {
					element.filters.item("DXImageTransform.Microsoft.Alpha").opacity = opacity;
				} catch (e) {
					// If it is not set initially, the browser will throw an error.
					// This will set it if it is not set yet.
					element.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity='
							+ opacity + ')';
				}
			} else {
				element.style.opacity = opacity / 100;
			}
		}

		if (opacity < 100) {
			setTimeout(function() {
				fadeIn(element, opacity);
			}, rate);
		}
	}


	function FileProgress(file, targetID) {
		this.fileProgressID = "divFileProgress";

		this.fileProgressWrapper = document.getElementById(this.fileProgressID);
		if (!this.fileProgressWrapper) {
			this.fileProgressWrapper = document.createElement("div");
			this.fileProgressWrapper.className = "progressWrapper";
			this.fileProgressWrapper.id = this.fileProgressID;

			this.fileProgressElement = document.createElement("div");
			this.fileProgressElement.className = "progressContainer";

			var progressCancel = document.createElement("a");
			progressCancel.className = "progressCancel";
			progressCancel.href = "#";
			progressCancel.style.visibility = "hidden";
			progressCancel.appendChild(document.createTextNode(" "));

			var progressText = document.createElement("div");
			progressText.className = "progressName";
			progressText.appendChild(document.createTextNode(file.name));

			var progressBar = document.createElement("div");
			progressBar.className = "progressBarInProgress";

			var progressStatus = document.createElement("div");
			progressStatus.className = "progressBarStatus";
			progressStatus.innerHTML = "&nbsp;";

			this.fileProgressElement.appendChild(progressCancel);
			this.fileProgressElement.appendChild(progressText);
			this.fileProgressElement.appendChild(progressStatus);
			this.fileProgressElement.appendChild(progressBar);

			this.fileProgressWrapper.appendChild(this.fileProgressElement);

			document.getElementById(targetID).appendChild(this.fileProgressWrapper);
			fadeIn(this.fileProgressWrapper, 0);

		} else {
			this.fileProgressElement = this.fileProgressWrapper.firstChild;
			this.fileProgressElement.childNodes[1].firstChild.nodeValue = file.name;
		}

		this.height = this.fileProgressWrapper.offsetHeight;

	}
	FileProgress.prototype.setProgress = function(percentage) {
		this.fileProgressElement.className = "progressContainer green";
		this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
		this.fileProgressElement.childNodes[3].style.width = percentage + "%";
	};
	FileProgress.prototype.setComplete = function() {
		this.fileProgressElement.className = "progressContainer blue";
		this.fileProgressElement.childNodes[3].className = "progressBarComplete";
		this.fileProgressElement.childNodes[3].style.width = "";

	};
	FileProgress.prototype.setError = function() {
		this.fileProgressElement.className = "progressContainer red";
		this.fileProgressElement.childNodes[3].className = "progressBarError";
		this.fileProgressElement.childNodes[3].style.width = "";

	};
	FileProgress.prototype.setCancelled = function() {
		this.fileProgressElement.className = "progressContainer";
		this.fileProgressElement.childNodes[3].className = "progressBarError";
		this.fileProgressElement.childNodes[3].style.width = "";

	};
	FileProgress.prototype.setStatus = function(status) {
		this.fileProgressElement.childNodes[2].innerHTML = status;
	};

	FileProgress.prototype.toggleCancel = function(show, swfuploadInstance) {
		this.fileProgressElement.childNodes[0].style.visibility = show ? "visible"
				: "hidden";
		if (swfuploadInstance) {
			var fileID = this.fileProgressID;
			this.fileProgressElement.childNodes[0].onclick = function() {
				swfuploadInstance.cancelUpload(fileID);
				return false;
			};
		}
	};

	module.exports = {
		SWFUploadHandler: SWFUploadHandler, 
		FileProgress: FileProgress
	}
});