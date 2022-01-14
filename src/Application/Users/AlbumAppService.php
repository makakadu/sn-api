<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\User\UserRepository;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\RequestParamsValidator;
use App\Application\ApplicationService;
use App\Domain\Model\Users\Albums\CustomAlbum\CustomAlbum;
use App\Domain\Model\Authorization\UserAlbumPhotosAuth;
use App\Domain\Model\Users\Albums\AlbumRepository;
use App\Domain\Model\Users\Albums\Album;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Users\ConnectionsList\ConnectionsListRepository;

abstract class AlbumAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    const NAME = 'name';
    const DESCRIPTION = 'description';
    
    protected PhotoAlbumRepository $albums;
    protected UserAlbumPhotosAuth $auth;
    protected ConnectionRepository $connections;
    protected ConnectionsListRepository $connectionsLists;
            
    function __construct(
        PhotoAlbumRepository $albums,
        UserRepository $users,
        UserAlbumPhotosAuth $auth,
        ConnectionRepository $connections,
        ConnectionsListRepository $connectionsLists
    ) {
        $this->albums = $albums;
        $this->users = $users;
        $this->auth= $auth;
        $this->connections = $connections;
        $this->connectionsLists = $connectionsLists;
    }
    
    protected function findAlbum(string $albumId): ?Album {
        return $this->albums->getById($albumId);
    }
    
    protected function findAlbumOrFail(string $albumId, bool $asTarget): Album {
        $album = $this->findAlbum($albumId);
        
        if(!$album || ($album && $album->user()->isDeleted())) {
            if($asTarget) {
                throw new NotExistException("Album $albumId not found");
            } else {
                throw new UnprocessableRequestException(222, "Album $albumId not found");
            }
        }
        
        return $album;
    }
    
    function findConnections(array $connectionsIds): array {
        return $this->connections->getByIds($connectionsIds);
    }
    
    function findConnectionsLists(array $listsIds): array {
        return $this->connectionsLists->getByIds($listsIds);
    }
    
        
    protected function validateName($value): void {
        $this->validator->string($value, "Name of album should be a string");
        $this->validator->maxLength($value, 500, "Name of album should have no more than 50 symbols");
    }

    protected function validateDescription($value): void {
        $this->validator->string($value, "Description of album should be a string");
        $this->validator->maxLength($value, 500, "Description of album should have no more than 300 symbols");
    }
}