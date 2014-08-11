<?php

class WebwinkelKeurAdmin {
    private $woocommerce = false;

    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
        add_action('admin_notices', array($this, 'invite_error_notices'));
        add_action('before_woocommerce_init', array($this, 'activate_woocommerce'));
    }

    public function admin_menu() {
        add_submenu_page('plugins.php', __('WebwinkelKeur'), __('WebwinkelKeur'),
                         'manage_options', 'webwinkelkeur', array($this, 'options_page'));
    }

    public function plugin_action_links($links, $file) {
        if($file == 'webwinkelkeur/webwinkelkeur.php') {
            $links[] = '<a href="admin.php?page=webwinkelkeur">' . __('Settings') . '</a>';
        }
        return $links;
    }

    public function activate_woocommerce() {
        $this->woocommerce = true;
    }

    public function options_page() {
        $errors = array();
        $updated = false;
        $fields = array(
            'wwk_shop_id',
            'wwk_api_key',
            'sidebar',
            'sidebar_position',
            'sidebar_top',
            'invite',
            'invite_delay',
            'tooltip',
            'javascript',
            'rich_snippet',
        );
        $config = array(
            'invite_delay'     => 3,
            'sidebar_position' => 'left',
            'tooltip'          => true,
            'javascript'       => true,
        );

        foreach($fields as $field_name) {
            $value = get_option('webwinkelkeur_' . $field_name, false);
            if($value !== false)
                $config[$field_name] = (string) $value;
            elseif(!isset($config[$field_name]))
                $config[$field_name] = '';
        }

        if(isset($_POST['webwinkelkeur_wwk_shop_id'])) {
            foreach($fields as $field_name)
                $config[$field_name] = (string) @$_POST['webwinkelkeur_' . $field_name];

            if(empty($config['wwk_shop_id']))
                $errors[] = __('Uw webwinkel ID is verplicht.');
            elseif(!ctype_digit($config['wwk_shop_id']))
                $errors[] = __('Uw webwinkel ID kan alleen cijfers bevatten.');

            if($config['invite'] && !$config['wwk_api_key'])
                $errors[] = __('Om uitnodigingen te versturen is uw API key verplicht.');

            if(!$errors) {
                foreach($config as $name => $value)
                    update_option('webwinkelkeur_' . $name, $value);
                $updated = true;
            }
        }
        
        require dirname(__FILE__) . '/options.php';
    }

    public function invite_error_notices() {
        global $wpdb;

        $errors = $wpdb->get_results("
            SELECT *
            FROM {$wpdb->prefix}webwinkelkeur_invite_error
            WHERE reported = 0
            ORDER BY time
        ");

        foreach($errors as $error) {
            ?>
            <div class="error"><p>
                <?php _e('Bij het versturen van de WebwinkelKeur uitnodiging is een fout opgetreden:') ?><br/>
                <?php echo esc_html($error->response); ?>
            </p></div>
            <?php
        }

        $error_ids = array();
        foreach($errors as $error) {
            $error_ids[] = (int) $error->id;
        }
        if($error_ids) {
            $wpdb->query("
                UPDATE {$wpdb->prefix}webwinkelkeur_invite_error
                SET reported = 1
                WHERE id IN (" . implode(',', $error_ids) . ")
            ");
        }
    }
}

new WebwinkelKeurAdmin;
