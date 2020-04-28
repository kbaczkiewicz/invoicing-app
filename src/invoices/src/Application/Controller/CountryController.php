<?php


namespace App\Application\Controller;


use App\Domain\Country\Service\CountryService;
use Symfony\Component\Routing\Annotation\Route;

class CountryController
{
    private $service;

    public function __construct(CountryService $service)
    {
        $this->service = $service;
    }

    /**
     * @Route()
     */
    public function getAll()
    {

    }
}