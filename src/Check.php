<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use DiDom\Document;
use DiDom\Query;
use DiDom\Element;

class Check
{
    private string $urlId;
    private ?int $id;
    private string $statusCode;
    private ?string $h1;
    private ?string $title;
    private ?string $description;
    private string $createdAt;

    public function __construct(string $urlId)
    {
        $this->urlId = $urlId;
        $this->setCreatedAt(Carbon::now()->format('Y-m-d H:i:s'));
    }

    public static function fromFetchArray(array $row): self
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



    public function check(string $url): void
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

        $title = $document->first('title');
        $this->title = $title instanceof  Element ? $title->text() : '';

        $h1 = $document->first('h1');
        $this->h1 = $h1 instanceof  Element ? $h1->text() : '';

        $meta = $document->first('meta[name=description]');
        $this->description = (string)$meta?->getAttribute('content');
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

    public function setId(int $id): self
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
