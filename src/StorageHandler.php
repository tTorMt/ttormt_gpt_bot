<?php

declare(strict_types=1);

namespace TtormtGptBot;

require_once __DIR__.'/../secret/secret.php';

use mysqli;

/**
 * Handles database interactions using mysqli.
 */
class StorageHandler
{
    private mysqli $database;
    private int $messagesLimit;
    private const QUERIES = [
        'getAssistantText' => 'SELECT assistant_character FROM user WHERE user_id = ?',
        'clearMessages' => 'DELETE FROM message WHERE user_id = ?',
        'changeAssistant' => 'UPDATE user SET assistant_character = ? WHERE user_id = ?',
        'storeMessages' => 'INSERT INTO message (user_id, content, role) VALUES (?, ?, "user"), (?, ?, "assistant")',
        'getMessages' => 'SELECT * FROM ( SELECT * FROM message WHERE user_id = ? ORDER BY message_id DESC LIMIT ? ) AS mes ORDER BY message_id',
    ];

    /**
     * Constructs a StorageHandler instance and establishes a database connection.
     */
    public function __construct() 
    {
        $config = parse_ini_file(__DIR__.'/../config/conf.ini');
        $this->messagesLimit = (int)$config['max_messages'] ?? 10;
        $this->database = new mysqli($config['host'], MYSQL_USER, MYSQL_PASS, $config['database']);
    }

    /**
     * Gets the assistant's characteristic for a specified user.
     *
     * @param int $userID The ID of the user.
     * @return string|false The assistant's characteristic, or false if not found.
     */
    public function getAssistant(int $userID): string|false
    {
        $stmt = $this->database->prepare(self::QUERIES['getAssistantText']);
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $assistant = $result->fetch_assoc();
        return empty($assistant) ? false : $assistant['assistant_character'];
    }

    /**
     * Deletes all messages associated with a specified user.
     *
     * @param int $userID The ID of the user.
     * @return void
     */
    public function clearMessages(int $userID)
    {
        $stmt = $this->database->prepare(self::QUERIES['clearMessages']);
        $stmt->bind_param('i', $userID);
        $stmt->execute();
    }

    /**
     * Changes the assistant's characteristic for a specified user.
     *
     * @param int $userID The ID of the user.
     * @param string $character The new assistant characteristic.
     * @return bool True if the update was successful, false otherwise.
     */
    public function changeAssistant(int $userID, string $character): bool
    {
        $stmt = $this->database->prepare(self::QUERIES['changeAssistant']);
        $stmt->bind_param('si', $character, $userID);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    /**
     * Stores a user's question and the assistant's answer in the database.
     *
     * @param int $userID The ID of the user.
     * @param string $question The user's question.
     * @param string $answer The assistant's answer.
     * @return bool True if both messages were successfully stored, false otherwise.
     */
    public function storeMessages(int $userID, string $question, string $answer): bool
    {
        $stmt = $this->database->prepare(self::QUERIES['storeMessages']);
        $stmt->bind_param('isis', $userID, $question, $userID, $answer);
        $stmt->execute();
        return $stmt->affected_rows === 2;
    }

    /**
     * Retrieves an array of messages associated with a specified user.
     *
     * @param int $userID The ID of the user.
     * @return array An associative array of messages, with each message as an associative array.
     */
    public function getMessages(int $userID): array
    {
        $stmt = $this->database->prepare(self::QUERIES['getMessages']);
        $stmt->bind_param('ii', $userID, $this->messagesLimit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
