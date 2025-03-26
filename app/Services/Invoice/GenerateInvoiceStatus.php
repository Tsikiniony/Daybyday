<?php
namespace App\Services\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceDiscount;
use App\Repositories\Money\Money;

class GenerateInvoiceStatus
{
    private $invoice;
    private $price;
    private $sum;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $calculator = new InvoiceCalculator($invoice);
        $discount = InvoiceDiscount::where('invoice_id', $this->invoice->id)->first();
        
        if($discount !== null) {
            $this->price = (int)$discount->discounted_amount;
        } else {
            $this->price = $calculator->getTotalPrice()->getAmount();
        }
        $this->sum = (int)$this->invoice->payments()->sum('amount');

        // $debug = [
        //     'invoice_id' => $invoice->id,
        //     'has_discount' => $discount !== null,
        //     'price' => $this->price,
        //     'sum' => $this->sum,
        //     'price_equals_sum' => $this->price === $this->sum,
        //     'price_type' => gettype($this->price),
        //     'sum_type' => gettype($this->sum),
        //     'is_draft' => $this->isDraft(),
        //     'is_unpaid' => $this->isUnPaid(),
        //     'is_paid' => $this->isPaid(),
        //     'is_partial' => $this->isPartialPaid(),
        //     'is_overpaid' => $this->isOverPaid(),
        //     'payments' => $this->invoice->payments()->get(['amount', 'payment_date'])->toArray()
        // ];

        // dd($debug);
    }

    public function createStatus()
    {
        $newStatus = $this->getStatus();
        $this->invoice->status = $newStatus;
        return $this->invoice->save();
    }

    public function getStatus()
    {
        // Vérifier si c'est payé en premier
        if ($this->isPaid()) {
            return InvoiceStatus::paid()->getStatus();
        }

        // Ensuite vérifier si c'est un brouillon
        if ($this->isDraft()) {
            return InvoiceStatus::draft()->getStatus();
        }

        // Puis vérifier si c'est non payé
        if ($this->isUnPaid()) {
            return InvoiceStatus::unpaid()->getStatus();
        }

        // Puis vérifier si c'est payé en trop
        if ($this->isOverPaid()) {
            return InvoiceStatus::overpaid()->getStatus();
        }

        // Enfin vérifier si c'est partiellement payé
        if ($this->isPartialPaid()) {
            return InvoiceStatus::partialPaid()->getStatus();
        }

        throw new \Exception(sprintf(
            "Can't generate invoice status for invoice: %d (price: %d, sum: %d, price_type: %s, sum_type: %s)",
            $this->invoice->id,
            $this->price,
            $this->sum,
            gettype($this->price),
            gettype($this->sum)
        ));
    }

    public function isDraft(): bool
    {
        return !$this->invoice->isSent();
    }

    public function isPartialPaid(): bool
    {
        return $this->sum > 0 && $this->sum < $this->price;
    }

    public function isPaid(): bool
    {
        // Convertir en string pour éviter les problèmes de type
        return (string)$this->sum === (string)$this->price;
    }

    public function isUnPaid(): bool
    {
        return $this->sum <= 0;
    }

    public function isOverPaid(): bool
    {
        return $this->sum > $this->price;
    }
}