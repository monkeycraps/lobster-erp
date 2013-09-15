<?php
class MyModelFormatter implements RedBean_IModelFormatter {
    public function formatModel($model) {

    	$arr = explode( '_', $model );
    	foreach( $arr as $key=>$one ){
    		$arr[$key] = ucfirst($one);
    	}
    	$model = implode( '', $arr );
        return $model.'Model';
    }
}