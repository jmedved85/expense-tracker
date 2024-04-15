<?php

declare(strict_types=1);

namespace App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

final class SupplierAdminController extends CRUDController
{
    // /**
    //  * @param string $id
    //  * @return Response
    //  */
    // public function commentsAction(string $id): Response
    // {
    //     $commentsList = $this->getComments($id);

    //     $template = 'Comments/comments_view_list.html.twig';

    //     return $this->renderWithExtraParams($template, [
    //         'commentsList' => $commentsList,
    //     ]);
    // }
}
