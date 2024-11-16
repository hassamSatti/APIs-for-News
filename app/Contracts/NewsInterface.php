<?php

namespace App\Contracts;

interface NewsInterface
{
    public function fetchNews(): array;
}