<?php
/**
 * Plugin Name: Virtuaria - Integração de Catálogo com Redes Sociais
 * Plugin URI: https://virtuaria.com.br
 * Description: Gerencia feed de produtos e Pixel para o facebook business.
 * Author: Virtuaria
 * Author URI: https://virtuaria.com.br/
 * Version: 1.2.0
 * License: GPLv2 or later
 *
 * @package Virtuaria/Integration/Facebook.
 */

defined( 'ABSPATH' ) || exit;

register_activation_hook( __FILE__, array( 'Virtuaria_Facebook_Integration', 'initialize_facebook_shopping_integration' ) );
register_deactivation_hook( __FILE__, array( 'Virtuaria_Facebook_Integration', 'deactivation_event_schedule' ) );
if ( ! class_exists( 'Virtuaria_Facebook_Integration' ) ) :
	require_once 'includes/class-generate-products-xml.php';
	/**
	 * Class definition.
	 */
	class Virtuaria_Facebook_Integration extends Generate_Products_XML {
		/**
		 * Instance from class.
		 *
		 * @var Virtuaria_Facebook_Integration
		 */
		private static $instance = null;

		/**
		 * Pixel code.
		 *
		 * @var string
		 */
		private $pixel;

		/**
		 * Initialize classe functions.
		 */
		private function __construct() {
			if ( ! class_exists( 'Woocommerce' ) ) {
				add_action( 'admin_notices', array( $this, 'missing_dependency' ) );
				return;
			}

			add_action( 'admin_menu', array( $this, 'add_submenu' ) );
			add_action( 'save_facebook_setup', array( $this, 'save_facebook_setup' ) );
			add_action( 'admin_init', array( $this, 'regenerate_feed' ) );
			add_action( 'facebook_generate_feed', array( $this, 'generate_feed' ) );
			add_action( 'init', array( $this, 'register_endpoint' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'template_include', array( $this, 'redirect_to_shopping_file' ) );
			add_action( 'virtuaria_ignore_product_to_feed_shopping', array( $this, 'ignore_products_from_category_groups' ), 10, 3 );
			add_filter( 'cron_schedules', array( $this, 'facebook_cron_events_frequency' ) );
			add_action( 'in_admin_footer', array( $this, 'display_review_info' ) );
			$this->pixel = get_option( 'virtuaria_facebook_pixel_code' );
			add_action( 'init', array( $this, 'virtuaria_facebook_image_size' ) );

			if ( $this->pixel ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'handle_scripts' ) );
			}
		}

		/**
		 * Get the class instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Display warning about missing dependency.
		 */
		public function missing_dependency() {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_attr_e( 'Virtuaria Integração com Facebook need Woocommerce 4.0+ to work!', 'virtuaria-facebook-integration' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Add submenu in marketing.
		 */
		public function add_submenu() {
			global $submenu;

			if ( isset( $submenu['marketing'] ) ) {
				add_submenu_page(
					'marketing',
					'Integração Facebook',
					'Integração Facebook',
					'remove_users',
					'facebook_integration',
					array( $this, 'setup_facebook_integration_page' )
				);
			} else {
				add_menu_page(
					'Integração Facebook',
					'Integração Facebook',
					'remove_users',
					'facebook_integration',
					array( $this, 'setup_facebook_integration_page' ),
					'dashicons-facebook'
				);
			}
		}

		/**
		 * Display Facebook integration setup.
		 */
		public function setup_facebook_integration_page() {
			require_once 'templates/form-setup-page.php';
		}

		/**
		 * Save facebook setup.
		 */
		public function save_facebook_setup() {
			if ( isset( $_POST['_wpnonce'] )
				&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'pixel_integration' )
				&& isset( $_POST['pixel'] ) ) {
				update_option( 'virtuaria_facebook_pixel_code', sanitize_text_field( wp_unslash( $_POST['pixel'] ) ) );

				$categories = isset( $_POST['tax_input']['product_cat'] )
					? array_map( 'sanitize_text_field', wp_unslash( $_POST['tax_input']['product_cat'] ) )
					: '';
				update_option( 'virtuaria_facebook_ignore_categories', $categories );

				$groups = isset( $_POST['tax_input']['product_group'] )
					? array_map( 'sanitize_text_field', wp_unslash( $_POST['tax_input']['product_group'] ) )
					: '';
				update_option( 'virtuaria_facebook_ignore_groups', $groups );

				if ( isset( $_POST['frequency'] ) ) {
					update_option( 'virtuaria_facebook_frequency_feed', sanitize_text_field( wp_unslash( $_POST['frequency'] ) ) );

					$event = wp_get_scheduled_event( 'facebook_generate_feed' );
					if ( $event && $_POST['frequency'] !== $event->schedule ) {
						wp_clear_scheduled_hook( 'facebook_generate_feed' );
						wp_schedule_event(
							strtotime( '05:00:00' ),
							sanitize_text_field( wp_unslash( $_POST['frequency'] ) ),
							'facebook_generate_feed'
						);
					}
				}

				if ( isset( $_POST['fb_image_size'] ) ) {
					update_option(
						'virtuaria_facebook_image_size',
						'yes'
					);
				} else {
					delete_option( 'virtuaria_facebook_image_size' );
				}
				echo '<div id="message" class="updated success">Configuração salva com sucesso!</div>';
			} else {
				echo '<div id="message" class="updated error">Desculpe! Não foi possível salvar esta configuração, tente novamente.</div>';
			}
		}

		/**
		 * Add scripts.
		 */
		public function handle_scripts() {
			wp_enqueue_script(
				'pixel-stats',
				plugin_dir_url( __FILE__ ) . '/public/js/pixel.js',
				array( 'jquery' ),
				filemtime( plugin_dir_path( __FILE__ ) . '/public/js/pixel.js' ),
				true
			);

			$setup = array( 'pixel' => $this->pixel );
			if ( is_wc_endpoint_url( 'order-received' ) ) {
				global $wp;

				$order_id = absint( $wp->query_vars['order-received'] );
				$order    = wc_get_order( $order_id );

				foreach ( $order->get_items() as $item ) {
					$setup['content_ids'][] = $item->get_id();
					$setup['contents'][]    = array(
						'id'       => $item->get_id(),
						'quantity' => $item->get_quantity(),
					);
				}

				$setup['value']        = $order->get_total();
				$setup['currency']     = get_option( 'woocommerce_currency' );
				$setup['content_name'] = 'Pedido Finalizado';
				$setup['content_type'] = 'product';
				$setup['content_ids']  = '[' . implode( ',', $setup['content_ids'] ) . ']';
				$setup['contents']     = wp_json_encode( $setup['contents'] );
				$setup['currency']     = get_option( 'woocommerce_currency' );
				$setup['num_items']    = count( $order->get_items() );
			}

			if ( is_product() ) {
				global $post;

				$product    = wc_get_product( $post->ID );
				$categories = $product->get_category_ids();

				$setup['content_ids']      = $post->ID;
				$setup['content_name']     = $post->post_title;
				$setup['contents'][]       = array(
					'id'       => $post->ID,
					'quantity' => 1,
				);
				$setup['contents']         = wp_json_encode( $setup['contents'] );
				$setup['content_type']     = $product->is_type( 'simple' ) ? 'product' : 'product_group';
				$setup['currency']         = get_option( 'woocommerce_currency' );
				$setup['value']            = $product->get_price();
				$setup['content_category'] = $categories ? get_term( $categories[0] )->name : '';
			}

			if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
				foreach ( WC()->cart->get_cart() as $item ) {
					$setup['content_ids'][] = $item['product_id'];
					$setup['contents'][]    = array(
						'id'       => $item['product_id'],
						'quantity' => $item['quantity'],
					);
				}
				$setup['content_ids'] = '[' . implode( ',', $setup['content_ids'] ) . ']';
				$setup['contents']    = wp_json_encode( $setup['contents'] );
				$setup['currency']    = get_option( 'woocommerce_currency' );
				$setup['num_items']   = count( WC()->cart->get_cart() );
				$setup['value']       = WC()->cart->get_total( 'float' );
			}

			wp_localize_script(
				'pixel-stats',
				'setup',
				$setup
			);
		}

		/**
		 * Run action to initialiaze facebook shopping integrate.
		 *
		 * @return void
		 */
		public static function initialize_facebook_shopping_integration() {
			if ( ! wp_next_scheduled( 'facebook_generate_feed' ) ) {
				wp_schedule_event( strtotime( '05:00:00' ), 'daily', 'facebook_generate_feed' );
			}
		}

		/**
		 * Uneschedule event on deactivation plugin.
		 *
		 * @return void
		 */
		public static function deactivation_event_schedule() {
			wp_clear_scheduled_hook( 'facebook_generate_feed' );
		}

		/**
		 * Create feed file.
		 */
		public function generate_feed() {
			$this->build_products_xml( plugin_dir_path( __FILE__ ), 'facebook shopping' );
		}

		/**
		 * Endpoint to homolog file.
		 */
		public function register_endpoint() {
			add_rewrite_rule( 'virtuaria-facebook-shopping(/)?', 'index.php?virtuaria-facebook-shopping=sim', 'top' );
		}

		/**
		 * Add query vars.
		 *
		 * @param array $query_vars the query vars.
		 * @return array
		 */
		public function add_query_vars( $query_vars ) {
			$query_vars[] = 'virtuaria-facebook-shopping';
			return $query_vars;
		}

		/**
		 * Redirect access to confirm page.
		 *
		 * @param string $template the template path.
		 * @return string
		 */
		public function redirect_to_shopping_file( $template ) {
			if ( false == get_query_var( 'virtuaria-facebook-shopping' ) ) {
				return $template;
			}

			return plugin_dir_path( __FILE__ ) . '/includes/download-shopping-file.php';
		}

		/**
		 * Ignore product categories.
		 *
		 * @param boolean    $ignore  true if product should be ignored.
		 * @param wc_product $product instance from product.
		 * @param string     $caller  identify caller from xml generate.
		 */
		public function ignore_products_from_category_groups( $ignore, $product, $caller ) {
			if ( 'facebook shopping' !== $caller ) {
				return $ignore;
			}

			$categories = get_option( 'virtuaria_facebook_ignore_categories' );
			$groups     = get_option( 'virtuaria_facebook_ignore_groups' );

			if ( $categories ) {
				$terms = wp_get_post_terms(
					$product->get_id(),
					'product_cat',
					array( 'include' => $categories )
				);

				if ( ! empty( $terms ) ) {
					$ignore = true;
				}
			}

			if ( ! $ignore && $groups ) {
				$terms = wp_get_post_terms(
					$product->get_id(),
					'product_group',
					array( 'include' => $groups )
				);

				if ( ! empty( $terms ) ) {
					$ignore = true;
				}
			}
			return $ignore;
		}

		/**
		 * Add custom schedules time.
		 *
		 * @param array $schedules the current schedules.
		 * @return array
		 */
		public function facebook_cron_events_frequency( $schedules ) {
			if ( ! isset( $schedules['daily'] ) ) {
				$schedules['daily'] = array(
					'interval' => 1 * DAY_IN_SECONDS,
					'display'  => 'Uma vez ao dia',
				);
			}

			if ( ! isset( $schedules['twice_day'] ) ) {
				$schedules['twice_day'] = array(
					'interval' => 12 * HOUR_IN_SECONDS,
					'display'  => 'A cada 12 horas',
				);
			}

			if ( ! isset( $schedules['every_eight_hours'] ) ) {
				$schedules['every_eight_hours'] = array(
					'interval' => 8 * HOUR_IN_SECONDS,
					'display'  => 'A cada 8 horas',
				);
			}

			if ( ! isset( $schedules['every_six_hours'] ) ) {
				$schedules['every_six_hours'] = array(
					'interval' => 6 * HOUR_IN_SECONDS,
					'display'  => 'A cada 6 horas',
				);
			}

			return $schedules;
		}

		/**
		 * Force regenerate feed.
		 */
		public function regenerate_feed() {
			if ( isset( $_GET['page'] )
				&& isset( $_GET['_wpnonce'] )
				&& 'facebook_integration' === $_GET['page']
				&& ! isset( $_POST['pixel'] ) ) {
				if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'force_regenerate_feed' ) ) {
					$this->generate_feed();
					$message = '<div id="message" class="updated success">Feed atualizado com sucesso!</div>';
				} else {
					$message = '<div id="message" class="updated error">Desculpe! Não foi possível atualizar o feed, tente novamente.</div>';
				}
			}

			if ( $message ) {
				set_transient(
					'virtuaria_facebook_feed_message',
					$message,
					60
				);
			}
		}

		/**
		 * Review info.
		 */
		public function display_review_info() {
			if ( isset( $_GET['page'] )
				&& 'facebook_integration' === $_GET['page'] ) {
				echo '<style>#wpfooter{display: block;position:static;}#wpbody-content {
					padding-bottom: 0;
				}
				h4.stars {
					margin-bottom: 0;
				}
				#wpcontent {
					display: table;
				}</style>';
				echo '<h4 class="stars">Avalie nosso trabalho ⭐</h4>';
				echo '<p class="review-us">Apoie o nosso trabalho. Se gostou do plugin, deixe uma avaliação positiva clicando <a href="https://wordpress.org/support/plugin/virtuaria-catalogo-redes-sociais/reviews?rate=5#new-post " target="_blank">aqui</a>. Desde já, nossos agradecimentos.</p>';
				echo '<h4 class="stars">Tecnologia Virtuaria ✨</h4>';
				echo '<p class="disclaimer">Desenvolvimento, implantação e manutenção de e-commerces e marketplaces para atacado e varejo. Soluções personalizadas para cada cliente. <a target="_blank" href="https://virtuaria.com.br">Saiba mais</a>.</p>';
			}
		}

		/**
		 * Create image size.
		 */
		public function virtuaria_facebook_image_size() {
			$image_size = 'yes' === get_option( 'virtuaria_facebook_image_size' );
			if ( $image_size ) {
				add_image_size(
					'virtuaria_facebook_image_size',
					900,
					900,
					true
				);
			}
		}
	}

	add_action( 'plugins_loaded', array( 'Virtuaria_Facebook_Integration', 'get_instance' ) );

endif;
