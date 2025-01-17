<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure that files ends with one blank line.
 */
final class BlankEOFSniff extends AbstractSniff
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        // Doesn't apply for empty files.
        if (0 === $tokenPosition) {
            return;
        }

        $token = $tokens[$tokenPosition];

        if (!$this->isTokenMatching($token, Token::EOF_TYPE)) {
            return;
        }

        $i = 0;
        while (
            isset($tokens[$tokenPosition - ($i + 1)])
            && $this->isTokenMatching($tokens[$tokenPosition - ($i + 1)], Token::EOL_TYPE)
        ) {
            $i++;
        }

        // Only 0 or 2+ blank lines are reported.
        if (1 === $i) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('A file must end with 1 blank line; found %d', $i),
            $token
        );

        // Only linting currently.
        if (null === $fixer) {
            return;
        }

        if (0 === $i) {
            $fixer->addNewlineBefore($tokenPosition);
        } else {
            $fixer->beginChangeset();
            while ($i >= 2) {
                $fixer->replaceToken($tokenPosition - $i, '');
                $i--;
            }
            $fixer->endChangeset();
        }
    }
}
