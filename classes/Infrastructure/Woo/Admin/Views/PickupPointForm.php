<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySettings;

class PickupPointForm
{
	public static function enqueueScripts(): void
	{
		if (!isset($_GET['page']) || 'sameday_pickup_points' !== $_GET['page']) {
			return;
		}

		add_thickbox();

		$pluginMainFile = SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH . 'samedaycourier-shipping.php';

		wp_enqueue_style(
			'sameday-thickboxform-style',
			plugins_url('assets/css/tickbox-form.css', $pluginMainFile),
			[],
			time()
		);
		wp_enqueue_script(
			'sameday-admin-helper',
			plugins_url('assets/js/helper.js', $pluginMainFile),
			['jquery'],
			time(),
			true
		);
		wp_enqueue_script(
			'sameday-admin-script',
			plugins_url('assets/js/adminPickupPoints.js', $pluginMainFile),
			['jquery'],
			time(),
			true
		);
		wp_localize_script(
			'sameday-admin-script',
			'samedayPickupPointsAdmin',
			[
				'nonces' => [
					'get_counties' => wp_create_nonce('get_counties'),
					'get_cities' => wp_create_nonce('get_cities'),
					'send_pickup_point' => wp_create_nonce('send_pickup_point'),
					'delete_pickup_point' => wp_create_nonce('delete_pickup_point'),
				],
			]
		);
	}

	public static function renderModals(): void
	{
		?>
		<div id="smd-thickbox" class="smd-modal" style="display: none;">
			<div class="smd-modal-container">
				<form id="thickbox-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>" >
					<input type="hidden" name="action" value="send_pickup_point">
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('send_pickup_point'); ?>">
					<h3><?= __("Add New Pickup Point", SamedayConstants::TEXT_DOMAIN)?></h3>
					<div class="form-group">
						<label for="pickupPointCountry">Country</label>
						<div class="form-input">
							<select name="pickupPointCountry" id="pickupPointCountry">
								<option value="<?php echo SamedayConstants::DEFAULT_COUNTRIES[SamedaySettings::getHostCountry()]['value']; ?>"><?php echo SamedayConstants::DEFAULT_COUNTRIES[SamedaySettings::getHostCountry()]['label']; ?></option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointCounty"><?= __("County", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<select name="pickupPointCounty" id="pickupPointCounty" required disabled>
							<!-- // Bind data from server via ajax request -->
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointCity"><?= __("City", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<select name="pickupPointCity" id="pickupPointCity" required disabled>
							<!-- // Bind data from server via ajax request -->
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointAddress"><?= __("Address", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<input type="text" name="pickupPointAddress" id="pickupPointAddress" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointDefault"><?= __("Default", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<input type="checkbox" name="pickupPointDefault" id="pickupPointDefault" value="1">
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointPostalCode"><?= __("Postal Code", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<input type="number" name="pickupPointPostalCode" id="pickupPointPostalCode" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointAlias"><?= __("Alias", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<input type="text" name="pickupPointAlias" id="pickupPointAlias" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointContactPersonName"><?= __("Contact Person Name", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<input type="text" name="pickupPointContactPersonName" id="pickupPointContactPersonName" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointContactPersonPhone"><?= __("Contact Person Phone", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<input type="number" name="pickupPointContactPersonPhone" id="pickupPointContactPersonPhone" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointEmail"><?= __("Email", SamedayConstants::TEXT_DOMAIN)?></label>
						<div class="form-input">
							<input type="email" name="pickupPointEmail" id="pickupPointEmail" required>
						</div>
					</div>
					<div class="form-footer">
						<input type="submit" value="Save" class="sameday_admin_button">
						<button type="button" class="sameday_admin_button" onclick="tb_remove();">Cancel</button>
					</div>
				</form>
			</div>
		</div>
		<div id="smd-thickbox-delete" class="smd-modal" style="display: none">
			<div class="smd-modal-container">
				<form id="form-deletePickupPoint" method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
					<input type="hidden" name="sameday_id" id="input-deletePickupPoint">
					<input type="hidden" name="action" value="delete_pickup_point">
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('delete_pickup_point'); ?>">
					<h3><?= __("Are you sure you want to delete this pickup point?", SamedayConstants::TEXT_DOMAIN)?></h3>
					<div class="form-footer">
						<input type="submit" name="submit" value="Submit" class="sameday_admin_button">
						<button type="button" class="sameday_admin_button" onclick="tb_remove();">Cancel</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}
}
