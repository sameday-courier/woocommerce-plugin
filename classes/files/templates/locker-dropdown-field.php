<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var array<string, array<int, array{id: int|string, label: string, selected: bool}>> $lockersByCity
 * @var string $shipTo
 */
?>
<tr>
    <th><label for="shipping-pickup-store-select"></label></th>
    <td>
        <select name="locker_id" id="shipping-pickup-store-select">
            <option value="" class="sameday-locker-placeholder">
                <?php echo $t('Select easyBox'); ?>
            </option>
            <?php foreach ($lockersByCity as $city => $cityLockers) : ?>
                <optgroup label="<?php echo esc_attr((string) $city); ?>" class="sameday-locker-optgroup"></optgroup>
                <?php foreach ($cityLockers as $locker) : ?>
                    <option value="<?php echo esc_attr((string) $locker['id']); ?>"
                            class="sameday-locker-option"
                        <?php echo $locker['selected'] ? "selected='selected'" : ''; ?>>
                        <?php echo esc_html($locker['label']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </select>
    </td>
</tr>
<?php if (null !== $shipTo) : ?>
    <tr id="showSamedayLockerDetailsCheckoutLine" class="shipping-pickup-store">
        <td><strong><?php echo $t('Ship to'); ?></strong></td>
        <th><span id="showLockerDetails"><?php echo $shipTo; ?></span></th>
    </tr>
<?php endif; ?>
