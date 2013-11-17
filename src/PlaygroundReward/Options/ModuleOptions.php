<?php

namespace PlaygroundReward\Options;

class ModuleOptions
{
    /**
     * @var bool
     */
    protected $enableDefaultEntities = true;

    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;
    
    /**
     * drive path to story media files
     */
    protected $media_path = 'public/media/reward';
    
    /**
     * url path to story media files
     */
    protected $media_url = 'media/reward';

    /**
     * @param boolean $enableDefaultEntities
     */
    public function setEnableDefaultEntities($enableDefaultEntities)
    {
        $this->enableDefaultEntities = $enableDefaultEntities;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableDefaultEntities()
    {
        return $this->enableDefaultEntities;
    }
    
    /**
     * Set media path
     *
     * @param  string $media_path
     * @return \PlaygroundFlow\Options\ModuleOptions
     */
    public function setMediaPath($media_path)
    {
        $this->media_path = $media_path;
    
        return $this;
    }
    
    /**
     * @return string
     */
    public function getMediaPath()
    {
        return $this->media_path;
    }
    
    /**
     *
     * @param  string $media_url
     * @return \PlaygroundFlow\Options\ModuleOptions
     */
    public function setMediaUrl($media_url)
    {
        $this->media_url = $media_url;
    
        return $this;
    }
    
    /**
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->media_url;
    }
}
