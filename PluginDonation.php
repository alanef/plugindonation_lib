<?php

namespace AlanEFPluginDonation;

class PluginDonation {
	protected $plugin_slug;
	protected $settings_hook;

	public function __construct( $plugin_slug, $settings_hook ) {
		$this->plugin_slug = $plugin_slug;
		$this->hook        = $settings_hook;
		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
		wp_add_inline_style( 'admin-bar', $script );
	}
}