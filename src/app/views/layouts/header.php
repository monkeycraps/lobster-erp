<header class="" >
	<div id="top-bar">
		<div class="row" >
			<div class='col-2 border'>
				<div class=''>
					<h1 class='logo pull-left'>合道OA <span style='font-size: 12px;'>BETA0.8</span></h1>
				</div>
			</div>
			<div class='col-5 border'>
				<ul class='list-inline' style='margin: 15px;'>
					<li><a href='/mission'>任务堂</a></li>
					<li><a href='/'>公告板</a></li>
					<li><a href='/tools'>工具箱</a></li>
					<li><a href='#'>合道百科</a></li>
				</ul>
			</div>
			<div class='col-5 row' style='padding-right: 0px;'>
				<div class='col-lg-6 pull-left' style='margin: 8px; overflow: hidden; padding: 0px;'>
					<div class='announce'>
						<b>【公告】</b>XXXXXXXXXXXX  7月23日 星期五
					</div>
					<div class='depot-left overflow: hidden;'>
						<b>【库存】</b>缺货产品 AR160 XXXX 7月23日 星期五
					</div>
				</div>
				<div class='col-lg-5' style='padding: 0px; height: 34px;'>
					<?php if( $controller->user->id ){
						echo $this->render( 'layouts/header_user.php', array( 'controller'=>$controller ) );
					} else {
						echo $this->render( 'layouts/header_guest.php', array( 'controller'=>$controller ) );
					} ?>
				</div>
			</div>
		</div>
	</div>
</header>