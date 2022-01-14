<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetCollections;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Users\SavesCollection\SavesCollection;
use App\DTO\Users\SavesCollectionDTO;
use App\DTO\CreatorDTO;

class GetCollectionsResponse implements \App\Application\BaseResponse {
    /** @var array<int, SavesCollectionDTO> $collections */
    public array $collections = [];
    
    /** @param array<int, SavesCollection> $collections */
    public function __construct(array $collections) {
        foreach($collections as $collection) {
            $creator = $collection->creator();
            $creatorPicture = $creator->currentPicture();
            
            $this->collections[] = new \App\DTO\Users\SavesCollectionDTO(
                $collection->id(),
                $collection->name(),
                $collection->description(),
                new CreatorDTO(
                    $creator->id(),
                    $creatorPicture ? $creatorPicture->small() : null,
                    $creator->firstName(), 
                    $creator->lastName()
                ),
                $collection->createdAt()->getTimestamp() * 1000,
                $collection->itemsCount()
            );
        }
    }
}
