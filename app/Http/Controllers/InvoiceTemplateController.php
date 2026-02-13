<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceTemplateController extends Controller
{
    public function index(): Response
    {
        $organization = current_organization();

        $templates = $organization->invoiceTemplates()
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('InvoiceTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('InvoiceTemplates/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $organization = current_organization();

        $organization->invoiceTemplates()->create([
            ...$validated,
            'slug' => \Str::slug($validated['name']),
            'design' => null,
            'dynamic_fields' => [],
        ]);

        return redirect()->route('invoice-templates.index')
            ->with('success', 'Template created successfully');
    }

    public function edit(InvoiceTemplate $invoiceTemplate): Response
    {
        $this->authorize('update', $invoiceTemplate);

        return Inertia::render('InvoiceTemplates/Edit', [
            'template' => $invoiceTemplate,
        ]);
    }

    public function update(Request $request, InvoiceTemplate $invoiceTemplate): RedirectResponse
    {
        $this->authorize('update', $invoiceTemplate);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'design' => ['nullable', 'string'],
            'dynamic_fields' => ['nullable', 'array'],
        ]);

        $invoiceTemplate->update($validated);

        return redirect()->back()
            ->with('success', 'Template updated successfully');
    }

    public function destroy(InvoiceTemplate $invoiceTemplate): RedirectResponse
    {
        $this->authorize('delete', $invoiceTemplate);

        $invoiceTemplate->delete();

        return redirect()->route('invoice-templates.index')
            ->with('success', 'Template deleted successfully');
    }

    public function preview(InvoiceTemplate $invoiceTemplate): Response
    {
        return Inertia::render('InvoiceTemplates/Preview', [
            'template' => $invoiceTemplate,
        ]);
    }
}
