<?php


namespace App\Domain\Country\Service;


use App\Domain\Country\Model\Country;
use App\Domain\Country\Repository\CountryRepository;
use Ramsey\Uuid\Uuid;

class CountryService
{
    private $repository;

    public function __construct(CountryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): array
    {
        $data['id'] = Uuid::uuid4();
        $this->repository->save(Country::create($data));

        return [$data['id']];
    }

    public function edit(string $id, array $data): void
    {
        if (null === $this->repository->get($id)) {
            throw new \InvalidArgumentException('Country not found');
        }

        $this->repository->save(Country::create($data), $id);
    }

    public function getAll(array $filters = []): array
    {
        return array_map(
            function (Country $country) {
                return $country->jsonSerialize();
            },
            $this->repository->getAll($filters)
        );
    }

    public function get(string $id): array
    {
        return $this->repository->get($id)->jsonSerialize();
    }

    public function delete(string $id)
    {

    }
}