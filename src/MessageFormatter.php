<?php

declare(strict_types=1);

namespace TtormtGptBot;

/**
 * Converts OpenAI API messages to MarkdownV2 format.
 */
class MessageFormatter
{
    private string $message;

    private const CHARACTER_LIMIT = 4000;

    // Special characters that need to be escaped
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

    // Corresponding escape sequences for the special characters
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

    // Markup patterns for different formatting
    private const MARKUPS = [
        'code' => '```',
        'header' => '###',
        'bold' => '**',
        'url' => '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/'
    ];

    /**
     * Constructs a MessageFormatter instance with the given message.
     *
     * @param string $message The message to be formatted.
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Formats the OpenAI API output to MarkdownV2.
     *
     * @return array The formatted array of messages in MarkdownV2.
     */
    public function format(): array
    {
        $result = '';
        $isInCode = false;
        $codeStartLine = '';
        $messageArray = [];

        foreach (explode(PHP_EOL, $this->message) as $line) {
            if (str_contains($line, self::MARKUPS['code'])) {
                $isInCode = !$isInCode;
                if ($isInCode) {
                    $codeStartLine = $line;
                } else {
                    $codeStartLine = '';
                }

                $result .= $line . PHP_EOL;
                continue;
            }

            if (str_contains($line, self::MARKUPS['header'])) {
                // To correct lines like "### 3. **Choose a Game Engine**"
                $line = str_replace('**', '', $line);
                $result .= '*' . $this->escape(explode(self::MARKUPS['header'] . ' ', $line)[1]) . '*' . PHP_EOL;
                if (strlen($result) >= self::CHARACTER_LIMIT) {
                    if ($isInCode) {
                        $result .= '```';
                        $messageArray[] = $result;
                        $result = $codeStartLine.PHP_EOL;
                    } else {
                        $messageArray[] = $result;
                        $result = '';
                    }
                }
                continue;
            }

            if (str_contains($line, self::MARKUPS['bold'])) {
                $line = $this->escape($line);
                $result .= str_replace('\*\*', '*', $line) . PHP_EOL;
                if (strlen($result) >= self::CHARACTER_LIMIT) {
                    if ($isInCode) {
                        $result .= '```';
                        $messageArray[] = $result;
                        $result = $codeStartLine.PHP_EOL;
                    } else {
                        $messageArray[] = $result;
                        $result = '';
                    }
                }
                continue;
            }

            if (preg_match(self::MARKUPS['url'], $line, $matches)) {
                $lineParts = explode($matches[0], $line);
                $result .= $this->escape($lineParts[0]) . '[' .
                    $this->escape($matches[1]) . ']' . '(' .
                    $this->escape($matches[2]) . ')' .
                    $this->escape($lineParts[1]);
                if (strlen($result) >= self::CHARACTER_LIMIT) {
                    if ($isInCode) {
                        $result .= '```';
                        $messageArray[] = $result;
                        $result = $codeStartLine.PHP_EOL;
                    } else {
                        $messageArray[] = $result;
                        $result = '';
                    }
                }
                continue;
            }

            $result .= $this->escape($line) . PHP_EOL;
            if (strlen($result) >= self::CHARACTER_LIMIT) {
                if ($isInCode) {
                    $result .= '```';
                    $messageArray[] = $result;
                    $result = $codeStartLine.PHP_EOL;
                } else {
                    $messageArray[] = $result;
                    $result = '';
                }
            }
        }

        $messageArray[] = $result;
        return $messageArray;
    }

    /**
     * Escapes special characters with their corresponding escape sequences.
     *
     * @param string $part The part of the message to escape.
     * @return string The escaped part of the message.
     */
    private function escape(string $part): string
    {
        return str_replace(self::SPECIAL_CHARS, self::REPLACEMENT, $part);
    }
}
