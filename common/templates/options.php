<form method="POST" action="">
    <div class="wrap">
        <h2><?= $plugin->getName(); ?></h2>
        <?php
        if ($updated) {
            echo '<div class=updated><p>', _e('Your changes have been saved.', 'webwinkelkeur'), '</p></div>';
        }
        foreach ($errors as $error) {
            echo '<div class=error><p>', $error, '</p></div>';
        }
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wwk-shop-id"><?php _e('Shop ID', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?= $plugin->getOptionName('wwk_shop_id');?>" type="text" id="wwk-shop-id" value="<?= esc_html($config['wwk_shop_id']); ?>" class="regular-text" pattern="[0-9]+" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wwk-api-key"><?php _e('API key', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?= $plugin->getOptionName('wwk_api_key');?>" type="text" id="wwk-api-key" value="<?= esc_html($config['wwk_api_key']); ?>" class="regular-text" />
                <p class="description">
                <?php printf(
            __('You\'ll find this information after logging in on %s.', 'webwinkelkeur'),
            sprintf(
                '<a href="%s" target="_blank">%s</a>',
                "https://{$plugin->getDashboardDomain()}/?ref=wordpress",
                $plugin->getName()
            )
        ); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('JavaScript integration', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-javascript" name="<?= $plugin->getOptionName('javascript');?>" value="1" <?php if ($config['javascript']) {
            echo 'checked';
        } ?> />
                        <?php printf(__('Yes, add the %s JavaScript to my website.', 'webwinkelkeur'), $plugin->getName()); ?>
                    </label>
                    <p class="description">
                    <?php printf(
            __('Use the JavaScript integration to add the sidebar and the tooltip to your website.<br>All settings for the sidebar and tooltip are located on the %s.', 'webwinkelkeur'),
            sprintf(
                '<a href="%s" target="_blank">%s</a>',
                "https://{$plugin->getDashboardDomain()}/integration",
                sprintf(__('%s Dashboard', 'webwinkelkeur'), $plugin->getName())
            )
        ); ?>
                    </p>
                </tda
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Invitations', 'webwinkelkeur'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="<?= $plugin->getOptionName('invite');?>" value="1" <?php if ($config['invite'] == 1) {
            echo 'checked';
        } ?> />
                            <?php _e('Yes, after every order.', 'webwinkelkeur'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="<?= $plugin->getOptionName('invite');?>" value="2" <?php if ($config['invite'] == 2) {
            echo 'checked';
        } ?> />
                            <?php _e('Yes, after a customer\'s first order.', 'webwinkelkeur'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="<?= $plugin->getOptionName('invite');?>" value="0" <?php if (!$config['invite']) {
            echo 'checked';
        } ?> />
                            <?php _e('No, don\'t send invitations.', 'webwinkelkeur'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label><?php _e('Order status for invitations', 'webwinkelkeur'); ?></label>
                </th>
                <td>
                    <?php if (!$plugin->isWoocommerceActivated()): ?>
                        <p class="description"><?php _e('Install and activate WooCommerce to use this functionality.', 'webwinkelkeur'); ?></p>
                    <?php else: ?>
                        <fieldset>
                            <div style="height: 150px; overflow: auto;">
                                <?php foreach (wc_get_order_statuses() as $key => $label): ?>
                                    <label>
                                        <input type="checkbox" name="<?= $plugin->getOptionName('order_statuses[]'); ?>"
                                               value="<?= $key; ?>" <?= in_array($key, $config['order_statuses']) ? 'checked' : ''; ?>>
                                        <?= $label; ?>
                                    </label> <br>
                                <?php endforeach; ?>
                            </div>
                            <p class="description">
                                <?php _e('The invitation is only sent when the order has the checked status.', 'webwinkelkeur'); ?>
                            </p>
                        </fieldset>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="<?= $plugin->getOptionName('limit_order_data');?>" value="1" <?php if ($config['limit_order_data']) {
            echo 'checked ';
        }?> />
                            <?= esc_html(sprintf(
            __('Do not send order information to %s', 'webwinkelkeur'),
            $plugin->getName()
        )); ?>
                            <p class="description">
                                <?= esc_html(sprintf(
            __('Please note: not all %s functionality will be available if you check this option!', 'webwinkelkeur'),
            $plugin->getName()
        )); ?>
                            </p>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-invite-delay"><?php _e('Invitation delay', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?= $plugin->getOptionName('invite_delay');?>" type="number" id="webwinkelkeur-invite-delay" value="<?= esc_html($config['invite_delay']); ?>" class="small-text" />
                <p class="description">
                <?php _e('The invitation will be send after the specified amount of days since the order has been shipped.', 'webwinkelkeur'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('Add rich snippet', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-rich-snippet" name="<?= $plugin->getOptionName('rich_snippet');?>" value="1" <?php if ($config['rich_snippet']) {
                    echo 'checked';
                } ?> />
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
