<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffEducationDocumentationApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_education_certificate_upload_dimension_validation()
    {
        Storage::fake('public');

        $staff = DB::table('tblper')->first();
        if (!$staff) {
            $this->markTestSkipped('No staff record found.');
        }

        $userId = $staff->UserID ?? 1;
        $headers = ['X-User-Id' => $userId];

        // 1. Upload valid image dimensions: 1976 x 1987 (<= 2000 x 2000) -> Should succeed
        $validImage1 = UploadedFile::fake()->image('certificate_1976_1987.jpg', 1976, 1987);

        $resValid1 = $this->postJson("/api/nextjs/hr/documentation/{$staff->ID}/education", [
            'categoryID' => 'Tertiary',
            'schoolattended' => 'University of Lagos',
            'schoolfrom' => '2016-01-01',
            'schoolto' => '2020-01-01',
            'certificateheld' => 'BSc Computer Science',
            'degreequalification' => 'BSc',
            'document' => $validImage1
        ], $headers);

        $resValid1->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Upload valid image dimensions: 700 x 600 (<= 2000 x 2000) -> Should succeed
        $validImage2 = UploadedFile::fake()->image('certificate_700_600.png', 700, 600);

        $resValid2 = $this->postJson("/api/nextjs/hr/documentation/{$staff->ID}/education", [
            'categoryID' => 'Secondary',
            'schoolattended' => 'King College',
            'schoolfrom' => '2010-01-01',
            'schoolto' => '2016-01-01',
            'certificateheld' => 'SSCE Certificate',
            'degreequalification' => 'WAEC',
            'document' => $validImage2
        ], $headers);

        $resValid2->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 3. Upload image with dimensions exceeding 2000 x 2000 (e.g. 2100 x 2200) -> Should fail with 422
        $invalidImage = UploadedFile::fake()->image('certificate_too_large.jpg', 2100, 2200);

        $resInvalid = $this->postJson("/api/nextjs/hr/documentation/{$staff->ID}/education", [
            'categoryID' => 'Professional',
            'schoolattended' => 'ICAN Institute',
            'schoolfrom' => '2021-01-01',
            'schoolto' => '2022-01-01',
            'certificateheld' => 'Chartered Accountant',
            'degreequalification' => 'ICAN',
            'document' => $invalidImage
        ], $headers);

        $resInvalid->assertStatus(422)
            ->assertJsonFragment([
                'status' => 'error',
                'message' => 'Certificate image dimensions (2100x2200) exceed the maximum allowed size of 2000x2000 pixels.'
            ]);
    }

    public function test_supporting_document_attachment_upload_dimension_validation()
    {
        Storage::fake('public');

        $staff = DB::table('tblper')->first();
        if (!$staff) {
            $this->markTestSkipped('No staff record found.');
        }

        $userId = $staff->UserID ?? 1;
        $headers = ['X-User-Id' => $userId];

        // 1. Upload valid image dimensions: 1976 x 1987 (<= 2000 x 2000) -> Should succeed
        $validImage = UploadedFile::fake()->image('appointment_letter_1976_1987.jpg', 1976, 1987);

        $resValid = $this->postJson("/api/nextjs/hr/documentation/{$staff->ID}/attachment", [
            'description' => 'Letter of Appointment',
            'filename' => $validImage
        ], $headers);

        $resValid->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Upload image with dimensions exceeding 2000 x 2000 (e.g. 2500 x 2500) -> Should fail with 422
        $invalidImage = UploadedFile::fake()->image('birth_certificate_too_large.jpg', 2500, 2500);

        $resInvalid = $this->postJson("/api/nextjs/hr/documentation/{$staff->ID}/attachment", [
            'description' => 'Birth Certificate',
            'filename' => $invalidImage
        ], $headers);

        $resInvalid->assertStatus(422)
            ->assertJsonFragment([
                'status' => 'error',
                'message' => 'Attachment image dimensions (2500x2500) exceed the maximum allowed size of 2000x2000 pixels.'
            ]);
    }
}
