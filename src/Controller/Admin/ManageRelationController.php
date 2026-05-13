<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Relation\ManageRelationDetailBuilderInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageRelationController extends AbstractController
{
    public function __construct(
        private readonly ManageRelationRegistryInterface $relationRegistry,
        private readonly ManageRelationDetailBuilderInterface $relationDetailBuilder,
    ) {
    }

    #[Route('/manage/relations', name: 'manage_relations', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Manage relations',
            'body_template' => 'manage/admin/relations.html.twig',
            'body_context' => [
                'relations' => $this->relationRegistry->getRelations(),
            ],
        ]);
    }

    #[Route('/manage/relations/{componentKey}/{relationKey}', name: 'manage_relation_detail', methods: ['GET'])]
    public function detail(string $componentKey, string $relationKey): Response
    {
        $detail = $this->relationDetailBuilder->buildRelationDetail($componentKey, $relationKey);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage relation "%s/%s" was not found.', $componentKey, $relationKey));
        }

        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Manage relation detail',
            'body_template' => 'manage/admin/relation_detail.html.twig',
            'body_context' => $detail,
        ]);
    }
}
