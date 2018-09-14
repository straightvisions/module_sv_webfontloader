<?php
	if(current_user_can('activate_plugins')){
?>
<div class="wrap" id="sv_settings">
	<div id="sv_header">
		<div id="sv_logo"><img src="<?php echo $this->get_url('lib/img/logo.png'); ?>" /></div>
	</div>
	<h1><?php echo get_admin_page_title(); ?></h1>
    <?php
		echo static::$s_fonts_upload->get_form_field();
		echo static::$settings->get_module_settings_form($this);
    ?>
</div>
<?php
	}
?>