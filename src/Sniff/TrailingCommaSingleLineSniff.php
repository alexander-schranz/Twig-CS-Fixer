<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensure that single-line arrays, objects and arguments list does not have a trailing comma.
 */
final class TrailingCommaSingleLineSniff extends AbstractSniff
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [')', '}', ']'])) {
            return;
        }

        $relatedToken = $token->getRelatedToken();
        Assert::notNull($relatedToken, 'A closer must have a related token.');

        if ($relatedToken->getLine() !== $token->getLine()) {
            // Multiline.
            return;
        }

        $previousPosition = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        Assert::notFalse($previousPosition, 'A closer cannot be the first token.');

        if (!$this->isTokenMatching($tokens[$previousPosition], Token::PUNCTUATION_TYPE, ',')) {
            return;
        }

        $fixer = $this->addFixableError(
            'Single-line arrays, objects and parameters lists should not have trailing comma.',
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken($previousPosition, '');
    }
}
