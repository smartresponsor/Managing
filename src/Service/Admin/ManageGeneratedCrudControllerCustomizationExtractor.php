<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Extracts intentionally customized generated-controller hooks before a
 * deterministic generator rewrite.
 *
 * Generated CRUD controllers are checked in as thin bridge classes. Some of
 * them may still own explicit component-facing override hooks, such as choice
 * maps for array-backed fields. This extractor preserves those hooks while the
 * writer refreshes the generated route/entity shell.
 */
final class ManageGeneratedCrudControllerCustomizationExtractor
{
    /**
     * @return list<string>
     */
    public function extractProtectedManageMethods(string $source): array
    {
        $methods = [];
        $offset = 0;

        while (preg_match('/\n(?P<indent> {4})(?:\/\*\*[\s\S]*?\*\/\n {4})?protected static function manage[A-Za-z0-9_]+\s*\(/', $source, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $methodStart = $match[0][1] + 1;
            $openBrace = strpos($source, '{', $methodStart);
            if (false === $openBrace) {
                break;
            }

            $methodEnd = $this->matchingBraceEnd($source, $openBrace);
            if (null === $methodEnd) {
                break;
            }

            $method = rtrim(substr($source, $methodStart, $methodEnd - $methodStart + 1));
            if (str_contains($method, 'function manageIsReadOnly()')) {
                $offset = $methodEnd + 1;
                continue;
            }

            $methods[] = $method;
            $offset = $methodEnd + 1;
        }

        return $methods;
    }

    public function extractCustomMethodsBlock(string $source): string
    {
        $methods = $this->extractProtectedManageMethods($source);
        if ([] === $methods) {
            return '';
        }

        return implode("\n\n", $methods)."\n\n";
    }

    private function matchingBraceEnd(string $source, int $openBrace): ?int
    {
        $depth = 0;
        $length = strlen($source);

        for ($index = $openBrace; $index < $length; ++$index) {
            $char = $source[$index];
            if ('{' === $char) {
                ++$depth;
                continue;
            }

            if ('}' !== $char) {
                continue;
            }

            --$depth;
            if (0 === $depth) {
                return $index;
            }
        }

        return null;
    }
}
