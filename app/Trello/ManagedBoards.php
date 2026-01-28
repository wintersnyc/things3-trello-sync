<?php

namespace App\Trello;

class ManagedBoards
{
    private const PATH = 'things3-trello/managed-boards.json';

    public static function all(): array
    {
        $path = storage_path('app/' . self::PATH);

        if (! file_exists($path)) {
            return ['boards' => []];
        }

        $json = file_get_contents($path);
        $data = json_decode($json ?: '[]', true);

        if (! is_array($data)) {
          return ['boards' => []];
        }

        $data['boards'] ??= [];
        return $data;
    }

    public static function register(string $boardId, array $meta, bool $dryRun): void
    {
        if ($dryRun) {
            return;
        }

        $data = self::all();
        $data['boards'][$boardId] = array_merge(
            $data['boards'][$boardId] ?? [],
            $meta,
            ['updated_at' => now()->toIso8601String()]
        );

        $dir = dirname(storage_path('app/' . self::PATH));
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents(
            storage_path('app/' . self::PATH),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
