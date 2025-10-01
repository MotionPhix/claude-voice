<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->otherOrganization = Organization::factory()->create();

    $this->owner = User::factory()->create();
    $this->admin = User::factory()->create();
    $this->manager = User::factory()->create();
    $this->accountant = User::factory()->create();
    $this->user = User::factory()->create();

    Membership::factory()->owner()->create([
        'user_id' => $this->owner->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->admin()->create([
        'user_id' => $this->admin->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->manager()->create([
        'user_id' => $this->manager->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->accountant()->create([
        'user_id' => $this->accountant->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->user()->create([
        'user_id' => $this->user->id,
        'organization_id' => $this->organization->id,
    ]);

    set_current_organization($this->organization);

    $this->invoice = Invoice::factory()->for($this->organization)->create();
});

describe('InvoicePolicy viewAny', function () {
    it('allows all roles to view invoices', function () {
        expect($this->owner->can('viewAny', Invoice::class))->toBeTrue();
        expect($this->admin->can('viewAny', Invoice::class))->toBeTrue();
        expect($this->manager->can('viewAny', Invoice::class))->toBeTrue();
        expect($this->accountant->can('viewAny', Invoice::class))->toBeTrue();
        expect($this->user->can('viewAny', Invoice::class))->toBeTrue();
    });

    it('denies users without organization membership', function () {
        set_current_organization(null);

        expect($this->owner->can('viewAny', Invoice::class))->toBeFalse();
    });
});

describe('InvoicePolicy view', function () {
    it('allows all roles to view invoices in their organization', function () {
        expect($this->owner->can('view', $this->invoice))->toBeTrue();
        expect($this->admin->can('view', $this->invoice))->toBeTrue();
        expect($this->manager->can('view', $this->invoice))->toBeTrue();
        expect($this->accountant->can('view', $this->invoice))->toBeTrue();
        expect($this->user->can('view', $this->invoice))->toBeTrue();
    });

    it('denies viewing invoices from other organizations', function () {
        $otherInvoice = Invoice::factory()->for($this->otherOrganization)->create();
        set_current_organization($this->organization);

        expect($this->owner->can('view', $otherInvoice))->toBeFalse();
    });
});

describe('InvoicePolicy create', function () {
    it('allows owner, admin, and manager to create invoices', function () {
        expect($this->owner->can('create', Invoice::class))->toBeTrue();
        expect($this->admin->can('create', Invoice::class))->toBeTrue();
        expect($this->manager->can('create', Invoice::class))->toBeTrue();
    });

    it('denies accountant and user from creating invoices', function () {
        expect($this->accountant->can('create', Invoice::class))->toBeFalse();
        expect($this->user->can('create', Invoice::class))->toBeFalse();
    });
});

describe('InvoicePolicy update', function () {
    it('allows owner, admin, manager, and accountant to update draft invoices', function () {
        $draftInvoice = Invoice::factory()->draft()->for($this->organization)->create();

        expect($this->owner->can('update', $draftInvoice))->toBeTrue();
        expect($this->admin->can('update', $draftInvoice))->toBeTrue();
        expect($this->manager->can('update', $draftInvoice))->toBeTrue();
        expect($this->accountant->can('update', $draftInvoice))->toBeTrue();
    });

    it('denies user role from updating invoices', function () {
        $draftInvoice = Invoice::factory()->draft()->for($this->organization)->create();

        expect($this->user->can('update', $draftInvoice))->toBeFalse();
    });

    it('denies updating sent invoices', function () {
        $sentInvoice = Invoice::factory()->sent()->for($this->organization)->create();

        expect($this->owner->can('update', $sentInvoice))->toBeFalse();
        expect($this->admin->can('update', $sentInvoice))->toBeFalse();
    });

    it('denies updating paid invoices', function () {
        $paidInvoice = Invoice::factory()->paid()->for($this->organization)->create();

        expect($this->owner->can('update', $paidInvoice))->toBeFalse();
    });
});

describe('InvoicePolicy delete', function () {
    it('allows owner, admin, and manager to delete draft invoices', function () {
        $draftInvoice = Invoice::factory()->draft()->for($this->organization)->create();

        expect($this->owner->can('delete', $draftInvoice))->toBeTrue();
        expect($this->admin->can('delete', $draftInvoice))->toBeTrue();
        expect($this->manager->can('delete', $draftInvoice))->toBeTrue();
    });

    it('denies accountant and user from deleting invoices', function () {
        $draftInvoice = Invoice::factory()->draft()->for($this->organization)->create();

        expect($this->accountant->can('delete', $draftInvoice))->toBeFalse();
        expect($this->user->can('delete', $draftInvoice))->toBeFalse();
    });

    it('denies deleting sent invoices', function () {
        $sentInvoice = Invoice::factory()->sent()->for($this->organization)->create();

        expect($this->owner->can('delete', $sentInvoice))->toBeFalse();
    });

    it('denies deleting paid invoices', function () {
        $paidInvoice = Invoice::factory()->paid()->for($this->organization)->create();

        expect($this->owner->can('delete', $paidInvoice))->toBeFalse();
    });
});

describe('InvoicePolicy send', function () {
    it('allows owner, admin, and manager to send draft invoices', function () {
        $draftInvoice = Invoice::factory()->draft()->for($this->organization)->create();

        expect($this->owner->can('send', $draftInvoice))->toBeTrue();
        expect($this->admin->can('send', $draftInvoice))->toBeTrue();
        expect($this->manager->can('send', $draftInvoice))->toBeTrue();
    });

    it('denies accountant and user from sending invoices', function () {
        $draftInvoice = Invoice::factory()->draft()->for($this->organization)->create();

        expect($this->accountant->can('send', $draftInvoice))->toBeFalse();
        expect($this->user->can('send', $draftInvoice))->toBeFalse();
    });

    it('denies sending already sent invoices', function () {
        $sentInvoice = Invoice::factory()->sent()->for($this->organization)->create();

        expect($this->owner->can('send', $sentInvoice))->toBeFalse();
    });
});

describe('InvoicePolicy duplicate', function () {
    it('allows owner, admin, and manager to duplicate invoices', function () {
        expect($this->owner->can('duplicate', $this->invoice))->toBeTrue();
        expect($this->admin->can('duplicate', $this->invoice))->toBeTrue();
        expect($this->manager->can('duplicate', $this->invoice))->toBeTrue();
    });

    it('denies accountant and user from duplicating invoices', function () {
        expect($this->accountant->can('duplicate', $this->invoice))->toBeFalse();
        expect($this->user->can('duplicate', $this->invoice))->toBeFalse();
    });
});

describe('InvoicePolicy downloadPdf', function () {
    it('allows all roles to download PDF', function () {
        expect($this->owner->can('downloadPdf', $this->invoice))->toBeTrue();
        expect($this->admin->can('downloadPdf', $this->invoice))->toBeTrue();
        expect($this->manager->can('downloadPdf', $this->invoice))->toBeTrue();
        expect($this->accountant->can('downloadPdf', $this->invoice))->toBeTrue();
        expect($this->user->can('downloadPdf', $this->invoice))->toBeTrue();
    });
});
