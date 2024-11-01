<?php
/**
 * Handle setup Facebook Integration form.
 *
 * @package Virtuaria/Integration/Facebook
 */

defined( 'ABSPATH' ) || exit;

$generate_feed_message = get_transient( 'virtuaria_facebook_feed_message' );
if ( isset( $_POST['pixel'] ) ) {
	do_action( 'save_facebook_setup' );
} elseif ( $generate_feed_message ) {
	echo wp_kses_post( $generate_feed_message );
}


$pixel           = get_option( 'virtuaria_facebook_pixel_code' );
$selected_cats   = get_option( 'virtuaria_facebook_ignore_categories' );
$selected_groups = get_option( 'virtuaria_facebook_ignore_groups' );
$frequency_feed  = get_option( 'virtuaria_facebook_frequency_feed', 'daily' );
$fb_image_size   = get_option( 'virtuaria_facebook_image_size' );

$frequencys = array(
	'daily'             => __( 'Uma vez ao dia - 02:00', 'virtuaria-facebook-integration' ),
	'twice_day'         => __( 'Duas vezes ao dia - 02:00 e 14:00', 'virtuaria-facebook-integration' ),
	'every_eight_hours' => __( 'Três vez ao dia - 02:00, 10:00 e 18:00', 'virtuaria-facebook-integration' ),
	'every_six_hours'   => __( 'Quatro vez ao dia - 02:00, 08:00, 14:00 e 20:00', 'virtuaria-facebook-integration' ),
);
?>

<h2>Virtuaria - Integração de Catálogo com Redes Sociais</h2>
<span>Define a configuração usada durante a comunicação com o Facebook.</span>
<p class="feed-link">Acesse o feed gerado clicando <a target="_blank" href="<?php echo esc_url( home_url( 'virtuaria-facebook-shopping' ) ); ?>">aqui</a>.</p>

<form class="fb-setup" action="" method="POST">
	<label for="pixel">Código Pixel</label>
	<input type="text" name="pixel" id="pixel" value="<?php echo esc_attr( $pixel ); ?>"/>
	<small>Define o Pixel usado para conectar a loja virtual as ferramentas do Meta Business (Facebook Business). O Pixel gera dados de rastreamento a partir das interações dos usuários. É utilizado para mensurar o desempenho de campanhas e para integrar a experiência de compra com as redes da Meta.</small>
	<label for="frequency">Frequencia de atualização do feed</label>
	<select name="frequency" id="frequency">
		<?php
		foreach ( $frequencys as $index => $text ) {
			echo '<option ' . ( $frequency_feed === $index ? 'selected' : '' ) . ' value="' . esc_attr( $index ) . '">' . esc_html( $text ) . '</option>';
		}
		?>
	</select>
	<label for="image-size">Tamanho da Imagem</label>
	<input type="checkbox" name="fb_image_size" id="image-size" value="yes" <?php checked( 'yes', $fb_image_size ); ?>>
	<small>Marque para usar o tamanho de imagem otimizado para a Meta (900x900). Para que imagens pré-existentes utilizem o novo tamanho é preciso regerar as imagens, utilize o plugin <a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerate Thumbnails</a> para isto. O Meta requer que as imagens possuam a <b>proporção (1:1)</b>, sendo o tamanho mínimo <b>500x500</b>. Caso está opção não seja marcada, o plugin usará o tamanho original da imagem.</small>
	<label for="product_cat-all">Ignorar Categorias</label>
	<div id="product_cat-all" class="tabs-panel">
		<ul id="product_catchecklist" data-wp-lists="list:product_cat" class="categorychecklist form-no-clear">
			<?php
			wp_terms_checklist(
				0,
				array(
					'taxonomy'      => 'product_cat',
					'selected_cats' => $selected_cats,
				)
			);
			?>
		</ul>
	</div>
	<?php
	if ( taxonomy_exists( 'product_group' ) ) :
		?>
		<label for="product_group-all">Ignorar Grupos</label>
		<div id="product_group-all" class="tabs-panel">
			<ul id="product_groupchecklist" data-wp-lists="list:product_group" class="categorychecklist form-no-clear">
				<?php
				wp_terms_checklist(
					0,
					array(
						'taxonomy'      => 'product_group',
						'selected_cats' => $selected_groups,
					)
				);
				?>
			</ul>
		</div>
		<?php
	endif;
	?>
	<div class="actions">
		<input type="submit" value="Salvar Alterações" class="button button-primary button-large" />
		<a class="button button-primary button-large" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=facebook_integration' ), 'force_regenerate_feed' ) ); ?>">
			Regerar Feed
		</a>
	</div>
	<?php wp_nonce_field( 'pixel_integration' ); ?>
</form>

<style>
	small {
		display: block;
		max-width: 410px;
	}
	.feed-link {
		font-size: 16px;
		font-weight: bold;
	}
	label {
		font-size: 16px;
		display: block;
	}
	.fb-setup > label {
		font-weight: bold;
		margin: 30px 0 10px;
	}
	h2 {
		font-size: 1.6em;
	}
	.fb-setup {
		margin-top: 20px;
	}
	#pixel {
		width: 410px;
		margin-bottom: 10px;
	}
	#message.error,
	#message.success {
		padding: 12px;
		margin-left: 0;
	}
	#product_group-all,
	#product_cat-all {
		max-height: 300px;
		overflow-y: auto;
		max-width: 350px;
		background-color: #fff;
		padding: 10px;
		margin: 10px 0;
	}
	.fb-setup .button.button-primary.button-large {
		margin-top: 30px;
	}
	ul.children {
		margin-left: 20px;
	}
	.children .selectit {
		font-size: 95%;
	}
</style>
