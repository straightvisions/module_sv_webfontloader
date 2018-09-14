<p><strong><?php _e('Please reload page after new fonts have been uploaded.', $this->get_module_name()); ?></strong></p>
<?php
	if($setting->get_data()){
	    echo '<div style="max-height:200px;overflow:auto;">';
		foreach($setting->get_data() as $attach){
			echo '<p><a href="'.get_edit_post_link($attach->ID).'" target="_blank">'.$attach->post_title.'</a></p>';
		}
		echo '</div>';
	}else{
		echo '<p>'.__('No fonts uploaded yet', $this->get_module_name()).'</p>';
	}
?>