<div id='modal-list'>
	<div class="modal fade" id="modal-login">
		<div class="modal-dialog">
			<form class='form-inline' action='/login' method='post'>
				<div class="modal-content">
					<?php echo Helper\Html::authToken() ?>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"
							aria-hidden="true">&times;</button>
						<h4 class="modal-title">登录</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="iptname">用户名</label> <input type="text" name='name'
								class="form-control" id="iptname" placeholder="请输入用户名">
						</div>
						<div class="form-group">
							<label for="iptpwd">密码</label> <input type="password" name='pwd'
								class="form-control" id="iptpwd" placeholder="请输入密码">
						</div>
					</div>
					<div class="modal-footer">
						<input type="submit" class="btn btn-primary" value='登录'>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>