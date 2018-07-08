<?php
$this->pageTitle=Yii::app()->name . ' - Browse Binary Files';
$this->breadcrumbs=array(
	'Binary Files',
);

?>
<p>Binary files (PE format) is used for symbolizing x86_64 crash dumps. </p>
<?php if(count(Yii::app()->user->getMyProjects())==0): ?>

You have no projects assigned.

<?php else: ?>

<!-- Project Selection Form -->
<div class="span-27 last" id="div_proj_selection">	
	<?php echo CHtml::beginForm(array('site/setCurProject'), 'get', array('id'=>'proj_form')); ?>
	<div class="span-27 last">
		Current Project:
		<?php 		
			$models = Yii::app()->user->getMyProjects();
			$projects = CHtml::listData($models, 'id', 'name');			
			echo CHtml::dropDownList('proj', array('selected'=>Yii::app()->user->getCurProjectId()), $projects); 			
		?>		
		Version:
		<?php 		
			$selVer = -1;
			$versions = Yii::app()->user->getCurProjectVersions($selVer);			
			echo CHtml::dropDownList('ver', array('selected'=>$selVer), $versions); 
		?>			
			
		<input type="submit" value="Ok" />
	</div>
	<?php echo CHtml::endForm(); ?>
</div>


<div class="span-27 last" >
<?php echo CHtml::beginForm(array('debugInfo/deleteMultiple'), 'post', array('name'=>'del_form')); ?>

<!-- Actions Toolbar -->
<div class="span-27 last">
	<div class="div_actions">
		<?php echo CHtml::link('Upload New File', $this->createUrl('binaryFile/uploadFile')); ?>
		
		<?php 
			echo CHtml::linkButton("Delete Selected", 
					array(
						'id'=>'delete_selected', 
						'form'=>'del_form',
						'confirm'=>"Are you sure you want to permanently delete selected debug info file(s)?"
					)); 
		?>		
	</div>	
</div>

<!-- Grid view -->
<div class="span-27 last">
    
<?php $this->widget('zii.widgets.grid.CGridView', array(
      'dataProvider'=>$dataProvider,
	  'selectableRows'=>null,
      'columns'=>array(
          array(            
              'name' => 'version',
			  'type' => 'raw',
			  'value' => '$data["name"]',	  			  			  
          ),
		  array(
			  'header'=>'Size',			  
              'name'=>'filesize',
              'value'=>'MiscHelpers::fileSizeToStr($data[\'size\'])',
			  'cssClassExpression' => '"column-right-align"',
          ),
          array(
             'name'=>'actions',
             'type' => 'raw',
             'value'=>'CHtml::link("delete", \'delete/?id=\'.$data["name"], ["confirm"=>"Are you sure you want to permanently delete selected debug info file(s)?"])',              
          ),          
      ),
 )); 
  
 ?>
 </div>
  
 <?php echo CHtml::endForm(); ?>
 </div>


 <?php 
 $script = <<<SCRIPT

$("#proj, #ver").bind('change', function(e)
{	
	$("#proj_form").submit();
});
SCRIPT;
 
 Yii::app()->getClientScript()->registerScript("BinaryFile", $script, CClientScript::POS_READY); ?>


<?php endif; ?> 
