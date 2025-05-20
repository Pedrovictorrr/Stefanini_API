<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome',
        'descricao',
        'data_inicio',
        'data_termino',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
