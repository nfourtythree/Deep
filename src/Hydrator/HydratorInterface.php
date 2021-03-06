<?php

/**
 * Deep
 *
 * @package      rsanchez\Deep
 * @author       Rob Sanchez <info@robsanchez.com>
 */

namespace rsanchez\Deep\Hydrator;

use rsanchez\Deep\Model\Entry;

/**
 * Hydrator interface
 *
 * Hydrators bind custom fields properties to Entry objects
 */
interface HydratorInterface
{
    /**
     * Preload any custom field data that resides in another DB table
     * @param  array $entryIds all the entry IDs in the collection (including related entries)
     * @return void
     */
    public function preload(array $entryIds);

    /**
     * Hydrate an Entry's custom field(s) that match the current hydrator type
     * @param  Entry $entry
     * @return void
     */
    public function hydrate(Entry $entry);
}
