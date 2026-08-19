<?php

declare(strict_types=1);

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

$t = static function (string $text): string {
    return TranslatorHandler::translate($text);
};

/**
 * @var array<int, array{
 *     awbNumber: string,
 *     weight: mixed,
 *     delivered: bool,
 *     deliveryAttempts: mixed,
 *     pickedUp: bool,
 *     pickedUpAt: string,
 *     history: array<int, array{
 *         name: string,
 *         label: string,
 *         state: string,
 *         date: string,
 *         county: string,
 *         transitLocation: string,
 *         reason: string
 *     }>
 * }> $packages
 */
?>
<table class="packages">
    <tr>
        <th></th>
        <th><?php echo $t('Parcel number'); ?></th>
        <th><?php echo $t('Parcel weight'); ?></th>
        <th><?php echo $t('Delivered'); ?></th>
        <th><?php echo $t('Delivery attempts'); ?></th>
        <th><?php echo $t('Is picked up'); ?></th>
        <th><?php echo $t('Picked up at'); ?></th>
    </tr>
    <?php if (empty($packages)) : ?>
        <tr>
            <td colspan="7" class="packages-empty"><?php echo $t('No data found'); ?></td>
        </tr>
    <?php else : ?>
        <?php foreach ($packages as $package) : ?>
            <tr>
                <td class="sameday-show-history-details"
                    value="-"
                    data-awb-number="<?php echo esc_attr((string) $package['awbNumber']); ?>">
                    <strong> + </strong>
                </td>
                <td><?php echo esc_html((string) $package['awbNumber']); ?></td>
                <td><?php echo esc_html((string) $package['weight']); ?></td>
                <td><?php echo $package['delivered'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo esc_html((string) $package['deliveryAttempts']); ?></td>
                <td><?php echo $package['pickedUp'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo esc_html($package['pickedUpAt']); ?></td>
            </tr>
            <tr>
                <td colspan="7" class="history-details-cell">
                    <table class="history" id="history-<?php echo esc_attr((string) $package['awbNumber']); ?>">
                        <tr>
                            <th><?php echo $t('Status'); ?></th>
                            <th><?php echo $t('Label'); ?></th>
                            <th><?php echo $t('State'); ?></th>
                            <th><?php echo $t('Date'); ?></th>
                            <th><?php echo $t('County'); ?></th>
                            <th><?php echo $t('Translation'); ?></th>
                            <th><?php echo $t('Reason'); ?></th>
                        </tr>
                        <?php if (empty($package['history'])) : ?>
                            <tr>
                                <td colspan="7" class="history-empty">
                                    <?php echo $t('No history data to display yet'); ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($package['history'] as $history) : ?>
                                <tr>
                                    <td><?php echo esc_html($history['name']); ?></td>
                                    <td><?php echo esc_html($history['label']); ?></td>
                                    <td><?php echo esc_html($history['state']); ?></td>
                                    <td><?php echo esc_html($history['date']); ?></td>
                                    <td><?php echo esc_html($history['county']); ?></td>
                                    <td><?php echo esc_html($history['transitLocation']); ?></td>
                                    <td><?php echo esc_html($history['reason']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
