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
					<li><a href='http://112.124.17.199:8000/' target='_blank'>合道百科</a></li>
					<!-- <li><a href='/admin'>后台</a></li> -->
				</ul>
			</div>
			<div class='col-5 row' style='padding-right: 0px;'>
				<div class='col-lg-6 pull-left announce-and-stackout' style='margin: 3px 8px;; overflow: hidden; padding: 0px;'>
					<?php if( $controller->user->id ) {?>

						<ul class='announce' style='height: 40px;'>
							<?php list( $announce_list ) = AnnounceModel::getListHeader( 2 ); 
								$first = true; foreach( $announce_list as $one ){ ?>
								<li class='<?php echo $first ? 'current' : '' ?>'>
									<a href='/index?id=<?php echo $one['id']?>'><?php echo $one['subject'] ?> <?php echo date( 'm月d日', strtotime($one['created']) ) ?> 
									<?php echo Helper\Html::dateOfWeek( $one['created'] ) ?></a></li>
							<?php $first = false; } ?>
						</ul>
					<?php }?>
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