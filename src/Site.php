<?php

namespace App;

use Carbon\Carbon;



class Site{

    private string $url;
    private string $id;
    private string $timestamp;

    public function __construct(string $url) {
        $this->timestamp = Carbon::now()->format('Y-m-d');
        $this->url = $url;

    }

    public function getId ()
    {
        return $this->id ?? null;
    }

    public function getUrl()
    {
        return $this->url ?? null;
    }

    public function getTimestamp()
    {
        return $this->timestamp ?? null;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }


    public static function isUrlValid(string $url)
    {
        return true;

    }


}





