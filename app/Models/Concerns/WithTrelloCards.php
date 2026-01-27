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

    public function updateOrCreateOnTrello(Card $card = null, bool $dryRun = false): ?Card
    {
        $card ??= $this->findCard();
        
        if ($card) {
            return $this->updateOnTrello($card, $dryRun);
        }

        // No Existing Card
        if (! $this->statusConfig()['create']) {
            // Respect Existing "Shouldn't Be Created" Logic
            if ($dryRun) {
                echo "Would: Skip create (status rules) for task {$this->uuid} ({$this->title})\n";
            }
            return null;
        }

        if ($dryRun) {
            echo "WOULD: Create Trello Card for Task {$this->uuid} ({$this->title}) on board '{$this->targetBoard('name')}' in list '{$this->targetList('name')}'\n";
            return null; // return null so the caller can treat it as "not created"
        }

        return Client::createCard($this);
    }

    public function updateOnTrello(Card $card = null, bool $dryRun = false): Card
    {
        $card ??= $this->findCard();

        if (! $card) {
            throw new Exception("Task {$this->uuid} has no card. Create it first via createOnTrello() or use updateOrCreateOnTrello().");
        }

        // If the task was updated more recently on Trello than in Things, we'll prioritize its (status) changes
        if (Carbon::parse($card->dateLastActivity)->isAfter($this->userModificationDate)) {
            $newStatus = collect($this->boardStatusConfig())->where('when.list', Client::listName($card->idList, $card->idBoard))->keys()->first();

            if ($newStatus !== null && $this->status !== $newStatus) {
                if ($dryRun) {
                    echo "WOULD: Update Things status for task {$this->uuid} ({this->title}) from {$this->status} to {$newStatus}\n";
                } else {
                    $this->update(['status' => $newStatus]);
            }
        }

        if ($dryRun) {
            echo "WOULD: Update Trello card {$card->id} for task {$this->uuid} ({$this->title})\n";
            return $card; // return existing card unchanged        
        }
        $card = Client::updateCard($card, $this);

        return $card;
    }
}
    public function deleteOnTrello(Card $card = null): bool
    {
        if ($card ??= $this->findCard()) {
            return Client::deleteCard($card);
        }

        return false;
    }
}
