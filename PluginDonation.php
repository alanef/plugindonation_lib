<?php

namespace AlanEFPluginDonation;

class PluginDonation {
	protected $plugin_slug;
	protected $settings_hook;

	public function __construct( $plugin_slug, $settings_hook ) {
		$this->plugin_slug   = $plugin_slug;
		$this->settings_hook = $settings_hook;
		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'plugins_loaded', array( $this, 'languages' ) );
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
     document.getElementById(cryptName).style.display = "block";
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
        <h2>
			<?php esc_html_e( 'Gift a donation anoymously via  Crypto currency', 'plugin-donation-lib' ); ?>
        </h2>
        <!-- Tab links -->
        <div class="tab">
            <button class="tablinks" onclick="openCrypto(event, 'BTC')"><img height="32"
                                                                             src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/BTC.png' ?>">
            </button>
            <button class="tablinks" onclick="openCrypto(event, 'BCH')"><img height="32"
                                                                             src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/BCH.png' ?>">Bitcoin
                Cash
            </button>
            <button class="tablinks" onclick="openCrypto(event, 'ETH')"><img height="32"
                                                                             src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/ETH.png' ?>">Ethereum
            </button>
            <button class="tablinks" onclick="openCrypto(event, 'DOGE')"><img height="32"
                                                                              src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/DOGE.png' ?>">Dogecoin
            </button>
        </div>

        <!-- Tab content -->
        <div id="BTC" class="tabcontent">
            <img style="float:right;" height="100"
                 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/BTC.png' ?>">
            <h3><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/BTC.png' ?>"></h3>
            <p>My Bitcoin donation wallet <a
                        href="https://www.blockchain.com/btc/address/bc1quhlwkyfnlcsc0ldd2dcvhqwlhqj7ll3vxz97er">bc1quhlwkyfnlcsc0ldd2dcvhqwlhqj7ll3vxz97er</a>
            </p>
        </div>
        <div id="BCH" class="tabcontent">
            <h3><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/BCH.png' ?>">Bitcoin
                Cash</h3>
            <p>London is the capital city of England.</p>
        </div>

        <div id="ETH" class="tabcontent">
            <h3><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/ETH.png' ?>">Ethereum
            </h3>
            <p>Paris is the capital of France.</p>
        </div>

        <div id="DOGE" class="tabcontent">
            <h3><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/crypto/logos/DOGE.png' ?>">Dogecoin
            </h3>
            <p>Tokyo is the capital of Japan.</p>
        </div>
        <h2>
			<?php esc_html_e( 'Gift a donation via PayPal', 'plugin-donation-lib' ); ?>
        </h2>
        <p>
            <a class="button-primary" href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
               target="_blank"><?php esc_html_e( 'PAYPAL DONATE LINK', 'plugin-donation-lib' ); ?></a>
        </p>
        <p>
			<?php esc_html_e( 'If it worked well for you, why not share that with the community through a review?', 'plugin-donation-lib' ); ?>
        </p>
        <p>
            <a class="button-secondary"
               href="https://wordpress.org/support/plugin/plugin-donation-lib/reviews/?view=all#new-post"
               target="_blank"><?php esc_html_e( 'SUBMIT A REVIEW', 'plugin-donation-lib' ); ?></a>
        </p>
        <p>
			<?php esc_html_e( 'Or support the community in another way, if you have language skills, why not translate the plugin, it is easy to do', 'plugin-donation-lib' ); ?>
        </p>
        <p>
            <a class="button-secondary"
               href="https://translate.wordpress.org/projects/wp-plugins/plugin-donation-lib/"
               target="_blank"><?php esc_html_e( 'TRANSLATE INTO YOUR LANGUAGE', 'plugin-donation-lib' ); ?></a>
        </p>
		<?php
	}
}