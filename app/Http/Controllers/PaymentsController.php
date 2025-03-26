<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\PaymentRequest;
use App\Models\Integration;
use App\Models\Invoice;
use App\Models\InvoiceDiscount;
use App\Models\Payment;
use App\Services\Invoice\GenerateInvoiceStatus;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\Services\Invoice\InvoiceCalculator;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Payment $payment
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Payment $payment)
    {
        if (!auth()->user()->can('payment-delete')) {
            session()->flash('flash_message', __("You don't have permission to delete a payment"));
            return redirect()->back();
        }
        $api = Integration::initBillingIntegration();
        if ($api) {
            $api->deletePayment($payment);
        }

        $payment->delete();
        // Mise à jour du statut de la facture
        $invoice = Invoice::find($payment->invoice_id);
        if ($invoice) {
            app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();
        }
        session()->flash('flash_message', __('Payment successfully deleted'));
        return redirect()->back();
    }

    public function addPayment(PaymentRequest $request, Invoice $invoice)
    {
        if (!$invoice->isSent()) {
            session()->flash('flash_message_warning', __("Can't add payment on Invoice"));
            return redirect()->route('invoices.show', $invoice->external_id);
        }

        if ($invoice->status == 'paid') {
            session()->flash('flash_message_warning', __("Invoice already paid"));
            return redirect()->route('invoices.show', $invoice->external_id);
        }

        $calculator = new InvoiceCalculator($invoice);
        $baseAmount = $calculator->getTotalPrice()->getAmount() - $invoice->payments()->sum('amount');
        
        // Vérifier s'il y a une remise
        $discount = InvoiceDiscount::where('invoice_id', $invoice->id)->first();
        if ($discount !== null) {
            $baseAmount = (int)$discount->discounted_amount - (int)$invoice->payments()->sum('amount');
        }

        // Vérifier si le montant du paiement est supérieur au montant dû
        if ($request->amount * 100 > $baseAmount) {
            session()->flash('flash_message_warning', __("The entered amount exceeds the due amount"));
            return redirect()->route('invoices.show', $invoice->external_id);
        }

        $payment = Payment::create([
            'external_id' => Uuid::uuid4()->toString(),
            'amount' => $request->amount * 100,
            'payment_date' => Carbon::parse($request->payment_date),
            'payment_source' => $request->source,
            'description' => $request->description,
            'invoice_id' => $invoice->id
        ]);

        $api = Integration::initBillingIntegration();
        if ($api && $invoice->integration_invoice_id) {
            $result = $api->createPayment($payment);
            $payment->integration_payment_id = $result["Guid"];
            $payment->integration_type = get_class($api);
            $payment->save();
        }

        app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();

        session()->flash('flash_message', __('Payment successfully added'));
        return redirect()->back();
    }


    public function updatePayment(Request $request, $external_id)
    {
        try {
            $payment = Payment::where('external_id', $external_id)->firstOrFail();
            
            // Validation des données
            $validated = $request->validate([
                'amount' => 'required|numeric',
                'description' => 'nullable|string',
            ]);
            
            // Mise à jour du paiement
            $payment->amount = $validated['amount'];
            if (isset($validated['description'])) {
                $payment->description = $validated['description'];
            }
            $payment->save();

            // Mise à jour du statut de la facture
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating payment:', [
                'external_id' => $external_id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentByExternalId($external_id)
    {
        $payment = Payment::where('external_id', $external_id)->first();
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        
        $result = [
            'payment' => $payment,
            'invoice' => Invoice::where('id', $payment->invoice_id)->first()
        ];
        
        return response()->json($result);
    }

    public function deletePayment($external_id)
    {
        try {
            $payment = Payment::where('external_id', $external_id)->firstOrFail();

            $api = Integration::initBillingIntegration();
            if ($api) {
                $api->deletePayment($payment);
            }
            
            // Supprimer le paiement
            $payment->delete();

            // Mise à jour du statut de la facture
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                app(GenerateInvoiceStatus::class, ['invoice' => $invoice])->createStatus();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting payment:', [
                'external_id' => $external_id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $invoice = Invoice::findOrFail($request->invoice_id);
        $amount = $request->amount;

        if ($request->apply_discount) {
            $activeRate = DiscountRate::where('is_active', true)->first();
            if ($activeRate) {
                // Créer l'enregistrement de remise
                InvoiceDiscount::create([
                    'invoice_id' => $invoice->id,
                    'discount_rate_id' => $activeRate->id,
                    'original_amount' => $invoice->amount,
                    'discounted_amount' => $amount
                ]);
            }
        }

        $payment = new Payment();
        $payment->amount = $amount;
        $payment->payment_date = now();
        $payment->invoice_id = $invoice->id;
        $payment->source = $request->source;
        $payment->description = $request->description;
        $payment->external_id = Uuid::uuid4()->toString();
        $payment->save();

        Session::flash('flash_message', __('Payment registered'));
        return redirect()->back();
    }
}