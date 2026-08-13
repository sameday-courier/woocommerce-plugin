<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySettings;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
class PickupPointForm
{
	public static function renderModals(): void
	{
		?>
		<div id="smd-thickbox" class="smd-modal" style="display: none;">
			<div class="smd-modal-container">
				<form id="thickbox-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>" >
					<input type="hidden" name="action" value="send_pickup_point">
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('send_pickup_point'); ?>">
					<h3><?= TranslatorHandler::translate("Add New Pickup Point")?></h3>
					<div class="form-group">
						<label for="pickupPointCountry">Country</label>
						<div class="form-input">
							<select name="pickupPointCountry" id="pickupPointCountry">
								<option value="<?php echo SamedayConstants::DEFAULT_COUNTRIES[SamedaySettings::getHostCountry()]['value']; ?>"><?php echo SamedayConstants::DEFAULT_COUNTRIES[SamedaySettings::getHostCountry()]['label']; ?></option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointCounty"><?= TranslatorHandler::translate("County")?></label>
						<div class="form-input">
							<select name="pickupPointCounty" id="pickupPointCounty" required disabled>
							<!-- // Bind data from server via ajax request -->
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointCity"><?= TranslatorHandler::translate("City")?></label>
						<div class="form-input">
							<select name="pickupPointCity" id="pickupPointCity" required disabled>
							<!-- // Bind data from server via ajax request -->
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointAddress"><?= TranslatorHandler::translate("Address")?></label>
						<div class="form-input">
							<input type="text" name="pickupPointAddress" id="pickupPointAddress" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointDefault"><?= TranslatorHandler::translate("Default")?></label>
						<div class="form-input">
							<input type="checkbox" name="pickupPointDefault" id="pickupPointDefault" value="1">
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointPostalCode"><?= TranslatorHandler::translate("Postal Code")?></label>
						<div class="form-input">
							<input type="number" name="pickupPointPostalCode" id="pickupPointPostalCode" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointAlias"><?= TranslatorHandler::translate("Alias")?></label>
						<div class="form-input">
							<input type="text" name="pickupPointAlias" id="pickupPointAlias" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointContactPersonName"><?= TranslatorHandler::translate("Contact Person Name")?></label>
						<div class="form-input">
							<input type="text" name="pickupPointContactPersonName" id="pickupPointContactPersonName" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointContactPersonPhone"><?= TranslatorHandler::translate("Contact Person Phone")?></label>
						<div class="form-input">
							<input type="number" name="pickupPointContactPersonPhone" id="pickupPointContactPersonPhone" required>
						</div>
					</div>
					<div class="form-group">
						<label for="pickupPointEmail"><?= TranslatorHandler::translate("Email")?></label>
						<div class="form-input">
							<input type="email" name="pickupPointEmail" id="pickupPointEmail" required>
						</div>
					</div>
					<div class="form-footer">
						<input type="submit" value="Save" class="sameday_admin_button">
						<button type="button" class="sameday_admin_button sameday-thickbox-cancel">Cancel</button>
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
					<h3><?= TranslatorHandler::translate("Are you sure you want to delete this pickup point?")?></h3>
					<div class="form-footer">
						<input type="submit" name="submit" value="Submit" class="sameday_admin_button">
						<button type="button" class="sameday_admin_button sameday-thickbox-cancel">Cancel</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}
}
