<?php return function (Valued\WordPress\TemplateData\Options $data) { ?>
<form method="POST" action="">
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php echo $data->plugin->getName(); ?></h2>
        <?php
        if($updated)
            echo "<div class=updated><p>", _e('Your changes have been saved.', 'webwinkelkeur'), "</p></div>";
        foreach($errors as $error)
            echo "<div class=error><p>", $error, "</p></div>";
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wwk-shop-id"><?php _e('Shop ID', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $data->plugin->getOptionName('wwk_shop_id');?>" type="text" id="wwk-shop-id" value="<?php echo esc_html($config['wwk_shop_id']); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wwk-api-key"><?php _e('API key', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $data->plugin->getOptionName('wwk_api_key');?>" type="text" id="wwk-api-key" value="<?php echo esc_html($config['wwk_api_key']); ?>" class="regular-text" />
                <p class="description">
                <?php printf(
                    __('You\'ll find this information after logging in on %s.', 'webwinkelkeur'),
                    sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        "https://{$data->plugin->getDashboardDomain()}/?ref=wordpress",
                        $data->plugin->getName()
                    )
                ); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('JavaScript integration', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-javascript" name="<?php echo $data->plugin->getOptionName('javascript');?>" value="1" <?php if($config['javascript']) echo 'checked'; ?> />
                        <?php printf(__('Yes, add the %s JavaScript to my website.', 'webwinkelkeur'), $data->plugin->getName()); ?>
                    </label>
                    <p class="description">
                    <?php printf(
                        __('Use the JavaScript integration to add the sidebar and the tooltip to your website.<br>All settings for the sidebar and tooltip are located on the %s.', 'webwinkelkeur'),
                        sprintf(
                            '<a href="%s" target="_blank">%s</a>',
                            "https://{$data->plugin->getDashboardDomain()}/integration",
                            sprintf(__('%s Dashboard', 'webwinkelkeur'), $data->plugin->getName())
                        )
                    ); ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Invitations', 'webwinkelkeur'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="<?php echo $data->plugin->getOptionName('invite');?>" value="1" <?php if($config['invite'] == 1) echo 'checked'; ?> />
                            <?php _e('Yes, after every order.', 'webwinkelkeur'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="<?php echo $data->plugin->getOptionName('invite');?>" value="2" <?php if($config['invite'] == 2) echo 'checked'; ?> />
                            <?php _e('Yes, after a customer\'s first order.', 'webwinkelkeur'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="<?php echo $data->plugin->getOptionName('invite');?>" value="0" <?php if(!$config['invite']) echo 'checked'; ?> />
                            <?php _e('No, don\'t send invitations.', 'webwinkelkeur'); ?>
                        </label>
                    </fieldset>
                    <?php if(!$data->plugin->woocommerce): ?>
                    <p class="description"><?php _e('Install and activate WooCommerce to use this functionality.', 'webwinkelkeur'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="<?php echo $data->plugin->getOptionName('limit_order_data');?>" value="1" <?php if($config['limit_order_data']) echo 'checked ';?> />
                            <?= esc_html(sprintf(
                                __('Do not send order information to %s', 'webwinkelkeur'),
                                $data->plugin->getName()
                            )); ?>
                            <p class="description">
                                <?= esc_html(sprintf(
                                    __('Please note: not all %s functionality will be available if you check this option!', 'webwinkelkeur'),
                                    $data->plugin->getName()
                                )); ?>
                            </p>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-invite-delay"><?php _e('Invitation delay', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $data->plugin->getOptionName('invite_delay');?>" type="text" id="webwinkelkeur-invite-delay" value="<?php echo esc_html($config['invite_delay']); ?>" class="small-text" />
                <p class="description">
                <?php _e('The invitation will be send after the specified amount of days since the order has been shipped.', 'webwinkelkeur'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('Add rich snippet', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-rich-snippet" name="<?php echo $data->plugin->getOptionName('rich_snippet');?>" value="1" <?php if($config['rich_snippet']) echo 'checked'; ?> />
                        <?php _e('Yes, add a rich snippet to the footer of my website.', 'webwinkelkeur'); ?>
                    </label>
                    <p class="description">
                        <?php _e('This allows Google to show your rating in the search results. Use at your own risk.', 'webwinkelkeur'); ?>
                        <a target="_blank" href="https://support.google.com/webmasters/answer/99170?hl=nl"><?php _e('More information.', 'webwinkelkeur'); ?></a>
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </div>
</form>
<?php } ?>
