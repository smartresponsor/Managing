<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Form\ManageFormDetailBuilderInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageFormController extends AbstractController
{
    public function __construct(
        private readonly ManageFormRegistryInterface $formRegistry,
        private readonly ManageFormDetailBuilderInterface $formDetailBuilder,
    ) {
    }

    #[Route('/manage/forms', name: 'manage_forms', methods: ['GET'])]
    public function index(): Response
    {
        $rows = [];

        foreach ($this->formRegistry->getForms() as $form) {
            $rows[] = [
                'form' => $form,
                'detailUrl' => $this->generateUrl('manage_form_detail', [
                    'componentKey' => $form->componentKey,
                    'formKey' => $form->formKey,
                ]),
            ];
        }

        return $this->render('manage/page/content.html.twig', [
            'page_title' => 'Manage forms',
            'content_title' => 'Manage forms',
            'content' => $this->renderView('manage/admin/forms.html.twig', [
                'rows' => $rows,
            ]),
        ]);
    }

    #[Route('/manage/forms/{componentKey}/{formKey}', name: 'manage_form_detail', methods: ['GET'])]
    public function detail(string $componentKey, string $formKey): Response
    {
        $detail = $this->formDetailBuilder->buildFormDetail($componentKey, $formKey);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage form "%s/%s" was not found.', $componentKey, $formKey));
        }

        return $this->render('manage/page/content.html.twig', [
            'page_title' => 'Manage form detail',
            'content_title' => 'Manage form detail',
            'content' => $this->renderView('manage/admin/form_detail.html.twig', $detail),
        ]);
    }
}
