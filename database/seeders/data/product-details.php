<?php

/*
 * Product details copied from the live safebuystore.com product pages (English text, SKU, stock, tag).
 * Keyed by the image base name used in CatalogSeeder.
 * Blocks: ['h', text] heading, ['p', text] paragraph, ['ul', [items]] bullet list, ['ol', [items]] numbered steps.
 */
return [
    'christian-dean-sun-cream' => [
        'sku' => '8809469776578', 'stock' => 199, 'tag' => 'Sunscreen', 'size' => '70ml',
        'summary' => 'Christian Dean Secret Tone Up Sun Cream SPF50+ PA+++ (70ml) is a multi-functional pink-tinted sunscreen and makeup base that brightens the complexion while protecting against UV rays.',
        'description' => [
            ['h', 'Key Details'],
            ['ul', [
                'Size: 70ml',
                'Protection: SPF50+ PA+++ broad-spectrum UV defense',
                'Finish: Light pink cream formulation that gives an instant bright, glowing tone and neutralizes yellow undertones',
                'Dual Function: Acts as both a sunscreen and a smoothing makeup base/primer',
            ]],
            ['h', 'Key Ingredients'],
            ['ul', [
                'Glutathione & Vitamin C: Provide vitality and a visible brightening effect',
                '12 Herbal/Plant Complexes: Offer soothing and sedative benefits to the skin',
                'Galactomyces & Hyaluronic Acid: Deliver deep, long-lasting moisture and hydration',
            ]],
        ],
        'specs' => ['Size' => '70ml', 'Protection' => 'SPF50+ PA+++'],
    ],

    'beaute-glutathione-cream' => [
        'sku' => '8809715721574', 'stock' => 96, 'tag' => 'Moisturizer', 'size' => '45ml',
        'summary' => 'Beaute Melasma-X Glutathione Brightening Tone Up Cream (45ml) is a Korean dual-function skincare treatment designed to provide an instant brightening glow while actively fading stubborn hyperpigmentation, dark spots, and melasma.',
        'description' => [
            ['h', 'Key Ingredients'],
            ['ul', [
                'Glutathione (100ppm): A powerful antioxidant that suppresses melanin synthesis to lighten dark spots.',
                'Niacinamide: Evens out skin tone, strengthens the moisture barrier, and improves overall texture.',
                'Arbutin: Works synergistically to reduce deep pigmentation and freckles.',
                'Centella Asiatica & Green Tea Extracts: Soothes irritated skin, reduces redness, and offers anti-inflammatory benefits.',
                'Hyaluronic Acid & Glycerin: Delivers deep cellular hydration to keep skin plump and supple.',
            ]],
            ['h', 'Main Benefits'],
            ['ul', [
                'Provides an immediate, natural tone-up effect upon application.',
                'Targets chronic skin concerns like melasma, age spots, freckles, and post-acne marks.',
                'Offers dual care for skin whitening and anti-aging/wrinkle management.',
                'Features a lightweight texture that absorbs quickly without a greasy residue.',
            ]],
        ],
        'how_to_use' => [
            ['ol', [
                'Cleanse your face with a gentle cleanser.',
                'Apply your preferred toner to balance skin pH.',
                'Dispense a small amount of the cream and massage evenly into the skin, paying close attention to pigmented areas.',
            ]],
        ],
        'specs' => ['Size' => '45ml'],
    ],

    'beaute-3d-renewal-cream' => [
        'sku' => '8809715720751', 'stock' => 164, 'tag' => 'Moisturizer', 'size' => '40ml',
        'summary' => 'The Beaute Melasma-X 3D Whitening Clinic Renewal Cream 40ml is a functional brightening cream formulated to reduce melasma, dark spots, and uneven pigmentation. Its lightweight, non-greasy formula absorbs quickly to deliver hydration while helping to tone and clarify the complexion.',
        'description' => [
            ['h', 'Key Benefits'],
            ['ul', [
                'Diminishes melasma, freckles, and age spots',
                'Brightens dull skin for a radiant tone',
                'Deeply hydrates and moisturizes',
                'Improves skin elasticity and texture',
            ]],
            ['h', 'Main Ingredients'],
            ['ul', [
                'Niacinamide: Functional whitening agent that targets hyperpigmentation',
                'Botanical Extracts: Includes ingredients like mulberry root and centella asiatica to soothe and repair skin',
            ]],
        ],
        'how_to_use' => [
            ['ul', [
                'Cleanse and tone your face',
                'Apply a small amount of cream evenly to your face and neck',
                'Gently massage until fully absorbed',
                'Use twice daily (morning and night)',
                'Follow up with a broad-spectrum sunscreen (SPF 30 or higher) during the day',
            ]],
        ],
        'specs' => ['Size' => '40ml'],
    ],

    'skin1004-sun-serum' => [
        'sku' => '8809576261592', 'stock' => 289, 'tag' => 'Serum', 'size' => '50ml', 'skin' => 'All skin types, especially sensitive and dry',
        'summary' => 'The SKIN1004 Madagascar Centella Hyalu-Cica Water-Fit Sun Serum SPF 50 PA (50ml) is an ultra-lightweight, serum-like chemical sunscreen. It delivers deep hydration, broad-spectrum UV protection, and soothing care with a dewy, non-sticky finish and zero white cast.',
        'description' => [
            ['h', 'Key Features & Benefits'],
            ['ul', [
                'High UV Defense: Provides strong SPF 50 PA protection against UVA and UVB rays.',
                'Hyalu-Cica Formula: Infused with Madagascar Centella Asiatica to calm and soothe irritated skin, alongside Hyaluronic Acid for deep, lasting hydration.',
                'Reef-Safe & Vegan: Formulated without harmful ingredients that damage marine ecosystems.',
                'Lightweight Texture: A breathable, water-based serum consistency that absorbs quickly and wears beautifully under makeup.',
                'Skin Type Suitability: Ideal for all skin types, especially sensitive and dry skin.',
            ]],
            ['h', 'Key Ingredients'],
            ['ul', [
                'Centella Asiatica Extract: Reduces redness, provides anti-inflammatory benefits, and heals damaged skin.',
                'Hyaluronic Acid: Enhances skin\'s moisture retention for a plump, smooth complexion.',
                'Niacinamide: Brightens the skin and supports a healthy, balanced complexion.',
            ]],
        ],
        'specs' => ['Size' => '50ml', 'Protection' => 'SPF 50 PA'],
    ],

    'faceshop-rice-cleanser' => [
        'tag' => 'Facewash', 'skin' => 'Normal, oily, combination and sensitive',
        'summary' => 'The Face Shop Rice Water Bright Foaming Cleanser is a popular, gentle K-beauty facial cleanser. Infused with nutrient-rich rice extract (Vitamins A, B, E) and soapwort, its whipped-cream texture deeply removes dirt, excess oil, and makeup without stripping natural moisture, for a luminous complexion.',
        'description' => [
            ['h', 'Key Benefits'],
            ['ul', [
                'Deep Cleanses: Effectively washes away daily impurities and is ideal as the second step in a double-cleansing routine.',
                'Brightens Skin: Rice water extracts help even out skin tone and diminish dullness for a radiant, healthy glow.',
                'Hydrates & Softens: The creamy formula leaves your skin feeling soft, supple, and nourished.',
            ]],
            ['h', 'Specifications & Ingredients'],
            ['ul', [
                'Key Ingredients: Rice Water Extract, Soapwort, and Moringa Oil.',
                'Skin Type: Suitable for normal, oily, combination and sensitive skin types.',
            ]],
        ],
        'how_to_use' => [
            ['ol', [
                'Lather an adequate amount in your palms with lukewarm water.',
                'Gently massage onto your face in circular motions.',
                'Rinse thoroughly with lukewarm water and pat dry.',
            ]],
        ],
        'specs' => ['Key ingredients' => 'Rice Water Extract, Soapwort, Moringa Oil'],
        // Two real sizes, sold on the old site as two separate products.
        'variants' => [
            ['size' => '100ml', 'sku' => '8800335741010', 'price' => 735, 'old' => 1050, 'stock' => 378, 'img' => 'faceshop-rice-cleanser-100'],
            ['size' => '150ml', 'sku' => '8800335745940', 'price' => 999, 'old' => 1150, 'stock' => 307, 'img' => 'faceshop-rice-cleanser-150'],
        ],
    ],

    'missha-sun-milk' => [
        'sku' => '8809581452367', 'stock' => 198, 'tag' => 'Sunscreen', 'size' => '70ml',
        'summary' => 'The Missha All Around Safe Block Soft Finish Sun Milk SPF50+ PA+++ is a lightweight, hybrid daily sunscreen that protects skin from UVA and UVB rays without feeling greasy or heavy. Infused with silica and porous powders, it absorbs excess oil for a silky, powdery matte finish.',
        'description' => [
            ['h', 'Why People Like This Sun Milk'],
            ['p', 'The Soft Finish Sun Milk is a popular choice for daily wear, especially in hot and humid climates.'],
            ['ul', [
                'Broad Spectrum Defense: SPF50+ (UVB) and PA+++ (UVA) shield the skin from sunburn and premature aging.',
                'Matte & Tone-Up Effect: Leaves a smooth, shine-free base that works well as a makeup primer, with a subtle, natural brightening effect.',
                'Water & Sweat Resistant: A double-layer polymer system creates a resilient barrier against water and sweat, ideal for outdoor activities.',
                'Nourishing Formula: Contains a "Natural Barrier Complex" of botanical extracts to keep the skin hydrated while controlling shine.',
            ]],
        ],
        'how_to_use' => [
            ['ol', [
                'Shake the bottle well 4-5 times before use.',
                'Apply a proper amount evenly over the face as the final step of your morning skincare routine.',
                'Gently pat the sun milk into your skin until fully absorbed.',
                'Reapply as needed for extended periods of outdoor sun exposure.',
            ]],
        ],
        'specs' => ['Size' => '70ml', 'Protection' => 'SPF50+ PA+++'],
    ],

    'skin1004-poremizing-ampoule' => [
        'sku' => '8809576261875', 'stock' => 200, 'tag' => 'Serum', 'size' => '30ml', 'skin' => 'Oily, combination and sensitive',
        'summary' => 'The SKIN1004 Madagascar Centella Poremizing Fresh Ampoule (30ml) is a lightweight, non-sticky serum designed to balance oil production and visibly minimize enlarged pores. Formulated with soothing Centella Asiatica, purifying Himalayan Pink Salt, and a firming 9-Peptide Complex.',
        'description' => [
            ['h', 'Key Features & Benefits'],
            ['ul', [
                'Pore Minimizing & Cleansing: Enriched with mineral-rich Himalayan Pink Salt, it clears out impurities and dead skin cells from deep within the pores.',
                'Soothing & Repairing: A high concentration of Centella Asiatica Extract calms redness, repairs damaged skin barriers, and hydrates.',
                'Improves Elasticity: A 9-Peptide complex aids collagen synthesis, helping to tighten stretched-out pores.',
                'Skin Type: Ideal for oily, combination, and sensitive skin types.',
                'Texture: Watery, fast-absorbing formula with a soft, matte finish that never feels heavy or greasy.',
            ]],
        ],
        'how_to_use' => [
            ['p', 'After cleansing and toning, use the dropper to apply a few drops to your face. Gently pat and press the ampoule into the skin until fully absorbed. Gentle enough for daily use, morning and evening.'],
        ],
        'specs' => ['Size' => '30ml'],
    ],

    '3w-clinic-uv-sunblock' => [
        'sku' => '8809192574892', 'stock' => 87, 'tag' => 'Sunscreen', 'size' => '70ml', 'skin' => 'All skin types, including sensitive and dry',
        'summary' => 'The 3W Clinic Intensive UV Sunblock Cream SPF50+ PA+++ (70ml) is a popular Korean skincare product that provides broad-spectrum protection against UVA and UVB rays. It works both as an everyday sunscreen and a hydrating makeup base.',
        'description' => [
            ['h', 'Key Features and Benefits'],
            ['ul', [
                'High Sun Protection: Rated SPF 50+ and PA+++ to defend against sunburn, premature aging, and dark spots.',
                'Soothing and Hydrating: Enriched with Aloe Barbadensis Leaf Extract, Hydrolyzed Collagen, and Red Ginseng to calm irritation and provide deep moisture without a greasy finish.',
                'Dual Functionality: A sunblock and a lightweight makeup base that subtly evens out skin tone without an unnatural white cast.',
            ]],
        ],
        'how_to_use' => [
            ['ol', [
                'As the final step of your basic skincare routine, take an adequate amount onto your fingertips.',
                'Gently spread and pat it evenly over the face, neck, and other areas exposed to UV rays.',
                'Reapply throughout the day if you spend extended time outdoors.',
            ]],
        ],
        'specs' => ['Brand origin' => 'South Korea', 'Size' => '70ml', 'Protection' => 'SPF50+ PA+++'],
    ],

    'joyroom-fc2-watch' => [
        'sku' => 'DS:260627', 'stock' => 8, 'tag' => 'Smart watch', 'color' => 'Space Grey',
        'summary' => 'The JOYROOM JR-FC2 Pro Classic Series Smart Watch (Space Grey) is an affordable wearable that blends a sleek, classic circular design with advanced everyday smart and fitness features.',
        'description' => [
            ['h', 'Key Features & Specifications'],
            ['ul', [
                'Display: 1.46-inch high-definition TFT screen, 360 × 360 resolution, 2.5D tempered glass.',
                'Design: Ergonomic zinc alloy body with a smart crown. Weighs 44g and measures 48mm.',
                'Bluetooth Calling: Bluetooth 5.4 and JOYROOM noise reduction for clear, hands-free on-wrist calls.',
                'Health & Fitness: Monitors heart rate, blood pressure, blood oxygen (SpO2), stress, and sleep. Over 120 sports modes with auto-recognition.',
                'Battery Life: 350mAh battery, up to 12 days standby or about 5 to 7 days of typical use. Charges fully in about 2 hours via a magnetic dock.',
                'Water Resistance: Rated 1 ATM, withstands splashes and brief immersion.',
            ]],
        ],
        'specs' => ['Display' => '1.46" TFT, 360 × 360', 'Bluetooth' => '5.4', 'Battery' => '350mAh', 'Weight' => '44g', 'Water resistance' => '1 ATM'],
    ],

    'anker-a30i-earbuds' => [
        'sku' => 'Code: 998', 'stock' => 34, 'tag' => 'Earbuds', 'color' => 'Black',
        'summary' => 'A chic lipstick-shaped case that makes a statement. The Soundcore A30i earbuds are tiny, lightweight and ergonomically designed for an optimal fit that feels virtually weightless.',
        'description' => [
            ['ul', [
                'Strong and Smart Noise Cancelling: Blocks out noise up to 46dB. Adaptive algorithms detect external sound and auto-adjust the noise cancelling level.',
                'Clear Sound with 3D Surround Sound Effect: Composite diaphragms for clear, well-balanced sound. Turn on 3D surround in the Soundcore app.',
                'AI-Enhanced Clear Calls: 4 built-in mics with an AI algorithm trained to pick up your voice precisely.',
                'Easy to Use: Stable Bluetooth 5.4 connection. Multipoint lets you connect your laptop and phone and switch between them. Use a single earbud when needed.',
            ]],
        ],
        'specs' => ['Noise cancelling' => 'Up to 46dB', 'Microphones' => '4', 'Bluetooth' => '5.4, multipoint'],
    ],

    'hoco-cw55-dock' => [
        'sku' => 'Code: 702', 'stock' => 27, 'tag' => 'Wireless Charging',
        'summary' => 'The Hoco CW55 Magnetic Charging Dock is an ultra-compact, travel-friendly wireless charger for Apple Watch. It has a strong magnetic connection and dual inputs (Type-C and Lightning), delivering 2.5W charging.',
        'description' => [
            ['h', 'Standout Features'],
            ['ul', [
                'Magnetic Alignment: A powerful magnetic module snaps to the back of the watch for a stable connection.',
                'Highly Portable: Only 19g, this tiny puck fits in your pocket for charging from a laptop or power bank.',
                'Dual Ports: Both USB-C and Lightning inputs, so you can use whichever cable you have.',
            ]],
        ],
        'specs' => [
            'Compatibility' => 'Apple Watch Series 1–9, SE/SE 2, Ultra/Ultra 2',
            'Input' => 'Type-C and Lightning (5V / 1A)',
            'Wireless output' => '2.5W',
            'Material' => 'Aluminum alloy and PC',
            'Dimensions' => '33 × 33 × 9mm',
            'Weight' => '19g',
        ],
    ],

    'hoco-j160b-powerbank' => [
        'sku' => 'Code: 1613', 'stock' => 86, 'tag' => 'Power Bank',
        'summary' => 'The Hoco J160B PD20W 20000mAh Magnetic Power Bank combines MagSafe-style wireless charging with 20W wired fast charging, in a sleek aluminum alloy shell with a digital LED battery display.',
        'description' => [
            ['ul', [
                'Capacity: 20,000mAh (74Wh).',
                'Magnetic wireless charging up to 15W (7.5W for iPhone).',
                '20W PD fast charging over USB-C.',
                'LED digital percentage screen shows exactly how much power is left.',
            ]],
        ],
        'specs' => [
            'Model' => 'J160B',
            'Total capacity' => '20,000mAh / 74Wh',
            'Wireless output' => '5W / 7.5W / 10W / 15W',
            'USB-C output' => '20W PD',
            'USB-C input' => '18W max',
            'Material' => 'Aluminum alloy + flame-retardant PC',
            'Dimensions' => '~115 × 75 × 29 mm',
            'Weight' => '~365 g',
        ],
    ],
];
