<?php

namespace Tests\Data\Webdav;

class GalleryResponse
{
    public static function year_title_image(): array
    {
        return                 [
            'name' => '{DAV:}response',
            'value' =>
                [
                    0 =>
                        [
                            'name' => '{DAV:}href',
                            'value' => '/remote.php/dav/files/stephan.koeninger/Bilder/2020/2020.08%20Perseiden/20200811-225920.JPG',
                            'attributes' =>
                                [
                                ],
                        ],
                    1 =>
                        [
                            'name' => '{DAV:}propstat',
                            'value' =>
                                [
                                    0 =>
                                        [
                                            'name' => '{DAV:}prop',
                                            'value' =>
                                                [
                                                    0 =>
                                                        [
                                                            'name' => '{DAV:}displayname',
                                                            'value' => '20200811-225920.JPG',
                                                            'attributes' =>
                                                                [
                                                                ],
                                                        ],
                                                    1 =>
                                                        [
                                                            'name' => '{http://nextcloud.org/ns}system-tags',
                                                            'value' =>
                                                                [
                                                                    0 =>
                                                                        [
                                                                            'name' => '{http://nextcloud.org/ns}system-tag',
                                                                            'value' => 'Galerie',
                                                                            'attributes' =>
                                                                                [
                                                                                    '{http://owncloud.org/ns}can-assign' => 'true',
                                                                                    '{http://owncloud.org/ns}id' => '9',
                                                                                    '{http://owncloud.org/ns}user-assignable' => 'true',
                                                                                    '{http://owncloud.org/ns}user-visible' => 'true',
                                                                                ],
                                                                        ],
                                                                ],
                                                            'attributes' =>
                                                                [
                                                                ],
                                                        ],
                                                ],
                                            'attributes' =>
                                                [
                                                ],
                                        ],
                                    1 =>
                                        [
                                            'name' => '{DAV:}status',
                                            'value' => 'HTTP/1.1 200 OK',
                                            'attributes' =>
                                                [
                                                ],
                                        ],
                                ],
                            'attributes' =>
                                [
                                ],
                        ],
                ],
            'attributes' =>
                [
                ],
        ]
            ;
    }

    public static function year_title_section_image(): array
    {
        return [
            'name' => '{DAV:}response',
            'value' =>
                [
                    0 =>
                        [
                            'name' => '{DAV:}href',
                            'value' => '/remote.php/dav/files/stephan.koeninger/Bilder/2020/2020.08%20Perseiden/01%20-%20Der%20Anfang/20200811-225920.JPG',
                            'attributes' =>
                                [
                                ],
                        ],
                    1 =>
                        [
                            'name' => '{DAV:}propstat',
                            'value' =>
                                [
                                    0 =>
                                        [
                                            'name' => '{DAV:}prop',
                                            'value' =>
                                                [
                                                    0 =>
                                                        [
                                                            'name' => '{DAV:}displayname',
                                                            'value' => '20200811-225920.JPG',
                                                            'attributes' =>
                                                                [
                                                                ],
                                                        ],
                                                    1 =>
                                                        [
                                                            'name' => '{http://nextcloud.org/ns}system-tags',
                                                            'value' =>
                                                                [
                                                                    0 =>
                                                                        [
                                                                            'name' => '{http://nextcloud.org/ns}system-tag',
                                                                            'value' => 'Galerie',
                                                                            'attributes' =>
                                                                                [
                                                                                    '{http://owncloud.org/ns}can-assign' => 'true',
                                                                                    '{http://owncloud.org/ns}id' => '9',
                                                                                    '{http://owncloud.org/ns}user-assignable' => 'true',
                                                                                    '{http://owncloud.org/ns}user-visible' => 'true',
                                                                                ],
                                                                        ],
                                                                ],
                                                            'attributes' =>
                                                                [
                                                                ],
                                                        ],
                                                ],
                                            'attributes' =>
                                                [
                                                ],
                                        ],
                                    1 =>
                                        [
                                            'name' => '{DAV:}status',
                                            'value' => 'HTTP/1.1 200 OK',
                                            'attributes' =>
                                                [
                                                ],
                                        ],
                                ],
                            'attributes' =>
                                [
                                ],
                        ],
                ],
            'attributes' =>
                [
                ],
        ];
    }
}
