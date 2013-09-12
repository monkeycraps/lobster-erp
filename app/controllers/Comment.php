<?php
class CommentController extends ApplicationController {
	protected $layout = 'frontend';

	function indexAction(){
		$id = $this->get( 'id' );
		if( !$id || !($mission = R::findOne( 'mission', 'id = ?', array( $id ) )) ){
			throw new Exception( 'no mission', 404 );
		}

		$list = array();
		$attach_list = array();
		if( $tmp = R::getAll( 'select c.*, 
			u.name as uname
			from comment c
			inner join user u on c.uid = u.id
			where c.deleted is null and c.mission_id = ? and (c.replyto is null or c.replyto = 0) order by c.id asc ', array( $id ) ) ){

			$ids = array();
			foreach( $tmp as $one ){

				$ids[] = $one['id'];

				$one['created'] = Helper\Html::date( $one['created'] );
				$one['updated'] = Helper\Html::date( $one['updated'] );

				$list[$one['id']] = $one;
			}

			if( $ids && $sub_tmp = R::getAll( 'select c.*, 
				u.name as uname
				from comment c 
				inner join user u on c.uid = u.id
				where c.deleted is null and c.replyto in( '. implode( ',', $ids ). ') order by c.id asc', array( $id ) ) ){

				foreach( $attach_tmp as $one ){
					!isset( $list[$one['replyto']]['reply_comment'] ) && $list[$one['replyto']]['reply_comment'] = array();
					$list[$one['replyto']]['reply_comment'][] = $one;
				}
			}

			if( $ids && $attach_tmp = R::getAll( 'select a.*
				from attach a where a.deleted is null and
				a.comment_id in ('. implode( ',', $ids ). ') order by a.id asc', array( $id ) ) ){

				foreach( $attach_tmp as $one ){
					!isset( $list[$one['comment_id']]['attach'] ) && $list[$one['comment_id']]['attach'] = array();
					$list[$one['comment_id']]['attach'][] = $one;
				}
			}
		}

		$this->list = $list;

		echo $this->renderPartial( 'comment/list' );
	}

	public function postAction() {

		if( !($id = $this->post( 'id' )) || !($model = R::findOne( 'comment',  'id = ?', array( $id ) ) ) ){
			$model = R::dispense( 'comment' );
			$model->mission_id = $this->post( 'mission_id' );
			$model->uid = $this->user->id;
			$model->created = Helper\Html::now();
		}else{
			if( $model->uid != $this->user->id ){
				throw new Exception( 'not your comment', 403 );
			}
		}
		$model->content = $this->post( 'content' );
		$model->replyto = $this->post( 'replyto', 0 );
		$model->updated = Helper\Html::now();
		R::store( $model );

		$attach = R::getAll( 'select a.*
				from attach a where a.deleted is null and
				a.comment_id = ? order by a.id asc', array( $model->id ) );

		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'uname'=>UserModel::getName( $model->uid ), 	
			'created'=>Helper\Html::date( $model->created ), 
			'updated'=>Helper\Html::date( $model->updated ), 
			'content'=>Comment\Comment::show( $model->content, $attach ), 
		)));
	}

	public function postImgAction() {

		if( !($id = $this->post( 'id' )) || !($model = R::findOne( 'comment',  'id = ?', array( $id ) ) ) ){
			$model = R::dispense( 'comment' );
			$model->mission_id = $this->post( 'mission_id' );
			$model->uid = $this->user->id;
			$model->created = Helper\Html::now();
		}else{
			if( $model->uid != $this->user->id ){
				throw new Exception( 'not your comment', 403 );
			}
		}

		if( !isset( $_FILES["Filedata"] ) ){
			throw new Exception( 'no upload file', 412 );
		}


		if ( ($_FILES["Filedata"]["size"] < 3000000 ) ){

			if ($_FILES["Filedata"]["error"] > 0){
				throw new Exception( $_FILES["Filedata"]["error"], 412 );
			} else {
				$arr = explode( '.', $_FILES["Filedata"]["name"] );
				$ext = strtolower( $arr[count($arr)-1] );

				switch( $ext ){
					case 'jpg':
					case 'jpeg':
					case 'png':
					case 'gif': 
						break;
					default:
						@unlink( $_FILES["Filedata"]["tmp_name"] );
						throw new Exception( 'invalid file', 412 );
						break;
				}

				$filename = uniqid( 'atta_' ). md5( time() ). '.'. $ext;
				$path = APP_PATH. "/public/attach/img/". $filename;
				move_uploaded_file( $_FILES["Filedata"]["tmp_name"], $path );

				switch( $ext ){
					case 'jpg':
					case 'jpeg':
						$source = imagecreatefromjpeg( $path );
						break;
					case 'png':
						$source = imagecreatefrompng( $path );
						break;
					case 'gif': 
						$source = imagecreatefromgif( $path );
						break;
					default:
						break;
				}
				list( $src_w, $src_h ) = getimagesize( $path );
				$w = intval( $src_w );
				$h = intval( $src_h );

				if( $w > 800 || $h > 800 ){

					$filename = uniqid( 'atta_' ). md5( time() ). '.'. $ext;
					$path_new = APP_PATH. "/public/attach/img/". $filename;

					if( $w > $h ){
						$scale = 800/$w;
					}else{
						$scale = 800/$h;
					}

					$final_w = intval( $w * $scale );
					$final_h = intval( $h * $scale );
					$target = imagecreatetruecolor( $final_w, $final_h );

					imagecopyresampled( $target, $source, 0, 0, 0, 0, $final_w, $final_h, $w, $h );

					// 保存
					imagejpeg($target, $path_new);
					imagedestroy($target);

					unlink( $path );

					$w =  $final_w;
					$h =  $final_h;

					$source = imagecreatefromjpeg( $path_new );
				}

				$url = '/attach/img/'. $filename;
				$size = $_FILES["Filedata"]["size"];

				if( $w > 200 || $h > 200 ){

					$filename = uniqid( 'atta_' ). '.'. $ext;
					$path_new = APP_PATH. "/public/attach/img/". $filename;

					if( $w > $h ){
						$scale = 200/$w;
					}else{
						$scale = 200/$h;
					}

					$final_w = intval( $w * $scale );
					$final_h = intval( $h * $scale );
					$target = imagecreatetruecolor( $final_w, $final_h );

					imagecopyresampled( $target, $source, 0, 0, 0, 0, $final_w, $final_h, $w, $h );

					// 保存
					imagejpeg($target, $path_new);
					imagedestroy($target);

					$small_url = '/attach/img/'. $filename;
				}else{
					$small_url = $url;
				}

			}
		} else {
			throw new Exception( 'invalid file', 412 );
		}

		$attach = R::dispense( 'attach' );
		$attach->type = 'img';
		$attach->url = $url;
		$attach->small_url = $small_url;
		$attach->comment = '';
		$attach->size = $size;
		$attach->created = Helper\Html::now();
		$id = R::store( $attach );

		$model->content = "[:attach-{$id}]";
		$model->replyto = $this->post( 'replyto', 0 );
		$model->updated = Helper\Html::now();
		$id = R::store( $model );

		$attach->comment_id = $id;
		R::store( $attach );

		$attach = R::getAll( 'select a.*
				from attach a where a.deleted is null and
				a.comment_id = ? order by a.id asc', array( $model->id ) );

		$this->renderJson ( array_merge ( $model->getIterator ()->getArrayCopy (), array (
			'uname'=>UserModel::getName( $model->uid ), 	
			'created'=>Helper\Html::date( $model->created ), 
			'updated'=>Helper\Html::date( $model->updated ), 
			'content'=>Comment\Comment::show( $model->content, $attach ), 
		)));
	}

	function deleteAction(){
		if( !($id = $this->post( 'id' )) || !($model = R::findOne( 'comment',  'id = ?', array( $id ) ) ) ){
			throw new Exception( 'no comment', 403 );
		}
		$model->deleted = Helper\Html::now();
		R::store( $model );
	}
}