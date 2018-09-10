<?php
	if(current_user_can('activate_plugins')){
?>
<div class="wrap" id="sv_settings">
	<div id="sv_header">
		<div id="sv_logo"><img src="<?php echo $this->get_url('lib/img/logo.png'); ?>" /></div>
	</div>
	<h2><?php echo get_admin_page_title(); ?></h2>
    <?php

    ?>
</div>
<?php
	}
?>