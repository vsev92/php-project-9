<?php

namespace App;

use Carbon\Carbon;



class Site{

    private string $url;
    private string $id;
    private string $timestamp;

    public function __construct(string $url) {
        $this->timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $this->url = $url;

    }

    public static function fromFetchArrayRow(array $row) {
       $site = new self($row['name']) ;
       $site->setTimestamp($row['created_at']);
       $site->setId($row['id']);
       return $site;

    }


    public function getId ()
    {
        return $this->id ?? null;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getUrl()
    {
        return $this->url ?? null;
    }


    public function getTimestamp()
    {
        return $this->timestamp ?? null;
    }

    private function setTimestamp(string $timestamp)
    {
        $this->timestamp = $timestamp;
    }


    public static function isUrlValid(string $url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;

    }




}





