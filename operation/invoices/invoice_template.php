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
							<?php echo '<div><p style="font-size:14px; color:black;">'.__('Customer address').'</p><br></div>' ?>
							<?php echo '<div style="font-size:14px;">'.$company_to['name'].'</div>' ?>
							<?php if ($company_to['fiscal_id']) { echo '<div style="font-size:14px;">'.__("Fiscal ID: ").$company_to['fiscal_id'].'</div>'; } ?>
							<?php echo '<div style="font-size:14px;">'.safe_output($company_to['address']).'</div>' ?>
							<?php echo '<div style="font-size:14px;">'.$company_to['country'].'</div>' ?>
						</div>
					</td>
					<td style="padding-left:5px;">
						<table>
							<tr>
								<td style="text-align:left; font-size:14px; color:black;">
									<?php echo __('Invoice ID'). ':</b> ' ?>
								</td>
								<td style="text-align:right; font-size:14px;">
									<?php echo $invoice['bill_id'] ?>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; font-size:14px; color:black;">
									<?php echo __('Issue date'). ':</b> ' ?>
								</td>
								<td style="text-align:right; font-size:14px;">
									<?php echo $invoice['invoice_create_date'] ?>
								</td>
							</tr>
							<?php
							if ($invoice["status"] == "paid") {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:14px; color:black;">';
									echo __('Payment date'). ': ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:14px;">';
									echo $invoice['invoice_payment_date'];
								echo '</td>';
							echo '</tr>';
							}
							if ($invoice["reference"]) {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:14px; color:black;">';
									echo __('Reference'). ': ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:14px;">';
									echo $invoice['reference'];
								echo '</td>';
							echo '</tr>';
							}
							if ($invoice["invoice_expiration_date"]) {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:14px; color:black;">';
									echo __('Expiration date'). ': ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:14px;">';
									echo $invoice['invoice_expiration_date'];
								echo '</td>';
							echo '</tr>';
							}
							?>
						</table>
					</td>
				</tr>
			</table>
			
			<table style="border-top: 2px solid black; padding: 10px 15px 0px 15px; width:620px;">
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
						echo '<td style="padding-top:5px; font-size:13px; text-align:left;">'.$invoice['concept1'].'</td>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:right;">'
							.format_numeric($invoice['amount1'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept2'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:left;">'.$invoice['concept2'].'</td>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:right;">'
							.format_numeric($invoice['amount2'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept3'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:left;">'.$invoice['concept3'].'</td>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:right;">'
							.format_numeric($invoice['amount3'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept4'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:left;">'.$invoice['concept4'].'</td>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:right;">'
							.format_numeric($invoice['amount4'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept5'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:left;">'.$invoice['concept5'].'</td>';
						echo '<td style="padding-top:5px; font-size:13px; text-align:right;">'
							.format_numeric($invoice['amount5'],2).' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				?>
			</table>
			<table style="border-top:2px solid black; border-bottom:1px solid black; width:620px; text-align:right; padding: 15px 15px 0px 15px;">
				<tr>
					<td style="padding-bottom:15px; font-size:14px; color:black;">
						<?php echo __('Total amount without taxes').'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:14px; color:black;">
						<?php echo __($config["invoice_tax_name"]). ' ('.$tax.'%)'.'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:14px; color:black;">
						<?php echo __('Total amount').'</b>' ?>
					</td>
				</tr>
				<tr>
					<td style="padding-bottom:15px; font-size:15px;">
						<?php echo '<b>'.format_numeric($amount,2).' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:15px;">
						<?php echo '<b>'.format_numeric($tax_amount,2).' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:15px;">
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
