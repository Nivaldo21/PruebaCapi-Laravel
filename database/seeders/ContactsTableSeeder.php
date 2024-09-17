<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Phone;
use App\Models\Email;
use App\Models\Address;
use Faker\Factory;


class ContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::query()->delete();
        $faker = Factory::create();

        foreach (range(1, 5000) as $index) {
            $contact = Contact::create([
                'name' => $faker->name,
                'notes' => $faker->sentence,
                'birthday' => $faker->date,
                'website' => $faker->url,
                'company' => $faker->company
            ]);

            // Crear varios teléfonos, emails y direcciones
            foreach (range(1, rand(1, 3)) as $i) {
                Phone::create(['contact_id' => $contact->id, 'number' => $faker->phoneNumber]);
                Email::create(['contact_id' => $contact->id, 'email' => $faker->email]);
                Address::create([
                    'contact_id' => $contact->id,
                    'street' => $faker->streetAddress,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'zip' => $faker->postcode,
                    'country' => $faker->country
                ]);
            }
        }
    }
}
