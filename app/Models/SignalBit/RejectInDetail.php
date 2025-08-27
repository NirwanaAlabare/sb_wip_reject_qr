<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectInDetail extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_in_detail';

    protected $fillable = [
        'id',
        'reject_in_id',
        'reject_type_id',
        'reject_area_id',
        'created_at',
        'updated_at',
    ];

    public function rejectInOut()
    {
        return $this->belongsTo(RejectInOut::class, 'reject_in_id', 'id');
    }
}
