<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use DiDom\Document;
use DiDom\Query;
use illuminate\support;


class Check{

    private string $urlId;
    private string $id;
    private string $statusCode;
    private string $h1;
    private string $title;
    private string $description;
    private string $createdAt;


    public function __construct(string $urlId)
    {
       $this->urlId = $urlId;
       $this->setCreatedAt(Carbon::now()->format('Y-m-d H:i:s'));
   }



    public static function fromFetchArray($arr)
    {
        $check = new self($arr['url_id']);
               $check->setId($arr['id'])
                        ->setStatusCode($arr['status_code'])
                         ->setH1($arr['h1'])
                          ->setTitle($arr['title'])
                            ->setDescription($arr['description'])
                              ->setCreatedAt($arr['created_at']);
        return $check;                      
     
    }


    public function check($url)
    {    
      // try {
      $client = new Client([   
           'base_uri' => $url,
           'timeout'  => 2.0,
        ]);


      

            $response = $client->get($url);
            $code = $response->getStatusCode(); // 200
            //$reason = $response->getReasonPhrase(); // OK
            $this->setStatusCode($code);

            $body = $response->getBody();
            $stringBody = (string) $body;
            $document = new Document($stringBody);

            if ($document->has('title')) {
                $elements = $document->find('title');
                // code
            }
            dump($elements[0]->text());
   
            if ($document->has('h1')) {
                $h1 = $document->find('h1')[0]->text();
                // code
            }
            dump($h1);

        
            if (count($metas = $document->find("//meta[contains(@attribute, 'content')]", Query::TYPE_XPATH)) > 0) {
                dump($metas[0]->xpath('//content'));
            }
      
            //$content = $document->find('meta')[0]->xpath('//content')[0]->text();

            //dump($content);
   
            

        
  
    

     
        

    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }


    public function setUrlId(string $urlId): self
    {
        $this->urlId = $urlId;

        return $this;
    }


    public function getId(): string
    {
        return $this->id ?? '';
    }


    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }


    public function getStatusCode(): string
    {
        return $this->statusCode ?? '';
    }


    public function setStatusCode(string $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }


    public function getH1(): string
    {
        return $this->h1 ?? '';
    }


    public function setH1(string $h1): self
    {
        $this->h1 = $h1;

        return $this;
    }


    public function getTitle(): string
    {
        return $this->title ?? '';
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getDescription(): string
    {
        return $this->description ?? '';
    }


    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}





