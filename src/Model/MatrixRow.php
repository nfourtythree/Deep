<?php

/**
 * Deep
 *
 * @package      rsanchez\Deep
 * @author       Rob Sanchez <info@robsanchez.com>
 */

namespace rsanchez\Deep\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use rsanchez\Deep\Collection\MatrixRowCollection;
use rsanchez\Deep\Collection\MatrixColCollection;

/**
 * Model for the matrix_data table
 */
class MatrixRow extends Model
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $table = 'matrix_data';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $primaryKey = 'row_id';

    /**
     * {@inheritdoc}
     */
    protected $hidden = array('site_id', 'entry_id', 'field_id', 'var_id', 'is_draft', 'row_order');

    /**
     * Cols associated with this row
     * @var \rsanchez\Deep\Collection\MatrixColCollection
     */
    protected $cols;

    /**
     * {@inheritdoc}
     *
     * @param  array                                         $models
     * @return \rsanchez\Deep\Collection\MatrixRowCollection
     */
    public function newCollection(array $models = array())
    {
        return new MatrixRowCollection($models);
    }

    /**
     * Filter by Entry ID
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int|array                             $entryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEntryId(Builder $query, $entryId)
    {
        $entryId = is_array($entryId) ? $entryId : array($entryId);

        return $query->whereIn('matrix_data.entry_id', $entryId);
    }

    /**
     * Set the Matrix columns for this row
     *
     * @param  \rsanchez\Deep\Collection\MatrixColCollection $cols
     * @return void
     */
    public function setCols(MatrixColCollection $cols)
    {
        $this->cols = $cols;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $hidden =& $this->hidden;

        $this->cols->each(function ($col) use (&$hidden) {
            $hidden[] = 'col_id_'.$col->col_id;
        });

        $array = parent::toArray();

        foreach ($array as &$row) {
            if (method_exists($row, 'toArray')) {
                $row = $row->toArray();
            }
        }

        return $array;
    }
}
