<form method="POST" action="">
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php _e('Webwinkelkeur'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wwk-shop-id"><?php _e('Webwinkel ID'); ?></label></th>
                <td><input name="webwinkelkeur_wwk_shop_id" type="text" id="wwk-shop-id" value="<?php echo esc_html($config['wwk_shop_id']); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wwk-api-key"><?php _e('API key'); ?></label></th>
                <td><input name="webwinkelkeur_wwk_api_key" type="text" id="wwk-api-key" value="<?php echo esc_html($config['wwk_api_key']); ?>" class="regular-text" />
                <p class="description">
                <?php _e('Deze gegevens vindt u na het inloggen op <a href="https://www.webwinkelkeur.nl/webwinkel/" target="_blank">WebwinkelKeur.nl</a>.<br />Klik op \'Keurmerk plaatsen\'. De gegevens zijn vervolgens onderaan deze pagina te vinden.'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-sidebar"><?php _e('Sidebar weergeven'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-sidebar" name="webwinkelkeur_sidebar" value="1" <?php if($config['sidebar']) echo 'checked'; ?> />
                        <?php _e('Ja, voeg de Webwinkelkeur Sidebar toe aan mijn website.'); ?>
                    </label>
                </td>
            </tr> 
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-invite"><?php _e('Uitnodigingen versturen'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-invite" name="webwinkelkeur_invite" value="1" <?php if($config['invite']) echo 'checked'; ?> />
                        <?php _e('Ja, verstuur een uitnodiging nadat een bestelling is verzonden.'); ?>
                    </label>
                    <?php if(!$this->woocommerce): ?>
                    <p class="description"><?php _e('Installeer en activeer WooCommerce om deze functionaliteit te kunnen gebruiken.'); ?></p>
                    <?php endif; ?>
                    <p class="description"><?php _e('Deze functionaliteit is alleen beschikbaar voor Plus-leden.'); ?></p>
                </td>
            </tr> 
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-invite-delay"><?php _e('Wachttijd voor uitnodiging'); ?></label></th>
                <td><input name="webwinkelkeur_invite_delay" type="text" id="webwinkelkeur-invite-delay" value="<?php echo esc_html($config['invite_delay']); ?>" class="small-text" />
                <p class="description">
                <?php _e('De uitnodiging wordt verstuurd nadat het opgegeven aantal dagen is verstreken na het verzenden van de bestelling.'); ?>
                </p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </div>
</form>
