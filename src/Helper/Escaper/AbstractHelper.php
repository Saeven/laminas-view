<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Escaper;

use Laminas\Escaper\Escaper as Escape;
use Laminas\View\Exception;
use Laminas\View\Helper;

use function is_array;
use function is_object;
use function is_string;
use function method_exists;

/**
 * @psalm-internal Laminas\View
 */
abstract class AbstractHelper extends Helper\AbstractHelper
{
    public const RECURSE_NONE   = 0x00;
    public const RECURSE_ARRAY  = 0x01;
    public const RECURSE_OBJECT = 0x02;

    /**
     * @deprecated
     *
     * @var string
     */
    protected $encoding;

    /** @var Escape|null */
    protected $escaper;

    /**
     * @param non-empty-string $encoding
     * @psalm-suppress DeprecatedProperty
     */
    public function __construct(?Escape $escaper = null, string $encoding = 'UTF-8')
    {
        $this->escaper  = $escaper;
        $this->encoding = $escaper ? $escaper->getEncoding() : $encoding;
    }

    /**
     * Invoke this helper: escape a value
     *
     * @param  mixed $value
     * @param  int   $recurse Expects one of the recursion constants;
     *                        used to decide whether or not to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function __invoke($value, $recurse = self::RECURSE_NONE)
    {
        if (is_string($value)) {
            return $this->escape($value);
        }

        if (is_array($value)) {
            if (! ($recurse & self::RECURSE_ARRAY)) {
                throw new Exception\InvalidArgumentException(
                    'Array provided to Escape helper, but flags do not allow recursion'
                );
            }
            foreach ($value as $k => $v) {
                $value[$k] = $this->__invoke($v, $recurse);
            }
            return $value;
        }

        if (is_object($value)) {
            if (! ($recurse & self::RECURSE_OBJECT)) {
                // Attempt to cast it to a string
                if (method_exists($value, '__toString')) {
                    return $this->escape((string) $value);
                }
                throw new Exception\InvalidArgumentException(
                    'Object provided to Escape helper, but flags do not allow recursion'
                );
            }
            if (method_exists($value, 'toArray')) {
                return $this->__invoke($value->toArray(), $recurse | self::RECURSE_ARRAY);
            }
            return $this->__invoke((array) $value, $recurse | self::RECURSE_ARRAY);
        }

        return $value;
    }

    /**
     * Escape a value for current escaping strategy
     *
     * @param  string $value
     * @return string
     */
    abstract protected function escape($value);

    /**
     * Set the encoding to use for escape operations
     *
     * @deprecated Will be removed in 3.0.
     *
     * @param  non-empty-string $encoding
     * @throws Exception\InvalidArgumentException
     * @return AbstractHelper
     * @psalm-suppress DeprecatedProperty
     */
    public function setEncoding($encoding)
    {
        if (null !== $this->escaper) {
            throw new Exception\InvalidArgumentException(
                'Character encoding settings cannot be changed once the Helper has been used or '
                . ' if a Laminas\Escaper\Escaper object (with preset encoding option) is set.'
            );
        }

        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Get the encoding to use for escape operations
     *
     * @deprecated Will be removed in 3.0.
     *
     * @return string
     * @psalm-suppress DeprecatedProperty
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set instance of Escape
     *
     * @deprecated The escaper should be provided to the constructor. This method will be removed in 3.0.
     *
     * @return $this
     * @psalm-suppress DeprecatedProperty
     */
    public function setEscaper(Escape $escaper)
    {
        $this->escaper  = $escaper;
        $this->encoding = $escaper->getEncoding();

        return $this;
    }

    /**
     * Get instance of Escaper
     *
     * @deprecated To be removed in 3.0
     *
     * @return Escape
     * @psalm-suppress DeprecatedProperty
     */
    public function getEscaper()
    {
        if (null === $this->escaper) {
            $this->escaper = new Escape($this->encoding);
        }

        return $this->escaper;
    }
}
