<div class="pull-right"  style='margin: 15px 0px;'>
	<div class='others dropdown' id='menu-message' style='margin-right: 10px; float: left' >
		<a data-toggle="dropdown" href="#">
			<span>消息</span><span class='message_cnt'></span>
			<span class="glyphicon glyphicon-chevron-down">&nbsp;</span>
		</a>
		<ul class="dropdown-menu message-list" aria-labelledby='menu-message'>
			<li><a href="#" class='open_message' data-toggle="tooltip" title='查看所有' data-placement='bottom' > <span class='glyphicon glyphicon-folder-open'></span> </a></li>
			<li><a href="#" class='check_all' data-toggle="tooltip" title='全部标记为已读' data-placement='bottom' > <span class='glyphicon glyphicon-check'></span> </a></li>
		</ul>
	</div>
	<div class="message dropdown pull-right" id='menu-user' style='margin-right: 20px; float: left' >
		<a data-toggle="dropdown" href="#">
			<span><?php echo $controller->user->username ?></span>
			<span class="glyphicon glyphicon-chevron-down">&nbsp;</span>
		</a>
		<ul class="dropdown-menu" aria-labelledby='menu-user'>
			<li><a href="/login/logout"> <i class="icon logout"></i>登出
			</a></li>
		</ul>
	</div>
</div>