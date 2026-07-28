<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;


if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Models\SamedayPackage;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
class AwbHistoryTable
{
    /**
     * @param $packages
     *
     * @return string
     */
    public static function addAwbHistoryTable($packages): string
    {
        $return = '<h3 style="text-align: center; color: #0A246A"> <strong> ' . TranslatorHandler::translate("Awb History") . '</strong> </h3>';

        $style = '<style>
                .packages {
                  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
                  border-collapse: collapse;
                  width: 100%;
                }
                
                .packages td, .packages th {
                  border: 1px solid #ddd;
                  padding: 8px;
                }
                
                .packages tr:nth-child(even){background-color: #f2f2f2;}
                
                .packages tr:hover {background-color: #FFFFFE;}
                
                .packages th {
                  padding-top: 14px;
                  padding-bottom: 14px;
                  text-align: left;
                  background-color: #f1f1f1;
                  color: #0A246A;
                }
                
                .history {
                  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
                  border-collapse: collapse;
                  width: auto;
                }
                
                .history td, .history th {
                  border: 1px solid #ddd;
                  padding: 8px;
                }
                
                .history tr:nth-child(even){background-color: #f2f2f2;}
                
                .history tr:hover {background-color: #FFFFFE;}
                
                .history th {
                  padding-top: 10px;
                  padding-bottom: 10px;
                  text-align: left;
                  background-color: #a3b745;
                  color: #FFFFFE;
                }
                </style>';

        $return .= $style;

        $packageRows = '';
        if (empty($packages)) {
            $packageRows = '<tr><td colspan="7" style="text-align: center">'. TranslatorHandler::translate("No data found") .'</td></tr>';
        }

        foreach ($packages as $package) {
            $summarySerialized = $package instanceof SamedayPackage
                ? ($package->getSummary() ?? '')
                : ($package['summary'] ?? '');
            $historySerialized = $package instanceof SamedayPackage
                ? ($package->getHistory() ?? '')
                : ($package['history'] ?? '');
            $summary = unserialize($summarySerialized, ['']);
            $packageHistory = unserialize($historySerialized, ['']);
            $historyRows = '';
            foreach ($packageHistory as $history) {
                $historyRows .= '
                <tr>
                    <td> '.$history->getName().' </td>
                    <td> '.$history->getLabel().'</td>
                    <td> '.$history->getState().' </td>
                    <td> '.$history->getDate()->format('Y-m-d H:i:s').' </td>
                    <td> '.$history->getCounty().' </td>
                    <td> '.$history->getTransitLocation().' </td>
                    <td> '.$history->getReason().' </td>
                </tr>
            ';
            }
            $packageRows .= '
                <tr>
                    <td style="text-align: center; cursor:pointer;" class="showHistoryDetails" value="-" data-awb-number="'.$summary->getParcelAwbNumber().'"> <strong> + </strong> </td>
                    <td> '.$summary->getParcelAwbNumber().'</td>
                    <td> '.$summary->getParcelWeight().' </td>
                    <td> '.($summary->isDelivered() ? "Yes" : "No").'</td>
                    <td> '.$summary->getDeliveryAttempts().'</td>
                    <td> '.($summary->isPickedUp() ? 'Yes' : 'No').'</td>
                    <td> '.($summary->getPickedUpAt() ? $summary->getPickedUpAt()->format('Y-m-d H:i:s') : '').'</td>                    
                </tr>
                <tr>
                    <td colspan="7">
                        <table class="history" id="history-'.$summary->getParcelAwbNumber().'" style="width: 100%; display: none; text-align: center">
                          <tr>
                            <th style="width: 15%">' . TranslatorHandler::translate("Status") . '</th>
                            <th style="width: 20%">' . TranslatorHandler::translate("Label") . '</th>
                            <th style="width: 15%">' . TranslatorHandler::translate("State") . '</th>
                            <th style="width: 15%">' . TranslatorHandler::translate("Date") . '</th>
                            <th style="width: 10%">' . TranslatorHandler::translate("County") . '</th>	
                            <th style="width: 15%">' . TranslatorHandler::translate("Translation") . '</th>		
                            <th style="width: 10%">' . TranslatorHandler::translate("Reason") . '</th>		    
                          </tr>
                          '.$historyRows.' 
                        </table>
                    </td>
                </tr>
        ';
        }

        $return .= '<table class="packages" style="width: 100%">
                  <tr>
                    <th></th>
                    <th>' . TranslatorHandler::translate("Parcel number") . '</th>
                    <th>' . TranslatorHandler::translate("Parcel weight") . '</th>
                    <th>' . TranslatorHandler::translate("Delivered") . '</th>
                    <th>' . TranslatorHandler::translate("Delivery attempts") . '</th>
                    <th>' . TranslatorHandler::translate("Is picked up") . '</th>
                    <th>' . TranslatorHandler::translate("Picked up at") . '</th>				    
                  </tr>
                  '.$packageRows.'		  
                </table>';

        $js = '
        <script>
            jQuery(document).ready(function($) {
                $(document).on("click", ".showHistoryDetails", function() {
                  let show = $(this).val();
                  let awbNumber = $(this).data("awb-number");
                  let table_id = "history-" + awbNumber;
                  if (show === "+") {
                      $("#"+table_id).css("display","block");
                      $(this).val("-");
                      $(this).html("<strong> - </strong>");
                  } else {
                      $("#"+table_id).css("display","none");
                      $(this).val("+");
                      $(this).html("<strong> + </strong>");
                  }			  	  
                });
                
                $(".showHistoryDetails").trigger("click");
            });
        </script>
    ';

        $return .= $js;

        return $return;
    }
}
