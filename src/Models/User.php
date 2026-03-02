<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Exceptions\EmailExistsException;
use App\ORM\Model;
use PDOException;

final class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['name', 'email'];

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if (isset($user->name) && is_string($user->name)) {
                $user->name = trim($user->name);
            }
            if (isset($user->email) && is_string($user->email)) {
                $user->email = strtolower(trim($user->email));
            }
        });
    }

    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        $perPage = min(max(1, $perPage), 100);
        $paginator = static::query()
            ->latest('id')
            ->paginate($perPage, $page);

        $paginator['data'] = array_map(
            static fn (self $user) => $user->toArray(),
            $paginator['data']
        );
        return $paginator;
    }

    public static function createUnique(string $name, ?string $email = null): self
    {
        $attempts = $email === null ? 5 : 1;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $candidate = $email ?? static::generateEmail();

            try {
                return static::create([
                    'name' => $name,
                    'email' => $candidate,
                ]);
            } catch (PDOException $e) {
                if (!static::isUniqueViolation($e)) {
                    throw $e;
                }

                if ($email !== null || $attempt === $attempts) {
                    throw new EmailExistsException('email_exists', 0, $e);
                }
            }
        }

        throw new EmailExistsException('email_exists');
    }

    public static function isUniqueViolation(PDOException $e): bool
    {
        $code = (string) $e->getCode();
        if ($code === '23000' || $code === '23505') {
            return true;
        }

        $message = strtolower($e->getMessage());
        return str_contains($message, 'unique') || str_contains($message, 'duplicate');
    }

    private static function generateEmail(): string
    {
        try {
            return 'user_' . bin2hex(random_bytes(8)) . '@example.com';
        } catch (\Throwable) {
            return 'user_' . uniqid('', true) . '@example.com';
        }
    }
}
