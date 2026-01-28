<?php

namespace App\Trello;

/**
 * @property string $id
 * @property string $name
 * @property bool $closed
 * @property int $pos
 * @property string $idBoard
 */
class BoardList extends APIResource
{
    public static function firstOrCreate(Board|string $board, string $name, bool $dryRun = false): static
    {
        $boardId = (string) $board;

        $existing = Client::getLists($boardId)->where('name', $name)->first();

        if ($existing) {
            return $existing;
        }

        if ($dryRun) {
            // IMPORTANT: no Trello writes in dry-run.
            // Return a placeholder list so downstream code can keep computing.
            return new static([
                'id' => 'dryrun-list-' . substr(sha1($boardId . '|' . $name), 0, 10),
                'name' => $name,
                'idBoard' => $boardId,
                'closed' => false,
                'pos' => 0,
            ]);
        }

        return Client::createList($boardId, $name);
    }
}
