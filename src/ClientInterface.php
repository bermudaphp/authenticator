<?php

namespace App\Auth;

interface ClientInterface
{
    public function getId(): string ;
    public function getSecret():? string ;
}