<?php

namespace AppBundle\Entity;

class Card implements \Serializable
{
    private function snakeToCamel($snake)
    {
        $parts = explode('_', $snake);
        return implode('', array_map('ucfirst', $parts));
    }

    public function serialize()
    {
        $serialized = [];
        if (empty($this->code)) {
            return $serialized;
        }

        $mandatoryFields = [
                'code',
                'position',
                'name',
                'gametext',
                'has_errata',
                'image_url',
                'uniqueness'
        ];

        $optionalFields = [
                'characteristics',
                'destiny',
                'episode_1',
                'episode_7',
                'lore'
        ];

        $externalFields = [
                'side',
                'set',
                'type',
                'subtype',
                'rarity'
        ];

        switch ($this->type->getCode()) {
            case 'admirals-order':
              break;
            case 'character':
              $optionalFields[] = 'ability';
              $optionalFields[] = 'armor';
              $optionalFields[] = 'clone_army';
              $optionalFields[] = 'deploy';
              $optionalFields[] = 'force_aptitude';
              $optionalFields[] = 'forfeit';
              $optionalFields[] = 'maneuver';
              $optionalFields[] = 'model_type';
              $optionalFields[] = 'nav_computer';
              $optionalFields[] = 'permanent_weapon';
              $optionalFields[] = 'pilot';
              $optionalFields[] = 'politics';
              $optionalFields[] = 'power';
              $optionalFields[] = 'presence';
              $optionalFields[] = 'republic';
              $optionalFields[] = 'separatist';
              $optionalFields[] = 'warrior';
              break;
            case 'creature':
              $optionalFields[] = 'defense_value';
              $optionalFields[] = 'defense_value_name';
              $optionalFields[] = 'deploy';
              $optionalFields[] = 'ferocity';
              $optionalFields[] = 'forfeit';
              $optionalFields[] = 'model_type';
              $optionalFields[] = 'selective';
              break;
            case 'defensive-shield':
              $optionalFields[] = 'grabber';
              break;
            case 'device':
              break;
            case 'effect':
              $optionalFields[] = 'grabber';
              break;
            case 'epic-event':
              break;
            case 'interrupt':
              break;
            case 'jedi-test':
              break;
            case 'location':
              $optionalFields[] = 'dark_side_icons';
              $optionalFields[] = 'dark_side_text';
              $optionalFields[] = 'light_side_icons';
              $optionalFields[] = 'light_side_text';
              $optionalFields[] = 'mobile';
              $optionalFields[] = 'planet';
              $optionalFields[] = 'scomp_link';
              $optionalFields[] = 'site_creature';
              $optionalFields[] = 'site_exterior';
              $optionalFields[] = 'site_interior';
              $optionalFields[] = 'site_starship';
              $optionalFields[] = 'site_underground';
              $optionalFields[] = 'site_underwater';
              $optionalFields[] = 'site_vehicle';
              $optionalFields[] = 'space';
              $optionalFields[] = 'system_parsec';
              break;
            case 'objective':
              break;
            case 'podracer':
              break;
            case 'starship':
              $optionalFields[] = 'ability';
              $optionalFields[] = 'armor';
              $optionalFields[] = 'clone_army';
              $optionalFields[] = 'deploy';
              $optionalFields[] = 'forfeit';
              $optionalFields[] = 'hyperspeed';
              $optionalFields[] = 'independent';
              $optionalFields[] = 'maneuver';
              $optionalFields[] = 'model_type';
              $optionalFields[] = 'nav_computer';
              $optionalFields[] = 'pilot';
              $optionalFields[] = 'power';
              $optionalFields[] = 'presence';
              $optionalFields[] = 'republic';
              $optionalFields[] = 'scomp_link';
              $optionalFields[] = 'trade_federation';
              break;
            case 'vehicle':
              $optionalFields[] = 'ability';
              $optionalFields[] = 'armor';
              $optionalFields[] = 'deploy';
              $optionalFields[] = 'forfeit';
              $optionalFields[] = 'landspeed';
              $optionalFields[] = 'model_type';
              $optionalFields[] = 'pilot';
              $optionalFields[] = 'power';
              $optionalFields[] = 'presence';
              $optionalFields[] = 'scomp_link';
              break;
            case 'weapon':
              $optionalFields[] = 'deploy';
              $optionalFields[] = 'forfeit';
              break;
        }

        foreach ($optionalFields as $optionalField) {
            $getter = 'get' . $this->snakeToCamel($optionalField);
            $serialized[$optionalField] = $this->$getter();
            if (!isset($serialized[$optionalField]) || $serialized[$optionalField] === '') {
                unset($serialized[$optionalField]);
            }
        }

        foreach ($mandatoryFields as $mandatoryField) {
            $getter = 'get' . $this->snakeToCamel($mandatoryField);
            $serialized[$mandatoryField] = $this->$getter();
        }

        foreach ($externalFields as $externalField) {
            $getter = 'get' . $this->snakeToCamel($externalField);
            $object = $this->$getter();
            if($object)
              $serialized[$externalField.'_code'] = $this->$getter()->getCode();
        }

        ksort($serialized);
        return $serialized;
    }

    public function unserialize($serialized)
    {
        throw new \Exception("unserialize() method unsupported");
    }

    public function toString()
    {
        return $this->name;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
      $typeCode = $this->type->getCode();
      if ($typeCode == "character") {
        return $this->subtype->getCode();
      }
      return $typeCode;
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $ability;

    /**
     * @var string
     */
    private $gametext;

    /**
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @var \DateTime
     */
    private $dateUpdate;

    /**
     * @var integer
     */
    private $armor;

    /**
     * @var integer
     */
    private $darkSideIcons;

    /**
     * @var integer
     */
    private $lightSideIcons;

    /**
     * @var integer
     */
    private $defenseValue;

    /**
     * @var integer
     */
    private $deploy;

    /**
     * @var integer
     */
    private $destiny;

    /**
     * @var integer
     */
    private $ferocity;

    /**
     * @var integer
     */
    private $forfeit;

    /**
     * @var integer
     */
    private $hyperspeed;

    /**
     * @var integer
     */
    private $landspeed;

    /**
     * @var integer
     */
    private $maneuver;

    /**
     * @var integer
     */
    private $politics;

    /**
     * @var integer
     */
    private $power;

    /**
     * @var integer
     */
    private $systemParsec;

    /**
     * @var string
     */
    private $characteristics;

    /**
     * @var string
     */
    private $darkSideText;

    /**
     * @var string
     */
    private $lightSideText;

    /**
     * @var string
     */
    private $defenseValueName;

    /**
     * @var string
     */
    private $modelType;

    /**
     * @var boolean
     */
    private $cloneArmy;

    /**
     * @var boolean
     */
    private $episode1;

    /**
     * @var boolean
     */
    private $episode7;

    /**
     * @var boolean
     */
    private $firstOrder;

    /**
     * @var boolean
     */
    private $grabber;

    /**
     * @var boolean
     */
    private $hasErrata;

    /**
     * @var boolean
     */
    private $independent;

    /**
     * @var boolean
     */
    private $mobile;

    /**
     * @var boolean
     */
    private $navComputer;

    /**
     * @var boolean
     */
    private $permanentWeapon;

    /**
     * @var boolean
     */
    private $pilot;

    /**
     * @var boolean
     */
    private $planet;

    /**
     * @var boolean
     */
    private $presence;

    /**
     * @var boolean
     */
    private $republic;

    /**
     * @var boolean
     */
    private $resistance;

    /**
     * @var boolean
     */
    private $scompLink;

    /**
     * @var boolean
     */
    private $selective;

    /**
     * @var boolean
     */
    private $separatist;

    /**
     * @var boolean
     */
    private $siteCreature;

    /**
     * @var boolean
     */
    private $siteExterior;

    /**
     * @var boolean
     */
    private $siteInterior;

    /**
     * @var boolean
     */
    private $siteStarship;

    /**
     * @var boolean
     */
    private $siteUnderground;

    /**
     * @var boolean
     */
    private $siteUnderwater;

    /**
     * @var boolean
     */
    private $siteVehicle;

    /**
     * @var boolean
     */
    private $space;

    /**
     * @var boolean
     */
    private $tradeFederation;

    /**
     * @var boolean
     */
    private $warrior;

    /**
     * @var string
     */
    private $forceAptitude;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var string
     */
    private $lore;

    /**
     * @var string
     */
    private $uniqueness;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reviews;

    /**
     * @var \AppBundle\Entity\Set
     */
    private $set;

    /**
     * @var \AppBundle\Entity\Type
     */
    private $type;

    /**
     * @var \AppBundle\Entity\Subtype
     */
    private $subtype;

    /**
     * @var \AppBundle\Entity\Side
     */
    private $side;

    /**
     * @var \AppBundle\Entity\Rarity
     */
    private $rarity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reviews = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Card
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Card
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Card
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set ability
     *
     * @param integer $ability
     *
     * @return Card
     */
    public function setAbility($ability)
    {
        $this->ability = $ability;

        return $this;
    }

    /**
     * Get ability
     *
     * @return integer
     */
    public function getAbility()
    {
        return $this->ability;
    }

    /**
     * Set gametext
     *
     * @param string $gametext
     *
     * @return Card
     */
    public function setGametext($gametext)
    {
        $this->gametext = $gametext;

        return $this;
    }

    /**
     * Get gametext
     *
     * @return string
     */
    public function getGametext()
    {
        return $this->gametext;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Card
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Card
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set armor
     *
     * @param integer $armor
     *
     * @return Card
     */
    public function setArmor($armor)
    {
        $this->armor = $armor;

        return $this;
    }

    /**
     * Get armor
     *
     * @return integer
     */
    public function getArmor()
    {
        return $this->armor;
    }

    /**
     * Set darkSideIcons
     *
     * @param integer $darkSideIcons
     *
     * @return Card
     */
    public function setDarkSideIcons($darkSideIcons)
    {
        $this->darkSideIcons = $darkSideIcons;

        return $this;
    }

    /**
     * Get darkSideIcons
     *
     * @return integer
     */
    public function getDarkSideIcons()
    {
        return $this->darkSideIcons;
    }

    /**
     * Set lightSideIcons
     *
     * @param integer $lightSideIcons
     *
     * @return Card
     */
    public function setLightSideIcons($lightSideIcons)
    {
        $this->lightSideIcons = $lightSideIcons;

        return $this;
    }

    /**
     * Get lightSideIcons
     *
     * @return integer
     */
    public function getLightSideIcons()
    {
        return $this->lightSideIcons;
    }

    /**
     * Set defenseValue
     *
     * @param integer $defenseValue
     *
     * @return Card
     */
    public function setDefenseValue($defenseValue)
    {
        $this->defenseValue = $defenseValue;

        return $this;
    }

    /**
     * Get defenseValue
     *
     * @return integer
     */
    public function getDefenseValue()
    {
        return $this->defenseValue;
    }

    /**
     * Set deploy
     *
     * @param integer $deploy
     *
     * @return Card
     */
    public function setDeploy($deploy)
    {
        $this->deploy = $deploy;

        return $this;
    }

    /**
     * Get deploy
     *
     * @return integer
     */
    public function getDeploy()
    {
        return $this->deploy;
    }

    /**
     * Set destiny
     *
     * @param integer $destiny
     *
     * @return Card
     */
    public function setDestiny($destiny)
    {
        $this->destiny = $destiny;

        return $this;
    }

    /**
     * Get destiny
     *
     * @return integer
     */
    public function getDestiny()
    {
        return $this->destiny;
    }

    /**
     * Set ferocity
     *
     * @param integer $ferocity
     *
     * @return Card
     */
    public function setFerocity($ferocity)
    {
        $this->ferocity = $ferocity;

        return $this;
    }

    /**
     * Get ferocity
     *
     * @return integer
     */
    public function getFerocity()
    {
        return $this->ferocity;
    }

    /**
     * Set forfeit
     *
     * @param integer $forfeit
     *
     * @return Card
     */
    public function setForfeit($forfeit)
    {
        $this->forfeit = $forfeit;

        return $this;
    }

    /**
     * Get forfeit
     *
     * @return integer
     */
    public function getForfeit()
    {
        return $this->forfeit;
    }

    /**
     * Set hyperspeed
     *
     * @param integer $hyperspeed
     *
     * @return Card
     */
    public function setHyperspeed($hyperspeed)
    {
        $this->hyperspeed = $hyperspeed;

        return $this;
    }

    /**
     * Get hyperspeed
     *
     * @return integer
     */
    public function getHyperspeed()
    {
        return $this->hyperspeed;
    }

    /**
     * Set landspeed
     *
     * @param integer $landspeed
     *
     * @return Card
     */
    public function setLandspeed($landspeed)
    {
        $this->landspeed = $landspeed;

        return $this;
    }

    /**
     * Get landspeed
     *
     * @return integer
     */
    public function getLandspeed()
    {
        return $this->landspeed;
    }

    /**
     * Set maneuver
     *
     * @param integer $maneuver
     *
     * @return Card
     */
    public function setManeuver($maneuver)
    {
        $this->maneuver = $maneuver;

        return $this;
    }

    /**
     * Get maneuver
     *
     * @return integer
     */
    public function getManeuver()
    {
        return $this->maneuver;
    }

    /**
     * Set politics
     *
     * @param integer $politics
     *
     * @return Card
     */
    public function setPolitics($politics)
    {
        $this->politics = $politics;

        return $this;
    }

    /**
     * Get politics
     *
     * @return integer
     */
    public function getPolitics()
    {
        return $this->politics;
    }

    /**
     * Set power
     *
     * @param integer $power
     *
     * @return Card
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * Get power
     *
     * @return integer
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Set systemParsec
     *
     * @param integer $systemParsec
     *
     * @return Card
     */
    public function setSystemParsec($systemParsec)
    {
        $this->systemParsec = $systemParsec;

        return $this;
    }

    /**
     * Get systemParsec
     *
     * @return integer
     */
    public function getSystemParsec()
    {
        return $this->systemParsec;
    }

    /**
     * Set characteristics
     *
     * @param string $characteristics
     *
     * @return Card
     */
    public function setCharacteristics($characteristics)
    {
        $this->characteristics = $characteristics;

        return $this;
    }

    /**
     * Get characteristics
     *
     * @return string
     */
    public function getCharacteristics()
    {
        return $this->characteristics;
    }

    /**
     * Set darkSideText
     *
     * @param string $darkSideText
     *
     * @return Card
     */
    public function setDarkSideText($darkSideText)
    {
        $this->darkSideText = $darkSideText;

        return $this;
    }

    /**
     * Get darkSideText
     *
     * @return string
     */
    public function getDarkSideText()
    {
        return $this->darkSideText;
    }

    /**
     * Set lightSideText
     *
     * @param string $lightSideText
     *
     * @return Card
     */
    public function setLightSideText($lightSideText)
    {
        $this->lightSideText = $lightSideText;

        return $this;
    }

    /**
     * Get lightSideText
     *
     * @return string
     */
    public function getLightSideText()
    {
        return $this->lightSideText;
    }

    /**
     * Set defenseValueName
     *
     * @param string $defenseValueName
     *
     * @return Card
     */
    public function setDefenseValueName($defenseValueName)
    {
        $this->defenseValueName = $defenseValueName;

        return $this;
    }

    /**
     * Get defenseValueName
     *
     * @return string
     */
    public function getDefenseValueName()
    {
        return $this->defenseValueName;
    }

    /**
     * Set modelType
     *
     * @param string $modelType
     *
     * @return Card
     */
    public function setModelType($modelType)
    {
        $this->modelType = $modelType;

        return $this;
    }

    /**
     * Get modelType
     *
     * @return string
     */
    public function getModelType()
    {
        return $this->modelType;
    }

    /**
     * Set cloneArmy
     *
     * @param boolean $cloneArmy
     *
     * @return Card
     */
    public function setCloneArmy($cloneArmy)
    {
        $this->cloneArmy = $cloneArmy;

        return $this;
    }

    /**
     * Get cloneArmy
     *
     * @return boolean
     */
    public function getCloneArmy()
    {
        return $this->cloneArmy;
    }

    /**
     * Set episode1
     *
     * @param boolean $episode1
     *
     * @return Card
     */
    public function setEpisode1($episode1)
    {
        $this->episode1 = $episode1;

        return $this;
    }

    /**
     * Get episode1
     *
     * @return boolean
     */
    public function getEpisode1()
    {
        return $this->episode1;
    }

    /**
     * Set episode7
     *
     * @param boolean $episode7
     *
     * @return Card
     */
    public function setEpisode7($episode7)
    {
        $this->episode7 = $episode7;

        return $this;
    }

    /**
     * Get episode7
     *
     * @return boolean
     */
    public function getEpisode7()
    {
        return $this->episode7;
    }

    /**
     * Set firstOrder
     *
     * @param boolean $firstOrder
     *
     * @return Card
     */
    public function setFirstOrder($firstOrder)
    {
        $this->firstOrder = $firstOrder;

        return $this;
    }

    /**
     * Get firstOrder
     *
     * @return boolean
     */
    public function getFirstOrder()
    {
        return $this->firstOrder;
    }

    /**
     * Set grabber
     *
     * @param boolean $grabber
     *
     * @return Card
     */
    public function setGrabber($grabber)
    {
        $this->grabber = $grabber;

        return $this;
    }

    /**
     * Get grabber
     *
     * @return boolean
     */
    public function getGrabber()
    {
        return $this->grabber;
    }

    /**
     * Set hasErrata
     *
     * @param boolean $hasErrata
     *
     * @return Card
     */
    public function setHasErrata($hasErrata)
    {
        $this->hasErrata = $hasErrata;

        return $this;
    }

    /**
     * Get hasErrata
     *
     * @return boolean
     */
    public function getHasErrata()
    {
        return $this->hasErrata;
    }

    /**
     * Set independent
     *
     * @param boolean $independent
     *
     * @return Card
     */
    public function setIndependent($independent)
    {
        $this->independent = $independent;

        return $this;
    }

    /**
     * Get independent
     *
     * @return boolean
     */
    public function getIndependent()
    {
        return $this->independent;
    }

    /**
     * Set mobile
     *
     * @param boolean $mobile
     *
     * @return Card
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return boolean
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set navComputer
     *
     * @param boolean $navComputer
     *
     * @return Card
     */
    public function setNavComputer($navComputer)
    {
        $this->navComputer = $navComputer;

        return $this;
    }

    /**
     * Get navComputer
     *
     * @return boolean
     */
    public function getNavComputer()
    {
        return $this->navComputer;
    }

    /**
     * Set permanentWeapon
     *
     * @param boolean $permanentWeapon
     *
     * @return Card
     */
    public function setPermanentWeapon($permanentWeapon)
    {
        $this->permanentWeapon = $permanentWeapon;

        return $this;
    }

    /**
     * Get permanentWeapon
     *
     * @return boolean
     */
    public function getPermanentWeapon()
    {
        return $this->permanentWeapon;
    }

    /**
     * Set pilot
     *
     * @param boolean $pilot
     *
     * @return Card
     */
    public function setPilot($pilot)
    {
        $this->pilot = $pilot;

        return $this;
    }

    /**
     * Get pilot
     *
     * @return boolean
     */
    public function getPilot()
    {
        return $this->pilot;
    }

    /**
     * Set planet
     *
     * @param boolean $planet
     *
     * @return Card
     */
    public function setPlanet($planet)
    {
        $this->planet = $planet;

        return $this;
    }

    /**
     * Get planet
     *
     * @return boolean
     */
    public function getPlanet()
    {
        return $this->planet;
    }

    /**
     * Set presence
     *
     * @param boolean $presence
     *
     * @return Card
     */
    public function setPresence($presence)
    {
        $this->presence = $presence;

        return $this;
    }

    /**
     * Get presence
     *
     * @return boolean
     */
    public function getPresence()
    {
        return $this->presence;
    }

    /**
     * Set republic
     *
     * @param boolean $republic
     *
     * @return Card
     */
    public function setRepublic($republic)
    {
        $this->republic = $republic;

        return $this;
    }

    /**
     * Get republic
     *
     * @return boolean
     */
    public function getRepublic()
    {
        return $this->republic;
    }

    /**
     * Set resistance
     *
     * @param boolean $resistance
     *
     * @return Card
     */
    public function setResistance($resistance)
    {
        $this->resistance = $resistance;

        return $this;
    }

    /**
     * Get resistance
     *
     * @return boolean
     */
    public function getResistance()
    {
        return $this->resistance;
    }

    /**
     * Set scompLink
     *
     * @param boolean $scompLink
     *
     * @return Card
     */
    public function setScompLink($scompLink)
    {
        $this->scompLink = $scompLink;

        return $this;
    }

    /**
     * Get scompLink
     *
     * @return boolean
     */
    public function getScompLink()
    {
        return $this->scompLink;
    }

    /**
     * Set selective
     *
     * @param boolean $selective
     *
     * @return Card
     */
    public function setSelective($selective)
    {
        $this->selective = $selective;

        return $this;
    }

    /**
     * Get selective
     *
     * @return boolean
     */
    public function getSelective()
    {
        return $this->selective;
    }

    /**
     * Set separatist
     *
     * @param boolean $separatist
     *
     * @return Card
     */
    public function setSeparatist($separatist)
    {
        $this->separatist = $separatist;

        return $this;
    }

    /**
     * Get separatist
     *
     * @return boolean
     */
    public function getSeparatist()
    {
        return $this->separatist;
    }

    /**
     * Set siteCreature
     *
     * @param boolean $siteCreature
     *
     * @return Card
     */
    public function setSiteCreature($siteCreature)
    {
        $this->siteCreature = $siteCreature;

        return $this;
    }

    /**
     * Get siteCreature
     *
     * @return boolean
     */
    public function getSiteCreature()
    {
        return $this->siteCreature;
    }

    /**
     * Set siteExterior
     *
     * @param boolean $siteExterior
     *
     * @return Card
     */
    public function setSiteExterior($siteExterior)
    {
        $this->siteExterior = $siteExterior;

        return $this;
    }

    /**
     * Get siteExterior
     *
     * @return boolean
     */
    public function getSiteExterior()
    {
        return $this->siteExterior;
    }

    /**
     * Set siteInterior
     *
     * @param boolean $siteInterior
     *
     * @return Card
     */
    public function setSiteInterior($siteInterior)
    {
        $this->siteInterior = $siteInterior;

        return $this;
    }

    /**
     * Get siteInterior
     *
     * @return boolean
     */
    public function getSiteInterior()
    {
        return $this->siteInterior;
    }

    /**
     * Set siteStarship
     *
     * @param boolean $siteStarship
     *
     * @return Card
     */
    public function setSiteStarship($siteStarship)
    {
        $this->siteStarship = $siteStarship;

        return $this;
    }

    /**
     * Get siteStarship
     *
     * @return boolean
     */
    public function getSiteStarship()
    {
        return $this->siteStarship;
    }

    /**
     * Set siteUnderground
     *
     * @param boolean $siteUnderground
     *
     * @return Card
     */
    public function setSiteUnderground($siteUnderground)
    {
        $this->siteUnderground = $siteUnderground;

        return $this;
    }

    /**
     * Get siteUnderground
     *
     * @return boolean
     */
    public function getSiteUnderground()
    {
        return $this->siteUnderground;
    }

    /**
     * Set siteUnderwater
     *
     * @param boolean $siteUnderwater
     *
     * @return Card
     */
    public function setSiteUnderwater($siteUnderwater)
    {
        $this->siteUnderwater = $siteUnderwater;

        return $this;
    }

    /**
     * Get siteUnderwater
     *
     * @return boolean
     */
    public function getSiteUnderwater()
    {
        return $this->siteUnderwater;
    }

    /**
     * Set siteVehicle
     *
     * @param boolean $siteVehicle
     *
     * @return Card
     */
    public function setSiteVehicle($siteVehicle)
    {
        $this->siteVehicle = $siteVehicle;

        return $this;
    }

    /**
     * Get siteVehicle
     *
     * @return boolean
     */
    public function getSiteVehicle()
    {
        return $this->siteVehicle;
    }

    /**
     * Set space
     *
     * @param boolean $space
     *
     * @return Card
     */
    public function setSpace($space)
    {
        $this->space = $space;

        return $this;
    }

    /**
     * Get space
     *
     * @return boolean
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * Set tradeFederation
     *
     * @param boolean $tradeFederation
     *
     * @return Card
     */
    public function setTradeFederation($tradeFederation)
    {
        $this->tradeFederation = $tradeFederation;

        return $this;
    }

    /**
     * Get tradeFederation
     *
     * @return boolean
     */
    public function getTradeFederation()
    {
        return $this->tradeFederation;
    }

    /**
     * Set warrior
     *
     * @param boolean $warrior
     *
     * @return Card
     */
    public function setWarrior($warrior)
    {
        $this->warrior = $warrior;

        return $this;
    }

    /**
     * Get warrior
     *
     * @return boolean
     */
    public function getWarrior()
    {
        return $this->warrior;
    }

    /**
     * Set forceAptitude
     *
     * @param boolean $forceAptitude
     *
     * @return Card
     */
    public function setForceAptitude($forceAptitude)
    {
        $this->forceAptitude = $forceAptitude;

        return $this;
    }

    /**
     * Get forceAptitude
     *
     * @return boolean
     */
    public function getForceAptitude()
    {
        return $this->forceAptitude;
    }

    /**
     * Set imageUrl
     *
     * @param boolean $imageUrl
     *
     * @return Card
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return boolean
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set lore
     *
     * @param boolean $lore
     *
     * @return Card
     */
    public function setLore($lore)
    {
        $this->lore = $lore;

        return $this;
    }

    /**
     * Get lore
     *
     * @return boolean
     */
    public function getLore()
    {
        return $this->lore;
    }

    /**
     * Set uniqueness
     *
     * @param boolean $uniqueness
     *
     * @return Card
     */
    public function setUniqueness($uniqueness)
    {
        $this->uniqueness = $uniqueness;

        return $this;
    }

    /**
     * Get uniqueness
     *
     * @return boolean
     */
    public function getUniqueness()
    {
        return $this->uniqueness;
    }

    /**
     * Add review
     *
     * @param \AppBundle\Entity\Review $review
     *
     * @return Card
     */
    public function addReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param \AppBundle\Entity\Review $review
     */
    public function removeReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Set set
     *
     * @param \AppBundle\Entity\Set $set
     *
     * @return Card
     */
    public function setSet(\AppBundle\Entity\Set $set = null)
    {
        $this->set = $set;

        return $this;
    }

    /**
     * Get set
     *
     * @return \AppBundle\Entity\Set
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\Type $type
     *
     * @return Card
     */
    public function setType(\AppBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set subtype
     *
     * @param \AppBundle\Entity\Subtype $subtype
     *
     * @return Card
     */
    public function setSubtype(\AppBundle\Entity\Subtype $subtype = null)
    {
        $this->subtype = $subtype;

        return $this;
    }

    /**
     * Get subtype
     *
     * @return \AppBundle\Entity\Subtype
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * Set side
     *
     * @param \AppBundle\Entity\Side $side
     *
     * @return Card
     */
    public function setSide(\AppBundle\Entity\Side $side = null)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * Get side
     *
     * @return \AppBundle\Entity\Side
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * Set rarity
     *
     * @param \AppBundle\Entity\Rarity $rarity
     *
     * @return Card
     */
    public function setRarity(\AppBundle\Entity\Rarity $rarity = null)
    {
        $this->rarity = $rarity;
        return $this;
    }
    /**
     * Get rarity
     *
     * @return \AppBundle\Entity\Rarity
     */
    public function getRarity()
    {
        return $this->rarity;
    }
}
