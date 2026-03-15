<?php

namespace App\Services;

use App\Models\PowerupCard;
use App\Models\User;
use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use Illuminate\Support\Facades\DB;

class PowerupService
{
    /**
     * Try to acquire a random power-up card for a user in a specific room.
     * Each student can only get one card per battle room.
     */
    public function tryAcquire(User $user, BattleRoom $room): ?PowerupCard
    {
        // Check if user already got a card in this room
        $existing = PowerupCard::where('user_id', $user->id)
            ->where('battle_room_id', $room->id)
            ->first();

        if ($existing) {
            return null;
        }

        $types = ['shield', 'boost', 'freeze'];
        $type = $types[array_rand($types)];

        return PowerupCard::create([
            'user_id'        => $user->id,
            'battle_room_id' => $room->id,
            'season_id'      => $user->current_season_id,
            'type'           => $type,
            'status'         => 'available',
            'acquired_at'    => now(),
        ]);
    }

    /**
     * Activate a power-up card for a participant.
     */
    public function activate(PowerupCard $card, BattleParticipant $participant): array
    {
        if ($card->status !== 'available' || $card->user_id !== $participant->user_id) {
            throw new \Exception('Power-up tidak tersedia atau bukan milik Anda.');
        }

        return DB::transaction(function () use ($card, $participant) {
            $effectDescription = '';

            switch ($card->type) {
                case 'shield':
                    $participant->active_powerup = 'shield';
                    $effectDescription = 'Pelindung aktif! Mengurangi beban mental pada kesalahan berikutnya.';
                    break;

                case 'boost':
                    // Move 2 steps forward
                    $participant->correct_count += 2;
                    $effectDescription = 'Boost aktif! Maju 2 langkah secara instan.';
                    break;

                case 'freeze':
                    // Freeze all other participants in the same room
                    BattleParticipant::where('battle_room_id', $participant->battle_room_id)
                        ->where('id', '!=', $participant->id)
                        ->update(['active_powerup' => 'freeze']);
                    $participant->active_powerup = 'none'; // Ensure activator is not frozen
                    $effectDescription = 'Freeze aktif! Lawan Anda membeku sementara.';
                    break;
            }

            // Update card status
            $card->update([
                'status'  => 'used',
                'used_at' => now(),
            ]);

            // Update participant stats
            $participant->powerup_used_count += 1;
            $participant->save();

            return [
                'type'   => $card->type,
                'effect' => $effectDescription,
            ];
        });
    }
}
