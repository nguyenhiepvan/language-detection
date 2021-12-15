<?php

declare(strict_types=1);

namespace LanguageDetection;

use ArrayIterator;
use JetBrains\PhpStorm\Pure;

/**
 * Class LanguageResult
 *
 * @copyright 2016-2018 Patrick Schur
 * @license https://opensource.org/licenses/mit-license.html MIT
 * @author Patrick Schur <patrick_schur@outlook.de>
 * @package LanguageDetection
 */
class LanguageResult implements \JsonSerializable, \IteratorAggregate, \ArrayAccess
{
    public const THRESHOLD = .025;

    /**
     * @var array
     */
    private array $result = [];

    /**
     * LanguageResult constructor.
     * @param array $result
     */
    public function __construct(array $result = [])
    {
        $this->result = $result;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->result[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->result[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->result[] = $value;
        } else {
            $this->result[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->result[$offset]);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)key($this->result);
    }

    /**
     * @param string ...$whitelist
     * @return LanguageResult
     */
    #[Pure]
    public function whitelist(string ...$whitelist): LanguageResult
    {
        return new LanguageResult(array_intersect_key($this->result, array_flip($whitelist)));
    }

    /**
     * @param string ...$blacklist
     * @return LanguageResult
     */
    #[Pure]
    public function blacklist(string ...$blacklist): LanguageResult
    {
        return new LanguageResult(array_diff_key($this->result, array_flip($blacklist)));
    }

    /**
     * @return array
     */
    public function close(): array
    {
        return $this->result;
    }

    /**
     * @return LanguageResult
     */
    public function bestResults(): LanguageResult
    {
        if (!count($this->result)) {
            return new LanguageResult;
        }

        $first = array_values($this->result)[0];

        return new LanguageResult(array_filter($this->result, static function ($value) use ($first) {
            return ($first - $value) <= self::THRESHOLD;
        }));
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->result);
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return LanguageResult
     */
    #[Pure]
    public function limit(int $offset, int $length = null): LanguageResult
    {
        return new LanguageResult(array_slice($this->result, $offset, $length));
    }
}
