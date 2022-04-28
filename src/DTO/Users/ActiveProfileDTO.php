<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\Users\ConnectionDTO;
use App\DTO\Users\SubscriptionDTO;
use App\DTO\Users\PictureDTO;

class ActiveProfileDTO extends ProfileDTO {
    
    public ?PictureDTO $picture;
    public ?CoverDTO $cover;
    public string $gender;
    public ?array $birthday;
    public string $country;
    public string $city;
    public string $status;
    public ?ConnectionDTO $connection;
    public ?SubscriptionDTO $subscription;
    public bool $banned;
    public bool $acceptMessages;
    public int $postsCount;
    public array $pictures;
    public array $covers;
    public string $firstName;
    public string $lastName;
    public string $username;
    
    public function __construct(
        string $id, ?PictureDTO $picture, ?CoverDTO $cover, string $firstName, string $lastName, string $username,
        string $gender, ?array $birthday,
        string $country, string $city,
        ?ConnectionDTO $connection,
        ?SubscriptionDTO $subscription,
        bool $banned, 
        string $status, bool $acceptMessages, int $postsCount,
        array $pictures,
        array $covers
    ) {
        parent::__construct($id);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
        $this->picture = $picture;
        $this->cover = $cover;
        $this->gender = $gender;
        $this->birthday = $birthday;
        $this->country = $country;
        $this->city = $city;
        $this->connection = $connection;
        $this->subscription = $subscription;
        $this->banned = $banned;
        $this->status = $status;
        $this->acceptMessages = $acceptMessages;
        $this->postsCount = $postsCount;
        $this->pictures = $pictures;
        $this->covers = $covers;
    }


}
