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
                                                                             src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BCH.png' ?>">Bitcoin
                Cash
            </button>
            <button class="tablinks" onclick="openCrypto(event, 'ETH')"><img height="32"
                                                                             src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/ETH.png' ?>">Ethereum
            </button>
            <button class="tablinks" onclick="openCrypto(event, 'DOGE')"><img height="32"
                                                                              src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/DOGE.png' ?>">Dogecoin
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
                <div><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BCH.png' ?>">Bitcoin
                    Cash
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
                <div><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/ETH.png' ?>">Ethereum
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
        <h3><?php esc_html_e( 'Contribute to the project in other ways', 'plugin-donation-lib' ); ?></h3>
        <p>
			<?php esc_html_e( 'If it worked well for you, why not share that with the community through a review?', 'plugin-donation-lib' ); ?>
        </p>
        <p>
            <a class="button-secondary"
               href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/reviews/?view=all#new-post"
               target="_blank"><?php esc_html_e( 'SUBMIT A REVIEW', 'plugin-donation-lib' ); ?></a>
        </p>
        <p>
			<?php esc_html_e( 'Or support the community in another way, if you have language skills, why not translate the plugin, it is easy to do', 'plugin-donation-lib' ); ?>
        </p>
        <p>
            <a class="button-secondary"
               href="https://translate.wordpress.org/projects/wp-plugins/<?php echo esc_attr( $this->plugin_slug ); ?>/"
               target="_blank"><?php esc_html_e( 'TRANSLATE INTO YOUR LANGUAGE', 'plugin-donation-lib' ); ?></a>
        </p>
		<?php
	}
}