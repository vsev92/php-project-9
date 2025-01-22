<?php

namespace App;

use Carbon\Carbon;



class Site
{

    private string $url;
    private string $id;
    private string $timestamp;
    private string $lastStatusCode;


    public function __construct(string $url) {
        $this->timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $urlCol = parse_url($url);
        $this->url = $urlCol["scheme"] . "://" . $urlCol["host"];
        $this->id = '';
        $this->lastStatusCode = '';

    }

    public static function fromFetchArrayRow(array $row) {
       $site = new self($row['name']) ;
       $site->setTimestamp($row['created_at']);
       $site->setId($row['id']);
       if(array_key_exists('status_code',$row)) {
         $site->setLastStatusCode((string)$row['status_code']);
       }
       return $site;

    }

    public static function isUrlValid(string $url)
    {
        return (filter_var($url, FILTER_VALIDATE_URL) !== false) && (mb_strlen($url)<=255);

    }


	function getUrl(): string 
    {
        return $this->url;
    }

	public function getId(): string 
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

	public function getTimestamp(): string 
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

	public function getLastStatusCode(): string 
    {
        return $this->lastStatusCode ?? '';
    }

    public function setLastStatusCode(string $statusCode): self
    {
        $this->lastStatusCode = $statusCode;

        return $this;
    }







}





