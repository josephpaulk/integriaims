<?php

?>
<div></div>
<div>
	<table>
		<tr>
			<td>
				<table border="1">
					<tr><td><?php echo $company_from['name'] ?></td></tr>
					<tr><td><?php echo $company_from['fiscal_id'] ?></td></tr>
					<tr><td><?php echo $company_from['address'] ?></td></tr>
					<tr><td><?php echo $company_from['country'] ?></td></tr>
				</table>
			</td>
			<td>
				<table border="1">
					<tr><td><?php echo $company_to['name'] ?></td></tr>
					<tr><td><?php echo $company_to['fiscal_id'] ?></td></tr>
					<tr><td><?php echo $company_to['address'] ?></td></tr>
					<tr><td><?php echo $company_to['country'] ?></td></tr>
				</table>
			</td>
		</tr>
	</table>
	<table border="1">
		<tr>
			<td><?php echo __('Bill id') ?>: <?php echo $invoice['bill_id'] ?></td>
			<td><?php echo __('Date') ?>: <?php echo $invoice['invoice_create_date'] ?></td>
			<td><?php echo __('Payment date') ?>: <?php echo $invoice['invoice_payment_date'] ?></td>
		</tr>
	</table>
</div>
<div>
	<table border="1">
		<tr>
			<th><?php echo __('Concept') ?></th>
			<th><?php echo __('Amount') ?></th>
		</tr>
		<?php
		
		if ($invoice['concept1'] != "N/A" || $invoice['amount1'] != 0.00) {
			echo '<tr>';
				echo '<td>'.$invoice['concept1'].'</td>';
				echo '<td>'.$invoice['amount1'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept2'] != "N/A" || $invoice['amount2'] != 0.00) {
			echo '<tr>';
				echo '<td>'.$invoice['concept2'].'</td>';
				echo '<td>'.$invoice['amount2'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept3'] != "N/A" || $invoice['amount3'] != 0.00) {
			echo '<tr>';
				echo '<td>'.$invoice['concept3'].'</td>';
				echo '<td>'.$invoice['amount3'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept4'] != "N/A" || $invoice['amount4'] != 0.00) {
			echo '<tr>';
				echo '<td>'.$invoice['concept4'].'</td>';
				echo '<td>'.$invoice['amount4'].'</td>';
			echo '</tr>';
		}
		if ($invoice['concept5'] != "N/A" || $invoice['amount5'] != 0.00) {
			echo '<tr>';
				echo '<td>'.$invoice['concept5'].'</td>';
				echo '<td>'.$invoice['amount5'].'</td>';
			echo '</tr>';
		}
		?>
	</table>
</div>
<div>
	<table border="1">
		<tr>
			<th><?php echo __('Description') ?></th>
		</tr>
		<tr>
			<td><?php echo $invoice['description'] ?></td>
		</tr>
	</table>
</div>
<div>
	<table border="1">
		<tr>
			<th><?php echo __('Subtotal') ?></th>
			<th><?php echo __($config["invoice_tax_name"]) ?></th>
			<th><?php echo __('Total') ?></th>
		</tr>
		<tr>
			<td><?php echo $subtotal ?></td>
			<td><?php echo $tax ?></td>
			<td><?php echo $total ?></td>
		</tr>
	</table>
</div>

