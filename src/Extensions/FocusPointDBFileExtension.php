<?php

namespace JonoM\FocusPoint\Extensions;

use JonoM\FocusPoint\FieldType\DBFocusPoint;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\View\ViewableData;

/**
 * @property DBFile|FocusPointDBFileExtension $owner
 */
class FocusPointDBFileExtension extends FocusPointExtension
{

    // silence some errors
    public $FocusPoint;

    /**
     * Get focus point for this image; Prevent failover to backend Image
     *
     * @return DBFocusPoint
     */
    public function getFocusPoint(): ?DBFocusPoint
    {
        if ($this->owner->hasDynamicData('focuspoint_object')) {
            return $this->owner->getDynamicData('focuspoint_object');
        }

        // If this DB file was generated from a source image,
        // let's copy the focus point across. This can happen if
        // using a non-focuspoint resize mechanism.
        /** @var ViewableData|FocusPointExtension $failover */
        $failover = $this->owner->getFailover();

        if ($failover && $failover->hasExtension(FocusPointExtension::class)) {
            $sourceFocus = $failover->FocusPoint;

            // Note: Let Width / Height be lazy loaded, so don't generate here
            $newFocusPoint = DBFocusPoint::create();
            $newFocusPoint->setValue(
                [
                    'X' => $sourceFocus->getX(),
                    'Y' => $sourceFocus->getY(),
                ],
                $this->owner
            );

            // Save this focus point and return
            $this->owner->setFocusPoint($newFocusPoint);

            return $newFocusPoint;
        }

        return null;
    }

    /**
     * Set a new focus point
     *
     * @param DBFocusPoint|null $point
     * @return $this
     */
    public function setFocusPoint(?DBFocusPoint $point): self
    {
        $this->owner->setDynamicData('focuspoint_object', $point);

        return $this;
    }
}
