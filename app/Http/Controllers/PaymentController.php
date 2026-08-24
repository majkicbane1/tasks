<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function create(Request $request)
    {
        $client = Client::with('projects')->findOrFail($request->integer('client_id'));

        return view('payments.create', ['payment' => new Payment(), 'client' => $client]);
    }

    public function store(Request $request)
    {
        $payment = Payment::create($this->normalizedPaymentData($request));

        return redirect()->route('clients.show', $payment->client)->with('status', 'Uplata je dodata.');
    }

    public function show(string $id)
    {
        return redirect()->route('dashboard');
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', ['payment' => $payment->load('client.projects'), 'client' => $payment->client]);
    }

    public function update(Request $request, Payment $payment)
    {
        $payment->update($this->normalizedPaymentData($request));

        if ($payment->isPaidPayment()) {
            $payment->workEntries()->update(['paid_at' => now()]);
        } elseif ($payment->isPendingInvoice()) {
            $payment->workEntries()->update(['paid_at' => null]);
        }

        return redirect()->route('clients.show', $payment->client)->with('status', 'Uplata je sacuvana.');
    }

    public function markPaid(Request $request, Payment $payment)
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            abort_unless((int) $payment->client_id === (int) $user->client_id && $payment->visible_to_client, 403);
        }

        abort_unless($payment->isPendingInvoice(), 404);

        $payment->update([
            'status' => 'paid',
            'paid_on' => now()->toDateString(),
        ]);
        $payment->workEntries()->update([
            'paid_at' => now(),
        ]);

        return back()->with('status', 'Racun je oznacen kao placen.');
    }

    public function destroy(Payment $payment)
    {
        $client = $payment->client;
        $payment->workEntries()->update([
            'payment_id' => null,
            'invoiced_at' => null,
            'paid_at' => null,
        ]);
        $payment->delete();

        return redirect()->route('clients.show', $client)->with('status', 'Uplata je obrisana.');
    }

    private function validatedPayment(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'paid_on' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['payment', 'expense'])],
            'invoice_issued' => ['nullable', 'boolean'],
            'method' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'visible_to_client' => ['nullable', 'boolean'],
        ]) + ['visible_to_client' => false];
    }

    private function normalizedPaymentData(Request $request): array
    {
        $data = $this->validatedPayment($request);
        $data['status'] = $data['type'] === 'payment' && $request->boolean('invoice_issued')
            ? 'pending_invoice'
            : 'paid';
        unset($data['invoice_issued']);

        return $data;
    }
}
