<?php


namespace App\Domain\Country\Repository;


use App\Domain\Country\Model\Country;

interface CountryRepository
{
    public function save(Country $country, ?string $id = null): void;
    public function getAll(array $filters = []): array;
    public function get(string $id): ?Country;
    public function delete(string $id): void;
}