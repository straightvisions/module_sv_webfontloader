<?php
if(current_user_can('activate_plugins')){
	?>
	<div class="sv_settings">
		<!--<div class="sv_side_menu">
			<a href="#" class="sv_brand"><img src="<?php //echo $this->get_url_lib_core('assets/logo.png'); ?>" /></a>
		</div>-->
		<div class="sv_content_wrapper">
			<div class="sv_content_title">
				<h1><?php echo get_admin_page_title(); ?></h1>
			</div>
			<div class="sv_content">
				<?php
					echo $this->s_fonts_upload->get_form_field();
					echo static::$settings->get_module_settings_form($this);
				?>
			</div>
		</div>
	</div>
	<?php
}
?>