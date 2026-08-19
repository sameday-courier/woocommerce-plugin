<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var string $actionUrl
 * @var int|string $serviceId
 * @var string $nonce
 * @var string $serviceName
 * @var bool $nameDisabled
 * @var float|int|string $price
 * @var float|int|string $priceFree
 * @var array<int, array{value: int, text: string, selected: bool}> $statuses
 */
?>
<strong style="font-size: large; color: #0A246A">
    <?php echo $t('Edit Service'); ?> - <?php echo esc_html($serviceName); ?>
</strong>
<form method="POST" onsubmit="" action="<?php echo esc_url($actionUrl); ?>">
    <input type="hidden" name="action" value="edit-service">
    <table class="form-table editServiceForm">
        <tbody>
            <input type="hidden"
                   name="samedaycourier-service-id"
                   value="<?php echo esc_attr((string) $serviceId); ?>">
            <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>">
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="samedaycourier-service-name">
                        <?php echo $t('Service Name'); ?><span style="color: #ff2222"> * </span>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text"
                           name="samedaycourier-service-name"
                           style="width: 297px; height: 36px;"
                           <?php echo $nameDisabled ? 'disabled' : ''; ?>
                           id="samedaycourier-service-name"
                           value="<?php echo esc_attr($serviceName); ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="samedaycourier-price">
                        <?php echo $t('Price'); ?><span style="color: #ff2222"> * </span>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <input type="number"
                           name="samedaycourier-price"
                           step="any"
                           style="width: 297px; height: 36px;"
                           id="samedaycourier-price"
                           value="<?php echo esc_attr((string) $price); ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="samedaycourier-free-delivery-price">
                        <?php echo $t('Free delivery price'); ?>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <input type="number"
                           name="samedaycourier-free-delivery-price"
                           step="any"
                           style="width: 297px; height: 36px;"
                           id="samedaycourier-free-delivery-price"
                           value="<?php echo esc_attr((string) $priceFree); ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="samedaycourier-status">
                        <?php echo $t('Status'); ?><span style="color: #ff2222"> * </span>
                    </label>
                </th>
                <td class="forminp forminp-text">
                    <select name="samedaycourier-status"
                            style="width: 297px; height: 36px;"
                            id="samedaycourier-status">
                        <?php foreach ($statuses as $status) : ?>
                            <option value="<?php echo esc_attr((string) $status['value']); ?>"
                                <?php echo $status['selected'] ? 'selected' : ''; ?>>
                                <?php echo (string) $status['text']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <button class="sameday_admin_button" type="submit" value="Submit">
                        <?php echo $t('Edit Service'); ?>
                    </button>
                </th>
            </tr>
        </tbody>
    </table>
</form>
