<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

final class ManagePhpClassNameExtractor
{
    public function classNameFromFile(\SplFileInfo $file): ?string
    {
        $tokens = token_get_all((string) file_get_contents($file->getPathname()));
        $namespace = $this->namespaceFromTokens($tokens);
        $shortName = $this->classShortNameFromTokens($tokens);

        if (null === $shortName) {
            return null;
        }

        return '' === $namespace ? $shortName : $namespace.'\\'.$shortName;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function namespaceFromTokens(array $tokens): string
    {
        $namespace = '';

        for ($index = 0, $count = count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];
            if (!is_array($token) || T_NAMESPACE !== $token[0]) {
                continue;
            }

            for (++$index; $index < $count; ++$index) {
                $part = $tokens[$index];
                if (is_array($part) && in_array($part[0], [T_STRING, T_NAME_QUALIFIED], true)) {
                    $namespace .= $part[1];
                    continue;
                }
                if ('\\' === $part) {
                    $namespace .= '\\';
                    continue;
                }
                if (';' === $part || '{' === $part) {
                    break;
                }
            }

            break;
        }

        return $namespace;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function classShortNameFromTokens(array $tokens): ?string
    {
        for ($index = 0, $count = count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];
            if (!is_array($token) || T_CLASS !== $token[0]) {
                continue;
            }

            if ($this->isClassNameReference($tokens, $index)) {
                continue;
            }

            for (++$index; $index < $count; ++$index) {
                $part = $tokens[$index];
                if (is_array($part) && T_STRING === $part[0]) {
                    return $part[1];
                }
            }
        }

        return null;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function isClassNameReference(array $tokens, int $classTokenIndex): bool
    {
        $previousIndex = $classTokenIndex - 1;
        while ($previousIndex >= 0 && is_array($tokens[$previousIndex]) && T_WHITESPACE === $tokens[$previousIndex][0]) {
            --$previousIndex;
        }

        return $previousIndex >= 0 && is_array($tokens[$previousIndex]) && T_DOUBLE_COLON === $tokens[$previousIndex][0];
    }
}
