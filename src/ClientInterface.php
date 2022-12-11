<?php

namespace Bermuda\Authentication;

interface ClientInterface
{
    public function getId(): string ;
    public function getSecret():? string ;
}
