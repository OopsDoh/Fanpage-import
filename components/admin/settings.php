<?php
/**
 * Facebook Fanpage Import Admin Component.
 *
 * This class initializes the component.
 *
 * @author  mahype, awesome.ug <very@awesome.ug>
 * @package Facebook Fanpage Import
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2016 Awesome UG (very@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

use skip\v1_0_0 as skip;

class FacebookFanpageImportAdminSettings
{
	var $name;
	var $errors = array();
	var $notices = array();

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	function __construct()
	{
		$this->name = get_class( $this );

		if( is_admin() )
		{
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		if( '' != skip\value( 'fbfpi_settings', 'app_id' ) && '' != skip\value( 'fbfpi_settings', 'app_secret' ) && '' != skip\value( 'fbfpi_settings', 'page_id' ) )
		{
			$this->test_con();
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	/**
	 * Testing Connection to Facebook API
	 *
	 * @todo Adding functionality
	 */
	public function test_con()
	{
	}

	/**
	 * Adds the Admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu()
	{
		add_submenu_page( 'tools.php', __( 'Facebook Fanpage Import Settings', 'facebook-fanpage-import' ), __( 'Fanpage Import', 'facebook-fanpage-import' ), 'activate_plugins', 'Component' . $this->name, array(
			$this,
			'admin_page'
		) );
	}

	/**
	 * Content of the admin page.
	 *
	 * @since 1.0.0
	 */
	public function admin_page()
	{
		echo '<div class="wrap">';

		echo '<div id="icon-options-general" class="icon32 icon32-posts-post"></div>';
		echo '<h2>' . __( 'Facebook Fanpage Import', 'facebook-fanpage-import' ) . '</h2>';
		echo '<p>' . __( 'Just put in your Fanpage ID and start importing.', 'facebook-fanpage-import' ) . '</p>';

		skip\form_start( 'fbfpi_settings' );

		/**
		 * Fanpage ID
		 */
		skip\textfield( 'page_id', __( 'Page ID', 'facebook-fanpage-import' ) );

		/**
		 * Select stream languages
		 */
		$available_languages = get_available_languages();

		if( !in_array( 'en_US', $available_languages ) )
		{
			$available_languages[] = 'en_US';
		}

		foreach( $available_languages AS $language )
		{
			$select_languages[] = array( 'value' => $language );
		}

		skip\select( 'stream_language', $select_languages, __( 'Facebook Language', 'facebook-fanpage-import' ) );

		/**
		 * Import WP Cron settings
		 */
		$select_schedules = array( array( 'label' => __( 'Never', 'facebook-fanpage-import' ), 'value' => 'never' ) );
		$schedules = wp_get_schedules(); // Getting WordPress schedules
		foreach( $schedules AS $key => $schedule )
		{
			$select_schedules[] = array( 'label' => $schedule[ 'display' ], 'value' => $key );
		}

		skip\select( 'update_interval', $select_schedules, __( 'Import Interval', 'facebook-fanpage-import' ) );

		/**
		 * Num of entries to import
		 */
		skip\select( 'update_num', '5,10,25,50,100,250', __( 'Entries to import', 'facebook-fanpage-import' ) );

		/**
		 * Select where to import, as posts or as own post type
		 */
		$args = array(
			array(
				'value' => 'posts',
				'label' => __( 'Posts' )
			),
			array(
				'value' => 'status',
				'label' => __( 'Status message (own post type)', 'facebook-fanpage-import' )
			)
		);
		skip\select( 'insert_post_type', $args, __( 'Insert Messages as', 'facebook-fanpage-import' ) );

		/**
		 * Select a category to apply to imported entries
		 */
		$args = array(
			array(
				'value' => 'none',
				'label' => __( 'No category', 'facebook-fanpage-import' ),
			)
		);
		$terms = get_terms( 'category' );
		foreach( $terms AS $term ) {
			$args[] = array(
				'value' => $term->term_id,
				'label' => $term->name,
			);
		}

		skip\select( 'insert_term_id', $args, __( 'Categorise Messages as', 'facebook-fanpage-import' ) );

		/**
		 * Select importing User
		 */
		$users = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
		$user_list = array();

		foreach( $users AS $user )
		{
			$user_list[] = array(
					'value' => $user->ID,
					'label' => $user->display_name
			);
		}

		skip\select( 'insert_user_id', $user_list, __( 'Inserting User', 'facebook-fanpage-import' ) );

		/**
		 * Post status
		 */
		$post_status_values = array(
			array(
				'value' => 'publish',
				'label' => __( 'Published' )
			),
			array(
				'value' => 'draft',
				'label' => __( 'Draft' )
			),
		);

		skip\select( 'insert_post_status', $post_status_values, __( 'Post status', 'facebook-fanpage-import' ) );

		/**
		 * Link target for imported links
		 */
		$link_select_values = array(
			array(
				'value' => '_self',
				'label' => __( 'same window', 'facebook-fanpage-import' )
			),
			array(
				'value' => '_blank',
				'label' => __( 'new window', 'facebook-fanpage-import' )
			),
		);

		skip\select( 'link_target', $link_select_values, __( 'Open Links in', 'facebook-fanpage-import' ) );

		/**
		 * Selecting post formats if existing
		 */
		if( current_theme_supports( 'post-formats' ) )
		{
			$post_formats = get_theme_support( 'post-formats' );

			if( FALSE != $post_formats )
			{
				$post_formats = $post_formats[ 0 ];
				$post_format_list = array();

				$post_format_list[] = array(
						'value' => 'none',
						'label' => __( '-- None --', 'facebook-fanpage-import' )
				);

				foreach( $post_formats as $post_format )
				{
					$post_format_list[] = array(
							'value' => $post_format,
							'label' => $post_format
					);
				}
				skip\select( 'insert_post_format', $post_format_list, __( 'Post format', 'facebook-fanpage-import' ) );
			}
		}

		skip\checkbox( 'own_css', 'yes', __( 'Deactivate Plugin CSS', 'facebook-fanpage-import' ) );

		do_action( 'fbfpi_settings_form' );

		/**
		 * Save Button
		 */
		skip\button( __( 'Save', 'facebook-fanpage-import' ) );

		/**
		 * Import button
		 */
		if( '' != skip\value( 'fbfpi_settings', 'page_id' ) )
		{
			if ( ! get_option( '_facebook_fanpage_import_next', false ) )
			{
				echo ' <input type="submit" name="bfpi-now" value="' . __( 'Import Now', 'facebook-fanpage-import' ) . '" class="button" style="margin-left:10px;" /> ';
			} else {
				echo ' <input type="submit" name="bfpi-next" value="' . __( 'Import Next', 'facebook-fanpage-import' ) . '" class="button" style="margin-left:10px;" /> <input type="submit" name="bfpi-stop" value="' . __( 'Stop', 'facebook-fanpage-import' ) . '" class="button" style="margin-left:10px;" /> ';
			}
		}

		skip\form_end();

		echo '</div>';
	}

	public function admin_notices()
	{
		if( count( $this->errors ) > 0 )
		{
			foreach( $this->errors AS $error )
				echo '<div class="updated"><p>' . __( 'Facebook Fanpage Import', 'facebook-fanpage-import' ) . ': ' . $error . '</p></div>';
		}

		if( count( $this->notices ) > 0 )
		{
			foreach( $this->notices AS $notice )
			{
				echo '<div class="updated"><p>' . __( 'Facebook Fanpage Import', 'facebook-fanpage-import' ) . ': ' . $notice . '</p></div>';
			}
		}
	}
}

$FacebookFanpageImportAdminSettings = new FacebookFanpageImportAdminSettings();
