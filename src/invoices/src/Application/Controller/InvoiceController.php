<?php


namespace App\Application\Controller;


use App\Domain\Invoice\Service\InvoiceService;
use App\Domain\Invoice\DTO\Invoice;
use App\Domain\Invoice\Event\InvoiceIssued;
use App\Domain\Invoice\Model\Address;
use App\Domain\Invoice\Model\Contact;
use App\Domain\Invoice\Model\Country;
use App\Domain\Invoice\Model\PaymentType;
use App\Domain\Invoice\Model\Position;
use App\Domain\Invoice\Value\AccountNumber;
use App\Domain\Invoice\Value\ISOCountryCode;
use App\Domain\Invoice\Value\PostCode;
use App\Domain\Invoice\Value\VatId;
use App\Domain\Invoice\Value\VatRate;
use Money\Currency;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("invoice", name="invoice_")
 */
class InvoiceController extends AbstractController
{
    private $invoiceApi;

    public function __construct(InvoiceService $invoiceApi)
    {
        $this->invoiceApi = $invoiceApi;
    }

    /**
     * @Route("", name="create", methods={"POST"})
     */
    public function createInvoice(Request $request)
    {
        $requestBody = json_decode($request->getContent(), true);
        $ownerId = $requestBody['ownerId'] ?? null;
        if (!$ownerId) {
            return new JsonResponse(['message' => 'Owner id not provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $invoiceId = $this->invoiceApi->create($ownerId);

        return new JsonResponse(['invoiceId' => $invoiceId], JsonResponse::HTTP_CREATED);
    }

    /**
     * @Route("/{invoiceId}/status/{statusName}", name="status_change", methods={"PATCH"}, format="json")
     */
    public function changeStatus(string $invoiceId, string $statusName)
    {
        try {
            $this->invoiceApi->changeStatus($invoiceId, $statusName);

            return new JsonResponse();
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => 'Status does not exist'], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Route("/{invoiceId}", name="draft", methods={"PATCH", "PUT"}, format="json")
     */
    public function saveDraft(Request $request, string $invoiceId)
    {
        if ('PATCH' === $request->getMethod()) {
            $this->invoiceApi->saveDraft($invoiceId, Invoice::createDraft(json_decode($request->getContent(), true)));
        } else {
            $this->invoiceApi->issue($invoiceId, Invoice::create(json_decode($request->getContent(), true)));
        }

        return new JsonResponse();
    }
}