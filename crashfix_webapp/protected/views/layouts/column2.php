<?php $this->beginContent('//layouts/main'); ?>
<table><tr><td style="vertical-align: top; width:100px;">

<div class="span-4 colborder">
		<div id="sidebar">
			<?php 	$this->widget('MainMenuPortlet',
					array('activeItem'=>isset($this->sidebarActiveItem)?$this->sidebarActiveItem:''));
			?>
		</div>
    </div>

</td><td>

<div class="span-23 last">
	<div id="adminmenu">
			<?php
				if(isset($this->adminMenuItem))
				{
					$this->widget('AdminMenuPortlet',
						array('activeItem'=>$this->adminMenuItem));
				}
			?>
		</div>
	</div>

	<div class="span-27 last">
	<?php if(isset($this->breadcrumbs)):?>
		<?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs
		)); ?><!-- breadcrumbs -->
	<?php endif?>
	</div>

    <div id="content">
		<?php echo $content; ?>
	</div><!-- content -->

</td></tr></table>

<?php $this->endContent(); ?>