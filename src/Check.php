<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use DiDom\Document;
use DiDom\Query;
use DiDom\Element;
use DOMElement;
use illuminate\support;

class Check
{
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



    public static function fromFetchArray(array $row)
    {
        $check = new self($row['url_id']);
        $check->setId($row['id'])
            ->setStatusCode($row['status_code'])
            ->setH1($row['h1'])
            ->setTitle($row['title'])
            ->setDescription($row['description'])
            ->setCreatedAt($row['created_at']);
        return $check;
    }


    public function check(string $url)
    {
        $client = new Client([
            'base_uri' => $url,
            'timeout'  => 2.0,
        ]);

        $response = $client->get($url);
        $code = $response->getStatusCode();
        $this->setStatusCode((string)$code);
        $body = $response->getBody();
        $stringBody = (string) $body;
        $document = new Document($stringBody);

        if ($document->has('title')) {
            $titlesCollection = $document->find('title');
            if (count($titlesCollection) > 0) {
                $title = ($titlesCollection[0]);
                $this->title = $title instanceof  \DiDom\Element ? $title->text() : '';
            }
        }

        if ($document->has('h1')) {
            $h1Collection =  $document->find('h1');
            if (count($h1Collection) > 0) {
                $h1 = $h1Collection[0];
                $this->h1 = $h1 instanceof \DiDom\Element ? $h1->text() : '';
                //$this->h1 = $h1->text();
            }
        }


        $metaCollection = $document->find("//meta[@name='description']", Query::TYPE_XPATH);


        if (count($metaCollection) > 0) {
            $meta = $metaCollection[0];
            if ($meta->hasAttribute('content')) {
                $this->description = (string)$meta->getAttribute('content');
            }
        }
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
