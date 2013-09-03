<header class="" >
	<div id="top-bar">
		<div class="row" >
			<div class='col-2 border'>
				<div class='col-4'>
					<h1 class='logo pull-left'>合道OA</h1>
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
			<div class='col-5 border'>
				<div class='pull-left' style='margin: 8px;'>
					<div class='announce'>
						<b>【公告】</b>XXXXXXXXXXXX  7月23日 星期五
					</div>
					<div class='depot-left'>
						<b>【库存】</b>缺货产品 AR160 XXXX  XXXX   7月23日 星期五
					</div>
				</div>
				<?php if( $controller->user->id ){
					echo $this->render( 'layouts/header_user.php', array( 'controller'=>$controller ) );
				} else {
					echo $this->render( 'layouts/header_guest.php', array( 'controller'=>$controller ) );
				} ?>
			</div>
		</div>
	</div>
	<?php if( !isset($controller->ishome) ){ ?>
		<div class='navbar row' id='form-tool-bar' style='height: 45px;'>
			<div class='col-2 border' style='padding: 10px;'>
				<div class='col-inner'>
					<div class="input-group">
						<span class="input-group-addon"><i class='glyphicon glyphicon-search'></i></span>
						<input id='search-data' name='search' placeholder='请输入单号' />
					</div>
				</div>
			</div>
			<div class='col-5 border' style='height: 45px'>
				<div class='col-inner'>
					
				</div>
			</div>
			<div class='col-5 border' style='padding-top: 5px;'>
				<div class='form-success bs-callout bs-callout-success mchide' style='margin: 0px;'>
					保存成功
				</div>
			</div>
		</div>
	<?php }?>
</header>