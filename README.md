# YTX - Content Owner channels library
This library allows you to link and remove channels from your YouTube content owner.

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
