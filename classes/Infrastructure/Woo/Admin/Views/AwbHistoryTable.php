<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Domain\Models\CarrierPackage;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

class AwbHistoryTable
{
    /**
     * @param $packages
     *
     * @return string
     */
    public static function addAwbHistoryTable($packages): string
    {
        $return = '';

        $packageRows = '';
        if (empty($packages)) {
            $packageRows = '<tr><td colspan="7" class="packages-empty">'
                . TranslatorHandler::translate("No data found")
                . '</td></tr>';
        }

        foreach ($packages as $package) {
            $summarySerialized = $package instanceof CarrierPackage
                ? ($package->getSummary() ?? '')
                : ($package['summary'] ?? '');
            $historySerialized = $package instanceof CarrierPackage
                ? ($package->getHistory() ?? '')
                : ($package['history'] ?? '');
            $summary = unserialize($summarySerialized, ['']);
            $packageHistory = unserialize($historySerialized, ['']);
            $historyRows = '';
            if (empty($packageHistory)) {
                $historyRows = '<tr><td colspan="7" class="history-empty">'
                    . TranslatorHandler::translate('No history data to display yet')
                    . '</td></tr>';
            } else {
                foreach ($packageHistory as $history) {
                    $historyRows .= '
                <tr>
                    <td> ' . $history->getName() . ' </td>
                    <td> ' . $history->getLabel() . '</td>
                    <td> ' . $history->getState() . ' </td>
                    <td> ' . $history->getDate()->format('Y-m-d H:i:s') . ' </td>
                    <td> ' . $history->getCounty() . ' </td>
                    <td> ' . $history->getTransitLocation() . ' </td>
                    <td> ' . $history->getReason() . ' </td>
                </tr>
            ';
                }
            }
            $packageRows .= '
                <tr>
                    <td class="sameday-show-history-details"
                        value="-"
                        data-awb-number="' . $summary->getParcelAwbNumber() . '">
                        <strong> + </strong>
                    </td>
                    <td> ' . $summary->getParcelAwbNumber() . '</td>
                    <td> ' . $summary->getParcelWeight() . ' </td>
                    <td> ' . ($summary->isDelivered() ? "Yes" : "No") . '</td>
                    <td> ' . $summary->getDeliveryAttempts() . '</td>
                    <td> ' . ($summary->isPickedUp() ? 'Yes' : 'No') . '</td>
                    <td> ' . ($summary->getPickedUpAt()
                        ? $summary->getPickedUpAt()->format('Y-m-d H:i:s')
                        : '') . '</td> 
                </tr>
                <tr>
                    <td colspan="7" class="history-details-cell">
                        <table class="history" id="history-' . $summary->getParcelAwbNumber() . '">
                          <tr>
                            <th>' . TranslatorHandler::translate("Status") . '</th>
                            <th>' . TranslatorHandler::translate("Label") . '</th>
                            <th>' . TranslatorHandler::translate("State") . '</th>
                            <th>' . TranslatorHandler::translate("Date") . '</th>
                            <th>' . TranslatorHandler::translate("County") . '</th>	
                            <th>' . TranslatorHandler::translate("Translation") . '</th>		
                            <th>' . TranslatorHandler::translate("Reason") . '</th>		    
                          </tr>
                          ' . $historyRows . ' 
                        </table>
                    </td>
                </tr>
        ';
        }

        $return .= '<table class="packages">
                  <tr>
                    <th></th>
                    <th>' . TranslatorHandler::translate("Parcel number") . '</th>
                    <th>' . TranslatorHandler::translate("Parcel weight") . '</th>
                    <th>' . TranslatorHandler::translate("Delivered") . '</th>
                    <th>' . TranslatorHandler::translate("Delivery attempts") . '</th>
                    <th>' . TranslatorHandler::translate("Is picked up") . '</th>
                    <th>' . TranslatorHandler::translate("Picked up at") . '</th>				    
                  </tr>
                  ' . $packageRows . '		  
                </table>';

        return $return;
    }
}
