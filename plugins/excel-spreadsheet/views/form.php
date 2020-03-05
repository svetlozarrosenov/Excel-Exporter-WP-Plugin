<?php
$currentPostType = $adminPageInstance->getCurrentPostType();

$currentPostTypeLoop = new WP_Query( array(
	'post_type' => $currentPostType,
	'posts_per_page' => 100,
) );

$metaKeys = $adminPageInstance->getPostMetaKeys( $currentPostType );
$postStatuses = $adminPageInstance->getPostStatuses();
?>

<div id="app" class="xlsx">
	<div class="shell">
		<div class="xlsx__container">	
			<form class="xlsx-export-form" action="<?php echo admin_url() . 'edit.php?post_type=' . $currentPostType . '&page=' . $_GET['page']; ?>" method="post">

				<label for="">
					<h2>Select posts</h2>
				</label>

				<select name="crb_selected_posts">
					<option value="0"><?php _e( 'All', 'crb' ); ?></option>

					<?php while( $currentPostTypeLoop->have_posts() ) : $currentPostTypeLoop->the_post(); ?>
						<option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
					<?php endwhile; ?>

					<?php wp_reset_postdata(); ?>
				</select>
				
				</br>

				<label for="">
					<h2>Select post status(works if all posts are selected)</h2>
				</label>
						
				<?php foreach ( $postStatuses as $postStatus => $postStatusName ) : ?>
					<?php
					$checked = '';
					if ( $postStatus === 'publish' ) {
						$checked = 'checked';
					}
					?>
					<input type="checkbox" name="post_statuses[]" value="<?php echo $postStatus; ?>" <?php echo $checked; ?>><?php echo $postStatusName; ?><br>
				<?php endforeach; ?>

				</br>

				<label for="">
					<h2>Select database values</h2>
				</label>
						
				<?php foreach ( $metaKeys as $metaKey => $val ) : ?>
					<input type="checkbox" name="<?php echo $metaKey; ?>" value="<?php echo $metaKey; ?>"><?php echo $metaKey; ?><br>
				<?php endforeach; ?>
				
				</br>
				
				<input name="export" type="submit" value="export" class="xlsx-export-form__btn">
			</form><!-- xlsx-export-form -->
		</div><!-- xlsx__container -->
	</div><!-- shell -->
</div><!-- xlsx -->