<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Segment;
use App\Models\Product;

class SegmentSeeder extends Seeder
{
    public function run()
    {
        $segment = Segment::firstOrCreate([
            'code' => 'gudang-a-1'
        ], [
            'name' => 'Gudang A',
            'description' => 'Lokasi utama di lantai 1'
        ]);

        // Assign first product if exists
        $product = Product::first();
        if ($product) {
            $product->segment_id = $segment->id;
            $product->save();
        }
    }
}
