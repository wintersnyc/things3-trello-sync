<?php

namespace App\Trello;

/**
 * @property string $id
 * @property string $name
 * @property string $desc
 * @property string $idOrganization
 */
class Board extends APIResource
{
    public static function firstOrCreate(Workspace|string $workspace, string $name, bool $dryRun = false): static
    {
        $workspace = (string) $workspace;

        $existing = Client::getBoards($workspace)->where('name', $name)->first();

        if ($existing) {
            // Register even existing boards so pull side can rely on the registry
            ManagedBoards::register($existing->id, [
                'name' => $existing->name ?? $name,
                'workspace' => $workspace,
                'source' => 'things',
            ], dryRun: $dryRun);

            return $existing;
        }

        if ($dryRun) {
            // IMPORTANT: No Trello Writes in Dry-Run.
            // Return a placeholder board so downstream code can keep computing.
            return new static([
                'id' => 'dryrun-board' . substr(sha1($workspace . '|' . $name), 0, 10),
                'name' => $name,
                'idOrganization' => $workspace,
            ]);
        }

        $created = Client::createBoard($workspace, $name);

        ManagedBoards::register($created->id, [
            'name' => $existing->name ?? $name,
            'workspace' => $workspace,
            'source' => 'things',
            'created at' => now()->toIso8601String(),
        ], dryRun: false);

        return $created;
    }
}
