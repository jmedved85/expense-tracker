<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Unit;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class UnitAdminController extends CRUDController
{
    #[Route('/switch-to-unit/{id}', name: 'switch_to_unit')]
    public function switchToUnitAction(int $id, Request $request): RedirectResponse
    {
        /** @var Unit|null $object */
        $object = $this->admin->getSubject();

        /* Set session variable */
        $request->getSession()->set('switched_into_unit', [
            'id' => $id,
            'name' => $object->getName()
        ]);

        return $this->redirectToRoute('sonata_admin_dashboard');
    }

    #[Route('/exit-unit', name: 'exit_unit')]
    public function exitUnitAction(Request $request): RedirectResponse
    {
        /* Unset session variable */
        $request->getSession()->remove('switched_into_unit');

        return $this->redirectToRoute('sonata_admin_dashboard');
    }
}
