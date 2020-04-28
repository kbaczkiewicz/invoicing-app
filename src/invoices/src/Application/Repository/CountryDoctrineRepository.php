<?php


namespace App\Application\Repository;


use App\Application\Entity\Country as CountryEntity;
use App\Application\Entity\AppUser;
use App\Domain\Country\Model\Country;
use App\Domain\Country\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CountryDoctrineRepository implements CountryRepository
{
    private $entityManager;
    private $doctrineRepository;
    private $userDoctrineRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->doctrineRepository = $entityManager->getRepository(CountryEntity::class);
        $this->userDoctrineRepository = $entityManager->getRepository(AppUser::class);
    }

    public function save(Country $country, ?string $id = null): void
    {
        if ($id) {
            /** @var CountryEntity $countryEntity */
            $countryEntity = $this->doctrineRepository->find($id);
            if (null === $countryEntity) {
                throw new \InvalidArgumentException('Country not found');
            }

            $countryEntity->editByModel($country);
        } else {
            $countryEntity = CountryEntity::createFromModel($country);
        }

        $owner = $this->userDoctrineRepository->find($country->getOwnerId());
        $countryEntity->setOwner($owner);
        $this->entityManager->persist($countryEntity);
        $this->entityManager->flush();
    }

    public function getAll(array $filters = []): array
    {
        return array_map(
            function (CountryEntity $country) {
                return $this->createByEntity($country);
            },
            $this->doctrineRepository->findBy($filters)
        );
    }

    public function get(string $id): ?Country
    {
        /** @var CountryEntity $countryEntity */
        $countryEntity = $this->doctrineRepository->find($id);
        if ($countryEntity) {
            return $this->createByEntity($countryEntity);
        }

        return null;
    }

    private function createByEntity(CountryEntity $countryEntity)
    {
        return Country::create(
            [
                'id' => $countryEntity->getId(),
                'name' => $countryEntity->getName(),
                'isoCode' => $countryEntity->getIsoCode(),
                'currency' => $countryEntity->getCurrency(),
                'ownerId' => $countryEntity->getOwner()->getId()
            ]
        );
    }

    public function delete(string $id): void
    {
        $this->entityManager->remove($this->get($id));
        $this->entityManager->flush();
    }
}