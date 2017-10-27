<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

<div class="wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
      <?php if (is_user_logged_in()) : ?>
      
            <h1>Envie um arquivo OPML com todos os podcasts que você assina!</h1>
            <p>A maioria dos agregadores de podcast tem a função de exportar sua lista de podcast como um arquivo .opml. Faça o upload deste arquivo para carregar sua
            lista de podcast toda de uma vez só.</p>

		    <form id="opml_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" enctype="multipart/form-data">
		    <input type="hidden" name="action" value="upload_opml">
            <label for="opml_file">Arquivo OPML</label>
            <input type="file" name="opml_file" id="opml_file" required>
            <br/><input id="opml_send_button" type="submit" value="Enviar">
            <div id="opml_loading" style=""><img style="width: 1em; margin: 0 0.3em;" src="<?php echo plugin_dir_url( __FILE__ ) . 'Loading_icon.gif' ?>" /> Aguarde. Carregando podcasts</div>
      <?php else : ?>
          <h1>Efetue o login</h1>
          <p>Para enviar sua lista de podcasts, é necessário efetuar o login</p>
      <?php endif; ?>
		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php get_footer();
