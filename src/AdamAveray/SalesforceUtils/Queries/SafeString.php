<?php
namespace AdamAveray\SalesforceUtils\Queries;

class SafeString {
    public const QUOTE_OPEN  = '\'';
    public const QUOTE_CLOSE = '\'';
    public const VALUE_NULL  = 'null';
    public const VALUE_TRUE  = 'TRUE';
    public const VALUE_FALSE = 'FALSE';
    public const DATETIME_FORMAT = 'c';

    /** @var array $charsAll A character map for characters to escape in all query params */
    private static $charsAll  = [
        "\n"    => '\\n',
        "\r"    => '\\r',
        "\t"    => '\\t',
        "\u{7}" => '\\b',
        "\f"    => '\\f',
        '"'     => '\\"',
        '\''    => '\\\'',
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
    public function __construct(string $string) {
        $this->string = $string;
    }

    /**
     * @return string The safe string value
     */
    public function __toString(): string {
        return (string)$this->string;
    }

    /**
     * @param mixed $value The value to escape
     * @param bool $isLike Whether the value is for a LIKE comparison
     * @param bool $quote Whether to quote the value
     * @return SafeString
     */
    public static function escape($value, bool $isLike = false, bool $quote = false): SafeString {
        if ($value === null) {
            $safe = self::VALUE_NULL;
        } else if (is_bool($value)) {
            $safe = $value ? self::VALUE_TRUE : self::VALUE_FALSE;
        } else if (is_int($value) || is_float($value)) {
            $safe = (string)$value;
        } else if ($value instanceof \DateTimeInterface) {
            $safe = $value->format(self::DATETIME_FORMAT);
        } else {
            $value = (string)$value;

            // Escape special chars
            $chars = self::$charsAll;
            if ($isLike) {
                $chars += self::$charsLike;
            }
            $escaped = str_replace(array_keys($chars), array_values($chars), $value);

            $safe = $escaped;
            if ($quote) {
                $safe = self::QUOTE_OPEN.$safe.self::QUOTE_CLOSE;
            }
        }

        return new SafeString($safe);
    }
}
