<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Videos\Video;
use App\Domain\Model\Common\PhotoService;

class VideoService {
    
    private PhotoService $photoService;
    private string $youtubeApiKey;
    
    function __construct(PhotoService $photoService, string $youtubeApiKey) {
        $this->photoService = $photoService;
        $this->youtubeApiKey = $youtubeApiKey;
    }
    
//        
//        if(isset($parsedUrl['query'])) {
//            $queryString = $parsedUrl['query'];
//            $firstQueryParam = \explode("&", $queryString)[0];
//            $id = \explode("=", $firstQueryParam)[1];
//        } else {
//            throw new \App\Application\Exceptions\MalformedRequestException("Incorrect address");
//        }
//
//        $client = new \Google_Client();
//        $client->setDeveloperKey($api_key);
//        $youtube = new \Google_Service_YouTube($client);
//        /** @var \Google_Service_YouTube_VideoListResponse $videosResponse */
//        $response = $youtube->videos->listVideos('snippet, id', array(
//            'id' => [$id],
//        ));
//        if(isset($response[0])) {
//            /** @var \Google_Service_YouTube_Video $video */
//            $video = $response[0];
//            /** @var \Google_Service_YouTube_VideoSnippet $snippet */
//            $snippet = $video->getSnippet();
//            $title = $snippet->getTitle();
//            $description = $snippet->getDescription();
//            /** @var \Google_Service_YouTube_ThumbnailDetails $preview */
//            $preview = $snippet->getThumbnails();
//            $imageUrl = $preview->getStandard()->getUrl();
//            if($preview->getMaxres()) {
//                $imageUrl = $preview->getMaxres()->getUrl();
//            }
//            $versions = $this->photoService->createVideoPreviews($id, $imageUrl);
//        }
    
    /** @return array<mixed> */
    function prepareVideoData(string $url): array {
        $data = [];
        
        $parsedUrl = parse_url($url);
        
        if(isset($parsedUrl['query'])) {
            $queryString = $parsedUrl['query'];
            $firstQueryParam = \explode("&", $queryString)[0];
            $id = \explode("=", $firstQueryParam)[1];
        } else {
            throw new \App\Application\Exceptions\MalformedRequestException("Incorrect url");
        }

        $client = new \Google_Client();
        $client->setDeveloperKey($this->youtubeApiKey);
        $youtube = new \Google_Service_YouTube($client);
        /** @var \Google_Service_YouTube_VideoListResponse $response */
        $response = $youtube->videos->listVideos('snippet', array('id' => [$id]));
        
        if(isset($response[0])) {
            /** @var \Google_Service_YouTube_Video $video */
            $video = $response[0];
            /** @var \Google_Service_YouTube_VideoSnippet $snippet */
            $snippet = $video->getSnippet();
            $data ['link'] = "https://www.youtube.com/watch?v=$id";
            $data['title'] = $snippet->getTitle();
            $data['description'] = $snippet->getDescription();
            $data['hosting'] = 'youtube';
            
            /** @var \Google_Service_YouTube_ThumbnailDetails $preview */
            $preview = $snippet->getThumbnails();
            $imageUrl = $preview->getStandard()->getUrl();
            if($preview->getMaxres()) {
                $imageUrl = $preview->getMaxres()->getUrl();
            }
            $data['previews'] = $this->photoService->createVideoPreviews($id, $imageUrl);
        } else {
            throw new UnprocessableRequestException(123, "Video by $url not found in youtube");
        }
        
        return $data;
    }

}