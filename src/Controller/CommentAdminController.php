<?php

declare(strict_types=1);

namespace App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CommentAdminController extends CRUDController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getComments(string $unitId = null): array
    {
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->entityManager->getRepository(Comment::class);

        $comments = [];

        return $comments;
    }
}
