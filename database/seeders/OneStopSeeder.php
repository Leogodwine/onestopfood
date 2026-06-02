<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ChefProfile;
use App\Models\Meal;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Delivery;
use App\Models\Payment;
use Database\Seeders\Concerns\UsesSeedPassword;
use Illuminate\Database\Seeder;

class OneStopSeeder extends Seeder
{
    use UsesSeedPassword;

    public function run(): void
    {
        $seedPassword = $this->seedPasswordHash();

        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@onestop.com',
            'password' => $seedPassword,
            'role' => 'admin',
            'status' => 'approved',
        ]);

        // Create specific chefs from layout
        $chefs = [
            [
                'name' => 'Elena Rodriguez',
                'email' => 'elena@onestop.com',
                'cuisine_type' => 'Mediterranean',
                'years_experience' => '12',
                'bio' => 'Elena grew up in a small coastal town in Spain, where she learned traditional Mediterranean cooking from her grandmother. Her dishes celebrate fresh seafood, olive oil, and seasonal vegetables with vibrant flavors that transport you to the Mediterranean coast.',
                'heritage_story' => 'Born in a small coastal town in Spain, Elena learned traditional Mediterranean cooking from her grandmother. Her dishes celebrate fresh seafood, olive oil, and seasonal vegetables.',
                'specialties' => 'Mediterranean Master, Seafood Expert, Organic Specialist',
                'specialties_list' => ['Mediterranean Master', 'Seafood Expert', 'Organic Specialist'],
                'kitchen_address' => 'Coastal Kitchen, Masaki',
                'meals' => [
                    ['name' => 'Paella Valenciana', 'price' => 32, 'description' => 'Traditional Spanish paella with saffron rice, seafood, and vegetables', 'category' => 'Mediterranean', 'is_heritage' => true, 'is_popular' => true],
                    ['name' => 'Grilled Octopus', 'price' => 28, 'description' => 'Tender grilled octopus with olive oil, lemon, and herbs', 'category' => 'Mediterranean', 'is_heritage' => false, 'is_popular' => true],
                ],
            ],
            [
                'name' => 'Sarah Chen',
                'email' => 'sarah@onestop.com',
                'cuisine_type' => 'Asian Fusion',
                'years_experience' => '12',
                'bio' => 'Sarah combines traditional Asian techniques with modern presentation. Her innovative approach to fusion cuisine has earned her recognition in top culinary magazines. She spent 8 years studying culinary arts across Asia, mastering techniques from Japan, Korea, Thailand, and Vietnam.',
                'heritage_story' => 'Sarah spent 8 years studying culinary arts across Asia, mastering techniques from Japan, Korea, Thailand, and Vietnam. This fusion masterpiece was born from her childhood split between Seoul and Los Angeles.',
                'specialties' => 'Fusion Expert, James Beard Nominee, Sustainable Cooking, Innovation Award',
                'specialties_list' => ['Fusion Expert', 'James Beard Nominee', 'Sustainable Cooking', 'Innovation Award'],
                'kitchen_address' => 'East Side Bistro, Oysterbay',
                'meals' => [
                    ['name' => 'Korean BBQ Tacos', 'price' => 18, 'description' => 'Marinated bulgogi beef in soft tortillas with kimchi slaw and spicy mayo', 'category' => 'Asian Fusion', 'origin' => 'Seoul-LA Fusion Heritage', 'heritage_story' => '200-Year-Old Fermentation Pot', 'is_heritage' => true, 'is_popular' => true],
                    ['name' => 'Miso Glazed Salmon', 'price' => 26, 'description' => 'Fresh salmon glazed with miso and served with steamed rice', 'category' => 'Asian Fusion', 'is_heritage' => false, 'is_popular' => true],
                    ['name' => 'Fresh Sushi Platter', 'price' => 42, 'description' => 'Assorted nigiri and maki rolls with fresh wasabi and pickled ginger', 'category' => 'Japanese', 'origin' => 'Tokyo Tsukiji Tradition', 'is_heritage' => false, 'is_popular' => false],
                ],
            ],
            [
                'name' => 'Antoine Dubois',
                'email' => 'antoine@onestop.com',
                'cuisine_type' => 'French Cuisine',
                'years_experience' => '15',
                'bio' => 'Trained in Lyon and Paris under Michelin-starred chefs, Antoine brings classical French techniques to modern dining. His passion for French gastronomy is evident in every meticulously crafted dish, from delicate soufflés to rich coq au vin.',
                'heritage_story' => 'Trained in Lyon and Paris under Michelin-starred chefs, Antoine brings classical French techniques to modern dining.',
                'specialties' => 'Michelin Trained, French Master, Classical Techniques',
                'specialties_list' => ['Michelin Trained', 'French Master', 'Classical Techniques'],
                'kitchen_address' => 'Le Petit Bistro, Mikocheni',
                'meals' => [
                    ['name' => 'Coq au Vin', 'price' => 34, 'description' => 'Classic French chicken braised in wine with mushrooms and onions', 'category' => 'French', 'is_heritage' => true, 'is_popular' => true],
                    ['name' => 'Beef Bourguignon', 'price' => 38, 'description' => 'Slow-cooked beef in red wine with vegetables and herbs', 'category' => 'French', 'is_heritage' => true, 'is_popular' => true],
                ],
            ],
            [
                'name' => 'Marco Rodriguez',
                'email' => 'marco@onestop.com',
                'cuisine_type' => 'Italian Cuisine',
                'years_experience' => '15',
                'bio' => 'With over 15 years of experience in authentic Italian cooking, Marco brings the flavors of Tuscany to your table. Trained in Milan and Rome, he specializes in handmade pasta and traditional sauces using recipes passed down through generations.',
                'heritage_story' => 'Born in the hills of Umbria, this carbonara recipe was passed down through four generations. Marco learned from his nonna who learned from her nonna, preserving the authentic flavors of Italian tradition.',
                'specialties' => 'Michelin Trained, Pasta Expert, 15+ Years Experience',
                'specialties_list' => ['Michelin Trained', 'Pasta Expert', '15+ Years Experience'],
                'kitchen_address' => 'Downtown Kitchen, Upanga',
                'meals' => [
                    ['name' => 'Truffle Carbonara', 'price' => 28, 'description' => 'Handmade pasta with pancetta, egg yolk, parmesan, and black truffle shavings', 'category' => 'Italian', 'origin' => 'Umbrian Family Tradition', 'heritage_story' => '4th Generation Recipe', 'is_heritage' => true, 'is_popular' => true],
                    ['name' => 'Artisan Chocolate Cake', 'price' => 16, 'description' => 'Rich dark chocolate cake with ganache frosting and gold leaf garnish', 'category' => 'Desserts', 'origin' => 'Turin Chocolate Heritage', 'heritage_story' => 'Nonna\'s Legacy Recipe', 'is_heritage' => true, 'is_popular' => false],
                ],
            ],
            [
                'name' => 'James Thompson',
                'email' => 'james@onestop.com',
                'cuisine_type' => 'American BBQ',
                'years_experience' => '20',
                'bio' => 'Master of the grill with a passion for slow-cooked meats and homemade sauces. James brings authentic Southern BBQ traditions with his own modern twist. His 20-year journey started in Texas pit houses and led him to compete in national BBQ championships.',
                'heritage_story' => 'James discovered this ranch during his apprenticeship in Kobe, where he learned the art of premium beef preparation from master butchers.',
                'specialties' => 'BBQ Master, Sauce Specialist, National Champion, 20+ Years Experience',
                'specialties_list' => ['BBQ Master', 'Sauce Specialist', 'National Champion', '20+ Years Experience'],
                'kitchen_address' => 'Smokehouse Central, Kariakoo',
                'meals' => [
                    ['name' => 'Premium Wagyu Steak', 'price' => 65, 'description' => 'Grade A5 Wagyu beef with roasted vegetables and red wine reduction', 'category' => 'BBQ', 'origin' => 'Hyogo Prefecture, Japan', 'heritage_story' => 'Kobe Apprenticeship Legacy', 'is_heritage' => true, 'is_popular' => true],
                    ['name' => 'Smoked Brisket Platter', 'price' => 24, 'description' => '12-hour smoked brisket with house-made BBQ sauce and pickles', 'category' => 'BBQ', 'origin' => 'Texas Cattle Drive Tradition', 'is_heritage' => false, 'is_popular' => true],
                ],
            ],
        ];

        $createdChefs = [];
        foreach ($chefs as $chefData) {
            $chef = User::create([
                'name' => $chefData['name'],
                'email' => $chefData['email'],
                'password' => $seedPassword,
                'role' => 'chef',
                'status' => 'approved',
                'phone' => '+255 626 ' . rand(100000, 999999),
            ]);

            ChefProfile::create([
                'user_id' => $chef->id,
                'bio' => $chefData['bio'],
                'heritage_story' => $chefData['heritage_story'],
                'specialties' => $chefData['specialties'],
                'specialties_list' => $chefData['specialties_list'],
                'kitchen_address' => $chefData['kitchen_address'],
                'food_handler_certificate_no' => 'FH-' . strtoupper(substr($chefData['name'], 0, 3)) . rand(1000, 9999),
                'years_experience' => $chefData['years_experience'],
                'cuisine_type' => $chefData['cuisine_type'],
            ]);

            // Create location for chef
            Location::create([
                'user_id' => $chef->id,
                'label' => explode(', ', $chefData['kitchen_address'])[1] ?? 'Dar es Salaam',
                'address_line' => $chefData['kitchen_address'],
                'city' => 'Dar es Salaam',
                'region' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'latitude' => -6.8 + (rand(0, 100) / 1000),
                'longitude' => 39.2 + (rand(0, 100) / 1000),
                'is_primary' => true,
            ]);

            // Create meals for chef
            foreach ($chefData['meals'] as $mealData) {
                Meal::create([
                    'chef_id' => $chef->id,
                    'name' => $mealData['name'],
                    'description' => $mealData['description'],
                    'heritage_story' => $mealData['heritage_story'] ?? null,
                    'origin' => $mealData['origin'] ?? null,
                    'prep_time_minutes' => rand(15, 45),
                    'price' => $mealData['price'],
                    'category' => $mealData['category'],
                    'dietary_tags' => null,
                    'image_path' => 'images/' . fake()->randomElement(['food 01.jpeg', 'food 03.png', 'african food 01.jpg', 'african food 02.jpg']),
                    'is_available' => true,
                    'is_heritage' => $mealData['is_heritage'],
                    'is_popular' => $mealData['is_popular'],
                ]);
            }

            $createdChefs[] = $chef;
        }

        // Create customers
        $customers = User::factory()->count(20)->customer()->create();
        foreach ($customers as $customer) {
            Location::factory()->primary()->create(['user_id' => $customer->id]);
        }

        // Create travelers
        $travelers = User::factory()->count(5)->traveler()->create();
        foreach ($travelers as $traveler) {
            \App\Models\TravelerProfile::factory()->create(['user_id' => $traveler->id]);
            Location::factory()->primary()->create(['user_id' => $traveler->id]);
        }

        // Create orders with reviews
        foreach ($createdChefs as $chef) {
            $chefMeals = Meal::where('chef_id', $chef->id)->get();
            
            // Skip if chef has no meals
            if ($chefMeals->isEmpty()) {
                continue;
            }
            
            // Create 20-50 orders per chef
            $orderCount = rand(20, 50);
            for ($i = 0; $i < $orderCount; $i++) {
                $customer = $customers->random();
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'chef_id' => $chef->id,
                    'status' => fake()->randomElement(['delivered', 'delivered', 'delivered', 'delivered', 'delivered', 'ready', 'out_for_delivery']),
                    'special_instructions' => fake()->optional(0.3)->sentence(),
                    'subtotal' => 0,
                    'delivery_fee' => rand(5, 15),
                    'total' => 0,
                    'delivery_location_id' => $customer->locations()->first()->id ?? null,
                    'created_at' => now()->subDays(rand(0, 90)),
                ]);

                // Add order items
                $mealCount = min(rand(1, 3), $chefMeals->count());
                $selectedMeals = $chefMeals->random($mealCount);
                $subtotal = 0;
                foreach ($selectedMeals as $meal) {
                    $quantity = rand(1, 3);
                    $unitPrice = $meal->price;
                    $lineTotal = $quantity * $unitPrice;
                    $subtotal += $lineTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'meal_id' => $meal->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);
                }

                $order->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $order->delivery_fee,
                ]);

                // Create payment
                Payment::create([
                    'order_id' => $order->id,
                    'method' => fake()->randomElement(['mpesa', 'tigo', 'airtel', 'card']),
                    'status' => $order->status === 'delivered' ? 'paid' : fake()->randomElement(['paid', 'pending']),
                    'amount' => $order->total,
                    'provider_reference' => 'REF-' . strtoupper(uniqid()),
                ]);

                // Create delivery if order is ready or delivered
                if (in_array($order->status, ['ready', 'out_for_delivery', 'delivered'])) {
                    $traveler = $travelers->random();
                    Delivery::create([
                        'order_id' => $order->id,
                        'traveler_id' => $traveler->id,
                        'status' => $order->status === 'delivered' ? 'delivered' : ($order->status === 'out_for_delivery' ? 'picked_up' : 'assigned'),
                        'traveler_earning' => $order->delivery_fee * 0.8, // 80% to traveler
                    ]);
                }

                // Create review for delivered orders
                if ($order->status === 'delivered' && fake()->boolean(70)) {
                    Review::create([
                        'order_id' => $order->id,
                        'customer_id' => $customer->id,
                        'chef_id' => $chef->id,
                        'traveler_id' => $order->delivery?->traveler_id,
                        'chef_rating' => fake()->randomElement([4, 4, 4, 5, 5, 5, 5]), // Bias towards high ratings
                        'traveler_rating' => fake()->optional(0.5)->randomElement([4, 5]),
                        'comment' => fake()->optional(0.6)->sentence(rand(5, 15)),
                    ]);
                }
            }
        }
    }
}
