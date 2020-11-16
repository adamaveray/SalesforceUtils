<?php
declare(strict_types=1);

namespace AdamAveray\SalesforceUtils\Queries;

class SafeString
{
    public const QUOTE_OPEN = '\'';
    public const QUOTE_CLOSE = '\'';
    public const VALUE_NULL = 'null';
    public const VALUE_TRUE = 'TRUE';
    public const VALUE_FALSE = 'FALSE';
    public const DATETIME_FORMAT = 'c';

    /** @var array $charsAll A character map for characters to escape in all query params */
    private static $charsAll = [
        "\n" => '\\n',
        "\r" => '\\r',
        "\t" => '\\t',
        "\u{7}" => '\\b',
        "\f" => '\\f',
        '"' => '\\"',
        '\'' => '\\\'',
    ];
    /** @var array $charsAll A character map for characters to escape in LIKE query params only */
    private static $charsLike = [
        '_' => '\\_',
        '%' => '\\%',
    ];

    /** @var string The safe string value */
    private $string;

    /**
     * @param string $string The safe string value
     */
    public function __construct(string $string)
    {
        $this->string = $string;
    }

    /**
     * @return string The safe string value
     */
    public function __toString(): string
    {
        return (string) $this->string;
    }

    /**
     * @param mixed $value The value to escape
     * @param bool $isLike Whether the value is for a LIKE comparison
     * @param bool $quote Whether to quote the value
     * @return SafeString
     */
    public static function escape(
        $value,
        bool $isLike = false,
        bool $quote = false
    ): SafeString {
        $safe = self::escapeValue($value, $isLike, $quote);
        return new SafeString($safe);
    }

    /**
     * @param array $value The array of values to escape
     * @return SafeString The array merged into a SOQL IN compatible string
     */
    public static function escapeArray(array $value): SafeString
    {
        $out = [];
        foreach ($value as $item) {
            $out[] = self::escapeValue($item, false, true);
        }
        return new SafeString(implode(', ', $out));
    }

    /**
     * @param mixed $value The value to escape
     * @param bool $isLike Whether the value is for a LIKE comparison
     * @param bool $quote Whether to quote the value
     * @return string The escaped string value
     */
    private static function escapeValue(
        $value,
        bool $isLike = false,
        bool $quote = false
    ): string {
        if ($value === null) {
            $safe = self::VALUE_NULL;
        } elseif (is_bool($value)) {
            $safe = $value ? self::VALUE_TRUE : self::VALUE_FALSE;
        } elseif (is_int($value) || is_float($value)) {
            $safe = (string) $value;
        } elseif ($value instanceof \DateTimeInterface) {
            $safe = $value->format(self::DATETIME_FORMAT);
        } else {
            $value = (string) $value;

            // Escape special chars
            $chars = self::$charsAll;
            if ($isLike) {
                $chars += self::$charsLike;
            }
            $escaped = str_replace(
                array_keys($chars),
                array_values($chars),
                $value,
            );

            $safe = $escaped;
            if ($quote) {
                $safe = self::QUOTE_OPEN . $safe . self::QUOTE_CLOSE;
            }
        }
        return $safe;
    }
}
