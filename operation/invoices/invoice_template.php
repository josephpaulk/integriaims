<table align="center">
	<tr>
		<td>
			<table style="width:620px; text-align:right;">
				<tr>
					<td style="text-align:left; font-size:16px; color:black; max-width:100px;">
						<?php echo  '<div><img style="width:150px; padding: 0px; align: left; margin: 0px;" align=left src="'.$header_logo.'"></div>';?>
					</td>
					<td style="font-size:14px; color:black;">
						<?php echo '<div style="font-size:14px;"><pre>'.$config["invoice_header"].'</pre></div>' ?>
					</td>
				</tr>
			</table>

<?php 
	if ($pdf_output == 1) { 
		if ($invoice["status"] == 'paid'){
			echo "<div style='z-index: 100; position: absolute; padding-left: 250px; padding-top: -500px; font-size: 64px; weight: bold; color: #cc0000';>";
			echo __("Paid");
			echo "</div>";
		}	
	}
?>
	
			<?php if ($pdf_output == 1) { echo '<br><h1 style="color:black; font-size:20px;">'.__('Invoice').'</h1>'; } ?>
			<table style="border-top:2px solid black; padding:5px 15px 0px 15px; width:620px; text-align:right;">
				<tr>
					<td style="padding-right:5px; text-align:left;">
						<div>
							<?php echo '<div><p style="font-size:13px; color:black;">'.__('Customer address').'</p><br></div>' ?>
							<?php echo '<div style="font-size:13px;">'.$company_to['name'].'</div>' ?>
							<?php if ($company_to['fiscal_id']) { echo '<div style="font-size:13px;">'.__("Fiscal ID: ").$company_to['fiscal_id'].'</div>'; } ?>
							<?php echo '<div style="font-size:13px;">'.safe_output($company_to['address']).'</div>' ?>
							<?php echo '<div style="font-size:13	px;">'.$company_to['country'].'</div>' ?>
						</div>
					</td>
					<td style="padding-left:5px;">
						<table>
							<tr>
								<td style="text-align:left; font-size:12px; color:black;">
									<?php echo __('Invoice ID'). ':</b> ' ?>
								</td>
								<td style="text-align:right; font-size:12px;">
									<?php echo $invoice['bill_id'] ?>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; font-size:12px; color:black;">
									<?php echo __('Issue date'). ':</b> ' ?>
								</td>
								<td style="text-align:right; font-size:12px;">
									<?php echo $invoice['invoice_create_date'] ?>
								</td>
							</tr>
							<?php
							if ($invoice["status"] == "paid") {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:12px; color:black;">';
									echo __('Payment date'). ': ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:12px;">';
									echo $invoice['invoice_payment_date'];
								echo '</td>';
							echo '</tr>';
							}
							if ($invoice["reference"]) {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:12px; color:black;">';
									echo __('Reference'). ': ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:12px;">';
									echo $invoice['reference'];
								echo '</td>';
							echo '</tr>';
							}
							if ($invoice["invoice_expiration_date"]) {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:12px; color:black;">';
									echo __('Expiration date'). ': ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:12px;">';
									echo $invoice['invoice_expiration_date'];
								echo '</td>';
							echo '</tr>';
							}
							?>
						</table>
					</td>
				</tr>
			</table>
			
			<table style="border-top: 2px solid black; padding: 5px 15px 0px 15px; width:620px;">
				<tr>
					<td style=" text-align:left; font-size:14px; color:black; width: 450px;">
						<?php echo __('Concept') ?>
					</td>
					<td style="text-align:right; font-size:14px; color:black;">
						<?php echo __('Amount') ?>
					</td>
				</tr>
				<?php
				
				if ($invoice['concept1'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$invoice['concept1'].'</td>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:right;">'
							.format_numeric($invoice['amount1'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept2'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$invoice['concept2'].'</td>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:right;">'
							.format_numeric($invoice['amount2'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept3'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$invoice['concept3'].'</td>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:right;">'
							.format_numeric($invoice['amount3'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept4'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$invoice['concept4'].'</td>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:right;">'
							.format_numeric($invoice['amount4'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept5'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$invoice['concept5'].'</td>';
						echo '<td style="padding-top:5px; font-size:11px; text-align:right;">'
							.format_numeric($invoice['amount5'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				?>
			</table>
			<table style="border-top: 1px solid grey; padding: 5px 15px 0px 15px; width:620px;">
				<tr>
					<td style=" text-align:left; font-size:12px;  width: 400px;">
						<?php echo __('Total amount without taxes or discounts') ?>
					</td>
					<td style=" text-align:right; font-size:12px; color:black;">
						<?php echo '<b>'.format_numeric($amount,2).' '.$invoice['currency'].'</b>' ?>
					</td>
				</tr>
			</table>
			<?php
			if ($before_amount != 0){
				echo '<table style="border-top: 1px solid grey; padding: 5px 15px 0px 15px; width:620px;">';
				echo '<tr>';
					echo '<td style=" text-align:left; font-size:12px; width: 220px;">';
						 echo $concept_discount_before; 
					echo '</td>';
					echo '<td style=" text-align:left; font-size:12px; width: 155px;">';
						 echo $discount_before.'%';
					echo '</td>';
					echo '<td style=" text-align:right; font-size:12px; color:black; ">';
						echo '<b>'.format_numeric($before_amount,2).' '.$invoice['currency'].'</b>';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
			}
			if ($tax != 0) {
			echo '<table style="border-top: 2px solid black; padding: 5px 15px 0px 15px; width:620px;">';
				echo '<tr>';
					echo '<td style=" text-align:left; font-size:12px; color:black; width: 220px;">';
						echo __('Concept Taxes');
					echo '</td>';
					echo '<td style=" text-align:left; font-size:12px; color:black; width: 155px;">';
						echo __('Taxes (%)');
					echo '</td>';
					echo '<td style="text-align:right; font-size:12px; color:black;">';
						echo __('Total Taxes'); 
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
				if (is_numeric($tax2)){	
					echo '<td>';
						echo '<table style="width:155px;">';									 
							echo '<tr>';
								echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$invoice['tax_name'].'</td>';
							echo '</tr>';
						echo '</table>';
					echo '</td>';
					echo '<td>';
						echo '<table style="width:155px;">';									 
							echo '<tr>';
								echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$invoice['tax'].'</td>';
							echo '</tr>';
						echo '</table>';
					echo '</td>';
					echo '<td>';
						echo '<table style="width:310px;">';									 
							echo '<tr>';
								echo '<td style="padding-top:5px; font-size:11px; text-align:right;">'.format_numeric($total_before * ($invoice['tax']/100)).' '.$invoice['currency'].'</td>';
							echo '</tr>';
						echo '</table>';
					echo '</td>';
				} else {
					echo '<td>';
						echo '<table style="width:155px;">';
								if ($invoice['tax_name'] != "") {
									foreach ( json_decode($invoice['tax_name']) as $key => $campo) { 
										echo '<tr>';
											echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$campo.'</td>';
										echo '</tr>';
									}
								}
						echo '</table>';
					echo '</td>';
					echo '<td>';
						echo '<table style="width:155px;">';	
								if ($invoice['tax'] != "") {
									foreach ( json_decode($invoice['tax']) as $key => $campo) { 
										echo '<tr>';
											echo '<td style="padding-top:5px; font-size:11px; text-align:left;">'.$campo.'%</td>';
										echo '</tr>';
									}
								}
						echo '</table>';
					echo '</td>';
					echo '<td>';
						echo '<table style="width:310px;">';
								if ($invoice['tax'] != "") {
									foreach ( json_decode($invoice['tax']) as $key => $campo) { 
										echo '<tr>';
											echo '<td style="padding-top:5px; font-size:11px; text-align:right;">'.format_numeric($total_before * ($campo/100)).' '.$invoice['currency'].'</td>';
										echo '</tr>';
									}
								}
						echo '</table>';
					echo '</td>';
					}
				echo '</tr>';
			echo '</table>';
			echo '<table style="border-top: 1px solid grey; padding: 5px 15px 0px 15px; width:620px;">';
				echo '<tr>';
					echo '<td style="font-size:12px; color:black; width:220px; text-align:left;">';
						echo __('Total Taxes'). '</b>';
					echo '</td>';
					echo '<td style="font-size:12px; color:black; width:155px; text-align:left;">';
						echo $tax.'%'.'</b>';
					echo '</td>';
					echo '<td style="font-size:12px; color:black; text-align:right;">';
						echo '<b>'.format_numeric($tax_amount,2).' '.$invoice['currency'].'</b>';
					echo '</td>';
				echo '</tr>';
			echo '</table>';
			}
			if ($irpf_amount != 0){
				echo '<table style="border-top: 1px solid black; padding: 5px 15px 0px 15px; width:620px;">';
					echo '<tr>';
						echo '<td style=" text-align:left; font-size:12px; color:black; width: 220px;">';
							echo '</b>'. $concept_retention .'</b>';
						echo '</td>';
						echo '<td style=" text-align:left; font-size:12px; color:black; width: 155px;">';
							echo $irpf.'%';
						echo '</td>';
						echo '<td style=" text-align:right; font-size:12px; color:black; ">';
							echo '<b>'.format_numeric($irpf_amount,2).' '.$invoice['currency'].'</b>';
						echo '</td>';
					echo '</tr>';
				echo '</table>';
			}
			?>
			<table style="border-top:2px solid black; border-bottom:1px solid black; width:620px; padding: 5px 15px 0px 15px;">
				<tr>
					<td style="padding-bottom:15px; width:124px; font-size:14px; color:black;">
						<?php echo '<b>'.__('Total amount without taxes or discounts').'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:14px; color:black;">
						<?php echo '<b>'.__('Discount before taxes').'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:14px; color:black;">
						<?php echo '<b>'.'Total Tax'. ' ('.$tax.'%)'.'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:14px; color:black;">
						<?php echo '<b>'.__('Retention').'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:14px; color:black;">
						<?php echo '<b>'.__('Total amount').'</b>' ?>
					</td>
				</tr>
				<tr>
					<td style="padding-bottom:15px; width:124px; font-size:15px;">
						<?php echo '<b>'.format_numeric($amount,2).' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:15px;">
						<?php echo '<b>'.format_numeric($before_amount,2).' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:15px;">
						<?php echo '<b>'.format_numeric($tax_amount,2).' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:15px;">
						<?php echo '<b>'.format_numeric($irpf_amount,2).' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; width:124px; font-size:15px;">
						<?php echo '<b>'.format_numeric($total,2).' '.$invoice['currency'].'</b>' ?>
					</td>
				</tr>
			</table>
			<?php
			if ($invoice['description']) {
				echo "<table style='border-bottom:1px solid black; width:620px; text-align:center; padding-bottom:15px; '>
							<tr>
								<td style='font-size:14px;'>
									<div><pre>".$invoice['description']."</pre></div>
								</td>
							</tr>
						</table>";
			}
			?>
		</td>
	</tr>
</table>
