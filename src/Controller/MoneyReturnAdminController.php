<?php

declare(strict_types=1);

namespace App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MoneyReturnAdminController extends CRUDController
{
    public function createAction(Request $request = null): Response
    {
        /* NOTE: redirecting example */
        $response = parent::createAction($request);

        // Check if the form was successfully submitted and saved
        if ($response instanceof RedirectResponse && $response->getStatusCode() === Response::HTTP_FOUND) {
            $uniqId = $request->query->get('uniqid');

            if ($request->request->has($uniqId) && isset($request->request->get($uniqId)['invoice'])) {
                $invoiceId = $request->request->get($uniqId)['invoice'];

                // Redirect to the invoice admins edit route with invoiceId as a parameter
                return $this->redirectToRoute('admin_app_invoice_edit', ['id' => $invoiceId]);
            }
        }

        return $response;
    }
}
