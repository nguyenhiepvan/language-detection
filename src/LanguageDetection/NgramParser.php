<?php

declare(strict_types=1);

namespace LanguageDetection;

use LanguageDetection\Tokenizer\TokenizerInterface;
use LanguageDetection\Tokenizer\WhitespaceTokenizer;
use LengthException;

/**
 * Class NgramParser
 *
 * @copyright 2016-2018 Patrick Schur
 * @license https://opensource.org/licenses/mit-license.html MIT
 * @author Patrick Schur <patrick_schur@outlook.de>
 * @package LanguageDetection
 */
abstract class NgramParser
{
    /**
     * @var int
     */
    protected int $minLength = 1;

    /**
     * @var int
     */
    protected int $maxLength = 3;

    /**
     * @var int
     */
    protected int $maxNgrams = 9000;

    /**
     * @var ?TokenizerInterface
     */
    protected ?TokenizerInterface $tokenizer = null;

    /**
     * @param int $minLength
     * @throws LengthException
     */
    public function setMinLength(int $minLength): void
    {
        if ($minLength <= 0 || $minLength >= $this->maxLength) {
            throw new LengthException('$minLength must be greater than zero and less than $this->maxLength.');
        }

        $this->minLength = $minLength;
    }

    /**
     * @param int $maxLength
     * @throws LengthException
     */
    public function setMaxLength(int $maxLength): void
    {
        if ($maxLength <= $this->minLength) {
            throw new LengthException('$maxLength must be greater than $this->minLength.');
        }

        $this->maxLength = $maxLength;
    }

    /**
     * @param int $maxNgrams
     * @throws LengthException
     */
    public function setMaxNgrams(int $maxNgrams): void
    {
        if ($maxNgrams <= 0) {
            throw new LengthException('$maxNgrams must be greater than zero.');
        }

        $this->maxNgrams = $maxNgrams;
    }

    /**
     * Sets the tokenizer
     *
     * @param TokenizerInterface $tokenizer
     */
    public function setTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }

    /**
     * @param string $str
     * @return array
     */
    private function tokenize(string $str): array
    {
        if (null === $this->tokenizer) {
            $this->tokenizer = new WhitespaceTokenizer();
        }

        return $this->tokenizer->tokenize($str);
    }

    /**
     * @param string $str
     * @return array
     */
    protected function getNgrams(string $str): array
    {
        $tokens = [];

        foreach ($this->tokenize($str) as $word) {
            $l = mb_strlen($word);

            for ($i = $this->minLength; $i <= $this->maxLength; ++$i) {
                for ($j = 0; ($i + $j - 1) < $l; ++$j, ++$tmp) {
                    $tmp = &$tokens[$i][mb_substr($word, $j, $i)];
                }
            }
        }

        foreach ($tokens as $i => $token) {
            $sum = array_sum($token);

            foreach ($token as $j => $value) {
                $tokens[$i][$j] = $value / $sum;
            }
        }

        if (!count($tokens)) {
            return [];
        }

        $tokens = array_merge(...$tokens);
        unset($tokens['_']);

        arsort($tokens, SORT_NUMERIC);

        return array_slice(
            array_keys($tokens),
            0,
            $this->maxNgrams
        );
    }
}
