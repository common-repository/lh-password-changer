<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
<form name="lh_password_changer-admin_form" id="lh_password_changer-admin_form" method="post" action="">
<input type="hidden" name="<?php echo $this->hidden_field_name; ?>" id="<?php echo $this->hidden_field_name; ?>" value="Y" />
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="<?php echo $this->page_id_field; ?>"><?php _e('Password Change Page Id:', $this->namespace ); ?></label></th>
<td><input type="number" name="<?php echo $this->page_id_field; ?>" id="<?php echo $this->page_id_field; ?>" value="<?php echo $this->options[ $this->page_id_field ]; ?>" size="10" /><a href="<?php echo get_permalink($this->options[ $this->page_id_field ]); ?>"><?php _e('Link', $this->namespace); ?></a></td>
</tr>
</table>
<?php submit_button(); ?>
</form>