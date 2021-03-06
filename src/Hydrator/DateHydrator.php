<?php

/**
 * Deep
 *
 * @package      rsanchez\Deep
 * @author       Rob Sanchez <info@robsanchez.com>
 */

namespace rsanchez\Deep\Hydrator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use rsanchez\Deep\Model\Entry;
use rsanchez\Deep\Hydrator\AbstractHydrator;
use Carbon\Carbon;

/**
 * Hydrator for the Date fieldtype
 */
class DateHydrator extends AbstractHydrator
{
    /**
     * {@inheritdoc}
     */
    public function hydrate(Entry $entry)
    {
        $fieldtype = $this->fieldtype;
        $collection = $this->collection;

        // loop through all date fields
        $entry->channel->fieldsByType($this->fieldtype)->each(function ($field) use ($entry) {

            $date = $entry->getAttribute('field_id_'.$field->field_id);

            $entry->setAttribute($field->field_name, $date ? Carbon::createFromFormat('U', $date) : null);

        });

        // loop through all matrix fields
        $entry->channel->fieldsByType('matrix')->each(function ($field) use ($collection, $entry, $fieldtype) {

            $entry->getAttribute($field->field_name)->each(function ($row) use ($collection, $entry, $field, $fieldtype) {

                $cols = $collection->getMatrixCols()->filter(function ($col) use ($field, $fieldtype) {
                    return $col->field_id === $field->field_id && $col->col_type === $fieldtype;
                });

                $cols->each(function ($col) use ($row) {
                    $date = $row->getAttribute('col_id_'.$col->col_id);

                    $row->setAttribute($col->col_name, $date ? Carbon::createFromFormat('U', $date) : null);
                });

            });

        });

        // loop through all grid fields
        $entry->channel->fieldsByType('grid')->each(function ($field) use ($collection, $entry, $fieldtype) {

            $entry->getAttribute($field->field_name)->each(function ($row) use ($collection, $entry, $field, $fieldtype) {

                $cols = $collection->getGridCols()->filter(function ($col) use ($field, $fieldtype) {
                    return $col->field_id === $field->field_id && $col->col_type === $fieldtype;
                });

                $cols->each(function ($col) use ($row) {
                    $date = $row->getAttribute('col_id_'.$col->col_id);

                    $row->setAttribute($col->col_name, $date ? Carbon::createFromFormat('U', $date) : null);
                });

            });

        });
    }
}
