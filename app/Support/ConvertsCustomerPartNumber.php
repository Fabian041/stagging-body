<?php

namespace App\Support;

trait ConvertsCustomerPartNumber
{
    /**
     * Konversi part number customer ke format ber-dash sesuai aturan per-customer.
     *
     * Sumber tunggal dari logika yang sebelumnya diduplikasi di:
     * - PullingController::convertPartNumber & ::post
     * - LoadingListController::storeDetail, ::kanbanScanned & ::storeDetails
     *
     * MMKI = customer_id 6, 23, 24 (mengikuti convertPartNumber pada jalur scan).
     */
    protected function convertCustomerPartNumber(string $customerPart, $customerId): string
    {
        $codeLength = strlen($customerPart);
        $lastDigit = substr($customerPart, -2);

        if ($codeLength == 12) {
            // TMMIN
            if ($lastDigit != '00') {
                return substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -2);
            }
            return substr(substr_replace($customerPart, '-', 5, 0), 0, -2);
        }

        if ($codeLength == 10) {
            if ($customerId == 14 || $customerId == 22) {
                // SUZUKI
                return substr_replace($customerPart, '-', 5, 0);
            }
            if ($customerId == 6 || $customerId == 23 || $customerId == 24) {
                // MMKI
                return $customerPart;
            }
            // TBINA
            return substr_replace($customerPart, '-', 5, 0);
        }

        if ($codeLength == 13) {
            // SUZUKI
            if ($lastDigit != '000') {
                return substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -3);
            }
            return substr(substr_replace($customerPart, '-', 5, 0), 0, -3);
        }

        return $customerPart;
    }
}
