<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Company;
use App\Models\LabelOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Dawud Mensah',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'status' => 'active',
                'last_login_at' => now()->subHours(3),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name' => 'Akosua Adjei',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'status' => 'active',
                'last_login_at' => now()->subDay(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'qa@example.com'],
            [
                'name' => 'Kwame Boateng',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'last_login_at' => now()->subDays(2),
            ],
        );

        $demoSlugs = [
            'akoma-naturals',
            'nhyira-cocoa-works',
            'savannah-herbal-labs',
            'voltapure-water-company',
            'adinkra-spice-kitchen',
            'tema-care-pharma',
        ];

        ScanLog::query()->where('metadata->source', 'active8_demo')->delete();
        ActivityLog::query()->where('metadata->source', 'active8_demo')->delete();
        Company::withTrashed()->whereIn('slug', $demoSlugs)->get()->each->forceDelete();

        $companyData = $this->companies();
        $allBatches = collect();
        $allQrCodes = collect();

        foreach ($companyData as $companyItem) {
            $company = Company::query()->create($companyItem['company']);
            $this->seedAssets($companyItem);

            $company->profile()->create(array_merge($companyItem['profile'], [
                'logo_path' => 'demo/'.$companyItem['slug'].'-logo.svg',
                'cover_image_path' => 'demo/'.$companyItem['slug'].'-cover.svg',
                'publication_status' => 'published',
                'published_at' => now()->subDays(rand(4, 18)),
            ]));

            $categories = collect($companyItem['categories'])
                ->mapWithKeys(fn (array $category) => [
                    $category['slug'] => ProductCategory::query()->create([
                        'company_id' => $company->id,
                        'name' => $category['name'],
                        'slug' => $category['slug'],
                        'description' => $category['description'],
                        'status' => 'active',
                    ]),
                ]);

            foreach ($companyItem['products'] as $productItem) {
                $category = $categories[$productItem['category_slug']];
                $product = Product::query()->create([
                    'company_id' => $company->id,
                    'name' => $productItem['name'],
                    'slug' => Str::slug($productItem['name']),
                    'sku' => $productItem['sku'],
                    'category' => $category->name,
                    'product_category_id' => $category->id,
                    'image_path' => 'demo/'.$productItem['image'],
                    'gallery_paths' => [
                        'demo/'.$productItem['image'],
                        'demo/'.$companyItem['slug'].'-cover.svg',
                    ],
                    'video_url' => $productItem['video_url'] ?? null,
                    'description' => $productItem['description'],
                    'ingredients' => $productItem['ingredients'],
                    'usage_instructions' => $productItem['usage_instructions'],
                    'faq_items' => $productItem['faqs'],
                    'manufacturer' => $companyItem['profile']['manufacturer_name'],
                    'country_of_origin' => 'Ghana',
                    'status' => $productItem['status'] ?? 'active',
                ]);

                foreach ($productItem['suitability'] as $index => $item) {
                    $product->suitabilityItems()->create(array_merge($item, [
                        'sort_order' => $index,
                        'is_public' => true,
                    ]));
                }

                foreach ($productItem['batches'] as $batchItem) {
                    $batch = Batch::query()->create([
                        'product_id' => $product->id,
                        'batch_number' => $batchItem['batch_number'],
                        'manufacture_date' => $batchItem['manufacture_date'],
                        'expiry_date' => $batchItem['expiry_date'],
                        'quantity' => $batchItem['quantity'],
                        'production_location' => $batchItem['production_location'],
                        'qa_status' => $batchItem['qa_status'],
                        'status' => $batchItem['status'],
                    ]);

                    $qrCode = QrCode::query()->create([
                        'batch_id' => $batch->id,
                        'token' => $batchItem['token'],
                        'verification_url' => url('/verify/'.$batchItem['token']),
                        'status' => $batchItem['qr_status'],
                        'generated_at' => Carbon::parse($batchItem['manufacture_date'])->addDays(2),
                        'activated_at' => $batchItem['qr_status'] === 'revoked' ? null : Carbon::parse($batchItem['manufacture_date'])->addDays(3),
                        'expires_at' => Carbon::parse($batchItem['qr_expires_at']),
                        'revoked_at' => $batchItem['qr_status'] === 'revoked' ? Carbon::parse($batchItem['revoked_at']) : null,
                        'revocation_reason' => $batchItem['revocation_reason'] ?? null,
                    ]);

                    LabelOrder::query()->create([
                        'company_id' => $company->id,
                        'batch_id' => $batch->id,
                        'label_type' => $batchItem['label_type'],
                        'label_size' => $batchItem['label_size'],
                        'print_quantity' => $batchItem['quantity'],
                        'printer_vendor' => $batchItem['printer_vendor'],
                        'status' => $batchItem['label_status'],
                        'delivery_date' => Carbon::parse($batchItem['manufacture_date'])->addDays(10),
                        'notes' => 'Demo label order for '.$batch->batch_number.' with batch-specific QR security seal.',
                    ]);

                    $allBatches->push($batch);
                    $allQrCodes->push($qrCode);
                }
            }
        }

        $this->seedScanLogs($allQrCodes);
        $this->seedActivityLogs($owner, $allBatches);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function companies(): array
    {
        return [
            [
                'slug' => 'akoma-naturals',
                'palette' => ['#0F766E', '#ECFDF5'],
                'company' => [
                    'name' => 'Akoma Naturals Ltd',
                    'slug' => 'akoma-naturals',
                    'registration_number' => 'CS140982026',
                    'industry' => 'Natural cosmetics and personal care',
                    'contact_person' => 'Ama Serwaa Mensah',
                    'email' => 'quality@akomanaturals.test',
                    'phone' => '+233 50 121 7190',
                    'country' => 'Ghana',
                    'region' => 'Greater Accra',
                    'city' => 'Accra',
                    'website' => 'https://akomanaturals.test',
                    'status' => 'active',
                    'notes' => 'Spintex-based cosmetics demo company inspired by Ghanaian shea butter and black soap value chains.',
                ],
                'profile' => [
                    'verification_headline' => 'Verify your original Akoma Naturals product',
                    'genuine_message' => 'This product matches an approved Akoma Naturals production batch. Confirm the batch number and seal before use.',
                    'suspicious_message' => 'This QR code has unusual scan activity. Please contact Akoma support before using the product.',
                    'invalid_message' => 'This code is not recognized by Akoma Naturals. The product may not be genuine.',
                    'support_text' => 'Send a photo of the seal, batch number, purchase location, and receipt to our quality desk.',
                    'support_email' => 'quality@akomanaturals.test',
                    'support_phone' => '+233 50 121 7190',
                    'website_url' => 'https://akomanaturals.test',
                    'social_links' => [
                        ['label' => 'Instagram', 'url' => 'https://instagram.com/akomanaturals'],
                        ['label' => 'Facebook', 'url' => 'https://facebook.com/akomanaturals'],
                    ],
                    'faq_items' => [
                        ['question' => 'How do I confirm the product is original?', 'answer' => 'Scan the hologram QR code and compare the company, product, and batch information with the package.'],
                        ['question' => 'What if the seal is broken?', 'answer' => 'Do not use the product. Report the shop location and send photos to support.'],
                        ['question' => 'Can one QR code be scanned more than once?', 'answer' => 'Yes, but repeated scans from unusual locations may be flagged for review.'],
                    ],
                    'custom_sections' => [
                        ['title' => 'Quality Promise', 'body' => 'Akoma products are batch recorded, QA reviewed, and sealed before distribution.'],
                    ],
                    'company_description' => 'Akoma Naturals produces shea butter, black soap, and botanical body care products for Ghanaian retailers and export buyers.',
                    'manufacturer_name' => 'Akoma Naturals Manufacturing Unit',
                    'factory_address' => 'Plot 24, Spintex Industrial Area, Accra, Ghana',
                    'certification_number' => 'FDA-GH-COS-DEMO-421',
                    'layout_style' => 'product-first',
                    'primary_color' => '#0F766E',
                    'secondary_color' => '#111827',
                    'text_color' => '#111827',
                    'background_color' => '#FFFFFF',
                    'button_color' => '#0F766E',
                    'button_text_color' => '#FFFFFF',
                    'button_style' => 'solid',
                    'font_family' => 'DM Sans',
                    'badge_style' => 'official',
                    'success_color' => '#16A34A',
                    'warning_color' => '#F59E0B',
                    'error_color' => '#DC2626',
                    'show_platform_branding' => true,
                ],
                'categories' => [
                    ['name' => 'Skincare', 'slug' => 'skincare', 'description' => 'Moisturizers, oils, and everyday skin care products.'],
                    ['name' => 'Cleansers', 'slug' => 'cleansers', 'description' => 'Soap bars and liquid cleansers made for daily use.'],
                ],
                'products' => [
                    $this->product('Shea Glow Body Butter', 'AK-SG-250', 'skincare', 'akoma-shea.svg', 'Whipped body butter made with northern Ghana shea butter, cocoa butter, baobab oil, and vitamin E.', 'Shea butter, cocoa butter, baobab oil, coconut oil, vitamin E, fragrance.', 'Apply a small amount to clean skin. External use only. Store away from direct heat.', [
                        ['question' => 'Is this suitable for children?', 'answer' => 'Use for children only under adult supervision and avoid the eye area.'],
                        ['question' => 'Why is the texture different in hot weather?', 'answer' => 'Natural shea butter can soften in warm rooms. Keep the container tightly closed.'],
                    ], [
                        ['type' => 'usage', 'short_label' => 'External use only', 'detail' => 'Avoid eyes and broken skin.', 'severity' => 'caution'],
                        ['type' => 'allergen', 'short_label' => 'Patch test advised', 'detail' => 'Test on a small area before full use.', 'severity' => 'caution'],
                    ], [
                        $this->batch('AK-SG-2026-001', '2026-04-12', '2028-04-12', 6400, 'Spintex Factory Line A', 'approved', 'printed', 'a8demo-akoma-sg-001', 'active', '2028-04-12', 'hologram', '30mm x 30mm', 'printed'),
                        $this->batch('AK-SG-2026-002', '2026-05-18', '2028-05-18', 5200, 'Spintex Factory Line B', 'approved', 'approved', 'a8demo-akoma-sg-002', 'active', '2028-05-18', 'tamper-proof', '25mm x 35mm', 'approved'),
                    ]),
                    $this->product('Osu Black Soap Cleanser', 'AK-BS-150', 'cleansers', 'akoma-soap.svg', 'Liquid black soap cleanser blended with neem, moringa, and aloe for everyday cleansing.', 'African black soap, aloe vera, neem oil, moringa extract, purified water.', 'Apply to wet skin, lather gently, and rinse thoroughly. Avoid direct contact with eyes.', [
                        ['question' => 'Can I use it on my face?', 'answer' => 'Yes, but patch test first if your skin is sensitive.'],
                    ], [
                        ['type' => 'usage', 'short_label' => 'Rinse well', 'detail' => 'Rinse with clean water after use.', 'severity' => 'info'],
                    ], [
                        $this->batch('AK-BS-2026-001', '2026-03-28', '2028-03-28', 4100, 'Spintex Factory Line C', 'approved', 'distributed', 'a8demo-akoma-bs-001', 'revoked', '2028-03-28', 'hologram', '30mm x 30mm', 'delivered', '2026-05-29', 'Reported duplicate labels in Makola retail channel'),
                    ]),
                ],
            ],
            [
                'slug' => 'nhyira-cocoa-works',
                'palette' => ['#7C2D12', '#FEF3C7'],
                'company' => [
                    'name' => 'Nhyira Cocoa Works Ltd',
                    'slug' => 'nhyira-cocoa-works',
                    'registration_number' => 'CS811732026',
                    'industry' => 'Cocoa foods and beverages',
                    'contact_person' => 'Kojo Asante',
                    'email' => 'trace@nhyiracocoa.test',
                    'phone' => '+233 24 603 8842',
                    'country' => 'Ghana',
                    'region' => 'Ashanti',
                    'city' => 'Kumasi',
                    'website' => 'https://nhyiracocoa.test',
                    'status' => 'active',
                    'notes' => 'Kumasi cocoa processing demo company for food product verification.',
                ],
                'profile' => $this->profile('Nhyira Cocoa Works', 'trace@nhyiracocoa.test', '+233 24 603 8842', 'Kumasi Light Industrial Area, Ashanti Region, Ghana', 'FDA-GH-FDB-DEMO-118', '#7C2D12', 'Verify your original Nhyira cocoa product'),
                'categories' => [
                    ['name' => 'Cocoa Beverages', 'slug' => 'cocoa-beverages', 'description' => 'Breakfast cocoa and drinking chocolate products.'],
                    ['name' => 'Snacks', 'slug' => 'snacks', 'description' => 'Roasted cocoa nibs and confectionery ingredients.'],
                ],
                'products' => [
                    $this->product('Golden Stool Cocoa Drink', 'NC-CD-500', 'cocoa-beverages', 'nhyira-cocoa.svg', 'Instant cocoa drink powder made with Ghana cocoa, light brown sugar, and a vanilla note.', 'Cocoa powder, sugar, soy lecithin, vanilla flavour, salt.', 'Add two tablespoons to hot water or milk. Stir until smooth.', [
                        ['question' => 'Does it contain milk?', 'answer' => 'No milk is added, but check the label for cross-contact information.'],
                        ['question' => 'How should I store it?', 'answer' => 'Keep sealed in a cool, dry place after opening.'],
                    ], [
                        ['type' => 'allergen', 'short_label' => 'Contains soy lecithin', 'detail' => 'Not suitable for people with soy allergies.', 'severity' => 'warning'],
                    ], [
                        $this->batch('NC-CD-2026-004', '2026-04-02', '2027-04-02', 12000, 'Kumasi Light Industrial Area', 'approved', 'distributed', 'a8demo-nhyira-cd-004', 'active', '2027-04-02', 'tamper-proof', '28mm x 28mm', 'delivered'),
                        $this->batch('NC-CD-2026-005', '2026-05-09', '2027-05-09', 9000, 'Kumasi Light Industrial Area', 'review', 'draft', 'a8demo-nhyira-cd-005', 'active', '2027-05-09', 'hologram', '28mm x 28mm', 'pending'),
                    ]),
                    $this->product('Adwene Cocoa Nibs', 'NC-NB-120', 'snacks', 'nhyira-nibs.svg', 'Roasted cocoa nibs for baking, smoothies, yoghurt, and snack blends.', 'Roasted cocoa nibs.', 'Use as topping or ingredient. Reseal after opening.', [
                        ['question' => 'Are the nibs sweetened?', 'answer' => 'No. They are unsweetened roasted cocoa nibs.'],
                    ], [
                        ['type' => 'storage', 'short_label' => 'Keep dry', 'detail' => 'Moisture can affect crunch and shelf life.', 'severity' => 'info'],
                    ], [
                        $this->batch('NC-NB-2026-002', '2026-02-16', '2027-02-16', 2800, 'Kumasi Light Industrial Area', 'approved', 'printed', 'a8demo-nhyira-nb-002', 'expired', '2026-05-20', 'hologram', '24mm x 24mm', 'printed'),
                    ]),
                ],
            ],
            [
                'slug' => 'savannah-herbal-labs',
                'palette' => ['#166534', '#DCFCE7'],
                'company' => [
                    'name' => 'Savannah Herbal Labs',
                    'slug' => 'savannah-herbal-labs',
                    'registration_number' => 'CS509442026',
                    'industry' => 'Herbal wellness and natural products',
                    'contact_person' => 'Mariam Yakubu',
                    'email' => 'support@savannahherbal.test',
                    'phone' => '+233 55 220 3940',
                    'country' => 'Ghana',
                    'region' => 'Northern',
                    'city' => 'Tamale',
                    'website' => 'https://savannahherbal.test',
                    'status' => 'active',
                    'notes' => 'Tamale demo company using moringa, baobab, hibiscus, and ginger supply chains.',
                ],
                'profile' => $this->profile('Savannah Herbal Labs', 'support@savannahherbal.test', '+233 55 220 3940', 'Industrial Area, Tamale, Northern Region, Ghana', 'FDA-GH-HRB-DEMO-209', '#166534', 'Verify your Savannah Herbal product'),
                'categories' => [
                    ['name' => 'Herbal Tonics', 'slug' => 'herbal-tonics', 'description' => 'Bottled herbal drinks and wellness tonics.'],
                    ['name' => 'Dried Herbs', 'slug' => 'dried-herbs', 'description' => 'Dried leaves, teas, and botanical packs.'],
                ],
                'products' => [
                    $this->product('Moringa Ginger Tonic', 'SH-MG-330', 'herbal-tonics', 'savannah-tonic.svg', 'A ginger and moringa wellness drink for adult consumers.', 'Water, ginger extract, moringa leaf extract, hibiscus, lemon, sugar.', 'Shake well. Serve chilled. Adults only. Do not exceed label directions.', [
                        ['question' => 'Can pregnant women take this?', 'answer' => 'Pregnant or breastfeeding customers should consult a qualified health professional first.'],
                        ['question' => 'Is this medicine?', 'answer' => 'This is a wellness beverage demo product. Follow the product label and seek medical advice for health conditions.'],
                    ], [
                        ['type' => 'pregnancy', 'short_label' => 'Ask a professional', 'detail' => 'Pregnant or breastfeeding customers should seek medical advice.', 'severity' => 'warning'],
                        ['type' => 'medical', 'short_label' => 'Adult use', 'detail' => 'Not intended for children.', 'severity' => 'caution'],
                    ], [
                        $this->batch('SH-MG-2026-007', '2026-05-01', '2027-05-01', 7600, 'Tamale Industrial Area', 'approved', 'printed', 'a8demo-savannah-mg-007', 'active', '2027-05-01', 'hologram', '30mm x 40mm', 'printed'),
                    ]),
                    $this->product('Baobab Hibiscus Tea', 'SH-BH-040', 'dried-herbs', 'savannah-tea.svg', 'Dried baobab fruit and hibiscus infusion blend packed in sachets.', 'Baobab fruit powder, hibiscus calyces, lemongrass.', 'Steep one sachet in hot water for five minutes.', [
                        ['question' => 'Does it contain caffeine?', 'answer' => 'No caffeine is added.'],
                    ], [
                        ['type' => 'storage', 'short_label' => 'Seal after opening', 'detail' => 'Keep sachets dry and away from strong odours.', 'severity' => 'info'],
                    ], [
                        $this->batch('SH-BH-2026-003', '2026-04-20', '2027-04-20', 3400, 'Tamale Packing Room 2', 'approved', 'approved', 'a8demo-savannah-bh-003', 'active', '2027-04-20', 'tamper-proof', '22mm x 35mm', 'approved'),
                    ]),
                ],
            ],
            [
                'slug' => 'voltapure-water-company',
                'palette' => ['#1D4ED8', '#DBEAFE'],
                'company' => [
                    'name' => 'VoltaPure Water Company',
                    'slug' => 'voltapure-water-company',
                    'registration_number' => 'CS244102026',
                    'industry' => 'Bottled water and packaging',
                    'contact_person' => 'Elorm Agbeko',
                    'email' => 'qa@voltapure.test',
                    'phone' => '+233 27 444 1882',
                    'country' => 'Ghana',
                    'region' => 'Volta',
                    'city' => 'Ho',
                    'website' => 'https://voltapure.test',
                    'status' => 'active',
                    'notes' => 'Water packaging demo company for high-volume scan scenarios.',
                ],
                'profile' => $this->profile('VoltaPure Water Company', 'qa@voltapure.test', '+233 27 444 1882', 'Ho Industrial Enclave, Volta Region, Ghana', 'FDA-GH-WTR-DEMO-077', '#1D4ED8', 'Verify your VoltaPure seal'),
                'categories' => [
                    ['name' => 'Bottled Water', 'slug' => 'bottled-water', 'description' => 'Packaged drinking water bottles and cartons.'],
                ],
                'products' => [
                    $this->product('VoltaPure Mineral Water 500ml', 'VP-MW-500', 'bottled-water', 'voltapure-water.svg', 'Packaged drinking water bottled for retail and wholesale distribution.', 'Treated drinking water.', 'Keep sealed until use. Store away from sunlight and chemicals.', [
                        ['question' => 'What should I check before drinking?', 'answer' => 'Confirm the cap seal is intact and scan the label QR if available.'],
                    ], [
                        ['type' => 'storage', 'short_label' => 'Keep sealed', 'detail' => 'Do not drink if the cap seal is broken.', 'severity' => 'caution'],
                    ], [
                        $this->batch('VP-MW-2026-011', '2026-05-14', '2027-05-14', 24000, 'Ho Bottling Line 1', 'approved', 'distributed', 'a8demo-voltapure-mw-011', 'active', '2027-05-14', 'security seal', '18mm x 18mm', 'delivered'),
                    ]),
                ],
            ],
            [
                'slug' => 'adinkra-spice-kitchen',
                'palette' => ['#B45309', '#FEF3C7'],
                'company' => [
                    'name' => 'Adinkra Spice Kitchen Ltd',
                    'slug' => 'adinkra-spice-kitchen',
                    'registration_number' => 'CS671902026',
                    'industry' => 'Food spices and condiments',
                    'contact_person' => 'Efua Dedei Quartey',
                    'email' => 'hello@adinkraspice.test',
                    'phone' => '+233 26 891 2003',
                    'country' => 'Ghana',
                    'region' => 'Western',
                    'city' => 'Takoradi',
                    'website' => 'https://adinkraspice.test',
                    'status' => 'active',
                    'notes' => 'Takoradi food processing demo company for condiments and spice jars.',
                ],
                'profile' => $this->profile('Adinkra Spice Kitchen', 'hello@adinkraspice.test', '+233 26 891 2003', 'Anaji Road Processing Yard, Takoradi, Ghana', 'FDA-GH-FOD-DEMO-332', '#B45309', 'Verify your Adinkra Spice product'),
                'categories' => [
                    ['name' => 'Condiments', 'slug' => 'condiments', 'description' => 'Shito, chilli sauces, and ready-to-use condiments.'],
                    ['name' => 'Spice Blends', 'slug' => 'spice-blends', 'description' => 'Dry spices for Ghanaian home cooking.'],
                ],
                'products' => [
                    $this->product('Smoked Tuna Shito', 'AS-ST-250', 'condiments', 'adinkra-shito.svg', 'Ghana-style black pepper sauce made with smoked tuna, chilli, ginger, garlic, and spices.', 'Vegetable oil, smoked tuna, chilli, onion, ginger, garlic, spices.', 'Use a clean dry spoon. Refrigerate after opening.', [
                        ['question' => 'Does it contain fish?', 'answer' => 'Yes. This product contains smoked tuna.'],
                    ], [
                        ['type' => 'allergen', 'short_label' => 'Contains fish', 'detail' => 'Not suitable for fish allergies.', 'severity' => 'warning'],
                    ], [
                        $this->batch('AS-ST-2026-006', '2026-05-03', '2027-05-03', 6000, 'Takoradi Sauce Room', 'approved', 'printed', 'a8demo-adinkra-st-006', 'active', '2027-05-03', 'security seal', '24mm x 24mm', 'printed'),
                    ]),
                    $this->product('Waakye Spice Blend', 'AS-WS-080', 'spice-blends', 'adinkra-spice.svg', 'Dry spice blend for waakye, stews, and rice dishes.', 'Sorghum leaves powder, cloves, bay leaf, pepper, onion powder, ginger.', 'Add gradually while cooking. Store dry after opening.', [
                        ['question' => 'Is it spicy?', 'answer' => 'It has mild heat and aromatic spice notes.'],
                    ], [
                        ['type' => 'storage', 'short_label' => 'Keep dry', 'detail' => 'Moisture can cause clumping.', 'severity' => 'info'],
                    ], [
                        $this->batch('AS-WS-2026-002', '2026-04-10', '2027-04-10', 4200, 'Takoradi Dry Pack Line', 'approved', 'distributed', 'a8demo-adinkra-ws-002', 'active', '2027-04-10', 'security seal', '22mm x 22mm', 'delivered'),
                    ]),
                ],
            ],
            [
                'slug' => 'tema-care-pharma',
                'palette' => ['#2563EB', '#EFF6FF'],
                'company' => [
                    'name' => 'TemaCare Pharma Ltd',
                    'slug' => 'tema-care-pharma',
                    'registration_number' => 'CS730552026',
                    'industry' => 'Pharmaceutical and wellness products',
                    'contact_person' => 'Dr. Nana Kofi Bediako',
                    'email' => 'pharmacovigilance@temacare.test',
                    'phone' => '+233 30 320 5018',
                    'country' => 'Ghana',
                    'region' => 'Greater Accra',
                    'city' => 'Tema',
                    'website' => 'https://temacare.test',
                    'status' => 'active',
                    'notes' => 'Tema Industrial Area demo company for regulated health product verification.',
                ],
                'profile' => $this->profile('TemaCare Pharma', 'pharmacovigilance@temacare.test', '+233 30 320 5018', 'Plot 48, Tema Industrial Estate, Greater Accra, Ghana', 'FDA-GH-PHR-DEMO-504', '#2563EB', 'Verify your TemaCare medicine'),
                'categories' => [
                    ['name' => 'Oral Liquids', 'slug' => 'oral-liquids', 'description' => 'Syrups and liquid health products.'],
                    ['name' => 'Powders', 'slug' => 'powders', 'description' => 'Powder sachets and reconstitution packs.'],
                ],
                'products' => [
                    $this->product('ZincPlus Vitamin Syrup', 'TC-ZV-100', 'oral-liquids', 'temacare-syrup.svg', 'Vitamin and zinc syrup demo product for pharmacy channel traceability.', 'Purified water, zinc gluconate, vitamin C, sorbitol, flavour.', 'Use only as directed on the product label or by a qualified professional.', [
                        ['question' => 'What should I do if the QR shows suspicious?', 'answer' => 'Do not use the product. Contact the pharmacy and TemaCare support.'],
                    ], [
                        ['type' => 'medical', 'short_label' => 'Follow label directions', 'detail' => 'Do not exceed recommended use.', 'severity' => 'warning'],
                        ['type' => 'age', 'short_label' => 'Check age guidance', 'detail' => 'Use according to the age instructions on the pack.', 'severity' => 'caution'],
                    ], [
                        $this->batch('TC-ZV-2026-010', '2026-04-26', '2027-10-26', 8500, 'Tema Industrial Estate Liquid Line', 'approved', 'printed', 'a8demo-temacare-zv-010', 'active', '2027-10-26', 'combo', '24mm x 24mm', 'printed'),
                    ]),
                    $this->product('HydraSalt ORS Sachet', 'TC-ORS-020', 'powders', 'temacare-ors.svg', 'Oral rehydration salts demo sachet pack for traceable pharmacy distribution.', 'Glucose, sodium chloride, potassium chloride, trisodium citrate.', 'Dissolve one sachet in the correct amount of clean water as stated on the label.', [
                        ['question' => 'Can I mix it with less water?', 'answer' => 'No. Follow the exact water volume on the label.'],
                    ], [
                        ['type' => 'usage', 'short_label' => 'Mix correctly', 'detail' => 'Incorrect mixing can affect safety.', 'severity' => 'warning'],
                    ], [
                        $this->batch('TC-ORS-2026-003', '2026-03-19', '2028-03-19', 16000, 'Tema Industrial Estate Powder Line', 'approved', 'distributed', 'a8demo-temacare-ors-003', 'active', '2028-03-19', 'combo', '18mm x 18mm', 'delivered'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function product(string $name, string $sku, string $categorySlug, string $image, string $description, string $ingredients, string $usage, array $faqs, array $suitability, array $batches): array
    {
        return compact('name', 'sku', 'categorySlug', 'image', 'description', 'ingredients', 'usage', 'faqs', 'suitability', 'batches') + [
            'category_slug' => $categorySlug,
            'usage_instructions' => $usage,
            'video_url' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function batch(string $batchNumber, string $manufactureDate, string $expiryDate, int $quantity, string $location, string $qaStatus, string $status, string $token, string $qrStatus, string $qrExpiresAt, string $labelType, string $labelSize, string $labelStatus, ?string $revokedAt = null, ?string $revocationReason = null): array
    {
        return [
            'batch_number' => $batchNumber,
            'manufacture_date' => $manufactureDate,
            'expiry_date' => $expiryDate,
            'quantity' => $quantity,
            'production_location' => $location,
            'qa_status' => $qaStatus,
            'status' => $status,
            'token' => $token,
            'qr_status' => $qrStatus,
            'qr_expires_at' => $qrExpiresAt,
            'label_type' => $labelType,
            'label_size' => $labelSize,
            'label_status' => $labelStatus,
            'printer_vendor' => collect(['SecurePrint Ghana', 'Tema HoloPrint', 'Adinkra Labels Ltd', 'TrustSeal Packaging'])->random(),
            'revoked_at' => $revokedAt,
            'revocation_reason' => $revocationReason,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(string $name, string $email, string $phone, string $address, string $certification, string $primaryColor, string $headline): array
    {
        return [
            'verification_headline' => $headline,
            'genuine_message' => 'This product is verified against an approved '.$name.' production batch.',
            'suspicious_message' => 'This QR code has unusual scan activity. Please contact support before using the product.',
            'invalid_message' => 'This verification code is not recognized by '.$name.'.',
            'support_text' => 'Share the batch number, product photos, and purchase location with our support team.',
            'support_email' => $email,
            'support_phone' => $phone,
            'website_url' => 'https://'.Str::slug($name).'.test',
            'social_links' => [
                ['label' => 'Instagram', 'url' => 'https://instagram.com/'.Str::slug($name, '')],
                ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/'.Str::slug($name)],
            ],
            'faq_items' => [
                ['question' => 'Why should I scan the QR code?', 'answer' => 'The QR code confirms the product and batch details recorded by the manufacturer.'],
                ['question' => 'What if the details do not match the package?', 'answer' => 'Do not use the product. Report the seller and contact support.'],
            ],
            'custom_sections' => [
                ['title' => 'Traceability Notice', 'body' => 'Each demo QR code is tied to one production batch and monitored for unusual scan patterns.'],
            ],
            'company_description' => $name.' is a Ghana-based demo manufacturer using Active8 to protect product trust across retail and distribution channels.',
            'manufacturer_name' => $name.' Manufacturing Unit',
            'factory_address' => $address,
            'certification_number' => $certification,
            'layout_style' => 'default',
            'primary_color' => $primaryColor,
            'secondary_color' => '#111827',
            'text_color' => '#111827',
            'background_color' => '#FFFFFF',
            'button_color' => $primaryColor,
            'button_text_color' => '#FFFFFF',
            'button_style' => 'solid',
            'font_family' => 'DM Sans',
            'badge_style' => 'official',
            'success_color' => '#16A34A',
            'warning_color' => '#F59E0B',
            'error_color' => '#DC2626',
            'show_platform_branding' => true,
        ];
    }

    private function seedAssets(array $companyItem): void
    {
        [$primary, $soft] = $companyItem['palette'];
        $slug = $companyItem['slug'];
        $name = $companyItem['company']['name'];

        Storage::disk('public')->put('demo/'.$slug.'-logo.svg', $this->svgLogo($name, $primary, $soft));
        Storage::disk('public')->put('demo/'.$slug.'-cover.svg', $this->svgCover($name, $primary, $soft, $companyItem['company']['city']));

        foreach ($companyItem['products'] as $product) {
            Storage::disk('public')->put('demo/'.$product['image'], $this->svgProduct($product['name'], $product['sku'], $primary, $soft));
        }
    }

    private function seedScanLogs($qrCodes): void
    {
        $locations = [
            ['Accra', 'Ghana', '102.176.65.20', 'Safari', 'mobile'],
            ['Tema', 'Ghana', '102.176.72.14', 'Chrome', 'mobile'],
            ['Kumasi', 'Ghana', '154.160.24.81', 'Chrome', 'mobile'],
            ['Tamale', 'Ghana', '197.251.45.18', 'Chrome', 'mobile'],
            ['Takoradi', 'Ghana', '41.66.212.9', 'Safari', 'mobile'],
            ['Ho', 'Ghana', '102.164.33.71', 'Firefox', 'desktop'],
            ['Cape Coast', 'Ghana', '41.210.14.22', 'Chrome', 'desktop'],
            ['Wa', 'Ghana', '197.253.12.66', 'Chrome', 'mobile'],
        ];
        $base = Carbon::parse('2026-05-20 08:15:00');

        foreach ($qrCodes->values() as $qrIndex => $qrCode) {
            $scanTotal = 4 + ($qrIndex % 7);

            for ($i = 0; $i < $scanTotal; $i++) {
                $location = $locations[($qrIndex + $i) % count($locations)];
                $status = match (true) {
                    $qrCode->status === 'revoked' => 'revoked',
                    $qrCode->isExpired() => 'expired',
                    $i === 5 && $qrIndex % 3 === 0 => 'suspicious',
                    default => 'genuine',
                };
                $riskFlags = collect([
                    'repeat_scan' => $i > 0,
                    'rapid_repeat_scan' => $i === 2 && $qrIndex % 2 === 0,
                    'same_ip_repeat' => $i === 3,
                    'same_device_repeat' => $i === 3,
                    'location_anomaly' => $i === 5 && $qrIndex % 3 === 0,
                ])->filter()->keys()->values()->all();
                $scannedAt = $base->copy()->addHours(($qrIndex * 9) + $i * 4);
                $fingerprint = hash('sha256', $qrCode->token.'|'.$location[2].'|'.$location[3]);

                ScanLog::query()->create([
                    'qr_code_id' => $qrCode->id,
                    'token_scanned' => $qrCode->token,
                    'status_result' => $status,
                    'scanned_at' => $scannedAt,
                    'ip_address' => $location[2],
                    'user_agent' => $location[4] === 'mobile'
                        ? 'Mozilla/5.0 (Linux; Android 14; Active8 Demo) AppleWebKit/537.36 Chrome/124.0 Mobile Safari/537.36'
                        : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; Active8 Demo) AppleWebKit/537.36 Chrome/124.0 Safari/537.36',
                    'country' => $location[1],
                    'city' => $location[0],
                    'device_type' => $location[4],
                    'browser' => $location[3],
                    'metadata' => [
                        'source' => 'active8_demo',
                        'scan_count' => $i + 1,
                        'previous_scan_at' => $i > 0 ? $scannedAt->copy()->subHours(4)->toIso8601String() : null,
                        'same_ip_scan_count' => $i === 3 ? 1 : 0,
                        'same_fingerprint_scan_count' => $i === 3 ? 1 : 0,
                        'fingerprint' => $fingerprint,
                        'geo_source' => 'seeded_demo',
                        'risk_flags' => $riskFlags,
                        'referer' => 'https://retailer-demo.active8.test/scan',
                        'accept_language' => 'en-GH,en;q=0.9',
                        'request_path' => 'verify/'.$qrCode->token,
                        'is_repeat_scan' => $i > 0,
                        'is_rapid_repeat_scan' => in_array('rapid_repeat_scan', $riskFlags, true),
                    ],
                ]);
            }
        }

        foreach ([
            ['Lagos', 'Nigeria', '197.210.54.18'],
            ['London', 'United Kingdom', '82.132.219.44'],
            ['Abidjan', 'Cote d Ivoire', '154.72.168.50'],
        ] as $index => [$city, $country, $ip]) {
            $token = 'unknown-demo-'.($index + 1).'-'.Str::lower(Str::random(8));

            ScanLog::query()->create([
                'qr_code_id' => null,
                'token_scanned' => $token,
                'status_result' => 'invalid',
                'scanned_at' => Carbon::parse('2026-05-31 11:00:00')->addHours($index * 3),
                'ip_address' => $ip,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36',
                'country' => $country,
                'city' => $city,
                'device_type' => 'desktop',
                'browser' => 'Chrome',
                'metadata' => [
                    'source' => 'active8_demo',
                    'scan_count' => 1,
                    'risk_flags' => ['unknown_token'],
                    'geo_source' => 'seeded_demo',
                    'reason' => 'Code not found in Active8 QR registry',
                    'fingerprint' => hash('sha256', $token.'|'.$ip),
                ],
            ]);
        }
    }

    private function seedActivityLogs(User $owner, $batches): void
    {
        foreach ($batches->values()->take(18) as $index => $batch) {
            $events = [
                ['batch.created', 'Batch '.$batch->batch_number.' created for '.$batch->product?->name.'.'],
                ['qr.generated', 'QR code generated for batch '.$batch->batch_number.'.'],
                ['qr.controls_updated', 'QR expiry controls reviewed for batch '.$batch->batch_number.'.'],
            ];
            $event = $events[$index % count($events)];

            ActivityLog::query()->create([
                'user_id' => $owner->id,
                'event' => $event[0],
                'subject_type' => Batch::class,
                'subject_id' => $batch->id,
                'description' => $event[1],
                'metadata' => [
                    'source' => 'active8_demo',
                    'batch_number' => $batch->batch_number,
                    'product_id' => $batch->product_id,
                    'status' => $batch->status,
                    'qa_status' => $batch->qa_status,
                    'qr_status' => $batch->qrCode?->status,
                    'company' => $batch->product?->company?->name,
                ],
                'created_at' => now()->subDays(12 - min($index, 11))->subHours($index),
                'updated_at' => now()->subDays(12 - min($index, 11))->subHours($index),
            ]);
        }
    }

    private function svgLogo(string $name, string $primary, string $soft): string
    {
        $initials = Str::of($name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('');

        return '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><rect width="512" height="512" rx="120" fill="'.$soft.'"/><circle cx="256" cy="256" r="162" fill="'.$primary.'" opacity=".14"/><rect x="146" y="146" width="220" height="220" rx="64" fill="'.$primary.'"/><text x="256" y="286" text-anchor="middle" font-family="Arial, sans-serif" font-size="96" font-weight="800" fill="#fff">'.$this->escape($initials).'</text></svg>';
    }

    private function svgCover(string $name, string $primary, string $soft, string $city): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="1400" height="520" viewBox="0 0 1400 520"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="'.$primary.'"/><stop offset="1" stop-color="#0f172a"/></linearGradient></defs><rect width="1400" height="520" fill="url(#g)"/><circle cx="1180" cy="110" r="220" fill="'.$soft.'" opacity=".18"/><circle cx="1040" cy="410" r="280" fill="#fff" opacity=".08"/><path d="M0 420 C260 320 470 500 700 390 S1120 260 1400 340 V520 H0Z" fill="#fff" opacity=".12"/><text x="80" y="190" font-family="Arial, sans-serif" font-size="58" font-weight="800" fill="#fff">'.$this->escape($name).'</text><text x="82" y="248" font-family="Arial, sans-serif" font-size="28" fill="#fff" opacity=".76">Verified Ghana production batches - '.$this->escape($city).'</text></svg>';
    }

    private function svgProduct(string $name, string $sku, string $primary, string $soft): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="900" viewBox="0 0 900 900"><rect width="900" height="900" rx="80" fill="'.$soft.'"/><circle cx="670" cy="220" r="170" fill="'.$primary.'" opacity=".16"/><rect x="250" y="175" width="400" height="550" rx="70" fill="#fff" stroke="'.$primary.'" stroke-width="10"/><rect x="300" y="245" width="300" height="110" rx="32" fill="'.$primary.'"/><text x="450" y="315" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" font-weight="800" fill="#fff">'.$this->escape($sku).'</text><text x="450" y="450" text-anchor="middle" font-family="Arial, sans-serif" font-size="46" font-weight="800" fill="#111827">'.$this->escape(Str::limit($name, 22, '')).'</text><path d="M330 585h240M350 635h200" stroke="'.$primary.'" stroke-width="22" stroke-linecap="round" opacity=".35"/></svg>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
