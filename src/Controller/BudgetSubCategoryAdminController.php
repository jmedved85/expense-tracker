<?php

declare(strict_types=1);

namespace App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class BudgetSubCategoryAdminController extends CRUDController
{
    // protected function redirectTo(Request $request, object $object): RedirectResponse
    // {
    //     return $this->redirect('/admin/app/budgetmaincategory/list');
    // }
}
