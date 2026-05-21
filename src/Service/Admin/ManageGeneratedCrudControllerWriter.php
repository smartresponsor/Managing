<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Owns deterministic PHP source generation for thin EasyAdmin CRUD controllers.
 *
 * ManageCrudControllerGenerator selects the primary resource for a component;
 * this writer is the only class that knows how that selection is rendered as a
 * generated controller file.
 */
final class ManageGeneratedCrudControllerWriter
{
    private ManageGeneratedCrudControllerSourceRenderer $sourceRenderer;

    private ManageGeneratedCrudControllerCustomizationExtractor $customizationExtractor;

    public function __construct(
        private readonly string $bundleDir,
        private readonly ManageCrudResourcePolicy $resourcePolicy = new ManageCrudResourcePolicy(),
        ?ManageGeneratedCrudControllerSourceRenderer $sourceRenderer = null,
        ?ManageGeneratedCrudControllerCustomizationExtractor $customizationExtractor = null,
    ) {
        $this->sourceRenderer = $sourceRenderer ?? new ManageGeneratedCrudControllerSourceRenderer($this->resourcePolicy);
        $this->customizationExtractor = $customizationExtractor ?? new ManageGeneratedCrudControllerCustomizationExtractor();
    }

    public function generatedDirectory(): string
    {
        return $this->bundleDir.'/src/Controller/Crud/Generated';
    }

    public function writeController(string $fqcn, string $entityClass, string $componentKey): void
    {
        $generatedDir = $this->generatedDirectory();
        if (!is_dir($generatedDir)) {
            mkdir($generatedDir, 0777, true);
        }

        $filePath = $generatedDir.'/'.$this->shortClassName($fqcn).'.php';
        $existingSource = is_file($filePath) ? (string) file_get_contents($filePath) : '';
        $content = $this->controllerSource($fqcn, $entityClass, $componentKey, $existingSource);

        if (is_file($filePath) && file_get_contents($filePath) === $content) {
            return;
        }

        file_put_contents($filePath, $content);
    }

    /**
     * @param list<class-string> $generatedControllers
     */
    public function removeStaleControllers(array $generatedControllers): void
    {
        $generatedDir = $this->generatedDirectory();
        $desiredFiles = [];
        foreach ($generatedControllers as $fqcn) {
            $desiredFiles[$generatedDir.'/'.$this->shortClassName($fqcn).'.php'] = true;
        }

        foreach (glob($generatedDir.'/*CrudController.php') ?: [] as $file) {
            if (!isset($desiredFiles[$file])) {
                @unlink($file);
            }
        }
    }

    public function controllerSource(string $fqcn, string $entityClass, string $componentKey, string $existingSource = ''): string
    {
        return $this->sourceRenderer->render(
            $fqcn,
            $entityClass,
            $componentKey,
            $this->customizationExtractor->extractCustomMethodsBlock($existingSource),
        );
    }

    private function shortClassName(string $fqcn): string
    {
        $position = strrpos($fqcn, '\\');

        return false === $position ? $fqcn : substr($fqcn, $position + 1);
    }
}
