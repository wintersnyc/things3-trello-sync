<?php

namespace App\Models\Concerns;

use App\Models\Task;
use App\Trello\BoardList;
use App\Trello\Card;
use App\Trello\Client;
use Exception;
use Illuminate\Support\Carbon;

/**
 * @mixin Task
 */
trait WithTrelloCards
{
    public function toCard(): ?Card
    {
        return $this->findCard() ?? Client::createCard($this);
    }

    public function findCard(): ?Card
    {
        return Card::forTask($this);
    }

    public function createOnTrello(): Card
    {
        return Client::createCard($this);
    }

    public function updateOrCreateOnTrello(
        Card $card = null,
        bool $dryRun = false,
        array &$dryRunMessages = null
    ): ?Card {
        $dryRunMessages ??= [];

        $card ??= $this->findCard();

        if ($card) {
            return $this->updateOnTrello($card, $dryRun, $dryRunMessages);
        }

        // No Existing Card
        if (!($this->statusConfig()['create'] ?? false)) {
            // Respect Existing "Shouldn't Be Created" Logic
            if ($dryRun) {
                $dryRunMessages[] = "Would: Skip create (status rules) for task {$this->uuid} ({$this->title})";
            }
            return null;
        }

        if ($dryRun) {
            $dryRunMessages[] =
                "WOULD CREATE Trello Card for Task {$this->uuid} ({$this->title}) " .
                "on board '{$this->targetBoard('name')}' in list '{$this->targetList('name')}'";
            return null; // treat as "not created"
        }

        return Client::createCard($this);
    }

    public function updateOnTrello(
        Card $card = null,
        bool $dryRun = false,
        array &$dryRunMessages = null
    ): Card {
        $dryRunMessages ??= [];

        $card ??= $this->findCard();

        if (!$card) {
            throw new Exception(
                "Task {$this->uuid} has no card. Create it first via createOnTrello() or use updateOrCreateOnTrello()."
            );
        }

        // If the task was updated more recently on Trello than in Things, we'll prioritize its (status) changes
        if (Carbon::parse($card->dateLastActivity)->isAfter($this->userModificationDate)) {
            $newStatus = collect($this->boardStatusConfig())
                ->where('when.list', Client::listName($card->idList, $card->idBoard))
                ->keys()
                ->first();

            if ($newStatus !== null && $this->status !== $newStatus) {
                if ($dryRun) {
                    $dryRunMessages[] =
                        "WOULD: Update Things status for task {$this->uuid} ({this->title}) " .
                        "from {$this->status} to {$newStatus}";
                } else {
                    $this->update(['status' => $newStatus]);
            }
        }

        if ($dryRun) {
            $dryRunMessages[] = "WOULD UPDATE Trello card {$card->id} for task {$this->uuid} ({$this->title})";
            return $card; // return existing card unchanged
        }

        return Client::updateCard($card, $this);
    }
}
    public function deleteOnTrello(Card $card = null, bool $dryRun = false, array &$dryRunMessages = null): bool
    {
        $dryRunMessages ??= [];

        $card ??= $this->findCard();
        if (!$card) {
            return false;
        }

        if ($dryRun) {
            $dryRunMessages[] = "WOULD DELETE Trello card {$card->id} for task {$this->uuid} ({$this->title})";
            return false;
        }

        return Client::deleteCard($card);

    }
}
