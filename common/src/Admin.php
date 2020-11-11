<?php
namespace Valued\WordPress;

class Admin {
    private $plugin;

    private $woocommerce = false;

    protected function get_default_config() {
        return [
            'invite_delay' => 3,
            'javascript' => true,
            'order_statuses' => ['wc-completed'],
        ];
    }

    public function __construct(BasePlugin $plugin) {
        $this->plugin = $plugin;
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('plugin_action_links', [$this, 'plugin_action_links'], 10, 2);
        add_action('admin_notices', [$this, 'invite_error_notices']);
        add_action('before_woocommerce_init', [$this, 'activate_woocommerce']);
    }

    public function admin_menu() {
        add_submenu_page(
            'options-general.php',
            $this->plugin->getName(),
            $this->plugin->getName(),
            'manage_options',
            $this->plugin->getSlug(),
            [$this, 'options_page']
        );
    }

    public function plugin_action_links($links, $file) {
        $path = "{$this->plugin->getSlug()}/{$this->plugin->getSlug()}.php";
        if ($file == $path) {
            $links[] = '<a href="admin.php?page=' . $this->plugin->getSlug() . '">'
                     . __('Settings') . '</a>';
        }
        return $links;
    }

    public function activate_woocommerce() {
        $this->woocommerce = true;
    }

    public function options_page() {
        $errors = [];
        $updated = false;

        $fields = [
            'wwk_shop_id' => function ($value) {
                if ($value == '') {
                    throw new ValidationException(__('Your shop ID is required.', 'webwinkelkeur'));
                }
                if (!ctype_digit($value)) {
                    throw new ValidationException(__('Your shop ID can only contain digits.', 'webwinkelkeur'));
                }
                return $value;
            },
            'wwk_api_key' => function ($value) {
                if ($value != '' && !preg_match('/^[a-z0-9.]+$/', $value)) {
                    throw new ValidationException(__('This is not a valid API key.', 'webwinkelkeur'));
                }
                return $value;
            },
            'invite' => 'boolval',
            'invite_delay' => 'intval',
            'limit_order_data' => 'intval',
            'javascript' => 'boolval',
            'order_statuses' => function ($value) {
                return array_map('strval', is_array($value) ? $value : []);
            },
            'rich_snippet' => 'boolval',
        ];

        $config = $this->get_default_config();

        foreach (array_keys($fields) as $field_name) {
            $value = get_option($this->plugin->getOptionName($field_name), false);
            if ($value !== false) {
                $config[$field_name] = $value;
            } elseif (!isset($config[$field_name])) {
                $config[$field_name] = '';
            }
        }

        if (isset($_POST[$this->plugin->getOptionName('wwk_shop_id')])) {
            foreach ($fields as $field_name => $sanitize) {
                try {
                    $config[$field_name] =
                        $sanitize(@$_POST[$this->plugin->getOptionName($field_name)]);
                } catch (ValidationException $e) {
                    $errors[] = $e->getMessage();
                    $config[$field_name] = '';
                }
            }

            if ($config['invite'] && !$config['wwk_api_key']) {
                $errors[] = __('To send invitations, your API key is required.', 'webwinkelkeur');
            }

            if (!$errors) {
                foreach ($config as $name => $value) {
                    update_option($this->plugin->getOptionName($name), $value);
                }
                $updated = true;
            }
        }

        echo $this->plugin->render('options', [
            'plugin' => $this->plugin,
            'errors' => $errors,
            'updated' => $updated,
            'config' => $config,
        ]);
    }

    public function invite_error_notices() {
        global $wpdb;

        $errors = $wpdb->get_results("
            SELECT *
            FROM {$this->plugin->getInviteErrorsTable()}
            WHERE reported = 0
            ORDER BY time
        ");

        foreach ($errors as $error) {
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

        $error_ids = [];
        foreach ($errors as $error) {
            $error_ids[] = (int) $error->id;
        }
        if ($error_ids) {
            $wpdb->query("
                UPDATE {$this->plugin->getInviteErrorsTable()}
                SET reported = 1
                WHERE id IN (" . implode(',', $error_ids) . ')
            ');
        }
    }
}
