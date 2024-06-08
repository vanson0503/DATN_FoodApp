<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class ReviewTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a Faker instance
        $faker = Faker::create();

        // Get the IDs of all customers, products, and orders
        $customerIds = DB::table('customer')->pluck('id');
        $productIds = DB::table('product')->pluck('id');
        $orderIds = DB::table('orders')->pluck('id');

        // Create 1000 random review records
        for ($i = 0; $i < 1000; $i++) {
            DB::table('review')->insert([
                'customer_id' => $faker->randomElement($customerIds),
                'product_id' => $faker->randomElement($productIds),
                'orders_id' => $faker->randomElement($orderIds), // Fetch random orders_id from orders table
                'rate' => $faker->numberBetween(1, 5),
                'content' => $faker->text,
                'created_time' => now(),
                'updated_time' => now(),
            ]);
        }
    }
}
