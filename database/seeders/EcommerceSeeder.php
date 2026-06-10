<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\User;
use App\Services\VariantGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        // --- Users ---
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'is_admin' => true],
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            ['name' => 'Customer', 'password' => Hash::make('password'), 'is_admin' => false],
        );

        // --- Attributes & values ---
        $color = Attribute::updateOrCreate(['slug' => 'color'], ['name' => 'Color', 'type' => 'swatch']);
        $storage = Attribute::updateOrCreate(['slug' => 'storage'], ['name' => 'Storage', 'type' => 'select']);

        $red   = AttributeValue::updateOrCreate(['attribute_id' => $color->id, 'value' => 'Red'], ['swatch' => '#ef4444']);
        $blue  = AttributeValue::updateOrCreate(['attribute_id' => $color->id, 'value' => 'Blue'], ['swatch' => '#3b82f6']);
        $g128  = AttributeValue::updateOrCreate(['attribute_id' => $storage->id, 'value' => '128GB'], []);
        $g256  = AttributeValue::updateOrCreate(['attribute_id' => $storage->id, 'value' => '256GB'], []);

        // --- Product with generated variants ---
        $product = Product::updateOrCreate(
            ['slug' => 'aurora-smartphone'],
            ['name' => 'Aurora Smartphone', 'description' => 'A flagship phone with vivid colours and ample storage.', 'is_active' => true],
        );

        // Use the same Cartesian generator the admin panel uses.
        $generator = new VariantGenerator();
        $combinations = $generator->cartesian([[$red, $blue], [$g128, $g256]]);

        $basePrices = ['128GB' => 49999, '256GB' => 59999];

        foreach ($combinations as $combo) {
            $values = collect($combo);
            $valueIds = $values->pluck('id')->sort()->values()->all();

            // Skip if this exact combination already exists.
            $exists = $product->variants()->with('attributeValues:id')->get()
                ->contains(fn ($v) => $v->attributeValues->pluck('id')->sort()->values()->all() === $valueIds);

            if ($exists) {
                continue;
            }

            $storageLabel = $values->firstWhere('attribute_id', $storage->id)->value;
            $sku = 'AURORA-'.$values->map(fn ($v) => Str::upper(Str::substr(Str::slug($v->value), 0, 3)))->join('-');

            $variant = $product->variants()->create([
                'sku'   => $sku,
                'price' => $basePrices[$storageLabel],
                'stock' => 25,
            ]);

            $variant->attributeValues()->sync($valueIds);
        }
    }
}
