<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectInOut extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_in_out';

    protected $fillable = [
        'id',
        'reject_id',
        'kode_numbering',
        'status',
        'type',
        'output_type',
        'created_by',
        'created_by_username',
        'created_at',
        'updated_at',
        'reworked_at'
    ];

    public function reject()
    {
        return $this->hasOne(Reject::class, 'id', 'reject_id');
    }

    public function rejectPacking()
    {
        return $this->hasOne(RejectPacking::class, 'id', 'reject_id');
    }
}
