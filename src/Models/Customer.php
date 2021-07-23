<?php

namespace Railroad\RailHelpScout\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Customer
 *
 * @package Railroad\RailHelpScout\Models
 *
 * @property integer $internal_id
 * @property integer $external_id
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'helpscout_customers';

    protected $primaryKey = 'internal_id';

    /**
     * Customer constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('railhelpscout.database_connection_name'));

        parent::__construct($attributes);
    }
}
