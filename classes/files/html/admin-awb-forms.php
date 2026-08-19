<?php

declare(strict_types=1);

/**
 * @var string $actionUrl
 * @var string $addAwbNonce
 * @var string $showAsPdfNonce
 * @var string $addNewParcelNonce
 * @var string $removeAwbNonce
 */
?>
<form id="addAwbForm" method="POST" action="<?php echo esc_url($actionUrl); ?>">
    <input type="hidden" name="action" value="add-awb">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($addAwbNonce); ?>">
</form>
<form id="showAsPdf" method="POST" action="<?php echo esc_url($actionUrl); ?>">
    <input type="hidden" name="action" value="show-as-pdf">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($showAsPdfNonce); ?>">
</form>
<form id="addNewParcelForm" method="POST" action="<?php echo esc_url($actionUrl); ?>">
    <input type="hidden" name="action" value="add-new-parcel">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($addNewParcelNonce); ?>">
</form>
<form id="removeAwb" method="POST" action="<?php echo esc_url($actionUrl); ?>">
    <input type="hidden" name="action" value="remove-awb">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($removeAwbNonce); ?>">
</form>
