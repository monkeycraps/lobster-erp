<div class="pull-right"  style='margin: 15px 0px;'>
	<div class="message dropdown pull-right" id='menu-user' style='margin-right: 20px;' >
		<a data-toggle="dropdown" href="#">
			<span><?php echo $controller->user->username ?></span>
			<span class="glyphicon glyphicon-chevron-down">&nbsp;</span>
		</a>
		<ul class="dropdown-menu" aria-labelledby='menu-user'>
			<li><a href="/login/logout"> <i class="icon logout"></i>登出
			</a></li>
		</ul>
	</div>
	<div class='others pull-right' style='margin-right: 10px;' >
		<span>通知</span>
		<a href="#" data-toggle="tooltip" title='全部标记为已读' > <span class='glyphicon glyphicon-check'></span> </a>
	</div>
	<div class='others pull-right' style='margin-right: 10px;' >
		<a href='/admin'>后台</a>
	</div>
</div>