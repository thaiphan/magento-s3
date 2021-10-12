<?php

class Thai_S3_Helper_S3
{
    /**
     * Determines whether an S3 region code is valid.
     *
     * @param string $regionInQuestion
     * @return bool
     */
    public function isValidRegion($regionInQuestion)
    {
        foreach ($this->getRegions() as $currentRegion) {
            if ($currentRegion['value'] == $regionInQuestion) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        return [
            [
                'value' => 'us-east-1',
                'label' => 'US East (N. Virginia)'
            ],
            [
                'value' => 'us-west-2',
                'label' => 'US West (Oregon)'
            ],
            [
                'value' => 'us-west-1',
                'label' => 'US West (N. California)'
            ],
            [
                'value' => 'eu-west-1',
                'label' => 'EU (Ireland)'
            ],
            [
                'value' => 'eu-central-1',
                'label' => 'EU (Frankfurt)'
            ],
            [
                'value' => 'ap-southeast-1',
                'label' => 'Asia Pacific (Singapore)'
            ],
            [
                'value' => 'ap-northeast-1',
                'label' => 'Asia Pacific (Tokyo)'
            ],
            [
                'value' => 'ap-southeast-2',
                'label' => 'Asia Pacific (Sydney)'
            ],
            [
                'value' => 'ap-northeast-2',
                'label' => 'Asia Pacific (Seoul)'
            ],
            [
                'value' => 'sa-east-1',
                'label' => 'South America (Sao Paulo)'
            ],
            [
                'value' => 'nyc3',
                'label' => 'DigitalOcean Spaces - New York 3'
            ],
            [
                'value' => 'sfo3',
                'label' => 'DigitalOcean Spaces - San Francisco 3'
            ],
            [
                'value' => 'ams3',
                'label' => 'DigitalOcean Spaces - Amsterdam 3'
            ],
            [
                'value' => 'sgp1',
                'label' => 'DigitalOcean Spaces - Singapore 1'
            ],
            [
                'value' => 'fra1',
                'label' => 'DigitalOcean Spaces - Frankfurt 1'
            ]
        ];
    }
}
