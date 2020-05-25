<?php
namespace Valued\WordPress;

class Admin {
    private $plugin;
    private $woocommerce = false;

    protected function get_default_config() {
        return array(
            'invite_delay'     => 3,
            'javascript'       => true,
        );
    }

    protected function get_config_fields() {
        return array(
            'wwk_shop_id',
            'wwk_api_key',
            'invite',
            'invite_delay',
            'limit_order_data',
            'javascript',
            'rich_snippet',
        );
    }

    public function __construct(BasePlugin $plugin) {
        $this->plugin = $plugin;
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
        add_action('admin_notices', array($this, 'invite_error_notices'));
        add_action('before_woocommerce_init', array($this, 'activate_woocommerce'));
    }

    public function admin_menu() {
        add_submenu_page(
            'options-general.php',
            $this->plugin->getName(),
            $this->plugin->getName(),
            'manage_options',
            $this->plugin->getSlug(),
            array($this, 'options_page')
        );
    }

    public function plugin_action_links($links, $file) {
        $path = "{$this->plugin->getSlug()}/{$this->plugin->getSlug()}.php";
        if($file == $path) {
            $links[] = '<a href="admin.php?page=' . $this->plugin->getSlug() . '">'
                     . __('Settings') . '</a>';
        }
        return $links;
    }

    public function activate_woocommerce() {
        $this->woocommerce = true;
    }

    public function options_page() {
        $errors = array();
        $updated = false;
        $fields = $this->get_config_fields();
        $config = $this->get_default_config();

        foreach($fields as $field_name) {
            $value = get_option($this->plugin->getOptionName($field_name), false);
            if($value !== false)
                $config[$field_name] = (string) $value;
            elseif(!isset($config[$field_name]))
                $config[$field_name] = '';
        }

        if(isset($_POST[$this->plugin->getOptionName('wwk_shop_id')])) {
            foreach($fields as $field_name)
                $config[$field_name] = (string) @$_POST[$this->plugin->getOptionName($field_name)];

            if(empty($config['wwk_shop_id']))
                $errors[] = __('Your shop ID is required.', 'webwinkelkeur');
            elseif(!ctype_digit($config['wwk_shop_id']))
                $errors[] = __('Your shop ID can only contain digits.', 'webwinkelkeur');

            if($config['invite'] && !$config['wwk_api_key'])
                $errors[] = __('To send invitations, your API key is required.', 'webwinkelkeur');

            if(!$errors) {
                foreach($config as $name => $value)
                    update_option($this->plugin->getOptionName($name), $value);
                $updated = true;
            }
        }

        require __DIR__ . '/../templates/options.php';
    }

    public function invite_error_notices() {
        global $wpdb;

        $errors = $wpdb->get_results("
            SELECT *
            FROM {$this->plugin->getInviteErrorsTable()}
            WHERE reported = 0
            ORDER BY time
        ");

        foreach($errors as $error) {
            ?>
            <div class="error"><p>
                <?php sprintf(
                    __('An error occurred while requesting the %s invitation:', 'webwinkelkeur'),
                    $this->plugin->getName()
                ); ?><br/>
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
                UPDATE {$this->plugin->getInviteErrorsTable()}
                SET reported = 1
                WHERE id IN (" . implode(',', $error_ids) . ")
            ");
        }
    }

}
