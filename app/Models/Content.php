<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Content
{
    public static function faqs(): array
    {
        $grouped = [];
        foreach (Database::all('SELECT * FROM iw_faqs ORDER BY sort_order, id') as $f) {
            $grouped[$f['category']][] = $f;
        }
        return $grouped;
    }

    public static function saveMessage(array $data): void
    {
        Database::insert('iw_contact_messages', $data);
    }

    public static function messages(): array
    {
        return Database::all('SELECT * FROM iw_contact_messages ORDER BY handled, created_at DESC LIMIT 300');
    }

    public static function markHandled(int $id): void
    {
        Database::update('iw_contact_messages', ['handled' => 1], 'id = :id', ['id' => $id]);
    }
}
