<?php 
class MyModelFormatter implements RedBean_IModelFormatter {
    public function formatModel($model) {
        return $model.'Model';
    }
}