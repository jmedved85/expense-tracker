<?php

namespace App\Utility;

use App\Entity\AccountType;
use App\Entity\Unit;
use App\Entity\User;
use App\Repository\UnitRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Twig\Environment;

class AppUtil
{
    public function __construct(
        // private string $projectDir,
        private RequestStack $requestStack,
        private Environment $twig,
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $securityContext,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getCurrentUserData(): ?array
    {
        if (is_null($this->tokenStorage)) {
            return null;
        }

        /* Get logged in user from the token */
        if ($this->tokenStorage->getToken()) {
            /** @var User $currentUser */
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $currentUserId = $currentUser->getId();
            $currentUserName = $currentUser->getUsername();
            $getSwitchedUnit = $this->getSwitchedUnit();
            $getSwitchedUnitId = $this->getSwitchedUnitId();
            $userUnits = $currentUser->getUserUnits();
            $userRoles = $currentUser->getRoles();
            $bankAccountType = AccountType::BANK;

            // $userUnits = [];

            // if (!empty($userUnit)) {
            //     foreach ($userUnit as $item) {
            //         array_push($userUnits, $item->getUnit());
            //     }
            // }

            /* Get user's role */

            $data = [];

            $data['user'] = $currentUser;
            $data['userId'] = $currentUserId;
            $data['userName'] = $currentUserName;
            $data['getSwitchedUnitId'] =
                isset($getSwitchedUnitId) ? $getSwitchedUnit->getId() : null;
            $data['bankAccountType'] = $bankAccountType;
            $data['userRoles'] = $userRoles;
            $data['isSuperAdmin'] = $this->isSuperAdmin();
            $data['userUnits'] = $userUnits;

            return $data;
        } else {
            return null;
        }
    }

    public function getSwitchedUnit(): ?Unit
    {
        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);

        $request = $this->requestStack->getCurrentRequest();

        if (is_null($request)) {
            return null;
        }

        $switchedIntoUnit = $request->getSession()->get('switched_into_unit');

        if ($switchedIntoUnit) {
            return $unitRepository->find($switchedIntoUnit);
        }

        return null;
    }

    public function getSwitchedUnitId(): ?int
    {
        $switchedIntoUnit = $this->getSwitchedUnit();

        if ($switchedIntoUnit) {
            return $switchedIntoUnit->getId();
        }

        return null;
    }

    public function isSuperAdmin(): bool
    {
        $securityContext = $this->securityContext;

        try {
            if ($securityContext->isGranted('ROLE_SUPER_ADMIN')) {
                return true;
            }
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }

        return false;
    }

    /**
     * Get the default header HTML for documents.
     */
    public function getHeaderHtml(?Unit $unit = null, ?DateTimeInterface $recordDate = null): string
    {
        return $this->twig->render('PDF/header.html.twig', [
            'unit' => $unit,
            // 'projectDir' => $this->projectDir,
            'recordDate' => $recordDate,
        ]);
    }

    /**
     * Get the default footer HTML for documents.
     */
    public function getFooterHtml(?Unit $unit = null, bool $currentTime = true): string
    {
        return $this->twig->render('PDF/footer.html.twig', [
            'unit' => $unit,
            'currentTime' => $currentTime,
            // 'projectDir' => $this->projectDir
        ]);
    }
}
