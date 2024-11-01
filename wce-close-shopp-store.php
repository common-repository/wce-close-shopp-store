<?php
/**
 *      Plugin name: WCE Close Shopp Store
 *      Plugin URI: http://wordpress.org/extend/plugins/wce-close-shopp-store/
 *      Author: WriteCraft Enterprises
 *      Author URI: http://www.writecraft.net
 *      Description: Close your Shopp store for maintenance, for a season, or for holidays, and redirect Shopp virtual pages to an editable Shopp Closed virtual page, or to an external link of your choice.  While closed, all your Shopp pages are still viewable for logged in Shopp Merchants and Administrators. Includes a dashboard widget so you can see at a glance whether your store is opened or closed, and where there is a button for easy access to the 'Close Shopp' options page.  Requires Shopp e-commerce plugin from shopplugin.net.
 *      Version: 1.0.1
 *      License: GNU General Public License v2.0
 *      License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
 ------------------------------------------------------------------------
    WriteCraft Enterprises 'Close Shopp Store', Copyright 2013 WriteCraft Enterprises (support@writecraft.net)
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
**/

function wce_shopp_modern_version_check() {
    if ( version_compare( SHOPP_VERSION , '1.2' , '<')) {
            
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            
    deactivate_plugins ( plugin_basename( __FILE__ ) );
            
    wp_die( __( 'This plugin requires at least Shopp 1.2.x. A current version of Shopp is not installed or is currently inactive. Please install and/or activate Shopp before activating the WCE Close Shopp Store plugin.', 'wce-close-shop-store' ), __( 'WCE Close Shopp Store', 'wce-close-shop-store' ), array( 'back_link' => true ));
            
    } else {
        $WceCloseShoppStore = new WceCloseShoppStore();
    }
}
add_action( 'plugins_loaded', 'wce_shopp_modern_version_check' );

class WceCloseShoppStore {
	
	
	protected $redirType;
	protected $ShoppClosed;
	protected $isShoppOpen;
	protected $StoreStatusMsg;
	protected $xlinkPage;
	
	
    public function __construct(){
		
	  function WceCloseShoppStore_activation() {
			$pluginOptions = get_option('wce_close_shopp_store_options');
				if ( false === $pluginOptions ) {
					// Install plugin
						$new_options = array(
						'version'=>'1.0.1',
						'closedtype'=>'open',
						'isopen'=>TRUE,
						'status'=>'OPEN',
						'redirtype'=>'storeclosedpage',
						'redirxlink'=> 'http://',
						'datefrom'=>'none',
						'dateto'=>'none',
						'pagetitle'=>'This Here Store is Closed, Pardner',
						'pagecontent'=>'Our store is closed until further notice.  Please check back again soon!'
		);
		
						add_option( 'wce_close_shopp_store_options', $new_options );
				} else if ( WceCloseShoppStore_VER != $pluginOptions['version'] ) {
					// Upgrade plugin
				}
			}
		
	
		register_activation_hook(__FILE__, 'WceCloseShoppStore_activation');
		
		
		$opt = get_option( 'wce_close_shopp_store_options');
		$this->isShoppOpen = TRUE;
        $this->ShoppClosed = $opt['closedtype'];
		$this->isShoppOpen = $opt['isopen'];
		$this->StoreStatusMsg = $opt['closedtype'];
		$this->redirType = $opt['redirtype'];
		
        if( $this->ShoppClosed == 'closeNow' ){
			$this->isShoppOpen = FALSE;
			$this->StoreStatusMsg = 'CLOSED';
		} elseif( $this->ShoppClosed == 'closeDates' ){
			$this->isShoppOpen = FALSE;	
			$this->StoreStatusMsg = 'CLOSED';		
		} else {
			$this->isShoppOpen = TRUE;
			$this->StoreStatusMsg = 'OPEN';
		}
		add_action( 'admin_menu', array(&$this, 'options_page_init') );
        add_action( 'register_activaton_hook', array(&$this, 'upon_install') );

		add_action( 'template_redirect', array(&$this, 'wce_shopp_open_close' )  );
				
		add_action( 'wp_dashboard_setup',  array(&$this, 'wce_shopp_closed_db_widget') );
    
		// add stylesheet for dashboard icon
		function admin_register_head() {
    	$siteurl = get_option('siteurl');
    	$url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/style.css';
   		 echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
		}
		add_action('admin_head', 'admin_register_head');	
	}
	
	
   public function options_page_init(){
	   
	   function db_shopp_menu_position() {
			global $menu;
			$pos = 53;
			foreach ($menu as $key => $menuitem) {
				if($menuitem[5] == 'toplevel_page_shopp-settings') $pos = $key+1;
			}
			return $pos;		
		}
	   
	   
         if(!current_user_can('shopp_products')){  
        		wp_die( __('You do not have sufficient permissions to access this page.'));  
    	 }  
		 	$position = db_shopp_menu_position();
            $hooks = array();
			$hooks[] = add_menu_page(__('Close Shopp'), __('Close Shopp'), 'shopp_products', 'wce-close-shop-store', array($this, 'option_page'), __(null), __($position));

			foreach($hooks as $hook) {
				add_action("admin_print_styles-{$hook}", array($this, 'load_assets'));
			}
    }

   public function set_shopp_closed(){
	   
        if( $_POST['wcecloseshopp'] == 'closeNow' ){
            $this->ShoppClosed = 'closeNow';     
			$this->isShoppOpen = FALSE;
			$this->StoreStatusMsg = 'CLOSED NOW';
		} elseif( $_POST['wcecloseshopp'] == 'closeDates' ){
            $this->ShoppClosed = 'closeDates' ;    
			$this->isShoppOpen = FALSE;
			$this->StoreStatusMsg = 'CLOSED for Dates ';
		} else {
			$this->ShoppClosed = 'open';
			$this->isShoppOpen = TRUE;
			$this->StoreStatusMsg = 'OPEN';
		}
		
		$opt = get_option( 'wce_close_shopp_store_options');
		
		
		$opt['closedtype'] = $this->ShoppClosed;
		$opt['isopen'] = $this->isShoppOpen;

		
		if ($_POST['wcecloseshopp'] == 'closeDates') {
			$opt['startDay'] = $_POST['start_day'];
			$opt['startMonth'] = $_POST['start_month'];
			$opt['startYear'] = $_POST['start_year'];
			$opt['endDay'] = $_POST['end_day'];
			$opt['endMonth'] = $_POST['end_month'];
			$opt['endYear'] = $_POST['end_year'];
			$this->StoreStatusMsg = $this->StoreStatusMsg .  $opt['startMonth']. '/' . $opt['startDay'] . '/' . $opt['startYear'] . ' until ' .  $opt['endMonth']. '/' . $opt['endDay'] . '/' . $opt['endYear'];
		} else {
			$opt['startDay'] = '';
			$opt['startMonth'] = '';
			$opt['startYear'] = '';
			$opt['endDay'] = '';
			$opt['endMonth'] = '';
			$opt['endYear'] = '';
		}
		
		$opt['status'] = $this->StoreStatusMsg;
		
		$opt['redirtype'] = $_POST['wceredirtype'];
		$opt['redirxlink'] = $_POST['xlinkurl'];
		$opt['pagetitle'] = $_POST['wceclosedpagetitle'];
		$htmlcontent = $_POST['wceclosedpagecontent'];		
		$opt['pagecontent'] = stripslashes($htmlcontent);
		
		update_option( 'wce_close_shopp_store_options', $opt );
	}
	  
    
    public function option_page(){
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ) self::set_shopp_closed();
        require_once 'assets/views/close-shopp-admin.php';
    }
	
	
	public function wce_shopp_closed_db_widget() {	
	  global $wp_meta_boxes;
	  

		function wce_close_shopp_status() {
			$msg = get_option( 'wce_close_shopp_store_options');
			echo '<table width="100%"><tr><td><strong>';
			echo $msg['status'];
			echo '</strong></td>';
			echo '<td align="right">';
			echo '<a class="button secondary" href="' . get_admin_url() . '/admin.php?page=wce-close-shop-store">';
			echo '<strong> OPEN / CLOSE SHOPP </strong></a>';
			echo '</td></tr></table>';
		}
		
		wp_add_dashboard_widget('wce_shopp_closed_message',
						'Your Shopp Store is . . .', 
						'wce_close_shopp_status' );
	}
	
	public function wce_shopp_open_close() {
		   global $wp;
           $plugindir = dirname( __FILE__ );
		   $opt = get_option( 'wce_close_shopp_store_options');
		   
		   function do_theme_redirect($url) {
				global $post, $wp_query;
				if (have_posts()) {
					include($url);
					die();
				} else {
					$wp_query->is_404 = true;
				}
		   }
		   
		function wce_add_page_content() {
			$opt = get_option( 'wce_close_shopp_store_options');
			$content = $opt['pagecontent'];
			return  $content;
		}

		function wce_add_page_title($title) {
			$opt = get_option( 'wce_close_shopp_store_options');
			if ( in_the_loop() ) {
				 return $opt['pagetitle'];
			} else {
				 return $title;
			}
		}
		
		function wce_admin_title($title) {
			if ( in_the_loop() ) {
				 $admintitle = "YOUR SHOPP STORE IS CLOSED! - " . $title ;
				 echo $admintitle;
			} else {
				 return $title;
			}
		}
				
		// set TRUE for Shopp is Open, FALSE for Shopp is Closed
		$isOpen = $opt['isopen'];
		// see if it is a shopp page
		
		function wce_is_shopp_pages() {
		$isShoppPage = FALSE;
			if (is_shopp_page()) $isShoppPage = TRUE;	
			if (shopp( 'storefront', 'is-frontpage' )) $isShoppPage = TRUE;
			if (shopp( 'catalog.is-landing')) $isShoppPage = TRUE;
			if (is_catalog_frontpage()) $isShoppPage = TRUE;
			if (is_account_page()) $isShoppPage = TRUE;
			if (is_shopp_product()) $isShoppPage = TRUE;	
			if (is_shopp_taxonomy()) $isShoppPage = TRUE;
			if (is_shopp_collection()) $isShoppPage = TRUE;
		return $isShoppPage;
		}	
		
		// see if current user is shopp_merchant or administrator
		$isAllowed = current_user_can('shopp_products');
		// send 'em somewhere else -- your home page, another store, a Shopp Closed page, endoftheinternet.com, whatever
		$redirtype = $opt['redirtype'];
		$xlink = $opt['redirxlink'];
		$xlinkPage = xlink_php_content;
			if (!$isOpen) {
				if ( wce_is_shopp_pages() && !$isAllowed) {
					if ($redirtype == 'storeclosedpage') {
						add_filter('the_content','wce_add_page_content');
						add_filter('the_title','wce_add_page_title');
       					$templatefilename = 'page.php';
						if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
							$return_template = TEMPLATEPATH . '/' . $templatefilename;
						} else {
							$return_template = $plugindir . '/assets/templates/' . $templatefilename;
						}
						do_theme_redirect($return_template);
					} else {
						// header( 'Location: ' . $xlink . '');
						// header('HTTP/1.1 302 Temporary Redirect');
						// exit;
						
						wp_redirect( $xlink , 302 ); 
						exit;						
					} // end external link oe closed page
				} else {
						if ( wce_is_shopp_pages() && $isAllowed) {
							add_filter('the_title','wce_admin_title', 10, 1);
						}
					} // end detect shopp pages	and who is allowed		
			} // end if Open/Closed
		

} // end of last function

} // End Class
		
