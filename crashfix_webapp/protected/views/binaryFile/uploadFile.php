<?php
$this->pageTitle=Yii::app()->name . ' - Upload Binary Files';
$this->breadcrumbs=array(
    'Upload new file',
);

?>

<!-- Project Selection Form -->
<div class="span-26 last" id="div_proj_selection">	
	<?php echo CHtml::beginForm(array('site/setCurProject'), 'get', array('id'=>'proj_form')); ?>
	<div class="span-18 last">
		Current Project:
		<?php 		
			$models = Yii::app()->user->getMyProjects();
			$projects = CHtml::listData($models, 'id', 'name');			
			echo CHtml::dropDownList('proj', array('selected'=>Yii::app()->user->getCurProjectId()), $projects); 			
		?>	
		</div>
	<?php echo CHtml::endForm(); ?>		
</div>

<div class="span-18 prepend-top last">
 
<div class="form">

<?php echo CHtml::beginForm(array('binaryFile/uploadFile'), 'post', array('enctype'=>'multipart/form-data')); ?>
	<div class="row">
		Version: <?php echo CHtml::textField('version'); ?>
	</div>
	
	<div class="row">
		7z archive with PE files: <?php echo CHtml::fileField('fileAttachment'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Upload'); ?>
	</div>

<?php echo CHtml::endForm(); ?>		

</div><!-- form -->

</div>