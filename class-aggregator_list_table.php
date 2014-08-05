<?php

if ( class_exists( 'WP_List_Table' ) ) {

	class Aggregator_List_Table extends WP_List_Table {

		/**
		 * Constructor, we override the parent to pass our own arguments
		 */
		public function __construct() {
			parent::__construct( array(
				'singular'=> 'wp_list_aggregator_site', // Singular label
				'plural' => 'wp_list_aggregator_sites', // plural label, also this well be one of the table css class
				'ajax'   => false // We won't support Ajax for this table
			) );
		}

		/**
		 * Define the columns that are going to be used in the table
		 *
		 * @return array $columns, the array of columns to use with the table
		 */
		public function get_columns() {
			return array (
				'col_site_domain' => __('Domain'),
				'col_sync_sites' => __('Syncing From'),
			);
		}

		/**
		 * @todo pagination
		 */
		public function prepare_items() {

			// Get all the sites
			$sites = get_site_option( 'aggregator_sync_sites' );

			if ( ! empty( $sites ) )
				$this->items = $sites;
			else
				$this->items = array();

		}

		public function display_rows() {

			// Get the sync sites to display
			$sites = $this->items;
			if ( empty( $sites ) )
				$this->no_items();

			// Get the columns registered in the get_columns and get_sortable_columns methods
			list( $columns, $hidden ) = $this->get_column_info();

			foreach ( $sites as $portal => $sync_sites ) {

				// Get the site info
				$portal = get_blog_details( $portal );

				// Open the line
				echo '<tr id="record_'.$portal->blog_id.'">';
				foreach ( $columns as $column_name => $column_display_name ) {

					// Style attributes for each col
					$class = "class='$column_name column-$column_name'";
					$style = "";
					if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
					$attributes = $class . $style;

					// Display the cell
					switch ( $column_name ) {

						case 'col_site_domain':

							// Define the action links order
							$actions = array(
								'edit' => '',
								/* @todo make activate/deactivate a thing
								'activate' => '',
								'deactivate' => '',*/
								'delete' => '',
							);

							// Create the links
							$actions['edit'] = '<span class="edit"><a href="' . esc_url( network_admin_url( 'settings.php?page=aggregator&action=edit&id=' . $portal->blog_id ) ) . '">' . __( 'Edit' ) . '</a></span>';
							$actions['delete']	= '<span class="delete"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'settings.php?page=aggregator&action=delete&id=' . $portal->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to delete the sync settings for %s.' ), $portal->domain ) ) ), 'confirm') ) . '">' . __( 'Delete' ) . '</a></span>';

							echo "<td $attributes>" . $portal->domain . $this->row_actions( $actions ) . '</td>';
							break;

						case 'col_sync_sites':
							echo "<td $attributes>";

							// Loop through each sync site getting relevant details for output
							foreach ( $sync_sites as $sync_site ) {

								$sync_site = get_blog_details( $sync_site );

								echo $sync_site->domain . '<br/>';

							}

							echo '</td>';
							break;
					}
				}

			}

		}

		function no_items() {
			_e( 'No sync settings found.' );
		}

	}

}