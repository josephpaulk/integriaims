<table align="center"><tr><td>
	<div style="width:620px; color:black; text-align:right;" align="right">
	<?php if ($pdf_output == 1) { echo '<br><br><br><h1 style="color:black; font-size:20px;">'.__('Invoice').'</h1>'; } ?>
	</div>
	<table style="border-top:2px solid black; width:620px; text-align:center;">
		<tr>
			<td style="text-align:left; font-size:16px; color:black;">
				<?php echo '<b>'.__('Invoice ID'). ':</b> '.$invoice['bill_id'] ?>
			</td>
			<td style="font-size:16px; color:black;">
				<?php echo '<b>'.__('Issue date'). ':</b> '.$invoice['invoice_create_date'] ?>
			</td>
			<td style="font-size:16px; color:black;">
				<?php
				if ($invoice["status"] == "paid") {
					echo '<b>'.__('Payment date'). ':</b> ' ?> <?php echo $invoice['invoice_payment_date'];
				}
				?>
			</td>
		</tr>
	</table>
	<table style="padding-left:20px; width:575px;">
		<tr>
			<td>
				<div style="padding-left:4px; padding-right:4px;">
					<?php echo '<div align="center"><b style="font-size:16px; color:white;">C</b><br><br></div>' ?>
					<?php echo '<div style="font-size:16px;padding-top:4px; padding-bottom:4px;">'.$company_from['name'].'</div>' ?>
					<?php echo '<div style="font-size:16px; padding-top:4px; padding-bottom:4px;">'.$company_from['fiscal_id'].'</div>' ?>
					<?php echo '<div style="font-size:16px; padding-top:4px; padding-bottom:4px;">'.$company_from['address'].'</div>' ?>
					<?php echo '<div style="font-size:16px; padding-top:4px; padding-bottom:4px;">'.$company_from['country'].'</div>' ?>
				</div>
			</td>
			<td>
				<div style="padding-left:4px; padding-right:4px;">
					<?php echo '<div align="center"><b style="font-size:16px; color:black;">'.__('Customer').'</b><br><br></div>' ?>
					<?php echo '<div style="font-size:16px; padding-top:4px; padding-bottom:4px;">'.$company_to['name'].'</div>' ?>
					<?php echo '<div style="font-size:16px; padding-top:4px; padding-bottom:4px;">'.$company_to['fiscal_id'].'</div>' ?>
					<?php echo '<div style="font-size:16px; padding-top:4px; padding-bottom:4px;">'.$company_to['address'].'</div>' ?>
					<?php echo '<div style="font-size:16px; padding-top:4px; padding-bottom:4px;">'.$company_to['country'].'</div>' ?>
				</div>
			</td>
		</tr>
	</table>
	<table style="border-top: 2px solid black; border-bottom:1px solid black; width:620px;">
		<tr>
			<td style="padding:15px; text-align:left; font-size:16px; color:black;">
				<?php echo '<b>'.__('Concept').'</b>' ?>
			</td>
			<td style="padding:15px; text-align:right; font-size:16px; color:black;">
				<?php echo '<b>'.__('Amount').'</b>' ?>
			</td>
		</tr>
		<?php
		
		if ($invoice['concept1'] != "") {
			echo '<tr>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:left;">'.$invoice['concept1'].'</td>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:right;">'
					.$invoice['amount1'].' '.$invoice['currency'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept2'] != "") {
			echo '<tr>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:left;">'.$invoice['concept2'].'</td>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:right;">'
					.$invoice['amount2'].' '.$invoice['currency'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept3'] != "") {
			echo '<tr>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:left;">'.$invoice['concept3'].'</td>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:right;">'
					.$invoice['amount3'].' '.$invoice['currency'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept4'] != "") {
			echo '<tr>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:left;">'.$invoice['concept4'].'</td>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:right;">'
					.$invoice['amount4'].' '.$invoice['currency'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept5'] != "") {
			echo '<tr>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:left;">'.$invoice['concept5'].'</td>';
				echo '<td style="padding:5px 15px 15px 15px; font-size:16px; text-align:right;">'
					.$invoice['amount5'].' '.$invoice['currency'].'</td>';
			echo '</tr>';
		}
		?>
	</table>
	<table style="padding-top:15px; padding-bottom:15px; width:620px;">
		<tr>
			<td><?php echo $invoice['description'] ?></td>
		</tr>
	</table>
	<table style="border-top:2px solid black; border-bottom:1px solid black; width:620px; text-align:right;">
		<tr>
			<td style="padding:15px; font-size:16px; color:black;">
				<?php echo '<b>'.__('Total amount without taxes').'</b>' ?>
			</td>
			<td style="padding:15px; font-size:16px; color:black;">
				<?php echo '<b>'.__($config["invoice_tax_name"]). ' ('.$tax.'%)'.'</b>' ?>
			</td>
			<td style="padding:15px; font-size:16px; color:black;">
				<?php echo '<b>'.__('Total amount').'</b>' ?>
			</td>
		</tr>
		<tr>
			<td style="padding:15px; font-size:18px;">
				<?php echo '<b>'.$amount.' '.$invoice['currency'].'</b>' ?>
			</td>
			<td style="padding:15px; font-size:18px;">
				<?php echo '<b>'.$tax_amount.' '.$invoice['currency'].'</b>' ?>
			</td>
			<td style="padding:15px; font-size:18px;">
				<?php echo '<b>'.$total.' '.$invoice['currency'].'</b>' ?>
			</td>
		</tr>
	</table>
</td></tr></table>
