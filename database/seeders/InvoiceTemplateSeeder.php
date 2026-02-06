<?php

namespace Database\Seeders;

use App\Models\InvoiceTemplate;
use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Default Template',
                'slug' => 'default',
                'description' => 'Clean and professional design perfect for any business',
                'view_path' => 'invoices.templates.default',
                'preview_image' => null,
                'is_free' => true,
                'price' => 0,
                'is_active' => true,
                'settings' => [
                    'primary_color' => '#4F46E5',
                    'font_family' => 'Inter',
                    'show_logo' => true,
                    'show_payment_info' => true,
                ],
            ],
            [
                'name' => 'Modern Template',
                'slug' => 'modern',
                'description' => 'Contemporary minimalist style with bold typography',
                'view_path' => 'invoices.templates.modern',
                'preview_image' => null,
                'is_free' => true,
                'price' => 0,
                'is_active' => true,
                'settings' => [
                    'primary_color' => '#0EA5E9',
                    'font_family' => 'Poppins',
                    'show_logo' => true,
                    'show_payment_info' => true,
                    'layout' => 'centered',
                ],
            ],
        ];

        foreach ($templates as $template) {
            InvoiceTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
