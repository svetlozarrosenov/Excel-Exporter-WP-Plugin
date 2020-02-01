<?php
$currentPostType = $AdminPageInstance->getCurrentPostType();

$currentPostTypeLoop = new WP_Query( array(
	'post_type' => $currentPostType,
	'posts_per_page' => 100,
) );

$metaKeys = $AdminPageInstance->getPostMetaKeys( $currentPostType );
?>

<div id="app" class="xlsx">
	<div class="shell">
		<div class="xlsx__container">	
			<form class="xlsx-export-form" action="<?php echo admin_url() . 'edit.php?post_type=' . $currentPostType . '&page=' . $_GET['page']; ?>" method="post">
				<select name="crb_selected_posts">
					<option value="0"><?php _e( 'All', 'crb' ); ?></option>

					<?php while( $currentPostTypeLoop->have_posts() ) : $currentPostTypeLoop->the_post(); ?>
						<option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
					<?php endwhile; ?>

					<?php wp_reset_postdata(); ?>
				</select>
				
				</br>
			
				<label for="">
					<h2>Chose which values to be exported</h2>
				</label>
			
				</br>
			
				<?php foreach ( $metaKeys as $metaKey => $val ) : ?>
					<input type="checkbox" name="<?php echo $metaKey; ?>" value="<?php echo $metaKey; ?>"><?php echo $metaKey; ?><br>
				<?php endforeach; ?>

				<input name="export" type="submit" value="export">
			</form><!-- xlsx-export-form -->
		</div><!-- xlsx__container -->
	</div><!-- shell -->
</div><!-- xlsx -->