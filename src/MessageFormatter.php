<?php

declare(strict_types=1);

namespace TtormtGptBot;

/**
 * Converts openAI API messages to MarkdownV2
 */
class MessageFormatter
{
    private string $message;
    private const SPECIAL_CHARS = [
        '_',
        '*',
        '[',
        ']',
        '(',
        ')',
        '~',
        '>',
        '#',
        '+',
        '-',
        '=',
        '|',
        '{',
        '}',
        '.',
        '!'
    ];
    private const REPLACEMENT = [
        '\_',
        '\*',
        '\[',
        '\]',
        '\(',
        '\)',
        '\~',
        '\>',
        '\#',
        '\+',
        '\-',
        '\=',
        '\|',
        '\{',
        '\}',
        '\.',
        '\!'
    ];

    private const MARKUPS = [
        'code' => '```',
        'header' => '###',
        'bold' => '**',
        'url' => '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/'
    ];

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Formats the openAI API output.
     */
    public function format(): string
    {
        $result = '';

        foreach (explode(PHP_EOL, $this->message) as $line) {
            if (str_contains($line, self::MARKUPS['code'])) {
                $result .= $line . PHP_EOL;
                continue;
            }

            if (str_contains($line, self::MARKUPS['header'])) {
                // To correct lines like "### 3. **Choose a Game Engine**"
                $line = str_replace('**', '', $line); 
                $result .= '*' . $this->escape(explode(self::MARKUPS['header'] . ' ', $line)[1]) . '*' . PHP_EOL;
                continue;
            }

            if (str_contains($line, self::MARKUPS['bold'])) {
                $line = $this->escape($line);
                $result .= str_replace('\*\*', '*', $line) . PHP_EOL;
                continue;
            }

            if (preg_match(self::MARKUPS['url'], $line, $matches))
            {
                $lineParts = explode($matches[0], $line);
                $result .= $this->escape($lineParts[0]) . '[' . 
                    $this->escape($matches[1]) . ']' . '(' .
                    $this->escape($matches[2]) . ')' .
                    $this->escape($lineParts[1]);
                continue;
            }

            $result .= $this->escape($line) . PHP_EOL;
        }
        return $result;
    }

    /**
     * Escapes special chars with '/'.
     */
    private function escape(string $part): string
    {
        return str_replace(self::SPECIAL_CHARS, self::REPLACEMENT, $part);
    }
}
