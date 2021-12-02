<?php
/*
 *  @version 1.1
 *  @licence GPL2 or Later
 *  @copyright Alan Fuller
 */

namespace AlanEFPluginDonation;

/**
 * @since 1.0
 */
class PluginDonation {
	/**
	 * @var string $plugin_slug plugin base name or slug
	 */
	protected $plugin_slug;
	/**
	 * @var string $settings_hook the page hook for the plugin settings page
	 */
	protected $settings_hook;
	/**
	 * @var string $plugin_file the full plugin path file e.g. my-plugin/my-plugin.php
	 */
	protected $plugin_file;

	/**
	 * @param string $plugin_slug plugin base name or slug
	 * @param string $settings_hook the page hook for the plugin settings page
	 * @param string $plugin_file the full plugin path file e.g. my-plugin/my-plugin.php
	 * @param string $donate_page the full url for a page with information on how to donate
	 * @param string $title the plugin name in human form
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin_slug, $settings_hook, $plugin_file, $donate_page, $title ) {
		$this->plugin_slug   = $plugin_slug;
		$this->settings_hook = $settings_hook;
		$this->plugin_file   = $plugin_file;
		$this->donate_page   = $donate_page;
		$this->title         = $title;
		$this->hooks();
	}

	/**
	 * @since 1.0
	 */
	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'plugins_loaded', array( $this, 'languages' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
		add_action( 'wp_ajax_pdl_dismiss_notice', array( $this, 'pdl_dismiss_notice' ) );
		add_action( 'wp_ajax_pdl_later_notice', array( $this, 'pdl_later_notice' ) );
		register_activation_hook( $this->plugin_file, array( $this, 'plugin_activate' ) );
		register_uninstall_hook(
			$this->plugin_file,
			array(
				'\AlanEFPluginDonation\PluginDonation',
				'plugin_uninstall',
			)
		);
	}

	/**
	 * @since 1.0
	 */
	public static function plugin_uninstall() {
		$x = plugin_basename( __FILE__ );
		do {
			$slug = $x;
			$x    = dirname( $x );
		} while ( ! empty( $x ) && '.' !== $x );
		delete_option( $slug . '_donate' );
		delete_option( $slug . '_review' );
	}

	/**
	 * @since 1.0
	 */
	public function plugin_activate() {
		$this->set_timers();
	}

	/**
	 * Sets the timer data for reminders if not already set
	 *
	 * @since 1.1
	 */
	public function set_timers() {
		$donate = get_option( $this->plugin_slug . '_donate', false );
		if ( false === $donate ) {
			add_option( $this->plugin_slug . '_donate', time() );
		}
		$review = get_option( $this->plugin_slug . '_review', false );
		if ( false === $review ) {
			add_option( $this->plugin_slug . '_review', time() );
		}
	}

	/**
	 * @since 1.0
	 */
	public function languages() {
		load_plugin_textdomain(
			'plugin-donation-lib',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/plugindonation_lib/languages'
		);
	}

	/**
	 * @param string $hook page hook provided by WordPress
	 *
	 * @since 1.0
	 */
	public function enqueue_styles( $hook ) {
		if ( $hook === $this->settings_hook ) {
			$this->add_inline_admin_style();

			return;
		}
	}

	/**
	 * Styles for the tab element on the admin display
	 *
	 * @since 1.0
	 */
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


	/**
	 * @param string $hook page hook provided by WordPress
	 *
	 * @since 1.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( $this->admin_page_we_use() ) {
			wp_enqueue_script( 'plugindonation_lib', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.0', false );
		}
	}

	/**
	 * Only on pages we want to be effective on touch
	 *
	 * @return bool
	 *
	 * @since 1.1
	 */
	public function admin_page_we_use() {
		$page             = get_current_screen()->base;
		$display_on_pages = array(
			'dashboard',
			'plugins',
			'tools',
			'options-general',
			$this->settings_hook,
		);

		return in_array( $page, $display_on_pages );
	}

	/**
	 * @since 1.0
	 */
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
					<button class="tablinks" onclick="openPDLTab(event, 'BTC')"><img height="32"
																					 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BTC.png'; ?>">
					</button>
					<button class="tablinks" onclick="openPDLTab(event, 'PP')"><img height="32"
																					src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/PP.png'; ?>">
					</button>
					<button class="tablinks" onclick="openPDLTab(event, 'BCH')"><img height="32"
																					 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BCH.png'; ?>"><br>Bitcoin
						Cash
					</button>
					<button class="tablinks" onclick="openPDLTab(event, 'ETH')"><img height="32"
																					 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/ETH.png'; ?>"><br>Ethereum
					</button>
					<button class="tablinks" onclick="openPDLTab(event, 'DOGE')"><img height="32"
																					  src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/DOGE.png'; ?>"><br>Dogecoin
					</button>

				</div>

				<!-- Tab content -->
				<div class="tabcontentwrap">
					<div id="BTC" class="tabcontent">
						<div>
							<img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BTC.png'; ?>">
						</div>
						<div>
							<?php esc_html_e( 'My Bitcoin donation wallet', 'plugin-donation-lib' ); ?><br><br> <strong><a
										href="https://www.blockchain.com/btc/address/bc1q04zt3yxxu282ayg3aev633twpqtw0dzzetp78x">bc1q04zt3yxxu282ayg3aev633twpqtw0dzzetp78x</a></strong>
						</div>
						<div>
							<img height="140"
								 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/BTC.png'; ?>">
						</div>
					</div>
					<div id="PP" class="tabcontent">
						<div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
								target="_blank"><img height="48"
													 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/PP.png'; ?>">
							</a></div>
						<div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
								target="_blank"><?php esc_html_e( 'Gift a donation via PayPal', 'plugin-donation-lib' ); ?>
							</a></div>
						<div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
								target="_blank"><img height="48"
													 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/PPcards.png'; ?>">
							</a></div>
					</div>
					<div id="BCH" class="tabcontent">
						<div><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/BCH.png'; ?>">
						</div>
						<div>
							<?php esc_html_e( 'My Bitcoin Cash address', 'plugin-donation-lib' ); ?><br><br><strong>bitcoincash:qpmn76wad2mwfhk3c9vhx77ex5nqhq2r0ursp8z6mp</strong>
						</div>
						<div>
							<img height="140"
								 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/BCH.png'; ?>">
						</div>
					</div>

					<div id="ETH" class="tabcontent">
						<div><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/ETH.png'; ?>">
						</div>
						<div>
							<?php esc_html_e( 'My Ethereum address', 'plugin-donation-lib' ); ?><br><br><strong>0x492Bdf65bcB65bC067Ab3886e9B79a7CDe9021BB</strong>
						</div>
						<div>
							<img height="140"
								 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/ETH.png'; ?>">
						</div>
					</div>
					<div id="DOGE" class="tabcontent">
						<h3><img height="48" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/DOGE.png'; ?>">Dogecoin
						</h3>
						<div>
							<?php esc_html_e( 'My Dogecoin address', 'plugin-donation-lib' ); ?><br><br><strong>D7nB2HsBxNPACis9fSgjqTShe4JfSztAjr</strong>
						</div>
						<div>
							<img height="140"
								 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/QRcodes/DOGE.png'; ?>">
						</div>
					</div>
				</div>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Contribute', 'plugin-donation-lib' ); ?></th>
			<td>
				<h3>
					<?php esc_html_e( 'Contribute to the Open Source Project in other ways', 'plugin-donation-lib' ); ?>
				</h3>
				<!-- Tab links -->
				<div class="tab">
					<button class="tablinks" onclick="openPDLTab(event, 'review-tab')"><img height="32"
																							src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/reviews.png'; ?>"><br><?php esc_html_e( 'Submit a review', 'plugin-donation-lib' ); ?>
					</button>
					<button class="tablinks" onclick="openPDLTab(event, 'translate-tab')"><img height="32"
																							   src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/translate.png'; ?>"><br><?php esc_html_e( 'Translate to your language', 'plugin-donation-lib' ); ?>
					</button>
					<button class="tablinks" onclick="openPDLTab(event, 'github-tab')"><img height="32"
																							src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/github.png'; ?>"><br>Help
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
													src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/translate.png'; ?>">
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
													 src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logos/github.png'; ?>"></a>
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
				   target="_blank"><?php esc_html_e( 'WordPress SUPPORT FORUM', 'plugin-donation-lib' ); ?></a>
			</td>
		</tr>
		<?php
	}

	/**
	 * @since 1.0
	 */
	public function display_admin_notice() {
		$this->set_timers();
		// Don't display notices to users that can't do anything about it.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		// Notices are only displayed on the dashboard, plugins, tools, and settings admin pages.
		if ( ! $this->admin_page_we_use() ) {
			return;
		}
		$user_id       = get_current_user_id();
		$um            = get_user_meta( $user_id, $this->plugin_slug . '_pdlib_dismissed_notices', true );
		$notice_donate = $this->plugin_slug . '_pdlib_notice_donate';
		if ( ! isset( $um[ $notice_donate ] ) || true !== $um[ $notice_donate ] ) {
			$donate = get_option( $this->plugin_slug . '_donate' );
			if ( false !== $donate && time() > (int) $donate + ( 6 * WEEK_IN_SECONDS ) ) {
				?>
				<div id="<?php echo esc_attr( $notice_donate ); ?>"
					 class="pdl_notice notice is-dismissible notice-warning">
					<p>
						<?php
						echo esc_html__( 'Hi I\'m Alan and I support the free plugin', 'plugin-dontaion-lib' ) .
							 ' <strong>' . esc_html( $this->title ) .
							 '</strong> ' . esc_html__( 'for you.  You have been using the plugin for a while now and WordPress has probably been through several updates by now. So I\'m asking if you can help keep this plugin free, by donating a very small amount of cash. If you can that would be a fantastic help to keeping this plugin updated.', 'plugin-donate-lib' );
						?>
					</p>
					<p>
						<a href="<?php echo esc_attr( $this->donate_page ); ?>"><?php esc_html_e( 'Donate via this page', 'plugin-donate-lib' ); ?></a>
					</p>
					<p><a class="remind" href=""><?php esc_html_e( 'Remind me later', 'plugin-donate-lib' ); ?></a></p>
					<p><a class="dismiss"
						  href=""><?php esc_html_e( 'I have already donated', 'plugin-donate-lib' ); ?></a></p>
					<p><a class="dismiss"
						  href=""><?php esc_html_e( 'I don\'t want to donate, dismiss this notice permanently', 'plugin-donate-lib' ); ?></a>
					</p>
				</div>
				<?php
			}
		}
		$notice_review = $this->plugin_slug . '_pdlib_notice_review';
		if ( ! isset( $um[ $notice_review ] ) || true !== $um[ $notice_review ] ) {
			$review = get_option( $this->plugin_slug . '_review' );
			if ( false !== $review && time() > (int) $review + ( 4 * WEEK_IN_SECONDS ) ) {
				?>
				<div id="<?php echo esc_attr( $notice_review ); ?>"
					 class="pdl_notice notice is-dismissible notice-sucess">
					<p>
						<?php
						echo esc_html__( 'Hi I\'m Alan and you have been using this plugin', 'plugin-dontaion-lib' ) .
							 ' <strong>' . esc_html( $this->title ) .
							 '</strong> ' . esc_html__( 'for a while - that is awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help spread the word and boost my motivation..', 'plugin-donate-lib' );
						?>
					</p>
					<p>
						<a target="_blank"
						   href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/reviews/?view=all#new-post"><?php esc_html_e( 'OK, you deserve it', 'plugin-donate-lib' ); ?></a>
					</p>
					<p><a class="remind" href=""><?php esc_html_e( 'Maybe later', 'plugin-donate-lib' ); ?></a></p>
					<p><a class="dismiss"
						  href=""><?php esc_html_e( 'Already done', 'plugin-donate-lib' ); ?></a></p>
					<p><a class="dismiss"
						  href=""><?php esc_html_e( 'No thanks, dismiss this request', 'plugin-donate-lib' ); ?></a>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * @since 1.0
	 */
	public function pdl_dismiss_notice() {
		if ( ! $this->valid_ajax_call() ) {
			return;
		}
		$user_id = get_current_user_id();
		$um      = get_user_meta( $user_id, $this->plugin_slug . '_pdlib_dismissed_notices', true );
		if ( ! is_array( $um ) ) {
			$um = array();
		}
		$um[ sanitize_text_field( $_POST['id'] ) ] = true;
		update_user_meta( $user_id, $this->plugin_slug . '_pdlib_dismissed_notices', $um );
		wp_die();
	}

	/**
	 * Check if doing ajax and capability
	 *
	 * @return bool
	 *
	 * @since 1.1
	 */
	private function valid_ajax_call() {
		if ( ! wp_doing_ajax() ) {
			return false;
		}
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @since 1.0
	 */
	public function pdl_later_notice() {
		if ( ! $this->valid_ajax_call() ) {
			return;
		}
		if ( sanitize_text_field( $_POST['id'] ) === $this->plugin_slug . '_pdlib_notice_donate' ) {
			// donate later
			$donate = get_option( $this->plugin_slug . '_donate' );
			if ( false !== $donate ) {
				update_option( $this->plugin_slug . '_donate', (int) $donate + ( 6 * WEEK_IN_SECONDS ) );
			}
		}
		if ( sanitize_text_field( $_POST['id'] ) === $this->plugin_slug . '_pdlib_notice_review' ) {
			// review later
			$review = get_option( $this->plugin_slug . '_review' );
			if ( false !== $review ) {
				update_option( $this->plugin_slug . '_review', (int) $review + ( 4 * WEEK_IN_SECONDS ) );
			}
		}
		wp_die();
	}
}
