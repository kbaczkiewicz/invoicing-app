<?php


namespace App\Application\Controller;


use App\Application\Entity\AppUser;
use App\Domain\Country\Service\CountryService;
use App\Domain\Invoice\DTO\Invoice;
use App\Domain\Invoice\Service\InvoiceService;
use App\Domain\Invoice\Value\Status\Created;
use App\Domain\Invoice\Value\Status\Draft;
use App\Domain\Invoice\Value\Status\Overdue;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use mikehaertl\wkhtmlto\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AppController extends AbstractController
{
    private $invoiceService;
    private $countryService;
    private $authenticationUtils;
    private $passwordEncoder;
    private $entityManager;
    private $session;
    private $appPath;

    public function __construct(
        InvoiceService $invoiceService,
        CountryService $countryService,
        AuthenticationUtils $authenticationUtils,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        KernelInterface $kernel
    ) {
        $this->invoiceService = $invoiceService;
        $this->countryService = $countryService;
        $this->authenticationUtils = $authenticationUtils;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->appPath = $kernel->getProjectDir();
    }

    /**
     * @Route("/", name="main")
     *
     */
    public function list()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $invoices = $this->invoiceService->getAll($this->getUser()->getId());

        return $this->render(
            'list.html.twig',
            ['invoices' => $invoices, 'statuses' => [new Created(), new Draft(), new Overdue()]]
        );
    }

    /**
     * @Route("/create")
     */
    public function create()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $invoiceId = $this->invoiceService->create($this->getUser()->getId())['invoiceId'];
        $countries = $this->countryService->getAll(['owner' => $this->getUser()]);

        return $this->render(
            'create.html.twig',
            ['invoiceId' => $invoiceId, 'invoice' => [], 'countries' => $countries, 'errors' => []]
        );
    }

    /**
     * @Route("/save/{invoiceId}")
     */
    public function save(Request $request, string $invoiceId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $invoiceData = $request->request->get('invoice');
        $invoiceData['products'] = $this->filterProducts($invoiceData['products']);
        $invoiceData = $this->supplyCountries($invoiceData);
        $response = $this->invoiceService->issue(
            $invoiceId,
            Invoice::create(
                [
                    'number' => $invoiceData['number'],
                    'issuer' => $invoiceData['issuer'],
                    'receiver' => $invoiceData['receiver'],
                    'products' => $invoiceData['products'],
                    'paymentType' => $invoiceData['paymentType'],
                    'paymentDate' => new \DateTime(),
                    'dateIssued' => new \DateTime('now - 1 day'),
                ]
            )
        );

        if (isset($response['messages'])) {
            return new JsonResponse($response);
        }

        return new RedirectResponse('/');
    }

    /**
     * @Route("/edit/{invoiceId}")
     */
    public function edit(Request $request, string $invoiceId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $invoice = $this->invoiceService->get($invoiceId, $this->getUser()->getId());
        $countries = $this->countryService->getAll(['owner' => $this->getUser()]);

        return $this->render(
            'create.html.twig',
            ['invoiceId' => $invoiceId, 'invoice' => $invoice, 'countries' => $countries, 'errors' => []]
        );
    }

    /**
     * @Route("/saveDraft/{invoiceId}", methods={"POST"})
     */
    public function saveDraft(Request $request, string $invoiceId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $invoiceData = json_decode($request->getContent(), true)['invoice'];
        $invoiceData = $this->supplyCountries($invoiceData);
        if (isset($invoiceData['products'])) {
            $invoiceData['products'] = $this->filterProducts($invoiceData['products']);
        }

        $response = $this->invoiceService->saveDraft(
            $invoiceId,
            Invoice::create(
                [
                    'number' => $invoiceData['number'],
                    'issuer' => $invoiceData['issuer'],
                    'receiver' => $invoiceData['receiver'],
                    'products' => isset($invoiceData['products']) ? $invoiceData['products'] : [],
                    'paymentType' => $invoiceData['paymentType'],
                    'paymentDate' => $invoiceData['paymentDate'],
                    'dateIssued' => $invoiceData['dateIssued'],
                ]
            )
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/show/{invoiceId}")
     */
    public function show(string $invoiceId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $invoice = $this->invoiceService->get($invoiceId, $this->getUser()->getId());

        return $this->render('show.html.twig', ['invoice' => $invoice]);
    }

    /**
     * @Route("/countries")
     */
    public function getCountries()
    {
        return $this->render('country/list.html.twig', ['countries' => $this->countryService->getAll()]);
    }

    /**
     * @Route("/country/create")
     */
    public function createCountry()
    {
        return $this->render('country/create.html.twig');
    }

    /**
     * @Route("/country/save", methods={"POST"})
     */
    public function saveCountry(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->countryService->create(
            $request->request->get('country') + ['ownerId' => $this->getUser()->getId()]
        );

        return new RedirectResponse('/countries');
    }

    /**
     * @Route("/login", name="login", methods={"GET", "POST"})
     */
    public function login()
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

    /**
     * @Route("/register", name="register", methods={"GET", "POST"})
     */
    public function register(Request $request)
    {
        try {
            $errors = [];
            if ('POST' === $request->getMethod()) {
                $email = $request->request->get('email');
                $plainPassword = $request->request->get('password');
                if (0 === preg_match('/^[0-9a-zA-Z]{1,}@[0-9a-zA-Z]{1,}\.[a-z]{2,3}$/', $email)) {
                    $errors['email'] = 'Email is invalid';
                }
                if (strlen($plainPassword) < 8) {
                    $errors['password'] = 'Password should be at least 8 characters long';
                }

                if (empty($errors)) {
                    $user = new AppUser();
                    $user->setEmail($request->request->get('email'));
                    $user->setPassword(
                        $this->passwordEncoder->encodePassword($user, $request->request->get('password'))
                    );

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return new RedirectResponse('/login');
                }

            }
        } catch (UniqueConstraintViolationException $e) {
            $errors['email'] = 'Email is not unique';
        }

        return $this->render('security/register.html.twig', ['errors' => $errors]);
    }

    /**
     * @Route("/generatePdf/{invoiceId}")
     */
    public function generatePdf(string $invoiceId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $invoice = $this->invoiceService->get($invoiceId, $this->getUser()->getId());

        $template = $this->renderView('pdf.html.twig', ['invoice' => $invoice]);
        $fileName = $this->saveFile($template);
        $response = new BinaryFileResponse('/tmp/'.$fileName.'.pdf');
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,

        );

        return $response;

    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {

    }

    /**
     * @Route("/countries/get", name="get_countries_json", methods={"GET"})
     */
    public function getCountriesAsJson()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return new JsonResponse($this->countryService->getAll());
    }

    private function filterProducts($products)
    {
        $filteredProducts = [];
        foreach ($products as $productData) {
            if (empty($productData['vatRate'])) {
                $productData['vatRate'] = 0.0;
                $productData['priceNett']['amount'] ? $productData['priceNett']['amount'] *= 100 : null;
            }

            if (empty($productData['quantity'])) {
                $productData['quantity'] = 0;
            }

            $productData['priceNett']['amount'] = (int)$productData['priceNett']['amount'] * 100;
            $filteredProducts[] = $productData;
        }

        return $filteredProducts;
    }

    private function supplyCountries($invoiceData)
    {
        $issuerCountry = $this->countryService->get($invoiceData['issuer']['billingAddress']['country']);
        $receiverCountry = $this->countryService->get($invoiceData['receiver']['billingAddress']['country']);
        $invoiceData['issuer']['billingAddress']['country'] = [
            'name' => $issuerCountry['name'],
            'isoCode' => $issuerCountry['isoCode'],
        ];

        $invoiceData['receiver']['billingAddress']['country'] = [
            'name' => $receiverCountry['name'],
            'isoCode' => $receiverCountry['isoCode'],
        ];

        return $invoiceData;
    }

    /**
     * @fixme UGLY workaround
     */
    private function saveFile(string $template)
    {
        $fileName = uniqid('file___');
        file_put_contents('/tmp/' . $fileName . '.html', $template);
        exec(
            sprintf(
                "xvfb-run -- wkhtmltopdf --lowquality '%s' '%s'",
                '/tmp/'.$fileName.'.html',
                '/tmp/'.$fileName.'.pdf'
            )
        );

        return $fileName;
    }
}
