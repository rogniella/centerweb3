<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cristal extends Model
{
    use HasFactory;

    protected $table = 'cristales';

    protected $primaryKey = 'Cri_rango_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'Cri_familia',
        'Cri_id',
        'Cri_esf',
        'Cri_esf_neg',
        'Cri_cil',
        'Cri_cil_neg',
        'Cri_add',
        'Cri_rango',
        'Cri_metodo',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'Cri_esf' => 'float',
        'Cri_esf_neg' => 'float',
        'Cri_cil' => 'float',
        'Cri_cil_neg' => 'float',
        'Cri_add' => 'float',
        'Cri_rango' => 'integer',
        'Cri_metodo' => 'integer',
    ];
}
