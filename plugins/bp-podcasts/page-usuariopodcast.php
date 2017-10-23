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

			<?php
			while ( have_posts() ) : the_post();
				
				$usuario_username = get_query_var('podcastusername');
				
				$usuario = get_user_by( "slug", $usuario_username );
				
				if($usuario) {
                    $list_grou_ids = BP_Groups_Member::get_group_ids( $usuario->ID);
                    
                    if($list_grou_ids["total"] > 0) {
                        foreach ($list_grou_ids["groups"] as $key => $group_id) {
                            $group = groups_get_group( array( 'group_id' => $group_id ) );
                            ?>
                            <h3><?php echo $group->name ?></h3>
                            <p><?php echo $group->description ?></p>
                        <?php
                        }
                    } else {
                    ?>
                            <h2>O Usuário não tem nenhum podcast</h2>
                    <?php
                    }
				} else {
				    echo "<p>Usuario Inexistente</p>";
				}

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php get_footer();
