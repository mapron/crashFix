<?php
$this->pageTitle=Yii::app()->name . ' - Browse Debug Info';
$this->breadcrumbs=array(
	'Debug Info',
);

?>
<div class="span-27 last">
<p>Here you can see debug information stats, grouped by project version and architecture.</p>
</div>
<?php if(count(Yii::app()->user->getMyProjects())==0): ?>

You have no projects assigned.

<?php else: ?>

<!-- Project Selection Form -->
<div class="span-27 last" id="div_proj_selection">
	<?php echo CHtml::beginForm(array('site/setCurProject'), 'get', array('id'=>'proj_form')); ?>
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
	<?php echo CHtml::endForm(); ?>

	<?php echo CHtml::beginForm(array('debugInfo/addVersion'), 'post'); ?>
		Add new version:
		<?php
            echo CHtml::textField('version');
		    echo CHtml::submitButton();
		?>
		<b>Warning!</b> Project version will be created immediately. Removing versions is not supported yet. Proper version management will be added in further releases.
	<?php echo CHtml::endForm(); ?>
</div>


<div class="span-27 last" >
<?php echo CHtml::beginForm(array('debugInfo/deleteMultiple'), 'post', array('name'=>'del_form')); ?>


<!-- Grid view -->
<div class="span-27 last">

<?php $this->widget('zii.widgets.grid.CGridView', array(
      'dataProvider'=>$dataProvider,
	  'selectableRows'=>null,
      'columns'=>array(
          array(
              'header'=>'Version',
              'name' => 'version',
			  'type' => 'raw',
              'footer'=> 'Total:',
          ),
          array(
              'header'=>'Arch',
              'name' => 'arch',
              'type' => 'raw',
          ),
		  array(
			  'header'=>'Size',
              'value'=>'MiscHelpers::fileSizeToStr($data[\'size\'])',
		      'footer'=>MiscHelpers::fileSizeToStr($dataProvider->getTotalSize()),
          ),
          array(
             'name'=>'action_delete',
             'type' => 'raw',
             'value'=>'$data["size"] > 0 ? CHtml::link("delete", "delete/?project=".urlencode($data["project"])."&version=".$data["version"]."&arch=".$data["arch"], ["confirm"=>"Are you sure you want to permanently delete selected debug info file(s)?"]) : ""',
          ),
          array(
              'name'=>'action_add',
              'type' => 'raw',
              'value'=>'CHtml::link("upload new file", "uploadFile/?project=".urlencode($data["project"])."&version=".$data["version"]."&arch=".$data["arch"])',
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

 Yii::app()->getClientScript()->registerScript("DebugInfo", $script, CClientScript::POS_READY); ?>


<?php endif; ?>
