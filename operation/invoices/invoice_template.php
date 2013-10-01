<table align="center">
	<tr>
		<td>
			<table style="width:620px; text-align:center;">
				<tr>
					<td style="text-align:left; font-size:16px; color:black; max-width:100px;">
						<?php echo  '<div><img style="width:250px; max-height:250px;" src="'.$header_logo.'"></div>';?>
					</td>
					<td style="font-size:16px; color:black;">
						<?php echo '<div style="font-size:14px;"><pre>'.$config["invoice_header"].'</pre></div>' ?>
					</td>
				</tr>
			</table>
			<?php if ($pdf_output == 1) { echo '<br><h1 style="color:black; font-size:20px;">'.__('Invoice').'</h1>'; } ?>
			<table style="border-top:2px solid black; padding:15px 15px 0px 15px; width:620px; text-align:center;">
				<tr>
					<td style="padding-right:5px; text-align:left;">
						<div>
							<?php echo '<div><b style="font-size:16px; color:black;">'.__('Customer address').'</b></div>' ?>
							<?php echo '<div style="font-size:14px;">'.$company_to['name'].'</div>' ?>
							<?php if ($company_to['fiscal_id']) { echo '<div style="font-size:14px;">'.$company_to['fiscal_id'].'</div>'; } ?>
							<?php echo '<div style="font-size:14px;">'.$company_to['address'].'</div>' ?>
							<?php echo '<div style="font-size:14px;">'.$company_to['country'].'</div>' ?>
						</div>
					</td>
					<td style="padding-left:5px;">
						<table>
							<tr>
								<td style="text-align:left; font-size:16px; color:black;">
									<?php echo '<b>'.__('Invoice ID'). ':</b> ' ?>
								</td>
								<td style="text-align:right; font-size:16px;">
									<?php echo $invoice['bill_id'] ?>
								</td>
							</tr>
							<tr>
								<td style="text-align:left; font-size:16px; color:black;">
									<?php echo '<b>'.__('Issue date'). ':</b> ' ?>
								</td>
								<td style="text-align:right; font-size:16px;">
									<?php echo $invoice['invoice_create_date'] ?>
								</td>
							</tr>
							<?php
							if ($invoice["status"] == "paid") {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:16px; color:black;">';
									echo '<b>'.__('Payment date'). ':</b> ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:16px;">';
									echo $invoice['invoice_payment_date'];
								echo '</td>';
							echo '</tr>';
							}
							if ($invoice["reference"]) {
							echo '<tr>';
								echo '<td style="text-align:left; font-size:16px; color:black;">';
									echo '<b>'.__('Reference'). ':</b> ';
								echo '</td>';
								echo '<td style="text-align:right; font-size:16px;">';
									echo $invoice['reference'];
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
					<td style=" text-align:left; font-size:16px; color:black;">
						<?php echo '<b>'.__('Concept').'</b>' ?>
					</td>
					<td style="text-align:right; font-size:16px; color:black;">
						<?php echo '<b>'.__('Amount').'</b>' ?>
					</td>
				</tr>
				<?php
				
				if ($invoice['concept1'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:left;">'.$invoice['concept1'].'</td>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:right;">'
							.$invoice['amount1'].' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept2'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:left;">'.$invoice['concept2'].'</td>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:right;">'
							.$invoice['amount2'].' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept3'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:left;">'.$invoice['concept3'].'</td>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:right;">'
							.$invoice['amount3'].' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept4'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:left;">'.$invoice['concept4'].'</td>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:right;">'
							.$invoice['amount4'].' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				if ($invoice['concept5'] != "") {
					echo '<tr>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:left;">'.$invoice['concept5'].'</td>';
						echo '<td style="padding-top:5px; font-size:14px; text-align:right;">'
							.$invoice['amount5'].' '.$invoice['currency'].'</td>';
					echo '</tr>';
				}
				?>
			</table>
			<table style="border-top:2px solid black; border-bottom:1px solid black; width:620px; text-align:right; padding: 15px 15px 0px 15px;">
				<tr>
					<td style="padding-bottom:15px; font-size:16px; color:black;">
						<?php echo '<b>'.__('Total amount without taxes').'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:16px; color:black;">
						<?php echo '<b>'.__($config["invoice_tax_name"]). ' ('.$tax.'%)'.'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:16px; color:black;">
						<?php echo '<b>'.__('Total amount').'</b>' ?>
					</td>
				</tr>
				<tr>
					<td style="padding-bottom:15px; font-size:17px;">
						<?php echo '<b>'.$amount.' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:17px;">
						<?php echo '<b>'.$tax_amount.' '.$invoice['currency'].'</b>' ?>
					</td>
					<td style="padding-bottom:15px; font-size:17px;">
						<?php echo '<b>'.$total.' '.$invoice['currency'].'</b>' ?>
					</td>
				</tr>
			</table>
			<?php
			if ($invoice['description']) {
				echo "<table style='border-bottom:1px solid black; width:620px; text-align:center; padding-bottom:15px; '>
							<tr>
								<td style='font-size:16px;'>
									<div><pre>".$invoice['description']."</pre></div>
								</td>
							</tr>
						</table>";
			}
			?>
		</td>
	</tr>
</table>
