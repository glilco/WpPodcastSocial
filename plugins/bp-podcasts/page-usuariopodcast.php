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
				
				    $args = array(
                         'user_id' => $usuario->ID
                    );
                    if ( bp_has_groups($args) ) : ?>
 
    <div class="pagination">
 
        <div class="pag-count" id="group-dir-count">
            <?php bp_groups_pagination_count() ?>
        </div>
 
        <div class="pagination-links" id="group-dir-pag">
            <?php bp_groups_pagination_links() ?>
        </div>
 
    </div>
 
    <ul id="groups-list" class="item-list">
    <?php while ( bp_groups() ) : bp_the_group(); ?>
 
        <li>
            <div class="item-avatar">
                <a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar( 'type=thumb&width=50&height=50' ) ?></a>
            </div>
 
            <div class="item">
                <div class="item-title"><a href="<?php bp_group_permalink() ?>"><?php bp_group_name() ?></a></div>
                <div class="item-meta"><span class="activity"><?php printf( __( 'active %s ago', 'buddypress' ), bp_get_group_last_active() ) ?></span></div>
 
                <div class="item-desc"><?php bp_group_description_excerpt() ?></div>
 
                <?php do_action( 'bp_directory_groups_item' ) ?>
            </div>
 
            <div class="action">
                <?php bp_group_join_button() ?>
 
                <div class="meta">
                    <?php bp_group_type() ?> / <?php bp_group_member_count() ?>
                </div>
 
                <?php do_action( 'bp_directory_groups_actions' ) ?>
            </div>
 
            <div class="clear"></div>
        </li>
 
    <?php endwhile; ?>
    </ul>
 
    <?php do_action( 'bp_after_groups_loop' ) ?>
 
<?php else: ?>
 
    <div id="message" class="info">
        <p><?php _e( 'There were no groups found.', 'buddypress' ) ?></p>
    </div>
 
<?php endif; 
				

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
