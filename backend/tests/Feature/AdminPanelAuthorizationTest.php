<?php

namespace Tests\Feature;

use App\Models\ClinicSubmission;
use App\Models\ContactInquiry;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AdminRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminRoleSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get(route('admin.clinic-submissions.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_and_edit_records_but_cannot_delete_them(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');
        $submission = $this->clinicSubmission();
        $inquiry = $this->contactInquiry();

        $this->actingAs($admin)
            ->get(route('admin.clinic-submissions.index'))
            ->assertOk();
        $this->get(route('admin.clinic-submissions.show', $submission))
            ->assertOk()
            ->assertSee(__('admin.status_pending'))
            ->assertDontSee('name="status"', false);
        $this->get(route('admin.clinic-submissions.create'))->assertOk();
        $this->post(route('admin.clinic-submissions.store'), [
            'clinic_name' => 'New Clinic',
            'contact_person' => 'Taylor Reed',
            'email' => 'taylor@example.test',
            'phone' => '+34987654321',
            'country' => 'Spain',
            'city' => 'Valencia',
            'clinic_type' => 'Private clinic',
            'preferred_contact_method' => 'Email',
            'preferred_language' => 'en',
            'consent' => true,
        ])->assertRedirect();
        $this->assertDatabaseHas('clinic_submissions', ['clinic_name' => 'New Clinic']);

        $this->get(route('admin.clinic-submissions.edit', $submission))->assertOk();
        $this->put(route('admin.clinic-submissions.update', $submission), [
            'clinic_name' => 'Updated Clinic',
            'contact_person' => 'Alex Smith',
            'email' => 'alex@example.test',
            'phone' => '+34123456789',
            'country' => 'Spain',
            'city' => 'Barcelona',
            'clinic_type' => 'Private clinic',
            'preferred_contact_method' => 'Email',
            'preferred_language' => 'es',
            'consent' => true,
        ])->assertRedirect(route('admin.clinic-submissions.show', $submission));
        $this->assertDatabaseHas('clinic_submissions', [
            'id' => $submission->id,
            'city' => 'Barcelona',
        ]);

        $this->get(route('admin.contact-inquiries.create'))->assertOk();
        $this->post(route('admin.contact-inquiries.store'), [
            'name' => 'New Contact',
            'email' => 'new@example.test',
            'subject' => 'New inquiry',
            'message' => 'Please contact our clinic.',
            'consent' => true,
        ])->assertRedirect();
        $this->assertDatabaseHas('contact_inquiries', ['subject' => 'New inquiry']);

        $this->get(route('admin.contact-inquiries.edit', $inquiry))->assertOk();
        $this->put(route('admin.contact-inquiries.update', $inquiry), [
            'name' => 'Updated Contact',
            'email' => 'jordan@example.test',
            'subject' => 'Updated inquiry',
            'message' => 'Please contact our clinic next week.',
            'consent' => true,
        ])->assertRedirect(route('admin.contact-inquiries.show', $inquiry));
        $this->assertDatabaseHas('contact_inquiries', [
            'id' => $inquiry->id,
            'subject' => 'Updated inquiry',
        ]);

        $this->delete(route('admin.clinic-submissions.destroy', $submission))->assertForbidden();
        $this->delete(route('admin.contact-inquiries.destroy', $inquiry))->assertForbidden();
    }

    public function test_super_admin_can_delete_records(): void
    {
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super-admin');
        $submission = $this->clinicSubmission();
        $inquiry = $this->contactInquiry();

        $this->actingAs($superAdmin)
            ->delete(route('admin.clinic-submissions.destroy', $submission))
            ->assertRedirect(route('admin.clinic-submissions.index'));
        $this->delete(route('admin.contact-inquiries.destroy', $inquiry))
            ->assertRedirect(route('admin.contact-inquiries.index'));

        $this->assertDatabaseMissing('clinic_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('contact_inquiries', ['id' => $inquiry->id]);
    }

    public function test_only_super_admin_can_change_clinic_submission_status(): void
    {
        $submission = $this->clinicSubmission();
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->patch(route('admin.clinic-submissions.status', $submission), ['status' => 'accepted'])
            ->assertForbidden();
        $this->assertDatabaseHas('clinic_submissions', [
            'id' => $submission->id,
            'status' => 'pending',
        ]);

        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->get(route('admin.clinic-submissions.show', $submission))
            ->assertOk()
            ->assertSee('name="status"', false);
        $this->patch(route('admin.clinic-submissions.status', $submission), ['status' => 'deleted'])
            ->assertSessionHasErrors('status');

        $this->actingAs($superAdmin)
            ->patch(route('admin.clinic-submissions.status', $submission), ['status' => 'meeting'])
            ->assertRedirect(route('admin.clinic-submissions.show', $submission));
        $this->assertDatabaseHas('clinic_submissions', [
            'id' => $submission->id,
            'status' => 'meeting',
        ]);
    }

    public function test_status_sidebar_pages_filter_submissions(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');
        $pending = $this->clinicSubmission();
        $pending->update(['clinic_name' => 'Awaiting Review']);
        $accepted = $this->clinicSubmission();
        $accepted->update([
            'clinic_name' => 'Approved Clinic',
            'status' => 'accepted',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.clinic-submissions.status-list', 'pending'))
            ->assertOk()
            ->assertViewHas('status', 'pending')
            ->assertSee('Awaiting Review')
            ->assertDontSee('Approved Clinic');
        $this->get(route('admin.clinic-submissions.status-list', 'accepted'))
            ->assertOk()
            ->assertViewHas('status', 'accepted')
            ->assertSee('Approved Clinic')
            ->assertDontSee('Awaiting Review');
        $this->get(route('admin.clinic-submissions.status-list', 'unknown'))->assertNotFound();
    }

    public function test_admin_exports_excel_compatible_csv_for_both_lead_types(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');
        $submission = $this->clinicSubmission();
        $submission->update(['city' => '=HYPERLINK("https://example.test")']);
        $inquiry = $this->contactInquiry();

        $clinicResponse = $this->actingAs($admin)->get(route('admin.clinic-submissions.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $clinicCsv = $clinicResponse->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $clinicCsv);
        $this->assertStringContainsString('Clinic or business name', $clinicCsv);
        $this->assertStringContainsString("'=HYPERLINK", $clinicCsv);

        $inquiryCsv = $this->get(route('admin.contact-inquiries.export'))
            ->assertOk()
            ->streamedContent();
        $this->assertStringContainsString('Automation inquiry', $inquiryCsv);
        $this->assertStringContainsString($inquiry->email, $inquiryCsv);
    }

    public function test_dashboard_shows_monthly_activity_without_clinic_size_average_cards(): void
    {
        Carbon::setTestNow('2026-11-25 12:00:00');
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super-admin');

        $first = $this->clinicSubmission();
        $first->update([
            'number_of_doctors' => '2',
            'number_of_locations' => '1',
            'status' => 'accepted',
        ]);
        $second = $this->clinicSubmission();
        $second->update([
            'number_of_doctors' => '4-8',
            'number_of_locations' => '3',
            'status' => 'meeting',
        ]);
        $this->contactInquiry();

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk();
        $response->assertViewMissing('averageStaff');
        $response->assertViewMissing('averageLocations');
        $response->assertViewHas('averageSubmissionsPerMonth', 2 / 6);
        $response->assertViewHas('averageInquiriesPerMonth', 1 / 6);
        $response->assertViewHas('acceptedRate', 50.0);
        $response->assertViewHas('meetingRate', 50.0);
        $response->assertDontSee('Average people working')
            ->assertDontSee('Average locations');
        $response->assertSee('admin-sidebar')
            ->assertSee('data-theme-toggle', false)
            ->assertSee(asset('admin.js'), false);
        foreach (['pending', 'accepted', 'refused', 'meeting'] as $status) {
            $response->assertSee(route('admin.clinic-submissions.status-list', $status), false);
        }
    }

    public function test_disabled_admin_sessions_are_logged_out(): void
    {
        $admin = User::factory()->create(['is_active' => false]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_new_admins_must_change_their_temporary_password(): void
    {
        $admin = User::factory()->create([
            'is_active' => true,
            'must_change_password' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.clinic-submissions.index'))
            ->assertRedirect(route('admin.password.edit'));

        $this->get(route('admin.password.edit'))->assertOk();
    }

    private function clinicSubmission(): ClinicSubmission
    {
        return ClinicSubmission::create([
            'clinic_name' => 'Northside Clinic',
            'contact_person' => 'Alex Smith',
            'email' => 'alex@example.test',
            'phone' => '+34123456789',
            'country' => 'Spain',
            'city' => 'Madrid',
            'clinic_type' => 'Private clinic',
            'preferred_contact_method' => 'Email',
            'preferred_language' => 'es',
            'consent' => true,
        ]);
    }

    private function contactInquiry(): ContactInquiry
    {
        return ContactInquiry::create([
            'name' => 'Jordan Doe',
            'email' => 'jordan@example.test',
            'subject' => 'Automation inquiry',
            'message' => 'Please contact our clinic.',
            'consent' => true,
        ]);
    }
}
