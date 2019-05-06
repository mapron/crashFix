<?php
$this->pageTitle=Yii::app()->name . ' - Upload Debug Info';
$this->breadcrumbs=array(
    'Upload new file',
);

?>

<!-- Project Selection Form -->
<div class="form">

<?php echo CHtml::beginForm(array("debugInfo/uploadFile/?project=".urlencode($data["project"])."&version=".$data["version"]."&arch=".$data["arch"]), 'post', array('enctype'=>'multipart/form-data')); ?>

	<div class="row span-27 last">
		7z archive with debug info or binaries files: <?php echo CHtml::fileField('fileAttachment', '', ['accept'=>".7z"]); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Upload'); ?>
	</div>

<?php echo CHtml::endForm(); ?>

</div><!-- form -->

</div>