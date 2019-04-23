<?php
$this->pageTitle=Yii::app()->name . ' - View Report #'.CHtml::encode($model->id);
$this->breadcrumbs=array(
	'Crash Reports'=>array('index'),
	'View Report #'.$model->id,
);

?>

<div class="span-27 prepend-top last">

<?php
$params = array('model'=>$model, 'stackFrames'=>$stackFrames);
$this->renderPartial('_viewSummary', $params);
?>

</div>