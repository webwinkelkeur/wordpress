<form method="POST" action="">
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php echo $this->settings['PLUGIN_NAME']; ?></h2>
        <?php
        if($updated)
            echo "<div class=updated><p>", _e('Your changes have been saved.', 'webwinkelkeur'), "</p></div>";
        foreach($errors as $error)
            echo "<div class=error><p>", $error, "</p></div>";
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wwk-shop-id"><?php _e('Shop ID', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $this->get_option_name('wwk_shop_id');?>" type="text" id="wwk-shop-id" value="<?php echo esc_html($config['wwk_shop_id']); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wwk-api-key"><?php _e('API key', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $this->get_option_name('wwk_api_key');?>" type="text" id="wwk-api-key" value="<?php echo esc_html($config['wwk_api_key']); ?>" class="regular-text" />
                <p class="description">
                <?php printf(__('You\'ll find this information after logging in on %s.', 'webwinkelkeur'), sprintf('<a href="%s" target="_blank">%s</a>', 'https://www.evalor.es/tienda/', $this->settings['PLUGIN_NAME'])); ?>
                <?php printf(__('Click on "%s". You will find the needed information at the bottom of the page.', 'webwinkelkeur'), 'Colocar sello'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-sidebar"><?php _e('Display sidebar', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-sidebar" name="<?php echo $this->get_option_name('sidebar');?>" value="1" <?php if($config['sidebar']) echo 'checked'; ?> />
                        <?php printf(__('Yes, add the %s sidebar to my website.', 'webwinkelkeur'), $this->settings['PLUGIN_NAME']); ?>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Sidebar position', 'webwinkelkeur'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('sidebar_position');?>" value="left" <?php if($config['sidebar_position'] == 'left') echo 'checked'; ?> />
                            <?php _e('Left', 'webwinkelkeur'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('sidebar_position');?>" value="right" <?php if($config['sidebar_position'] == 'right') echo 'checked'; ?> />
                            <?php _e('Right', 'webwinkelkeur'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-sidebar-top"><?php _e('Sidebar top offset', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $this->get_option_name('sidebar_top');?>" type="text" id="webwinkelkeur-sidebar-top" value="<?php echo esc_html($config['sidebar_top']); ?>" class="small-text" />
                <p class="description">
                <?php _e('The number of pixels between the top of the page and the sidebar.', 'webwinkelkeur'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Invitations', 'webwinkelkeur'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('invite');?>" value="1" <?php if($config['invite'] == 1) echo 'checked'; ?> />
                            <?php _e('Yes, after every order.', 'webwinkelkeur'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('invite');?>" value="2" <?php if($config['invite'] == 2) echo 'checked'; ?> />
                            <?php _e('Yes, after a customer\'s first order.', 'webwinkelkeur'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('invite');?>" value="0" <?php if(!$config['invite']) echo 'checked'; ?> />
                            <?php _e('No, don\'t send invitations.', 'webwinkelkeur'); ?>
                        </label>
                    </fieldset>
                    <?php if(!$this->woocommerce): ?>
                    <p class="description"><?php _e('Install and activate WooCommerce to use this functionality.', 'webwinkelkeur'); ?></p>
                    <?php endif; ?>
                    <p class="description"><?php _e('This functionality is only available for Plus packages.', 'webwinkelkeur'); ?></p>
                </td>
            </tr> 
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-invite-delay"><?php _e('Invitation delay', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $this->get_option_name('invite_delay');?>" type="text" id="webwinkelkeur-invite-delay" value="<?php echo esc_html($config['invite_delay']); ?>" class="small-text" />
                <p class="description">
                <?php _e('The invitation will be send after the specified amount of days since the order has been shipped.', 'webwinkelkeur'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-tooltip"><?php _e('Display tooltip', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-tooltip" name="<?php echo $this->get_option_name('tooltip');?>" value="1" <?php if($config['tooltip']) echo 'checked'; ?> />
                        <?php printf(__('Yes, add the %s tooltip to my website.', 'webwinkelkeur'), $this->settings['PLUGIN_NAME']); ?>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('JavaScript integration', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-javascript" name="<?php echo $this->get_option_name('javascript');?>" value="1" <?php if($config['javascript']) echo 'checked'; ?> />
                        <?php printf(__('Yes, add the %s JavaScript to my website.', 'webwinkelkeur'), $this->settings['PLUGIN_NAME']); ?>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('Add rich snippet', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-rich-snippet" name="<?php echo $this->get_option_name('rich_snippet');?>" value="1" <?php if($config['rich_snippet']) echo 'checked'; ?> />
                        <?php _e('Yes, add a rich snippet to the footer of my website.', 'webwinkelkeur'); ?>
                    </label>
                    <p class="description">
                        <?php _e('This allows Google to show your rating in the search results. Use at your own risk.', 'webwinkelkeur'); ?>
                        <a target="_blank" href="https://support.google.com/webmasters/answer/99170?hl=es"><?php _e('More information.', 'webwinkelkeur'); ?></a>
                    </p>
                </td>
            </tr> 
        </table>
        <?php submit_button(); ?>
    </div>
</form>
