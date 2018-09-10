<?php
	if(current_user_can('activate_plugins')){
?>
<div class="wrap" id="sv_settings">
	<div id="sv_header">
		<div id="sv_logo"><img src="<?php echo $this->get_url('lib/img/logo.png'); ?>" /></div>
	</div>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<form method="post" action="options.php" enctype="multipart/form-data">
	<?php
		settings_fields($this->get_module_name()); // $option_group from register_settings()
		do_settings_sections($this->get_module_name()); // $page from add_settings_section()
		submit_button();
	?>
	</form>
</div>
<?php
	}
?>