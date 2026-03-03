<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParkingSlot;

class ParkingSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
    {
        $locationId = 1; 

        for ($i = 1; $i <= 2; $i++) {
            if ($i <= 5) {
                $type = 'compact';
            } elseif ($i <= 15) {
                $type = 'large';
            } elseif ($i <= 18) {
                $type = 'electric';
            } else {
                $type = 'pwd';
            }

            $status = 'available';
            if ($i == 1 || $i == 5) {
                $status = 'occupied';
            } elseif ($i == 3) {
                $status = 'reserved';
            }

            ParkingSlot::create([
                'location_id' => $locationId,
                'slot_number' => $i,
                'type'        => $type,
                'status'      => $status,
            ]);
        }
    }
}
