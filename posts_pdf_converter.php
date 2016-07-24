<?php
/* 
	This file is part of the plugin of exponentmediapdfconverter
*/

function posts_pdf_converter_function()	{
	global $wpdb, $table_prefix,$current_user;
	?>
	<form method="post" action="#" id="posts_pdf_converter_form" name="posts_pdf_converter_form">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script language="JavaScript">
		var wpurl = '<?php bloginfo('wpurl'); ?>';
		function convertToPDF()	{
			var allVals = '';
			var inputs = document.getElementsByTagName("input");
			var cbs = [];
			var checked = [];
			for (var i = 0; i < inputs.length; i++) {
				if (inputs[i].type == "checkbox") {
					cbs.push(inputs[i]);
					if (inputs[i].checked) {
						allVals = allVals + ',' + inputs[i].value;
					}
				}
			}
			
			if(allVals == '')	{
				alert('Please select from the list.');
			}
			else	{
				jQuery.noConflict();
				jQuery.ajax({
					type: "POST",
					url: wpurl + "/wp-content/plugins/exponentmediapdfconverter/processpdf.php",
					data: '&task=processPDF&postype=posts&values='+allVals,
					beforeSend: function() {
						jQuery("#loaddata").html("<img src='"+wpurl+"/wp-content/plugins/exponentmediapdfconverter/images/loading.gif' alt='loading...' title='loading...' style='color:#33cc33'/>");
						//jQuery("#successmessage").html("<img src='"+wpurl+"/wp-content/plugins/exponentmediapdfconverter/images/loading.gif' alt='loading...' title='loading...' style='color:#33cc33'/>");
					},
					success: function(response){
						jQuery("#loaddata").html(response);
						jQuery("#successmessage").html('<div class="updated">Post(s) successfully converted to PDF.</div>');
					}
				});
			}
		}
		
		function checkAll()	{
			var field = document.getElementsByName('post_ids[]');
			for (i = 0; i < field.length; i++)
				field[i].checked = true;
		}

		function uncheckAll()	{
			var field = document.getElementsByName('post_ids[]');
			for (i = 0; i < field.length; i++)
				field[i].checked = false;
		}
		
		function processCheckBox(field)	{
			if(field.checked == true)	{
				checkAll();
			}
			else	{
				uncheckAll();
			}
		}
		
	</script>
	<div class="wrap">
		<div class="icon32" id="icon-plugins"><br></div>
		<h2>Posts PDF Converter</h2>
		<span id="successmessage"></span>
		<div style="margin-top:10px;margin-bottom:15px;">
			<input onclick="convertToPDF()" type="button" style="cursor:pointer;" value="Convert to PDF" class="button-primary">
		</div>
			<table cellspacing="0" class="widefat post fixed">
				<thead>
					<tr>
						<th scope="col" width="2%"><input type="checkbox" id="checkcheckbox" name="checkcheckbox" onclick="processCheckBox(document.posts_pdf_converter_form.checkcheckbox)"></th>
						<th scope="col" >Title</th>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" width="2%"></th>
						<th scope="col" >Title</th>
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
								<td><?php the_title(); ?></td>
							</tr>
							<?php
						}
						wp_reset_postdata();
					?>
				</tbody>
			</table>
			<span id="loaddata"></span>
	</div>
	</form>
	<?php
}