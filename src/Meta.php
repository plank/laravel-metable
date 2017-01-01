<?php

namespace Plank\Metable;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{

	protected $cachedValue;

	protected $guarded = ['id', 'metable_type', 'metable_id', 'type'];
	protected $attributes = [
		'type' => 'null',
		'value' => ''
	];

	public function metable()
	{
		return $this->morphTo();
	}

	public function getValueAttribute()
	{
		if (isset($this->cachedValue)) {
			return $this->cachedValue;
		}
		$handler = $this->getDataTypeRegistry()->getHandlerForType($this->type);
		return $this->cachedValue = $handler->unserializeValue($this->attributes['value']);
	}

	public function setValueAttribute($value)
	{
		$registry = $this->getDataTypeRegistry();

		$this->attributes['type'] = $registry->getTypeForValue($value);
		$this->attributes['value'] = $registry->getHandlerForType($this->type)->serializeValue($value);

		$this->cachedValue = null;
	}

	protected function getDataTypeRegistry()
	{
		return app('metable.datatype.registry');
	}
}
