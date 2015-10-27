# YTX - Content Owner channels library
This library allows you to link and remove channels from your YouTube content owner.

[![Latest Stable Version](https://poser.pugx.org/janyksteenbeek/ytx-cochannels/v/stable)](https://packagist.org/packages/janyksteenbeek/ytx-cochannels)
[![Total Downloads](https://poser.pugx.org/janyksteenbeek/ytx-cochannels/downloads)](https://packagist.org/packages/janyksteenbeek/ytx-cochannels)
[![License](https://poser.pugx.org/janyksteenbeek/ytx-cochannels/license)](https://packagist.org/packages/janyksteenbeek/ytx-cochannels)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8ea976cf-6266-497f-bbac-e134f8f94b1f/mini.png)](https://insight.sensiolabs.com/projects/8ea976cf-6266-497f-bbac-e134f8f94b1f)

## Add channels
    /**
     * Add (a) channel(s) to the content owner
     *
     * @param $channels
     * @param bool $canViewRevenue
     * @param bool $webClaiming
     * @return bool|mixed
     */
    public function addChannel($channels, $canViewRevenue = true, $webClaiming = true)

## Remove channels

    /**
     * Remove (a) channel(s) from the content owner
     * Note: You have to specify a channel ID without UC
     *
     * @param $channels
     * @return bool|mixed
     */
    public function removeChannel($channels)
