<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use ProtoneMedia\LaravelCrossEloquentSearch\Search;

class SearchController extends Controller
{
    /**
     * Global search across multiple models using Cross Eloquent Search.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'invoices' => [],
                'clients' => [],
                'payments' => [],
            ]);
        }

        $organizationId = current_organization();

        // Perform cross-model search
        $results = Search::new()
            ->add(
                Invoice::where('organization_id', $organizationId)->with('client:id,name'),
                ['number', 'notes'],
                'invoices'
            )
            ->add(
                Client::where('organization_id', $organizationId),
                ['name', 'email', 'phone', 'address'],
                'clients'
            )
            ->add(
                Payment::whereHas('invoice', function ($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId);
                })->with('invoice:id,number'),
                ['amount', 'notes'],
                'payments'
            )
            ->beginWithWildcard()
            ->endWithWildcard()
            ->search($query)
            ->take(15); // Total limit across all models

        // Group results by model type
        $groupedResults = [
            'invoices' => [],
            'clients' => [],
            'payments' => [],
        ];

        foreach ($results as $result) {
            $model = $result->searchable;
            $type = $result->type;

            if ($type === 'invoices') {
                $groupedResults['invoices'][] = [
                    'id' => $model->id,
                    'uuid' => $model->uuid,
                    'number' => $model->number,
                    'client_name' => $model->client->name ?? 'No client',
                    'status' => $model->status,
                ];
            } elseif ($type === 'clients') {
                $groupedResults['clients'][] = [
                    'id' => $model->id,
                    'uuid' => $model->uuid,
                    'name' => $model->name,
                    'email' => $model->email ?? 'No email',
                ];
            } elseif ($type === 'payments') {
                $groupedResults['payments'][] = [
                    'id' => $model->id,
                    'uuid' => $model->uuid,
                    'amount' => number_format($model->amount, 2),
                    'invoice_id' => $model->invoice_id,
                    'invoice_number' => $model->invoice->number ?? 'Unknown',
                ];
            }
        }

        return response()->json($groupedResults);
    }
}
