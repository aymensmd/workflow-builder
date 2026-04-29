<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Credential extends Model
{
    use HasFactory;
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'service_name',
        'encrypted_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function setEncryptedTokenAttribute(string $plainToken): void
    {
        $this->attributes['encrypted_token'] = Crypt::encryptString($plainToken);
    }

    public function getDecryptedTokenAttribute(): ?string
    {
        if (!isset($this->attributes['encrypted_token'])) {
            return null;
        }

        return Crypt::decryptString($this->attributes['encrypted_token']);
    }
}
