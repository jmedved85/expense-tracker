<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BudgetMainCategory;
use App\Entity\BudgetSubCategory;
use App\Entity\Unit;
use App\Repository\BudgetMainCategoryRepository;
use App\Repository\BudgetSubCategoryRepository;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BudgetMainCategoryAdminController extends CRUDController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param Request $request
     * @param string $type
     * @return Response
     * @Route("/addNewCategoryModal/{type}", name="add_new_category_modal")
     */
    public function addNewCategoryModalAction(Request $request, string $type): Response
    {
        $template = 'Budget/add_budget_category_modal_form.html.twig';

        return $this->render($template, [
            'type' => $type
        ]);
    }

    /**
     * @param Request $request
     * @param string $type
     * @return RedirectResponse
     * @Route("/addNewCategory/{type}", name="add_new_category")
     */
    public function addNewCategoryAction(Request $request, string $type): RedirectResponse
    {
        /** @var BudgetSubCategoryRepository $budgetSubCategoryRepository */
        $budgetSubCategoryRepository = $this->entityManager->getRepository(BudgetSubCategory::class);
        /** @var BudgetMainCategoryRepository $budgetMainCategoryRepository */
        $budgetMainCategoryRepository = $this->entityManager->getRepository(BudgetMainCategory::class);
        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);

        // $unit = $unitRepository->findOneBy(['id' => $unitId]);

        $name = $request->request->get('addNewCategoryNameInput');

        if ($type == 'main') {
            $budgetMainCategory = new BudgetMainCategory();
            $budgetMainCategory->setName($name);
            // $budgetMainCategory->setUnit($unit);

            $budgetMainCategoryRepository->add($budgetMainCategory, true);
        } else {
            $budgetMainCategoryId = $request->request->get('mainCategorySelect');
            $budgetMainCategory = $budgetMainCategoryRepository->findOneBy(['id' => $budgetMainCategoryId]);

            $budgetSubCategory = new BudgetSubCategory();
            $budgetSubCategory->setName($name);
            $budgetSubCategory->setBudgetMainCategory($budgetMainCategory);
            // $budgetSubCategory->setUnit($type == 'main' ? $unit : $budgetMainCategory->getUnit());

            $budgetSubCategoryRepository->add($budgetSubCategory, true);
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * @Route("/getBudgetCategories", name="get_budget_categories")
     */
    public function getBudgetCategoriesAction(Request $request): JsonResponse
    {
        $budgetMainCategoryRepository = $this->entityManager->getRepository(BudgetMainCategory::class);

        $data = json_decode($request->getContent(), true);
        $mainCategoryId = $data['mainCategoryId'];

        $budgetMainCategory = $budgetMainCategoryRepository->findOneBy(['id' => $mainCategoryId]);
        $categoriesList = $budgetMainCategory->getBudgetSubCategories()->toArray();

        $categoriesListArr = [];

        foreach ($categoriesList as $category) {
            $categoryArr = [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ];

            $categoriesListArr[] = $categoryArr;
        }

        usort($categoriesListArr, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return new JsonResponse($categoriesListArr);
    }

    protected function redirectTo(Request $request, object $object): RedirectResponse
    {
        // Create a RedirectResponse
        return $this->redirect('/admin/app/budgetmaincategory/list');
    }
}
