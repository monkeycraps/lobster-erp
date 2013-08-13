<header class="">
	<div id="nav-header">
		<div class="row">
			<div class='col-3 border'>
				<h1 class='logo pull-left'>合道电器</h1>
				<div class="dropdown pull-right" style='margin: 15px 10px;'>
  					<a data-toggle="dropdown" href="#">
  						<span class="glyphicon glyphicon-chevron-down">&nbsp;</span>
  					</a>
					<ul class="dropdown-menu">
						<li class="project-item project-275cdfc39019 selected">
							<div class="pull-right hide setting">
								<a class="edit-project"><i class="icon cog"></i></a>
							</div> 
							<a href="#" class="name">用户教程项目</a>
						</li>
						<li class="project-item project-954fc06f3edc ">
							<div class="pull-right hide setting">
								<a class="edit-project"><i class="icon cog"></i></a>
							</div> 
							<a href="/projects/954fc06f3edc" class="name">
								<span>monkeycraps</span>
							</a>
						</li>
						<li class="project-item project-acf5b9551580 ">
							<div class="pull-right hide setting">
								<a class="edit-project"><i class="icon cog"></i></a>
							</div> <a href="/projects/acf5b9551580" class="name"><span>todo</span></a>
						</li>
						<li class="project-item project-6b12321bf1ad ">
							<div class="pull-right hide setting">
								<a class="edit-project"><i class="icon cog"></i></a>
							</div> <a href="/projects/6b12321bf1ad" class="name"><span>协作任务</span></a>
						</li>
	
						<li><a class="new-project"> <i class="icon new"></i>创建新项目
						</a></li>
					</ul>
				</div>
			</div>
			<div class='col-5 border'>
			</div>
			<div class='col-4 border'>
				<?php if( $controller->user->id ){
					echo $this->render( 'layouts/header_user.php' );
				} else {
					echo $this->render( 'layouts/header_guest.php' );
				} ?>
			</div>
		</div>
	</div>
</header>