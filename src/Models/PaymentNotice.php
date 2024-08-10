<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated
 */
class PaymentNotice extends Model
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}
