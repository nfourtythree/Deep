<?php

/**
 * Deep
 *
 * @package      rsanchez\Deep
 * @author       Rob Sanchez <info@robsanchez.com>
 */

namespace rsanchez\Deep\Repository;

use rsanchez\Deep\Collection\ChannelCollection;
use rsanchez\Deep\Model\Channel;
use rsanchez\Deep\Repository\FieldRepository;

/**
 * Repository of all Channels
 */
class ChannelRepository
{
    /**
     * Collection of all Channels
     * @var \rsanchez\Deep\Collection\ChannelCollection
     */
    protected $collection;

    /**
     * Repository of all Fields
     * @var \rsanchez\Deep\Repository\FieldRepository
     */
    protected $fieldRepository;

    /**
     * Array of Channels keyed by channel_id
     * @var array
     */
    protected $channelsById = array();

    /**
     * Array of Channels keyed by channel_name
     * @var array
     */
    protected $channelsByName = array();

    /**
     * Constructor
     *
     * @param \rsanchez\Deep\Model\Channel              $model
     * @param \rsanchez\Deep\Repository\FieldRepository $fieldRepository
     */
    public function __construct(Channel $model, FieldRepository $fieldRepository)
    {
        $this->collection = $model->all();
        $this->fieldRepository = $fieldRepository;

        foreach ($this->collection as $channel) {
            $channel->fields = $this->fieldRepository->getFieldsByGroup($channel->field_group);
            $this->channelsById[$channel->channel_id] = $channel;
            $this->channelsByName[$channel->channel_name] = $channel;
        }
    }

    /**
     * Get Collection of Channels by channel ID
     *
     * @var array $channelIds
     * @return \rsanchez\Deep\Collection\ChannelCollection
     */
    public function getChannelsById(array $channelIds)
    {
        return $this->collection->filter(function ($channel) use ($channelIds) {
            return in_array($channel->channel_id, $channelIds);
        });
    }

    /**
     * Get Collection of Channels by channel name
     *
     * @var array $channelNames
     * @return \rsanchez\Deep\Collection\ChannelCollection
     */
    public function getChannelsByName(array $channelNames)
    {
        return $this->collection->filter(function ($channel) use ($channelNames) {
            return in_array($channel->channel_name, $channelNames);
        });
    }

    /**
     * Alias to getChannelById
     * @var int $id
     * @return \rsanchez\Deep\Model\Channel|null
     */
    public function find($id)
    {
        return $this->getChannelById($id);
    }

    /**
     * Get single Channel by channel ID
     *
     * @var int $channelId
     * @return \rsanchez\Deep\Model\Channel|null
     */
    public function getChannelById($channelId)
    {
        return array_key_exists($channelId, $this->channelsById) ? $this->channelsById[$channelId] : null;
    }

    /**
     * Get single Channel by channel name
     *
     * @var string $channelName
     * @return \rsanchez\Deep\Model\Channel|null
     */
    public function getChannelByName($channelName)
    {
        return array_key_exists($channelName, $this->channelsByName) ? $this->channelsByName[$channelName] : null;
    }
}
