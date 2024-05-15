<?php

declare(strict_types=1);

namespace App\Block;

use App\Entity\Unit;
use App\Repository\UnitRepository;
use App\Utility\AppUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;
use Sonata\AdminBundle\Admin\Pool;

class AccountingTopBlockService extends AbstractBlockService
{
    // protected Environment $twig;

    // private const TEMPLATE = 'Block/block_top_menu.html.twig';

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
    //     'groups' => false,
    //     'title' => 'Top Menu',
    //     'template' => self::TEMPLATE,
    //     ]);
    // }

    // public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    // {
    //     /** @var UnitRepository $unitRepository */
    //     $unitRepository = $this->entityManager->getRepository(Unit::class);

    //     /* Get unit id if unit is switched */
    //     $switchedIntoUnitId = $this->appUtil->getSwitchedUnitId();

    //     if ($switchedIntoUnitId) {
    //         $unit = $unitRepository->findOneBy(['id' => $switchedIntoUnitId, 'active' => true]);
    //     } else {
    //         $unit = $unitRepository->findBy(['active' => true]);
    //     }

    //     return $this->renderResponse(self::TEMPLATE, [
    //         'block' => $blockContext->getBlock(),
    //         'unit' => $unit ?? null,
    //     ], $response);
    // }
}
