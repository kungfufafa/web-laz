<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $samples = [
            [
                'title' => 'Kajian Tafsir Al-Fatihah',
                'youtube_id' => 'dQw4w9WgXcQ',
                'description' => 'Pembahasan makna dan kandungan Surat Al-Fatihah untuk kehidupan sehari-hari.',
            ],
            [
                'title' => 'Adab Menuntut Ilmu',
                'youtube_id' => 'ScMzIvxBSi4',
                'description' => 'Kajian ringkas tentang adab, niat, dan konsistensi dalam menuntut ilmu syar’i.',
            ],
            [
                'title' => 'Keutamaan Sedekah di Waktu Sulit',
                'youtube_id' => 'jNQXAC9IVRw',
                'description' => 'Motivasi beramal di masa sempit dan bagaimana sedekah membawa keberkahan.',
            ],
        ];

        $selected = fake()->randomElement($samples);

        return [
            'title' => $selected['title'],
            'youtube_id' => $selected['youtube_id'],
            'description' => $selected['description'],
            'thumbnail' => "https://img.youtube.com/vi/{$selected['youtube_id']}/hqdefault.jpg",
            'is_published' => fake()->boolean(),
        ];
    }
}
