<?php

if (! defined( 'ABSPATH' ) ) {
    exit;
}

function samedaycourierCreateAwbHistoryTable($packages) {
    $return = '<h3 style="text-align: center; color: #0A246A"> <strong> ' . __("Awb History", "samedaycourier") . '</strong> </h3>';

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
        $packageRows = '<tr><td colspan="7" style="text-align: center">'. __("No data found", "samedaycourier") .'</td></tr>';
    }

    foreach ($packages as $package) {
        $summary = unserialize($package['summary']);
        $packageHistory = unserialize($package['history']);
        $historyRows = '';
        foreach ($packageHistory as $history) {
            $historyRows .= '
                    <td> '.$history->getName().' </td>
                    <td> '.$history->getLabel().'</td>
                    <td> '.$history->getState().' </td>
                    <td> '.$history->getDate()->format('Y-m-d H:i:s').' </td>
                    <td> '.$history->getCounty().' </td>
                    <td> '.$history->getTransitLocation().' </td>
                    <td> '.$history->getReason().' </td>
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
                            <th style="width: 15%">' . __("Status", "samedaycourier") . '</th>
                            <th style="width: 20%">' . __("Label", "samedaycourier") . '</th>
                            <th style="width: 15%">' . __("State", "samedaycourier") . '</th>
                            <th style="width: 15%">' . __("Date", "samedaycourier") . '</th>
                            <th style="width: 10%">' . __("County", "samedaycourier") . '</th>	
                            <th style="width: 15%">' . __("Translation", "samedaycourier") . '</th>		
                            <th style="width: 10%">' . __("Reason", "samedaycourier") . '</th>		    
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
                    <th>' . __("Parcel number", "samedaycourier") . '</th>
                    <th>' . __("Parcel weight", "samedaycourier") . '</th>
                    <th>' . __("Delivered", "samedaycourier") . '</th>
                    <th>' . __("Delivery attempts", "samedaycourier") . '</th>
                    <th>' . __("Is picked up", "samedaycourier") . '</th>
                    <th>' . __("Picked up at", "samedaycourier") . '</th>				    
                  </tr>
                  '.$packageRows.'		  
                </table>';

    $js = '
        <script>
            jQuery(document).ready(function($) {
                $(document).on("click", ".showHistoryDetails", function() {
                  show = $(this).val();
                  awbNumber = $(this).data("awb-number");
                  table_id = "history-" + awbNumber;
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

