<form method="POST" action="">
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php _e('WebwinkelKeur', 'webwinkelkeur'); ?></h2>
        <?php
        if($updated)
            echo "<div class=updated><p>", _e('Uw wijzigingen zijn opgeslagen.', 'webwinkelkeur'), "</p></div>";
        foreach($errors as $error)
            echo "<div class=error><p>", $error, "</p></div>";
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wwk-shop-id"><?php _e('Webwinkel ID', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $this->get_option_name('wwk_shop_id');?>" type="text" id="wwk-shop-id" value="<?php echo esc_html($config['wwk_shop_id']); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wwk-api-key"><?php _e('API key', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $this->get_option_name('wwk_api_key');?>" type="text" id="wwk-api-key" value="<?php echo esc_html($config['wwk_api_key']); ?>" class="regular-text" />
                <p class="description">
                <?php _e('Deze gegevens vindt u na het inloggen op <a href="https://www.webwinkelkeur.nl/webwinkel/" target="_blank">WebwinkelKeur.nl</a>.<br />Klik op \'Keurmerk plaatsen\'. De gegevens zijn vervolgens onderaan deze pagina te vinden.', 'webwinkelkeur'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('JavaScript-integratie', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-javascript" name="<?php echo $this->get_option_name('javascript');?>" value="1" <?php if($config['javascript']) echo 'checked'; ?> />
                        <?php _e('Ja, voeg de WebwinkelKeur JavaScript toe aan mijn website.', 'webwinkelkeur'); ?>
                    </label>
                    <p class="description">
                    <?php _e('Gebruik de JavaScript-integratie om de sidebar en de tooltip op uw site te plaatsen.<br>Alle instellingen voor de sidebar en de tooltip, vindt u in het <a href="https://dashboard.webwinkelkeur.nl/integration" target="_blank">WebwinkelKeur Dashboard</a>.', 'webwinkelkeur'); ?>
                    </p>
                </td>
            </tr> 
            <tr valign="top">
                <th scope="row"><?php _e('Uitnodigingen versturen', 'webwinkelkeur'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('invite');?>" value="1" <?php if($config['invite'] == 1) echo 'checked'; ?> />
                            Ja, na elke bestelling.
                        </label><br>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('invite');?>" value="2" <?php if($config['invite'] == 2) echo 'checked'; ?> />
                            Ja, alleen bij de eerste bestelling.
                        </label><br>
                        <label>
                            <input type="radio" name="<?php echo $this->get_option_name('invite');?>" value="0" <?php if(!$config['invite']) echo 'checked'; ?> />
                            Nee, geen uitnodigingen versturen.
                        </label>
                    </fieldset>
                    <?php if(!$this->woocommerce): ?>
                    <p class="description"><?php _e('Installeer en activeer WooCommerce om deze functionaliteit te kunnen gebruiken.', 'webwinkelkeur'); ?></p>
                    <?php endif; ?>
                    <p class="description"><?php _e('Deze functionaliteit is alleen beschikbaar voor Plus-leden.', 'webwinkelkeur'); ?></p>
                </td>
            </tr> 
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-invite-delay"><?php _e('Wachttijd voor uitnodiging', 'webwinkelkeur'); ?></label></th>
                <td><input name="<?php echo $this->get_option_name('invite_delay');?>" type="text" id="webwinkelkeur-invite-delay" value="<?php echo esc_html($config['invite_delay']); ?>" class="small-text" />
                <p class="description">
                <?php _e('De uitnodiging wordt verstuurd nadat het opgegeven aantal dagen is verstreken na het verzenden van de bestelling.', 'webwinkelkeur'); ?>
                </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="webwinkelkeur-javascript"><?php _e('Rich snippet sterren', 'webwinkelkeur'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="webwinkelkeur-rich-snippet" name="<?php echo $this->get_option_name('rich_snippet');?>" value="1" <?php if($config['rich_snippet']) echo 'checked'; ?> />
                        <?php _e('Ja, voeg een rich snippet toe aan de footer.', 'webwinkelkeur'); ?>
                    </label>
                    <p class="description">
                        Google kan hiermee uw waardering in de zoekresultaten tonen. Gebruik op eigen risico.
                        <a target="_blank" href="https://support.google.com/webmasters/answer/99170?hl=nl">Meer informatie.</a>
                    </p>
                </td>
            </tr> 
        </table>
        <?php submit_button(); ?>
    </div>
</form>
