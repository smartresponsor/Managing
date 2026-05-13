<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Probe\ManageProbeDetailBuilderInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRunnerInterface;
use App\Managing\Value\ManageProbeDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageProbeController extends AbstractController
{
    public function __construct(
        private readonly ManageProbeRegistryInterface $probeRegistry,
        private readonly ManageProbeRunnerInterface $probeRunner,
        private readonly ManageProbeDetailBuilderInterface $probeDetailBuilder,
    ) {
    }

    #[Route('/manage/probes', name: 'manage_probes', methods: ['GET'])]
    public function index(): Response
    {
        $rows = [];
        foreach ($this->probeRegistry->getProbes() as $probe) {
            $rows[] = [
                'probe' => $probe,
                'runUrl' => $this->generateUrl('manage_probe_run', [
                    'componentKey' => $probe->componentKey,
                    'probeKey' => $probe->probeKey,
                ]),
                'detailUrl' => $this->generateUrl('manage_probe_detail', [
                    'componentKey' => $probe->componentKey,
                    'probeKey' => $probe->probeKey,
                ]),
            ];
        }

        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Manage probes',
            'body_template' => 'manage/admin/probes.html.twig',
            'body_context' => [
                'rows' => $rows,
            ],
        ]);
    }

    #[Route('/manage/probes/{componentKey}/{probeKey}', name: 'manage_probe_detail', methods: ['GET'])]
    public function detail(string $componentKey, string $probeKey): Response
    {
        $detail = $this->probeDetailBuilder->buildProbeDetail($componentKey, $probeKey);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage probe "%s/%s" was not found.', $componentKey, $probeKey));
        }

        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Manage probe detail',
            'body_template' => 'manage/admin/probe_detail.html.twig',
            'body_context' => array_merge($detail, [
                'runUrl' => $this->generateUrl('manage_probe_run', [
                    'componentKey' => $componentKey,
                    'probeKey' => $probeKey,
                ]),
            ]),
        ]);
    }

    #[Route('/manage/probes/{componentKey}/{probeKey}/run', name: 'manage_probe_run', methods: ['GET'])]
    public function run(string $componentKey, string $probeKey): Response
    {
        $probe = $this->findProbe($componentKey, $probeKey);
        if (!$probe instanceof ManageProbeDefinition) {
            throw $this->createNotFoundException(sprintf('Manage probe "%s/%s" was not found.', $componentKey, $probeKey));
        }

        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Run Manage probe',
            'body_template' => 'manage/admin/probe_result.html.twig',
            'body_context' => [
                'probe' => $probe,
                'result' => $this->probeRunner->runProbe($probe),
            ],
        ]);
    }

    private function findProbe(string $componentKey, string $probeKey): ?ManageProbeDefinition
    {
        foreach ($this->probeRegistry->getProbes() as $probe) {
            if ($probe->componentKey === $componentKey && $probe->probeKey === $probeKey) {
                return $probe;
            }
        }

        return null;
    }
}
