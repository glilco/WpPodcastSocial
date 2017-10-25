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
            <h1>Cadastre um podcast</h1>
            <p>Se você não encontrou algum podcast que você assina, envie o endereço do feed que ele será cadastrado.</p>

		    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
		    <input type="hidden" name="action" value="cadastra_podcast">
            <label for="feed_url">Podcast feed url</label>
            <input type="text" name="feed_url" id="feed_url" required>
            <input type="submit" value="Cadastrar">


		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php get_footer();
