<?php

namespace AlanEFPluginDonation;

class PluginDonation {
	protected $plugin_slug;
	protected $settings_hook;
	protected $plugin_file;

	public function __construct( $plugin_slug, $settings_hook, $plugin_file ) {
		$this->plugin_slug   = $plugin_slug;
		$this->settings_hook = $settings_hook;
		$this->plugin_file   = $plugin_file;
		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'plugins_loaded', array( $this, 'languages' ) );
		register_activation_hook( $this->plugin_file, array( $this, 'plugin_activate' ) );
		register_uninstall_hook(
			$this->plugin_file,
			array(
				'\AlanEFPluginDonation\PluginDonation',
				'plugin_uninstall',
			)
		);
	}

	public static function plugin_uninstall() {
		$x = plugin_basename( __FILE__ );
		do {
			$slug = $x;
			$x    = dirname( $x );
		} while ( ! empty( $x ) && '.' !== $x );

		delete_option( $slug . '_donate' );
		delete_option( $slug . '_review' );
	}

	public function plugin_activate() {
		$donate = get_option( $this->plugin_slug . '_donate', false );
		if ( false === $donate ) {
			add_option( $this->plugin_slug . '_donate', time() );
		}
		$review = get_option( $this->plugin_slug . '_review', false );
		if ( false === $review ) {
			add_option( $this->plugin_slug . '_review', time() );
		}

	}

	public function languages() {
		load_plugin_textdomain(
			'plugin-donation-lib',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/plugindonation_lib/languages'
		);
	}

	public function enqueue_styles( $hook ) {
		if ( $hook === $this->settings_hook ) {
			$this->add_inline_admin_style();

			return;
		}
	}

	private function add_inline_admin_style() {
		$style = <<<EOT
/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons that are used to open the tab content */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
  flex-wrap: wrap;
  gap: 20px;
  align-items: center;
}
.tabcontent div {
  flex-grow: 1;
}
.tabcontent div:nth-of-type(2) {
  flex-basis: 250px;
}
div.tabcontentwrap div:first-child{
  display: flex;
}
EOT;

		wp_add_inline_style( 'admin-bar', $style );
	}


	public function enqueue_scripts( $hook ) {
		if ( $hook === $this->settings_hook ) {
			$this->add_inline_admin_script();

			return;
		}
	}

	private function add_inline_admin_script() {
		$script = <<<EOT
function openCrypto(evt, cryptName) {
    evt.preventDefault();
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
     tabcontent = document.getElementsByClassName("tabcontent");
     for (i = 0; i < tabcontent.length; i++) {
          tabcontent[i].style.display = "none";
     }

     // Get all elements with class="tablinks" and remove the class "active"
     tablinks = document.getElementsByClassName("tablinks");
     for (i = 0; i < tablinks.length; i++) {
          tablinks[i].className = tablinks[i].className.replace(" active", "");
     }

     // Show the current tab, and add an "active" class to the button that opened the tab
     document.getElementById(cryptName).style.display = "flex";
     evt.currentTarget.className += " active";
}
EOT;
		wp_add_inline_script( 'admin-bar', $script );
	}

	public function display() {
		?>
        <tr valign="top">
            <th scope="row"><?php esc_html_e( 'Gift a Donation', 'plugin-donation-lib' ); ?></th>
            <td>
                <p>
					<?php esc_html_e( 'Hi, I\'m Alan and I built this free plugin to solve problems I had, and I hope it solves your problem too.', 'plugin-donation-lib' ); ?>
                </p>
                <p>
					<?php esc_html_e( 'It would really help me know that others find it useful and a great way of doing this is to gift me a small donation', 'plugin-donation-lib' ); ?>
                </p>
                <h3>
					<?php esc_html_e( 'Gift a donation: select your desired option', 'plugin-donation-lib' ); ?>
                </h3>
                <!-- Tab links -->
                <div class="tab">
                    <button class="tablinks" onclick="openCrypto(event, 'BTC')"><img height="32"
                                                                                     src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BTC.png' ?>">
                    </button>
                    <button class="tablinks" onclick="openCrypto(event, 'PP')"><img height="32"
                                                                                    src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/PP.png' ?>">
                    </button>
                    <button class="tablinks" onclick="openCrypto(event, 'BCH')"><img height="32"
                                                                                     src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BCH.png' ?>"><br>Bitcoin
                        Cash
                    </button>
                    <button class="tablinks" onclick="openCrypto(event, 'ETH')"><img height="32"
                                                                                     src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/ETH.png' ?>"><br>Ethereum
                    </button>
                    <button class="tablinks" onclick="openCrypto(event, 'DOGE')"><img height="32"
                                                                                      src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/DOGE.png' ?>"><br>Dogecoin
                    </button>

                </div>

                <!-- Tab content -->
                <div class="tabcontentwrap">
                    <div id="BTC" class="tabcontent">
                        <div>
                            <img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BTC.png' ?>">
                        </div>
                        <div>
							<?php esc_html_e( 'My Bitcoin donation wallet', 'plugin-donation-lib' ); ?><br><br> <strong><a
                                        href="https://www.blockchain.com/btc/address/bc1q04zt3yxxu282ayg3aev633twpqtw0dzzetp78x">bc1q04zt3yxxu282ayg3aev633twpqtw0dzzetp78x</a></strong>
                        </div>
                        <div>
                            <img height="140"
                                 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/BTC.png' ?>">
                        </div>
                    </div>
                    <div id="PP" class="tabcontent">
                        <div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
                                target="_blank"><img height="48"
                                                     src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/PP.png' ?>">
                            </a></div>
                        <div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
                                target="_blank"><?php esc_html_e( 'Gift a donation via PayPal', 'plugin-donation-lib' ); ?>
                            </a></div>
                        <div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
                                target="_blank"><img height="48"
                                                     src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/PPcards.png' ?>">
                            </a></div>
                    </div>
                    <div id="BCH" class="tabcontent">
                        <div><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BCH.png' ?>">
                        </div>
                        <div>
							<?php esc_html_e( 'My Bitcoin Cash address', 'plugin-donation-lib' ); ?><br><br><strong>bitcoincash:qpmn76wad2mwfhk3c9vhx77ex5nqhq2r0ursp8z6mp</strong>
                        </div>
                        <div>
                            <img height="140"
                                 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/BCH.png' ?>">
                        </div>
                    </div>

                    <div id="ETH" class="tabcontent">
                        <div><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/ETH.png' ?>">
                        </div>
                        <div>
							<?php esc_html_e( 'My Ethereum address', 'plugin-donation-lib' ); ?><br><br><strong>0x492Bdf65bcB65bC067Ab3886e9B79a7CDe9021BB</strong>
                        </div>
                        <div>
                            <img height="140"
                                 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/ETH.png' ?>">
                        </div>
                    </div>
                    <div id="DOGE" class="tabcontent">
                        <h3><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/DOGE.png' ?>">Dogecoin
                        </h3>
                        <div>
							<?php esc_html_e( 'My Dogecoin address', 'plugin-donation-lib' ); ?><br><br><strong>D7nB2HsBxNPACis9fSgjqTShe4JfSztAjr</strong>
                        </div>
                        <div>
                            <img height="140"
                                 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/DOGE.png' ?>">
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e( 'Contribute', 'plugin-donation-lib' ); ?></th>
            <td>
                <!-- Tab links -->
                <div class="tab">
                    <button class="tablinks" onclick="openCrypto(event, 'review-tab')"><img height="32"
                                                                                            src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/reviews.png' ?>"><br><?php esc_html_e( 'Submit a review', 'plugin-donation-lib' ); ?>
                    </button>
                    <button class="tablinks" onclick="openCrypto(event, 'translate-tab')"><img height="32"
                                                                                               src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/translate.png' ?>"><br><?php esc_html_e( 'Translate to your language', 'plugin-donation-lib' ); ?>
                    </button>
                    <button class="tablinks" onclick="openCrypto(event, 'github-tab')"><img height="32"
                                                                                            src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/github.png' ?>"><br>Help
                        Develop
                    </button>
                </div>
                <!-- Tab content -->
                <div class="tabcontentwrap">
                    <div id="review-tab" class="tabcontent">
                        <div>
                            <a class="button-secondary"
                               href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/reviews/?view=all#new-post"
                               target="_blank"><?php esc_html_e( 'SUBMIT A REVIEW', 'plugin-donation-lib' ); ?></a>
                        </div>
                        <div>
                            <p><?php esc_html_e( 'If you are happy with the plugin the we would love a review. Even if you are not so happy feedback is always useful, but if you have issues we would love you to make a support request first so we can try and help.', 'plugin-donation-lib' ); ?></p>
                        </div>
                        <div>
                            <a class="button-secondary"
                               href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                               target="_blank"><?php esc_html_e( 'SUPPORT FORUM', 'plugin-donation-lib' ); ?></a>
                        </div>
                    </div>
                    <div id="translate-tab" class="tabcontent">
                        <div>
                            <a href="https://translate.wordpress.org/projects/wp-plugins/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                               target="_blank"><img height="48"
                                                    src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/translate.png' ?>">
                            </a></div>
                        <div>
                            <p><?php esc_html_e( 'Providing some translations for a plugin is very easy and can be done via the WordPress system. You can easily contribute to the community and you don\'t need to translate it all.', 'plugin-donation-lib' ); ?> </p>
                        </div>
                        <div><a class="button-secondary"
                                href="https://translate.wordpress.org/projects/wp-plugins/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                                target="_blank"><?php esc_html_e( 'TRANSLATE INTO YOUR LANGUAGE', 'plugin-donation-lib' ); ?></a>
                        </div>
                    </div>
                    <div id="github-tab" class="tabcontent">
                        <div><a href="https://github.com/alanef/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                                target="_blank"><img height="48"
                                                     src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/github.png' ?>"></a>
                        </div>
                        <div>
                            <p><?php esc_html_e( 'As an open source project you are welcome to contribute to the development of the software if you can. The development plugin is hosted on GitHub.', 'plugin-donation-lib' ); ?></p>
                        </div>
                        <div>
                            <a class="button-secondary"
                               href="https://github.com/alanef/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                               target="_blank"><?php esc_html_e( 'CONTRIBUTE ON GITHUB', 'plugin-donation-lib' ); ?></a>
                        </div>
                    </div>

                </div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e( 'Get Support', 'plugin-donation-lib' ); ?></th>
            <td>
                <a class="button-secondary"
                   href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                   target="_blank"><?php esc_html_e( 'WORDPRESS SUPPORT FORUM', 'plugin-donation-lib' ); ?></a>
            </td>
        </tr>
		<?php
	}
}