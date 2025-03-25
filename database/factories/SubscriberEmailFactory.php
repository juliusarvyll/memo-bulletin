<?php

namespace Database\Factories;

use App\Models\SubscriberEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriberEmailFactory extends Factory
{
    protected $model = SubscriberEmail::class;

    public function definition(): array
    {
        // Use a mix of more realistic domains that aren't RFC 2606 reserved
        $realDomains = [
            'gmail.com', 'yahoo.com', 'outlook.com',
            'hotmail.com', 'aol.com', 'icloud.com',
            'protonmail.com', 'mail.com', 'zoho.com'
        ];

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $domain = $this->faker->randomElement($realDomains);
        $customEmail = strtolower($firstName . '.' . $lastName . '@' . $domain);

        return [
            'email' => $customEmail,
            'name' => $this->faker->boolean(70) ? $firstName . ' ' . $lastName : null,
            'is_active' => $this->faker->boolean(80),
            'verified_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-3 months', 'now') : null,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function verified(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'verified_at' => now()->subDays(rand(1, 30)),
            ];
        });
    }

    public function unverified(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'verified_at' => null,
            ];
        });
    }
}
