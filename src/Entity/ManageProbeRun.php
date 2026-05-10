<?php

declare(strict_types=1);

namespace App\Managing\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'manage_probe_run')]
class ManageProbeRun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $componentKey = '';

    #[ORM\Column(length: 160)]
    private string $probeKey = '';

    #[ORM\Column(length: 40)]
    private string $status = 'pending';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComponentKey(): string
    {
        return $this->componentKey;
    }

    public function setComponentKey(string $componentKey): self
    {
        $this->componentKey = $componentKey;

        return $this;
    }

    public function getProbeKey(): string
    {
        return $this->probeKey;
    }

    public function setProbeKey(string $probeKey): self
    {
        $this->probeKey = $probeKey;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
