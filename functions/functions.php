<?php

function generateServiceTable( $services ) {
	$serviceRows = '';
	foreach ($services as $service) {
		$serviceRows .= '<tr>
							<td> '.$service->sameday_id.' </td>
							<td> '.$service->sameday_name.' </td>
							<td> '.$service->name.' </td>
							<td> '.$service->price.' </td>
							<td> '.$service->price_free.' </td>
							<td> '.$service->status.' </td>
						</tr>';
	}

	if (empty($services)) {
		$serviceRows = '<tr><td colspan="6" style="text-align: center;">'. __('No data found') .'</td></tr>';
	}

	$table = '<div class="wrap">
					<table class="wp-list-table widefat fixed striped posts">
						<thead>
							<tr>
								<th scope="col"> '.__("Sameday ID").'</th>
								<th scope="col"> '.__("Sameday name").'</th>
								<th scope="col"> '.__("Name").'</th>
								<th scope="col"> '.__("Price").'</th>
								<th scope="col"> '.__("Price free").'</th>
								<th scope="col"> '.__("Status").'</th>
							</tr>
						</thead>
						<tbody>
							'.$serviceRows.'
						</tbody>
						<tfoot>
							<tr>						
								<th colspan="6" style="text-align: right;"> <a href="http://plugins56.com/woocommerce-3.4/wp-admin/plugins.php?page=samedaycourier-services/refreshServices"> Refresh pickup points </a> </th>
							</tr>
						</tfoot>
					</table>
				</div>';

	return $table;
}

function generatePickupPointTable( $pickupPoints ) {
	$pickupPointRows = '';
	foreach ($pickupPoints as $pickupPoint) {
		$pickupPointRows .= '<tr>
							<td> '.$pickupPoint->sameday_id.' </td>
							<td> '.$pickupPoint->sameday_alias.' </td>
							<td> '.$pickupPoint->city.' </td>
							<td> '.$pickupPoint->county.' </td>
							<td> '.$pickupPoint->address.' </td>
							<td> '.$pickupPoint->contactPersons.' </td>
							<td> '.$pickupPoint->default_pickup_point.' </td>
						</tr>';
	}

	if (empty($pickupPoints)) {
		$pickupPointRows = '<tr><td colspan="7" style="text-align: center;">'. __('No data found') .'</td></tr>';
	}

	$table = '<div class="wrap">
					<table class="wp-list-table widefat fixed striped posts">
						<thead>
							<tr>
								<th scope="col"> '.__("Sameday ID").'</th>
								<th scope="col"> '.__("Sameday name").'</th>
								<th scope="col"> '.__("City").'</th>
								<th scope="col"> '.__("County").'</th>
								<th scope="col"> '.__("Address").'</th>
								<th scope="col"> '.__("Contact person").'</th>
								<th scope="col"> '.__("Default pickup point").'</th>
							</tr>
						</thead>
						<tbody>
							'.$pickupPointRows.'
						</tbody>
						<tfoot>
							<tr>						
								<th colspan="7" style="text-align: right;"> <a href="http://plugins56.com/woocommerce-3.4/wp-admin/plugins.php?page=samedaycourier-services/refreshServices"> Refresh pickup points </a> </th>
							</tr>
						</tfoot>
					</table>
				</div>';

	return $table;
}
