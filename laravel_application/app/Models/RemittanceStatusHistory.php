<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class RemittanceStatusHistory
 * @package App\Models
 */
class RemittanceStatusHistory extends Model
{
    public $table = 'remittance_status_history';

    public $timestamps = false;

    public $fillable = [
        'remittance_id',
        'status',
        'notes',
        'updated_by',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Get remittance
     */
    public function remittance()
    {
        return $this->belongsTo(Remittance::class, 'remittance_id');
    }

    /**
     * Get user who updated
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
