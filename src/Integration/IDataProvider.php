<?php

namespace src\Integration;

interface IDataProvider
{
    public function get(array $request);
}