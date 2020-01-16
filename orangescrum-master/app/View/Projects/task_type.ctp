<div class="user_profile_con">
<!--Tabs section starts -->
<?php echo $this->element("company_settings");?>
</div>
<?php if (isset($task_types) && !empty($task_types)) {?>
<div class="impexp_div" style="border:0;margin-top:0;margin-bottom:5px;">
     <div class="note_txt fl">
        <?php echo __("12 Default Task Types listed and the #of Tasks associated with them. You can remove any of them by unchecking the checkbox and save the changes."); ?><br/><?php echo __("Click on the"); ?> "<b>+ <?php echo __('New Task Type'); ?></b>" <?php echo __("and add your new Task Types."); ?>
    </div>
    <div class="fr"><button class="btn btn_blue" onclick="addNewTaskType();" style="padding: 5px;margin-right: 3px !important;margin-top:25px">+ <?php echo __("New Task Type"); ?></button></div>
    <div class="cb"></div>
</div>
<div class="fl import-csv-file" style="border:1px solid #ccc;width:100%;">
    <form name="task_types" id="task_types" method="post" action="javascript:void(0);">
    <?php 
    $cnt = 1;
    $custom = 0;
    $default = 0;
    $t_key = 0;
    foreach ($task_types as $key => $value) {
	$t_key = $key+1;
	if ($cnt%3 == 0) {
	    $cb = '<div class="cb"></div>';
	} else {
	    $cb = "";
	}
	
	$checked = 'checked="checked"';
	if (isset($sel_types) && !empty($sel_types)) {
	    if (intval($value['Type']['is_exist'])) {
		$checked = 'checked="checked"';
	    } else {
		$checked = '';
	    }
	}
	if (intval($value['Total']['cnt'])) {
	    //$disabled = 'disabled="true"';
	    $isDelete = 0;
	} else {
	    $isDelete = 1;
	    //$disabled = '';
	}
	?>
	
	<?php 
		if($value['Type']['company_id'] != 0 && !$custom){  
		    $custom = 1;		    
	?>
	<div style="padding: 5px 0;">
	    <div style="padding-bottom: 3px;margin-bottom: 10px;font-weight: bold;color:#38B1E2;font-size:15px;border-bottom:1px dotted #CCCCCC"><?php echo __("Custom Task Type"); ?></div>	
	<?php }else if($value['Type']['company_id'] == 0 && !$default){ $default = 1;?>  
	   <div style="padding: 5px 0;">
	       <div style="padding-bottom: 3px;margin-bottom: 10px;font-weight: bold;color:#38B1E2;font-size:15px;border-bottom:1px dotted #CCCCCC"><?php echo __("Default Task Type"); ?></div>
	<?php }?>
	
	<div class="fl" style="width: 32%;padding-left:2px;" id="dv_tsk_<?php echo $value['Type']['id'];?>">
	    <div class="fl dv_tsktyp" style="min-width: 10%;width: auto;" data-id="<?php echo $value['Type']['id'];?>">
		<div class="fl">
		    <label style="cursor: pointer;">
			<div class="fl">
			    <input type="checkbox" class="all_tt" value="<?php echo $value['Type']['id'];?>" name="data[Type][<?php echo $value['Type']['id'];?>]" <?php echo $checked;?> <?php echo $disabled;?>/>
			</div>
			<div class="fl" style="margin:3px 0 0 10px;">
			    <span style="<?php if (intval($value['Type']['company_id'])){ ?>color: #666666;<?php } else {?>color: #999;<?php }?>"><b><?php echo __($value['Type']['name'], true); ?></b></span>
			    <span style="margin-left: 3px;font-weight: normal;">
				(<?php echo $value['Type']['short_name'];?>)
			    </span>
			</div>
			<div class="cb"></div>
		    </label>
		</div>
        
		<?php if (intval($value['Total']['cnt'])) {?>
		<div class="fl task-type-cnt" title="<?php echo $value['Total']['cnt'].__(' Task(s)');?>"><?php echo $value['Total']['cnt'];?></div>
		<?php }?>
		<?php if (intval($value['Type']['company_id'])){ ?>
		<div class="fl" id="edit_dvtsk_<?php echo $value['Type']['id'];?>" style="padding: 3px 3px 3px 6px;display: none;">
		    <span id="edit_lding_tsk_<?php echo $value['Type']['id'];?>" style="display: none;">
			<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="Loading..." />
		    </span>
		    <span id="edit_tsk_<?php echo $value['Type']['id'];?>">
			<a href="javascript:void(0);" onclick="editTaskType(this);" data-name="<?php echo $value['Type']['name'];?>" data-id="<?php echo $value['Type']['id'];?>" data-sortname="<?php echo $value['Type']['short_name'];?>">
			    <img src="<?php echo HTTP_IMAGES; ?>images/type_edit.png" alt="Edit" title="Edit" />
			</a>
		    </span>
		</div>
		<?php } ?>
		<?php if (intval($value['Type']['company_id']) && $isDelete){ ?>
		<div class="fl" id="del_dvtsk_<?php echo $value['Type']['id'];?>" style="padding: 3px 3px 3px 4px;display: none;">
		    <span id="lding_tsk_<?php echo $value['Type']['id'];?>" style="display: none;">
			<img src="<?php echo HTTP_IMAGES; ?>images/del.gif" alt="Loading..." title="Loading..." />
		    </span>
		    <span id="del_tsk_<?php echo $value['Type']['id'];?>">
			<a href="javascript:void(0);" onclick="deleteTaskType(this);" data-name="<?php echo $value['Type']['name'];?>" data-id="<?php echo $value['Type']['id'];?>">
			    <img src="<?php echo HTTP_IMAGES; ?>images/close_hover.png" alt="Delete" title="Delete" />
			</a>
		    </span>
		</div>
		<?php } ?>
	    </div>
	    <div class="cb"></div>
	</div>
	<?php echo $cb;?>	
	<?php if((intval($task_types[$t_key]['Type']['company_id']) == 0) && ($default == 0)){ $cnt = 0; ?>
         </div>	  
	 <div class="cb"></div>
	<?php } else if($key == (count($task_types)-1)){ ?>
	</div>		
	<?php } ?>
    <?php 
	$cnt++;
    }?>
    <div class="import_btn_div fl" style="width: 100%;height: 60px;">
	<img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..."  id="loader_img_tt" style="display: none;position: absolute;"/>
	<button type="button" id="tt_save_btn" name="tt_save_btn" class="btn btn_blue" onclick="return saveTaskType();">
	    <i class="icon-big-tick"></i>
	    <span style="color: #fff;"><?php echo __("Save"); ?></span>
	</button>
    </div>
    </form>
</div>
<div class="cb"></div>
<?php }
else {
?>
<div class="impexp_div" style="border:none">
	<span style="font-size:13px;"><?php echo __("Task Types are independent of Projects, but please create a Project to get started."); ?></span>
    <br/><br/>
   	<button onclick="newProject();" type="button" class="btn btn_blue"><?php echo __("Create Project"); ?></button>
    
</div>

<?php	
}
?>

<script type="text/javascript">
    $(document).ready(function(){
	$('.dv_tsktyp').hover(function(){
	    var tid = $(this).attr('data-id');
	    if ($(this).find("#del_dvtsk_"+tid).length || $(this).find("#edit_dvtsk_"+tid).length) {
		$(this).find("#del_dvtsk_"+tid).show();
		$(this).find("#edit_dvtsk_"+tid).show();		
	    }
	}, function(){
	    var tid = $(this).attr('data-id');
	    if ($(this).find("#del_dvtsk_"+tid).length || $(this).find("#edit_dvtsk_"+tid).length) {
		$(this).find("#del_dvtsk_"+tid).hide();
		$(this).find("#edit_dvtsk_"+tid).hide();
	    }
	});
    });
</script>
