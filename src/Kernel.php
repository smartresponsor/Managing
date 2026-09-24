<?php

declare(strict_types=1);

namespace App\Managing;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Standalone Symfony kernel for independent Managing build, verification, and debugging.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
