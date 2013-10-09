<?php

namespace PlaygroundReward\Options;

class ModuleOptions implements
    ActionListOptionsInterface
{
    /**
     * @var string
     */
    protected $actionEntityClass = 'PlaygroundReward\Entity\Action';

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
     * @TODO: change "things" below
     * Array of "things" to show in the action list
     */
    protected $actionListElements = array('Id' => 'id', 'Name' => 'name', 'Subject' => 'subject', 'Verb' => 'verb', 'Target' => 'complement');

    protected $actionMapper = 'PlaygroundReward\Mapper\Action';

    public function setUserMapper($actionMapper)
    {
        $this->actionMapper = $actionMapper;
    }

    public function getUserMapper()
    {
        return $this->actionMapper;
    }

    public function setActionListElements(array $listElements)
    {
        $this->actionListElements = $listElements;
    }

    public function getActionListElements()
    {
        return $this->actionListElements;
    }

    /**
     * set user entity class name
     *
     * @param  string        $userEntityClass
     * @return ModuleOptions
     */
    public function setActionEntityClass($actionEntityClass)
    {
        $this->actionEntityClass = $actionEntityClass;

        return $this;
    }

    /**
     * get user entity class name
     *
     * @return string
     */
    public function getActionEntityClass()
    {
        return $this->actionEntityClass;
    }

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
