<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceVerifier;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\UserPermissionChecker;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class EditService
{
    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct()
    {
        $this->samedayServiceRepository = new SamedayServiceRepository();
    }

    /**
     * @return void
     */
    public function execute(): void
    {


        if (null === $_POST['samedaycourier-service-name'] ?? null) {
            $_POST['samedaycourier-service-name'] = SamedayConstants::OOH_SERVICES_LABELS[
                Helper::getHostCountry()
            ];
        }

        $post_fields = array(
            'id' => array(
                'required' => true,
                'value' => $_POST['samedaycourier-service-id']
            ),
            'name' => array(
                'required' => true,
                'value' =>  $_POST['samedaycourier-service-name']
            ),
            'price' => array(
                'required' => true,
                'value' => $_POST['samedaycourier-price']
            ),
            'price_free' => array(
                'required' => false,
                'value' => $_POST['samedaycourier-free-delivery-price'] ?: null
            ),
            'status' => array(
                'required' => false,
                'value' => $_POST['samedaycourier-status']
            )
        );

        $errors = array();

        foreach ($post_fields as $field => $field_value) {
            if ($field_value['required'] && ('' === trim($field_value['value']))) {
                $errors[] = __("The $field must not be empty", SamedayConstants::TEXT_DOMAIN);
            }
        }

        $priceFree = null;
        if ((float) $post_fields['price_free']['value'] > 0) {
            $priceFree = (float) $post_fields['price_free']['value'];
        }

        if (empty($errors)) {
            $currentService = $this->samedayServiceRepository->getService((int) $post_fields['id']['value']);
            if (null === $currentService) {
                Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_services']);
            }

            $service = array(
                'id' => (int) $post_fields['id']['value'],
                'name' => Helper::sanitizeInput($post_fields['name']['value']),
                'price' => (float) $post_fields['price']['value'],
                'price_free' => $priceFree,
                'status' => (int) $post_fields['status']['value']
            );

            $this->samedayServiceRepository->updateService($service);

            // Update PUDO
            if ($currentService->getSamedayCode() === SamedayConstants::LOCKER_NEXT_DAY_CODE) {
                $pudoService = $this->samedayServiceRepository->getServiceSamedayByCode(
                    SamedayConstants::PUDO_CODE
                );

                if (null !== $pudoService) {
                    $this->samedayServiceRepository->updateService(
                        [
                            'id' => $pudoService->getId(),
                            'status' => $service['status'],
                        ]
                    );
                }
            }

            Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_services']);
        }

        Redirector::to(
            'edit.php',
            [
                'post_type' => 'page',
                'page' => 'sameday_services',
                'action' => 'edit',
                'id' => (int) $post_fields['id']['value'],
            ]
        );
    }
}
