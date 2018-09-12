<p><strong><?php _e('Please reload page after new fonts have been uploaded.', $this->get_module_name()); ?></strong></p>
<?php
	if($param['uploads']){
		foreach($param['uploads'] as $attach){
			echo '<p><a href="'.get_edit_post_link($attach->ID).'" target="_blank">'.$attach->post_title.'</a></p>';
		}
	}else{
		echo '<p>'.__('No fonts uploaded yet', $this->get_module_name()).'</p>';
	}
	
?>