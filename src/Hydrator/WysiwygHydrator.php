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
use rsanchez\Deep\Collection\EntryCollection;
use rsanchez\Deep\Model\Entry;
use rsanchez\Deep\Hydrator\AbstractHydrator;
use rsanchez\Deep\Repository\SiteRepository;
use rsanchez\Deep\Repository\UploadPrefRepositoryInterface;

/**
 * Hydrator for the WYSIWYG fields
 */
class WysiwygHydrator extends AbstractHydrator
{
    /**
     * UploadPref model repository
     * @var \rsanchez\Deep\Repository\SiteRepository
     */
    protected $siteRepository;

    /**
     * UploadPref model repository
     * @var \rsanchez\Deep\Repository\UploadPrefRepositoryInterface
     */
    protected $uploadPrefRepository;

    /**
     * {@inheritdoc}
     *
     * @param \rsanchez\Deep\Collection\EntryCollection               $collection
     * @param string                                                  $fieldtype
     * @param \rsanchez\Deep\Repository\SiteRepository                $siteRepository
     * @param \rsanchez\Deep\Repository\UploadPrefRepositoryInterface $uploadPrefRepository
     */
    public function __construct(EntryCollection $collection, $fieldtype, SiteRepository $siteRepository, UploadPrefRepositoryInterface $uploadPrefRepository)
    {
        parent::__construct($collection, $fieldtype);

        $this->siteRepository = $siteRepository;

        $this->uploadPrefRepository = $uploadPrefRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(Entry $entry)
    {
        $fieldtype = $this->fieldtype;
        $collection = $this->collection;
        $uploadPrefRepository = $this->uploadPrefRepository;

        $parse = array($this, 'parse');

        // loop through all this fields
        $entry->channel->fieldsByType($this->fieldtype)->each(function ($field) use ($entry, $parse) {

            $value = $entry->getAttribute('field_id_'.$field->field_id);

            $value = call_user_func($parse, $value);

            $entry->setAttribute($field->field_name, $value);

        });

        // loop through all matrix fields
        $entry->channel->fieldsByType('matrix')->each(function ($field) use ($collection, $entry, $fieldtype, $parse) {

            $entry->getAttribute($field->field_name)->each(function ($row) use ($collection, $entry, $field, $fieldtype, $parse) {

                $cols = $collection->getMatrixCols()->filter(function ($col) use ($field, $fieldtype) {
                    return $col->field_id === $field->field_id && $col->col_type === $fieldtype;
                });

                $cols->each(function ($col) use ($row, $parse) {
                    $value = $row->getAttribute('col_id_'.$col->col_id);

                    $value = call_user_func($parse, $value);

                    $row->setAttribute($col->col_name, $value);
                });

            });

        });

        // loop through all grid fields
        $entry->channel->fieldsByType('grid')->each(function ($field) use ($collection, $entry, $fieldtype, $parse) {

            $entry->getAttribute($field->field_name)->each(function ($row) use ($collection, $entry, $field, $fieldtype, $parse) {

                $cols = $collection->getGridCols()->filter(function ($col) use ($field, $fieldtype) {
                    return $col->field_id === $field->field_id && $col->col_type === $fieldtype;
                });

                $cols->each(function ($col) use ($row, $parse) {
                    $value = $row->getAttribute('col_id_'.$col->col_id);

                    $value = call_user_func($parse, $value);

                    $row->setAttribute($col->col_name, $value);
                });

            });

        });
    }

    /**
     * Parse a string for these values:
     *
     * {filedir_X}, {assets_X:file_name}, {page_X}
     *
     * @param  string $value WYSIWYG content
     * @return string
     */
    protected function parse($value)
    {
        preg_match_all('#{page_(\d+)}#', $value, $pageMatches);

        foreach ($pageMatches[1] as $i => $entryId) {
            if ($pageUri = $this->siteRepository->getPageUri($entryId)) {
                $value = str_replace($pageMatches[0][$i], $pageUri, $value);
            }
        }

        preg_match_all('#{filedir_(\d+)}#', $value, $filedirMatches);

        foreach ($filedirMatches[1] as $i => $id) {
            if ($uploadPref = $this->uploadPrefRepository->find($id)) {
                $value = str_replace($filedirMatches[0][$i], $uploadPref->url, $value);
            }
        }

        // this is all we need to do for now, since we are only supporting Assets locally, not S3 etc.
        preg_match_all('#{assets_\d+:(.*?)}#', $value, $assetsMatches);

        foreach ($assetsMatches[1] as $i => $url) {
            $value = str_replace($assetsMatches[0][$i], $url, $value);
        }

        return $value;
    }
}
