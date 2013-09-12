<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
namespace Comment;

class Comment {

	private static $show_path = '/img/emoji/';

	private static $emoji;

	public static function getEmoji(){
		if( self::$emoji ){
			return self::$emoji;
		}
		return self::$emoji = new \Vandpibe\Emoji\Emoji( APP_PATH. '/public/img/emoji' );
	}

	private static function parse( $str, $attach = array(), $type = null ){

		if( $attach ){
			$list = array();
			foreach( $attach as $one ){
				if( $one['type'] != 'img' ){
					continue;
				}
				$list['[:attach-'. $one['id'].']'] = '<a onclick="return false;" href="'. $one['url'] .'" ><img src="'. $one['small_url']. '" /></a>';
			}
			$str = str_replace( array_keys($list), array_values($list), $str);
		}

       	$str = preg_replace( '/\[([^\]]*)\]/', '<img src="'. self::$show_path .'$1'.$type.'.png" />', $str );

        return $str;
    }

    public static function show( $str, $attch = array(), $type = '_24' ){
    	return self::parse( $str, $attch, $type );
    }

    public static function showAll( $type = 24 ){

    	$emoji = self::getEmoji();

		$all = $emoji->all();
		$out = '<div><ul class="list-inline">';
		foreach( $all as $one ){
			if( strlen( $one ) < 3  || substr( $one, -3, 3 ) != '_'. $type ){
				continue;
			}
			$data = substr( $one, 0, strlen( $one ) - 3 );
			$out .= "<li data='{$data}'>[$one]</li>";
		}
		$out .= '</ul></div>';
    	return self::parse( $out );
    }

    

}
