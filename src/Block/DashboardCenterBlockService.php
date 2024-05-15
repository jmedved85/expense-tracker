<?php

declare(strict_types=1);

namespace App\Block;

use App\Entity\Account;
use App\Entity\Unit;
use App\Repository\AccountRepository;
use App\Repository\UnitRepository;
use App\Utility\AppUtil;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class DashboardCenterBlockService extends AbstractBlockService
{
    // private const TEMPLATE = 'Block/block_dashboard_center.html.twig';

    // public function __construct(
    //     ?Environment $twig,
    //     private Pool $pool,
    //     private AppUtil $appUtil,
    //     private EntityManagerInterface $entityManager
    // ) {
    //     parent::__construct($twig);
    // }

    // public function configureSettings(OptionsResolver $resolver): void
    // {
    //     $resolver->setDefaults([
    //         'groups' => false,
    //         'title' => 'Notifications',
    //         'template' => self::TEMPLATE,
    //     ]);
    // }

    // public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    // {
    //     /** @var AccountRepository $accountRepository */
    //     $accountRepository = $this->entityManager->getRepository(Account::class);
    //     /** @var UnitRepository $unitRepository */
    //     $unitRepository = $this->entityManager->getRepository(Unit::class);

    //     /* Get unit id if unit is switched */
    //     $switchedIntoUnitId = $this->appUtil->getSwitchedUnitId();

    //     $unit = null;

    //     if ($switchedIntoUnitId) {
    //         $accounts = $accountRepository->findBy(['unit' => $switchedIntoUnitId], ['name' => 'ASC']);
    //         $unit = $unitRepository->findOneBy(['id' => $switchedIntoUnitId]);
    //     }

    //     return $this->renderResponse(self::TEMPLATE, [
    //       'block' => $blockContext->getBlock(),
    //       'accounts' => $accounts ?? null,
    //       'unit' => $unit ?? null,
    //     ], $response);
    // }
}
