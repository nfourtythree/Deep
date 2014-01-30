<?php

namespace rsanchez\Entries\Entity\Field;

use rsanchez\Entries\Db\DbInterface;
use rsanchez\Entries\Channel\Channel;
use rsanchez\Entries\Entity\Field;
use rsanchez\Entries\Entity\Field\Factory as EntityFieldFactory;
use rsanchez\Entries\Col\Factory as ColFactory;
use rsanchez\Entries\Entity\Collection as EntityCollection;
use rsanchez\Entries\Property\AbstractProperty;
use rsanchez\Entries\Entity\Entity;
use IteratorAggregate;

class Matrix extends Field implements IteratorAggregate
{
    public $total_rows = 0;
    protected $preload = true;
    protected $preloadHighPriority = true;

    public function __construct(
        $value,
        AbstractProperty $property,
        EntityCollection $entries,
        $entity = null,
        EntityFieldFactory $entryFieldFactory,
        ColFactory $colFactory
    ) {
        parent::__construct($value, $property, $entries, $entity);

        $this->entryFieldFactory = $entryFieldFactory;
        $this->colFactory = $colFactory;
    }

    public function __invoke()
    {
        return $this;
    }

    public function __toString()
    {
        return $this->total_rows ? (string) $this->total_rows : '';
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->value);
    }

    //@TODO move this to channelField
    public function preload(DbInterface $db, $entryIds, $fieldIds)
    {
        $query = $db->where_in('field_id', $fieldIds)
                    ->get('matrix_cols');

        $cols = $query->result();

        $query->free_result();

        $query = $db->where_in('field_id', $fieldIds)
                    ->where_in('entry_id', $entryIds)
                    ->order_by('entry_id asc, field_id asc, row_order asc')
                    ->get('matrix_data');

        $payload = array(
            'cols' => $cols,
        );

        foreach ($query->result() as $row) {
            if (! isset($payload[$row->entry_id])) {
                $payload[$row->entry_id] = array();
            }

            if (! isset($payload[$row->entry_id][$row->field_id])) {
                $payload[$row->entry_id][$row->field_id] = array();
            }

            $payload[$row->entry_id][$row->field_id][] = $row;
        }

        $query->free_result();

        return $payload;
    }

    public function hydrate($payload)
    {
        static $channelFields = array();

        $this->value = array();

        if (! isset($payload[$this->entity->entry_id][$this->property->id()])) {
            return;
        }

        $rows = $payload[$this->entity->entry_id][$this->property->id()];

        foreach ($payload['cols'] as $col) {
            if (! isset($channelFields[$col->col_id])) {
                $channelFields[$col->col_id] = $this->colFactory->createProperty($col);
            }
        }

        foreach ($rows as &$row) {
            foreach ($payload['cols'] as $col) {
                $property = 'col_id_'.$col->col_id;
                $value = property_exists($row, $property) ? $row->$property : '';
                $field = $this->entryFieldFactory->createField($value, $channelFields[$col->col_id], $this->entries, $this->entity);
                $row->{$col->col_name} = $field;
            }

            $this->total_rows++;

            $this->value[] = $row;
        }
    }
}
