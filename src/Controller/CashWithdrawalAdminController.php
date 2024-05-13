<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

final class CashWithdrawalAdminController extends TransactionAdminController
{
    /**
     * @param string $id
     *
     * @return Response
     */
    public function addBankFeeLinkToModalAction(string $id): Response
    {
        return parent::addBankFeeLinkToModalAction($id);
    }
}
