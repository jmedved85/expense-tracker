<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AddFundsAdminController extends TransactionAdminController
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

    /**
     * @param Request|null $request
     *
     * @return Response
     */
    public function createAction(Request $request = null): Response
    {
        return parent::createAction($request);
    }
}
