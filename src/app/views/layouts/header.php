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
	<?php if( !isset($controller->ishome) ){ ?>
		<div class='navbar row' style='height: 45px;' id='form-tool-bar'>
			<div class='col-2 border' style='padding: 10px;'>
				<div class='col-inner'>
					<div class="input-group">
						<span class="input-group-addon"><i class='glyphicon glyphicon-search'></i></span>
						<input id='search-data' name='search' placeholder='请输入单号' />
					</div>
				</div>
			</div>
			<div class='col-5 border' style='height: 45px; padding-top: 7px;'>
				<div class='list-success bs-callout bs-callout-success mchide col-5' style='margin: 0px;'>
					保存成功
				</div>
				<div class='list-message bs-callout bs-callout-info mchide col-5' style='margin: 0px;'>
					
				</div>
				<div class='col-5 pull-right' style='padding-top: 7px;' >
					<ul class='pull-right list-inline'>
						<li><a class='word-btn word-btn-success batch-submit'>提交</a></li>
						<li><a class='list-refresh' href='#' data-toggle="tooltip" data-placement="bottom" title="" data-original-title="刷新"><i class='glyphicon glyphicon-refresh'></i></a></li>
					</ul>
				</div>
			</div>
			<div class='col-5 row' style='padding-top: 5px;'>
				<div class='form-success bs-callout bs-callout-success mchide col-5' style='margin: 0px;'>
					保存成功
				</div>
				<div class='form-message bs-callout bs-callout-info mchide col-5' style='margin: 0px;'>
					
				</div>
				<div class='col-5 pull-right' style='padding-top: 7px;' >
					<ul class='pull-right list-inline'>
						<li><a class='form-refresh' href='#' data-toggle="tooltip" data-placement="bottom" title="" data-original-title="刷新"><i class='glyphicon glyphicon-refresh'></i></a></li>
						<li><a href='#' class='mission-type-change' data-toggle="tooltip" data-placement="bottom" title="" data-original-title="更改任务类型"><i class='glyphicon glyphicon-cog'></i></a></li>
						<li><a href='#' data-toggle="tooltip" data-placement="bottom" title="" data-original-title="编辑"><i class='glyphicon glyphicon-edit'></i></a></li>
						<li><a href='#' data-toggle="tooltip" data-placement="bottom" title="" data-original-title="历史记录"><i class='glyphicon glyphicon-briefcase'></i></a></li>
					</ul>
				</div>
			</div>
		</div>
	<?php }?>
</header>