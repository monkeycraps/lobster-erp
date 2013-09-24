<?php
class AttachController extends AdminBaseController {
	protected $layout = '';
	
	public function postImgAction() {
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

				if( $w > 1000 || $h > 1000 ){

					$filename = uniqid( 'atta_' ). md5( time() ). '.'. $ext;
					$path_new = APP_PATH. "/public/attach/img/". $filename;

					if( $w > $h ){
						$scale = 1000/$w;
					}else{
						$scale = 1000/$h;
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

		$this->renderJson ( array(
			'err'=> 0, 
			'url'=> 'http://'. $this->getRequest()->getHostName(). $url, 
		));
	}

}
