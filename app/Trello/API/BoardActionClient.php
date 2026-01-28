<?php

namespace App\Trello\API;

trait BoardActionClient
{
    /**
     * Fetch recent actions for a board.
     * Read-only. Used for Trello → Things detection.
     */
    public function getBoardActions(string $boardId, int $limit = 25): array
    {
        return $this->get("boards/{$boardId}/actions", [
            'limit'  => $limit,
            'filter' => 'createCard,updateCard,commentCard',
        ]);
    }
}
