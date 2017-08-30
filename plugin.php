<?php
/*
Plugin Name: Pancake Redbooth
Plugin URI: #
Description: Plugin for Redbooth integration.
Version: 1.0
Author: Pancake Creative
*/

define('REDBOOTH_CLIENT_ID', 'REDBOOTH_CLIENT_ID');
define('REDBOOTH_CLIENT_SECRET', 'REDBOOTH_CLIENT_SECRET');
define('REDBOOTH_REDIRECT_URL', 'REDBOOTH_REDIRECT_URL');

require 'vendor/autoload.php';

class VMPancakeRedbooth
{
    public $pluginTitle = 'Pancake Redbooth';
    public $pluginSlug = 'plugin-pancake-redbooth';
    public $settingsPageTitle = 'Pancake Redbooth';

	private $redbooth = null;

	private $user_id;

    static private $_instance = null;

    private function __construct() {
        add_action( 'admin_menu', array(&$this, 'admin_menu') );

		add_action('init', function() {
			$this->set_user_id(get_current_user_id());

			if (preg_match('/\/redbooth/', $_SERVER['REQUEST_URI']) && isset($_GET['code'])) {
				$access_token_info = $this->request_access_token($_GET['code']);
				if ($access_token_info) {
					set_transient('REDBOOTH_ACCESS_TOKEN_'.$this->get_user_id(), $access_token_info['access_token'], 3600);
					set_transient('REDBOOTH_REFRESH_TOKEN_'.$this->get_user_id(), $access_token_info['refresh_token'], 3600);
				}

				header('Location: ' . admin_url('/tools.php?page=plugin-pancake-redbooth'));
				die();
			}
		});

		if (!wp_next_scheduled('redbooth_refresh_tokens_cron')) {
			wp_schedule_event( time(), 'hourly', 'redbooth_refresh_tokens_cron' );
		}

		add_action('redbooth_refresh_tokens_cron', function() {
			$users = get_users(array('fields' => array('ID')));
			if (!empty($users)) {
				foreach($users as $user) {
					$this->set_user_id($user->ID);
					if ($this->get_access_token()) {
						$this->refresh_tokens();
					}
				}
			}
		});
	}

    static public function this()
    {
        if ( !self::$_instance ) {
            self::$_instance = new VMPancakeRedbooth();
        }

        return self::$_instance;
    }

    public function admin_menu()
    {
        add_submenu_page( 'tools.php', $this->pluginTitle, $this->pluginTitle, 'manage_options', $this->pluginSlug, array(&$this, 'plugin_options') );
    }

	public function get_user_id() {
		return $this->user_id;
	}

	public function set_user_id($user_id) {
		$this->redbooth = null;
		$this->user_id = $user_id;
	}

	public function redbooth() {
		if (!is_null($this->redbooth)) return $this->redbooth;

		$this->redbooth = new \Redbooth\Service(
			REDBOOTH_CLIENT_ID,      // update with your client id
			REDBOOTH_CLIENT_SECRET,  // update with your client secret
			$this->get_access_token(),   // update with your user's access token
			$this->get_refresh_token(),  // update with your user's refresh token
			REDBOOTH_REDIRECT_URL    // update with your redirect URL
		);

		return $this->redbooth;
	}

	public function get_access_token() {
		return get_transient('REDBOOTH_ACCESS_TOKEN_'.$this->get_user_id());
	}

	public function get_refresh_token() {
		return get_transient('REDBOOTH_REFRESH_TOKEN_'.$this->get_user_id());
	}

	public function refresh_tokens() {
		$res = $this->redbooth()->refreshToken();
		set_transient('REDBOOTH_ACCESS_TOKEN_'.$this->get_user_id(), $res->access_token, 3600);
		set_transient('REDBOOTH_REFRESH_TOKEN_'.$this->get_user_id(), $res->refresh_token, 3600);
	}

	public function request_access_token($code) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://redbooth.com/oauth2/token',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => array(
				'client_id' => REDBOOTH_CLIENT_ID,
				'client_secret' => REDBOOTH_CLIENT_SECRET,
				'code' => $code,
				'grant_type' => 'authorization_code',
				'redirect_uri' => REDBOOTH_REDIRECT_URL
			)
		));

		$resp = curl_exec($curl);
		curl_close($curl);

		$json = json_decode($resp, true);
		if (isset($json['access_token'])) {
			return $json;
		}

		return false;
	}

	public function get_auth_url() {
		return 'https://redbooth.com/oauth2/authorize?client_id='.REDBOOTH_CLIENT_ID.'&redirect_uri='.urlencode(REDBOOTH_REDIRECT_URL).'&response_type=code';
	}

    public function plugin_options()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        ?>
        <h2><?php echo $this->settingsPageTitle; ?></h2>
        <?php
        $hidden_field_name = $this->pluginSlug.'-hidden';
        $requestSent = true;
        if ( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
            $requestSent = true;
            // Update here options or run actions here
            ?>
            <div class="updated"><p><strong><?php _e('Settings saved.'); ?></strong></p></div>
        <?php
        }

		$token = $this->get_access_token();

		if ($this->get_access_token()) {
			try {
				$res = $this->redbooth()->getMe();
				echo 'My name is ' . $res->first_name . ' ' . $res->last_name . "\n";
			} catch (\Redbooth\Exception\InvalidTokenException $e) {
				$this->refresh_tokens();
			}
		}
        ?>

        <form name="form1" method="post" action=""method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<!--            <table id="table_keys" class="wp-list-table widefat striped">
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table> -->

            <p class="submit">
				<a class="button-primary" href="<?php echo $this->get_auth_url(); ?>"><?php _e('Authorize'); ?></a>
      <!--          <input type="submit" name="Save settings" class="button-primary" value="<?php esc_attr_e('Save settings') ?>" /> -->
            </p>
        </form>
    <?php
    }
}

VMPancakeRedbooth::this();
