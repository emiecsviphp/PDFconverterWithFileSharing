<?php
/* 
	This file is part of the plugin of exponentmediapdfconverter
*/

function status_posts_pdf_converter_function()	{
	global $wpdb, $table_prefix,$current_user;
	
	$wpurl = get_bloginfo('wpurl');
	?>
	<form method="post" action="#" id="status_pdf_converter_form" name="status_pdf_converter_form">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script language="JavaScript">
		var wpurl = '<?php bloginfo('wpurl'); ?>';		
	</script>
	<div class="wrap">
		<div class="icon32" id="icon-plugins"><br></div>
		<h2>Posts Status</h2>
			<table cellspacing="0" class="widefat post fixed">
				<thead>
					<tr>
						<th scope="col" width="2%"><input type="checkbox" id="checkcheckbox" name="checkcheckbox"></th>
						<th scope="col" width="50%">Title</th>
						<th scope="col" >Author</th>
						<th scope="col" >Date Published</th>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" width="2%"></th>
						<th scope="col" width="50%">Title</th>
						<th scope="col" >Author</th>
						<th scope="col" >Date Published</th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					
						$args = array(
							'post_type' => 'post',
							'post_status' => 'publish'
						 );
						$the_query = new WP_Query( $args );
						while ( $the_query->have_posts() ) {
							$the_query->the_post();							
							?>
							<tr>
								<td><input type="checkbox" name="post_ids[]" id="post_ids" value="<?php the_ID(); ?>"></td>
								<td>
									<b><a href="<?php echo $wpurl; ?>/wp-admin/post.php?post=<?php echo get_the_ID(); ?>&action=edit">
										<?php the_title(); ?>
									</a></b>
									<ul>
										<?php
										$rows = $wpdb->get_results( "SELECT `returnurl` FROM `".$table_prefix."pdf_returned_urls` WHERE `post_id` = '".get_the_ID()."'" );
										if(count($rows) > 0)	{
											foreach($rows as $row)	{
												?>
												<li><a target="_blank" href="<?php echo $row->returnurl ; ?>"><?php echo $row->returnurl ; ?></a></li>
												<?php
											}
										}
										?>
									</ul>
								</td>
								<td><?php the_author() ?></td>
								<td> <?php the_time('m/j/y g:i A') ?> </td>
							</tr>
							<?php
						}
						wp_reset_postdata();
					?>
				</tbody>
			</table>
	</div>
	</form>
	<?php
}